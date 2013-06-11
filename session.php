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
$newSave = optional_param('newSave', 0, PARAM_INT);
$ready = optional_param('ready', 0, PARAM_INT);
$message = '';


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
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

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
	
	$questions = $DB->get_records('notepad_questions', array('sid' => $session->id));
	$qids = array_keys($questions);
	
	
	$prev_probe_responses = array();
	$prev_activity_responses = array();
	$prev_question_responses = array();
	
	if ($pids) {
		$prev_probe_responses = $DB->get_records_select('notepad_probe_responses', "uid = $USER->id AND pid IN (" . implode(",",$pids) . ") ");
	} 
	
	if ($aids) {
		$prev_activity_responses = $DB->get_records_select('notepad_activity_responses', "uid = $USER->id AND aid IN (" . implode(",",$aids) . ") ");
	} 

	if ($qids) {
		$prev_question_responses = $DB->get_records_select('notepad_question_responses', "uid = $USER->id AND qid IN (" . implode(",",$qids) . ") ");
	} 

 $mform = new notepad_edit_form("/mod/notepad/session.php?id={$session->id}", array('probes' => $probes, 'activities' => $activities, 'questions' => $questions, 'session' => $session));
    
 if ($responses = $mform->get_data()) {

 	notepad_debug('HELLO');
 	$timenow = time();
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
 
  $form_items = array();
  $form_question = array(); 

  foreach ($responses as $key => $response) {
    
    $exploded_key = explode("-",$key);
    
    $keysize = sizeof($exploded_key);    
    
    if ($keysize == 3) {
  
    	list($table, $field, $item_id) = $exploded_key; 		
    	$form_items[$table][$item_id][$field] = $response;
    	
    }  else if ($keysize == 2) {
    		list($table, $field) = $exploded_key;
    		$form_question[$field] = $response;
    }

   }
   
    	notepad_debug($responses);
    	notepad_debug($form_items);
    	
     foreach ($form_items as $table => $item_ids) {
   	    foreach ($item_ids as $item_id => $fields) {
		
		      $new_response = new stdClass();

		     
		      if ($table == 'probe') { 
		      	$new_response->pid = $item_id;
		      	$new_response->plans = $fields['plans'];
		      	if (array_key_exists("useradio",$fields))  $new_response->useradio = $fields['useradio'];
		      } else if ($table == 'activity') {
		      	$new_response->aid = $item_id;
		      	$new_response->plans = $fields['plans'];
		      	if (array_key_exists("useradio",$fields))  $new_response->useradio = $fields['useradio'];
		      } else {

			    $new_response->uid = $USER->id;
			    $new_response->qid = $item_id;
			    $new_response->response = $fields['response'];
			   
			    if (array_key_exists("submit_session", $form_question)) {
				    $new_response->submit_session = $form_question['submit_session'];      	

				    // check if they had previously submitted the session to facilitors
				    $prev_question_responses_id = array_shift(array_keys($prev_question_responses));
				    $ready_response = $prev_question_responses[$prev_question_responses_id]->submit_session;

				    if (!$ready_response) {
					    // send a message on reload
					    $ready = '&ready=1';
					    }
					}


		       } 
		      $DB->insert_record('notepad_' . $table . '_responses',$new_response);
    	}
    
    }
   

  echo 'Got data';
  redirect("session.php?id=$id&newSave=1$ready");
}

// set existing data.
$form_data = array();


foreach ($prev_question_responses as $response) {
  
	$form_data['question-response-' . $response->qid] = $response->response; 
	// TODO: this should be moved to another table
	$form_data['question-submit_session'] = $response->submit_session;
}



foreach ($prev_probe_responses as $response) {
  
	$form_data['probe-plans-' . $response->pid] = $response->plans; 
	$form_data['probe-useradio-' . $response->pid] = $response->useradio;  

}

foreach ($prev_activity_responses as $response) {
  
	$form_data['activity-plans-' . $response->aid] = $response->plans; 
	$form_data['activity-useradio-' . $response->aid] = $response->useradio;  

}

$mform->set_data($form_data);

 
  // Output starts here
  
echo $OUTPUT->header();
echo $OUTPUT->heading($notepad->name);

$i = 0;
$num_sessions = count($sessions);
echo "<div class='notepad-session-list'>";
echo "<form>";
echo "<select onchange='window.location.href=this.options[this.selectedIndex].value'>";
echo "<option value=''>Go to..</option>";
foreach ($sessions as $session) {
  echo '<option value="'. $CFG->wwwroot . '/mod/notepad/session.php?id=' . $session->id . '">' . $session->name . '</option>';
}
echo '<option value="'. $CFG->wwwroot . '/mod/notepad/print.php?n=' . $notepad->id . '&amp;sid=' . $session->id  . '">Print my notebook</option>';
echo "</select>";
echo "</form>";

echo "</div>";

echo "<div id='directions'><h4>$session->directions</h4></div>";

if ($message) {
	echo "<div class='message'>$message</div>";
}

$mform->display();


   
// Finish the page
echo $OUTPUT->footer();
