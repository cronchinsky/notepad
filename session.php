<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of a problem in the sort module.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Add in the classify form.
require_once(dirname(__FILE__) . '/notepad_edit_form.php');

// Grab the sid from the url
$id = optional_param('id', 0, PARAM_INT); // session ID
$user =  optional_param('u', 0, PARAM_INT);
$newSave = optional_param('newSave', 0, PARAM_INT);
$ready = optional_param('ready', 0, PARAM_INT);
$message = '';

if ($user) {
  $session_user = $user;
} else {
  $session_user = $USER->id;
}

$session_user = ($user ? $user: $USER->id);

// Load the session from the url ID
$session = $DB->get_record('notepad_sessions', array('id' => $id));

// If the session is not found, throw an error
if (!$session) {
  error('That session does not exist!');
} 

if ($newSave) {
	$message = '<h3>Your notebook session has been saved.</h3>';
}

if ($ready) {
	$message .= '<h3>Your notebook session has been submitted to facilitators.</h3>';
}

// Load the notepad activity, course, and cm context from the problem, and up the chain.
$notepad = $DB->get_record('notepad', array('id' => $session->nid));
$sessions = $DB->get_records('notepad_sessions', array('nid' => $notepad->id), 'weight');

$entry = $DB->get_record("notepad_entries", array("uid" => $USER->id, "notepad" => $notepad->id));

$course = $DB->get_record('course', array('id' => $notepad->course));
if ($course->id) {
  $cm = get_coursemodule_from_instance('notepad', $notepad->id, $course->id, false, MUST_EXIST);
}
else {
  error('Could not find the course!');
}

// This is some moodle stuff that seems to be necessary :)
require_login($course, true, $cm);
//$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$context = context_module::instance($cm->id);

// Log this page view.
add_to_log($course->id, 'notepad', 'view', "session.php?id={$cm->id}", $session->name, $cm->id);

/// Print the page header

  $PAGE->set_url('/mod/notepad/session.php', array('id' => $session->id));
  $PAGE->set_title(format_string($session->name));
  $PAGE->set_heading(format_string($course->fullname));
  $PAGE->set_context($context);
  $PAGE->add_body_class('notepad-session-view');
  $PAGE->set_pagelayout('standard');
  notepad_set_display_type($notepad);


// Add the necssary CSS and javascript

  $PAGE->requires->css('/mod/notepad/css/notepad.css');
  $PAGE->requires->js('/mod/notepad/scripts/jquery.min.js');
  $PAGE->requires->js('/mod/notepad/scripts/notepad.js');


	$probes = $DB->get_records('notepad_probes', array('sid' => $session->id));
	$pids = array_keys($probes);
	
	$activities = $DB->get_records('notepad_activities', array('sid' => $session->id));
	$aids = array_keys($activities);
	
	$questions = $DB->get_records('notepad_questions', array('sid' => $session->id), 'weight');
	$qids = array_keys($questions);
	//notepad_debug($questions);
	
	$comparisons = $DB->get_records('notepad_comparisons', array('sid' => $session->id), 'weight');
	$cids = array_keys($comparisons);
	//notepad_debug($comparisons);


	$prev_probe_responses = array();
	$prev_activity_responses = array();
	$prev_question_responses = array();
	$prev_comparison_responses = array();
	
	$directions = $session->directions;
	
	if ($pids) {
		$prev_probe_responses = $DB->get_records_select('notepad_probe_responses', "uid = $USER->id AND pid IN (" . implode(",",$pids) . ") ");
	} 
	
	if ($aids) {
		$prev_activity_responses = $DB->get_records_select('notepad_activity_responses', "uid = $USER->id AND aid IN (" . implode(",",$aids) . ") ");
	} 

	if ($qids) {
		$prev_question_responses = $DB->get_records_select('notepad_question_responses', "uid = $USER->id AND qid IN (" . implode(",",$qids) . ") ");
	} 
	
	if ($cids) {
	  //$cid_string = implode(",",$cids);
	  //$sql = "SELECT * FROM {notepad_comparison_responses} WHERE uid = ? AND cid IN (?) order by field(weight, ?)";
	  //$prev_comparison_responses = $DB->get_records_sql($sql, array($USER->id, $cid_string, $cid_string));
		$prev_comparison_responses = $DB->get_records_select('notepad_comparison_responses', "uid = $USER->id AND cid IN (" . implode(",",$cids) . ")");
	} 


  if ($session->wysiwyg) {
    $maxfiles = 99;             // TODO: add some setting
    $maxbytes = $CFG->maxbytes; // TODO: add some settin
    $definitionoptions = array('trusttext'=>true, 'subdirs'=>false, 'maxfiles'=>$maxfiles, 'maxbytes'=>$maxbytes, 'context'=>$context);
    if ($session_wysiwyg = $DB->get_record('notepad_wysiwyg', array('sid' => $session->id, 'uid' => $USER->id))) {
	  	$session_wysiwyg = file_prepare_standard_editor($session_wysiwyg, 'textfield', $definitionoptions, $context, 'mod_notepad', 'notepad', $session_wysiwyg->id);
      // store the updated value values
      $DB->update_record('notepad_wysiwyg', $session_wysiwyg);

      //refetch complete entry
      $session_wysiwyg = $DB->get_record('notepad_wysiwyg', array('sid' => $session->id, 'uid' => $USER->id));
    } else {
        $session_wysiwyg->uid = $USER->id;
        $session_wysiwyg->sid = $session->id;
        $session_wysiwyg->textfield = '';
        $session_wysiwyg->textfieldtrust = 0;
        $session_wysiwyg->textfieldformat = FORMAT_HTML;
				$session_wysiwyg->id = $DB->insert_record('notepad_wysiwyg', $session_wysiwyg);
    }
  }
	$mform = new notepad_edit_form("/mod/notepad/session.php?id={$session->id}", array('probes' => $probes, 'activities' => $activities, 'questions' => $questions, 'comparisons' => $comparisons, 'session' => $session, 'context' => $context));

	if ($responses = $mform->get_data()) {
    //notepad_debug($responses);
    if (empty($responses->id) && ($session->wysiwyg)) {
        $responses->id            = $session_wysiwyg->id;
    }
    $responses->textfield        = '';          // updated later
    $responses->textfieldformat  = FORMAT_HTML; // updated later
    $responses->textfieldtrust   = 0;           // updated later
    $responses->sid	             = $session->id;
    $responses->uid				 			 = $USER->id;

   
    $timenow = time();

    $newentry = new stdClass();

    $newentry->modified = $timenow;
 
 	if ($entry) {
        $newentry->id = $entry->id;
        if (!$DB->update_record("notepad_entries", $newentry)) {
            print_error("Could not update your notepad");
        }
        $logaction = "update entry";
        
    } else {
        $newentry->uid = $USER->id;
        $newentry->notepad = $notepad->id;
        if (!$newentry->id = $DB->insert_record("notepad_entries", $newentry)) {
            print_error("Could not insert a new notepad entry");
        }
        $logaction = "add entry";
    } 
 
  
  
  if ($pids) {
  	$DB->delete_records_select('notepad_probe_responses',"pid IN (" . implode(",",$pids) . ") AND uid = $USER->id");
  }
  
  if ($aids) {
  	$DB->delete_records_select('notepad_activity_responses',"aid IN (" . implode(",",$aids) . ") AND uid = $USER->id");
  }
  
  if ($qids) {
  	$DB->delete_records_select('notepad_question_responses',"qid IN (" . implode(",",$qids) . ") AND uid = $USER->id");
  }
  
  if ($cids) {
  	$DB->delete_records_select('notepad_comparison_responses',"cid IN (" . implode(",",$cids) . ") AND uid = $USER->id");
  }
 
  $form_items = array();
  $form_question = array(); 

  
  foreach ($responses as $key => $response) {
       
    $exploded_key = explode("-",$key);
    
    $keysize = sizeof($exploded_key);  
    //notepad_debug($key);
    //notepad_debug($keysize);  
    
    if ($keysize == 3) { 
    	list($table, $field, $item_id) = $exploded_key; 		
    	$form_items[$table][$item_id][$field] = $response;
    	
    }  else if ($keysize == 2) {
    		list($table, $field) = $exploded_key;
    		$form_question[$field] = $response;
    }

   }
   
     
     //notepad_debug($responses);
     //notepad_debug($form_items);
     //break;
     $ready = '&ready=0';
     foreach ($form_items as $table => $item_ids) {
   	    foreach ($item_ids as $item_id => $fields) {
		      //notepad_debug($table);
		      $new_response = new stdClass();

		      $new_response->uid = $USER->id;
		      if ($table == 'probe') { 
		        //notepad_debug($item_id);
		        //notepad_debug($fields);
		      	$new_response->pid = $item_id;
		      	$new_response->plans = $fields['plans'];
		      	if (array_key_exists("useradio",$fields))  $new_response->useradio = $fields['useradio'];
		      } else if ($table == 'activity') {
		      	$new_response->aid = $item_id;
		      	$new_response->plans = $fields['plans'];
		      	if (array_key_exists("useradio",$fields))  $new_response->useradio = $fields['useradio'];
					} else if ($table == 'comparison') {
		      	$new_response->cid = $item_id;
		      	$new_response->responsea = $fields['responsea'];
		      	$new_response->responseb = $fields['responseb'];
		      } else {			    
			    	$new_response->qid = $item_id;
						$new_response->response = $fields['response'];
			   
			    if (array_key_exists("submit_session", $form_question)) {
				    $new_response->submit_session = $form_question['submit_session'];      	

				    // check if they had previously submitted the session to facilitors
				    $key_arr = array_keys($prev_question_responses);
				    $prev_question_responses_id = array_shift($key_arr);
				    $ready_response = $prev_question_responses[$prev_question_responses_id]->submit_session;

				    if (!$ready_response) {
					    // send a message on reload
					    $ready = '&ready=1';
					    }
						}
		      } 
		      //notepad_debug($new_response);
		      //notepad_debug($table);
		      $DB->insert_record('notepad_' . $table . '_responses',$new_response);
    	}
    
    }
    
    if ($session->wysiwyg) {
      // save and relink embedded images and save attachments
      $responses = file_postupdate_standard_editor($responses, 'textfield', $definitionoptions, $context, 'mod_notepad', 'notepad', $responses->id);
      // store the updated value values
      $DB->update_record('notepad_wysiwyg', $responses);
    }
   
  redirect("session.php?id=$id&newSave=1$ready");
}

// set existing data.
$form_data = array();


foreach ($prev_question_responses as $response) {
  
	$form_data['question-response-' . $response->qid] = $response->response; 
	// TODO: this should be moved to another table
	$form_data['question-submit_session'] = $response->submit_session;
}


foreach ($prev_comparison_responses as $response) {
  
	$form_data['comparison-responsea-' . $response->cid] = $response->responsea; 
	$form_data['comparison-responseb-' . $response->cid] = $response->responseb; 

}



foreach ($prev_probe_responses as $response) {
  
	$form_data['probe-plans-' . $response->pid] = $response->plans; 
	$form_data['probe-useradio-' . $response->pid] = $response->useradio;  

}

foreach ($prev_activity_responses as $response) {
  
	$form_data['activity-plans-' . $response->aid] = $response->plans; 
	$form_data['activity-useradio-' . $response->aid] = $response->useradio;  

}

if ($session->wysiwyg) {
  $draftid_editor = file_get_submitted_draft_itemid('textfield_editor');
  $currenttext = file_prepare_draft_area($draftid_editor,$context->id,'mod_notepad','notepad', $session_wysiwyg->id, array('subdirs'=>true),$session_wysiwyg->textfield);
  $form_data['textfield_editor'] = array('text'=>$currenttext,'format'=>$session_wysiwyg->textfieldformat,'itemid'=>$draftid_editor);
}
//notepad_debug($form_data);

$mform->set_data($form_data);

 
  // Output starts here
  
echo $OUTPUT->header();
echo $OUTPUT->heading($notepad->name);

/*
$i = 0;
$num_sessions = count($sessions);
*/
echo '<h1>' . $session_user . '</h1>';
if (has_capability('mod/notepad:edit', $context)) {
  $all_users = get_users_by_capability($context, 'mod/notepad:addentries', '', '', '', '', $groups);
	$users = $all_users;
  echo "<div class='notepad-user-list'><form>";
  echo "<select onchange='window.location.href=this.options[this.selectedIndex].value'>";
		
	echo "<option value=''>Show user..</option>";
	foreach ($users as $user_id => $user) {
		echo '<option value="'. $CFG->wwwroot . '/mod/notepad/session.php?id=' . $id . '&amp;u=' . $user->id . '">' . $user->firstname . ' ' . $user->lastname . '</option>';
	}
	echo "</select>";
	echo "</form></div>";
}

echo "<div class='notepad-session-list'>";
echo "<form>";
echo "<select onchange='window.location.href=this.options[this.selectedIndex].value'>";
echo "<option value=''>Go to..</option>";
foreach ($sessions as $notepad_session) {
  echo '<option value="'. $CFG->wwwroot . '/mod/notepad/session.php?id=' . $notepad_session->id . '">' . $notepad_session->name . '</option>';
}
echo '<option value="'. $CFG->wwwroot . '/mod/notepad/print.php?n=' . $notepad->id . '&amp;sid=' . $session->id  . '">Print my notebook</option>';
echo "</select>";
echo "</form>";

echo "</div>";


echo "<div id='directions'><h4>$directions</h4></div>";

if ($message) {
	echo "<div class='message'>$message</div>";
}

$mform->display();


   
// Finish the page
echo $OUTPUT->footer();
