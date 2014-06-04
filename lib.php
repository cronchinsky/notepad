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
 * Library of interface functions and constants for module notepad
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the notepad specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** example constant */
//define('notepad_ULTIMATE_ANSWER', 42);

////////////////////////////////////////////////////////////////////////////////
// Moodle core API                                                            //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function notepad_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        case FEATURE_GRADE_HAS_GRADE:   return true;
        default:                        return null;
    }
}

/**
 * Saves a new instance of the notepad into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $notepad An object from the form in mod_form.php
 * @param mod_notepad_mod_form $mform
 * @return int The id of the newly inserted notepad record
 */
function notepad_add_instance(stdClass $notepad, mod_notepad_mod_form $mform = null) {
    global $DB;

    $notepad->timecreated = time();
    $cmid        = $notepad->coursemodule;
    $draftitemid = $notepad->notepad['itemid'];

    
    $returnid = $DB->insert_record('notepad', $notepad);
    $notepad->id = $returnid;

    # You may have to add extra stuff in here #
    notepad_grade_item_update($notepad);

    return $returnid;
}

/**
 * Updates an instance of the notepad in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $notepad An object from the form in mod_form.php
 * @param mod_notepad_mod_form $mform
 * @return boolean Success/Fail
 */
function notepad_update_instance(stdClass $notepad, mod_notepad_mod_form $mform = null) {
    global $DB;

    $notepad->timemodified = time();
    $notepad->id = $notepad->instance;

    # You may have to add extra stuff in here #
    notepad_grade_item_update($notepad);
    
    $DB->update_record('notepad', $notepad);

    return $DB->update_record('notepad', $notepad);
    //return true;
}

/**
 * Removes an instance of the notepad from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function notepad_delete_instance($id) {
    global $DB;

    if (! $notepad = $DB->get_record('notepad', array('id' => $id))) {
        return false;
    }

    # TODO: Delete any dependent records here # 
    //$DB->delete_records('notepad_question_instances', array('quiz' => $quiz->id));
    //$DB->delete_records('quiz_feedback', array('quizid' => $quiz->id));

    notepad_grade_item_delete($notepad);

    $DB->delete_records('notepad', array('id' => $notepad->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function notepad_user_outline($course, $user, $mod, $notepad) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $notepad the module instance record
 * @return void, is supposed to echp directly
 */
function notepad_user_complete($course, $user, $mod, $notepad) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in notepad activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function notepad_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link notepad_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function notepad_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see notepad_get_recent_mod_activity()}

 * @return void
 */
function notepad_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function notepad_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function notepad_get_extra_capabilities() {
    return array();
}

////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Is a given scale used by the instance of notepad?
 *
 * This function returns if a scale is being used by one notepad
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $notepadid ID of an instance of this module
 * @return bool true if the scale is used by the given notepad instance
 */
function notepad_scale_used($notepadid, $scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('notepad', array('id' => $notepadid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of notepad.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any notepad instance
 */
function notepad_scale_used_anywhere($scaleid) {
    global $DB;

    /** @example */
    if ($scaleid and $DB->record_exists('notepad', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give notepad instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $notepad instance object with extra cmidnumber and modname property
 * @return void
 */
function notepad_grade_item_update(stdClass $notepad) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    $item = array();
    $item['itemname'] = clean_param($notepad->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $notepad->grade;
    $item['grademin']  = 0;

    return grade_update('mod/notepad', $notepad->course, 'mod', 'notepad', $notepad->id, 0, null, $item);
}

/**
 * Update notepad grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $notepad instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function notepad_update_grades(stdClass $notepad, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /** @example */
    
    $grades = notepad_get_user_grades($notepad, $userid);
	 // populate array of grade objects indexed by userid

    grade_update('mod/notepad', $notepad->course, 'mod', 'notepad', $notepad->id, 0, $grades);
}


/**
 * Get notepad grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $notepad instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function notepad_get_user_grades(stdClass $notepad, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

	$grading_info = grade_get_grades($courseid, 'mod', $notepad, $notepad->id, array_keys($users));
 
	$grade_item_grademax = $grading_info->items[0]->grademax;
	
	foreach ($users as $user) {
    	$user_final_grade[$user->id] = $grading_info->items[0]->grades[$user->id];
	}
	
	return $user_final_grade;

 }

/**
 * Delete grade item for given notepad
 *
 * @global stdClass
 * @param object $quiz object
 * @return object quiz
 */
function notepad_grade_item_delete($notepad) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/notepad', $notepad->course, 'mod', 'notepad', $notepad->id, 0, NULL, array('deleted' => 1));
}

function notepad_get_users_done($notepad, $currentgroup) {
    global $DB;

    
    $sql = "SELECT u.* FROM {notepad_entries} n 
            JOIN {user} u ON n.uid = u.id ";
    
    // Group users
    if ($currentgroup != 0) {
        $sql.= "JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = '$currentgroup'";
    }
    
    $sql.= " WHERE n.notepad = '$notepad->id' ORDER BY n.modified DESC";
    $notepads = $DB->get_records_sql($sql);

    $cm = notepad_get_coursemodule($notepad->id);
    if (!$notepads || !$cm) {
        return NULL;
    }

    // remove unenrolled participants
    foreach ($notepads as $key => $user) {
        
        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        
        $canadd = has_capability('mod/notepad:addentries', $context, $user);
        $entriesmanager = has_capability('mod/notepad:edit', $context, $user);
        
        if (!$entriesmanager and !$canadd) {
            unset($notepads[$key]);
        } 
    }
    
    return $notepads;
}

function notepad_print_user_entry($course, $user, $entry, $teachers, $grades) {
    
    global $USER, $OUTPUT, $DB, $CFG;
    
    require_once($CFG->dirroot.'/lib/gradelib.php');

    echo "\n<table border=\"1\" cellspacing=\"0\" valign=\"top\" cellpadding=\"10\">";
        
    echo "\n<tr>";
    echo "\n<td rowspan=\"2\" width=\"35\" valign=\"top\">";
    echo $OUTPUT->user_picture($user, array('courseid' => $course->id));
    echo "</td>";
    echo "<td nowrap=\"nowrap\" width=\"100%\"><h3><a class=\"toggleLink\">".fullname($user);
    if ($entry) {
        //echo "&nbsp;&nbsp;<font size=\"1\">".get_string("lastedited").": ".userdate($entry->modified)."</font>";
    }
    echo "</a></h3></td>";
    echo "</tr>";

    echo "\n<tr><td width=\"100%\">";
    if ($entry) {
        //echo format_text($entry->text, $entry->format);
    } else {
        echo "No notepad entry";    }
    echo "</td></tr>";

    if ($entry) {
       
        echo "\n<tr  class=\"toggle\">";
        echo "<td width=\"35\" valign=\"top\">";
/*
        if (!$entry->teacher) {
            $entry->teacher = $USER->id;
        }
        if (empty($teachers[$entry->teacher])) {
            $teachers[$entry->teacher] = $DB->get_record('user', array('id' => $entry->teacher));
        }
        echo $OUTPUT->user_picture($teachers[$entry->teacher], array('courseid' => $course->id));
*/
        echo "</td>";
        echo "<td>Entered:";
        
        
        $attrs = array();
        $hiddengradestr = '';
        $gradebookgradestr = '';
        //$feedbackdisabledstr = '';
       // $feedbacktext = $entry->entrycomment;
        
        // If the grade was modified from the gradebook disable edition
        $grading_info = grade_get_grades($course->id, 'mod', 'notepad', $entry->notepad, array($user->id));
        if ($gradingdisabled = $grading_info->items[0]->grades[$user->id]->locked || $grading_info->items[0]->grades[$user->id]->overridden) {
            $attrs['disabled'] = 'disabled';
          //  $hiddengradestr = '<input type="hidden" name="r'.$entry->id.'" value="'.$entry->rating.'"/>';
            $gradebooklink = '<a href="'.$CFG->wwwroot.'/grade/report/grader/index.php?id='.$course->id.'">';
            $gradebooklink.= $grading_info->items[0]->grades[$user->id]->str_long_grade.'</a>';
            $gradebookgradestr = '<br/>'.get_string("gradeingradebook", "notepad").':&nbsp;'.$gradebooklink;
            
           // $feedbackdisabledstr = 'disabled="disabled"';
           //$feedbacktext = $grading_info->items[0]->grades[$user->id]->str_feedback;
        }
        
        // Grade selector
        echo html_writer::select($grades, 'r'.$entry->id, get_string("nograde").'...', $attrs);
        echo $hiddengradestr;
        //if ($entry->timemarked) {
           // echo "&nbsp;&nbsp;<font size=\"1\">".userdate($entry->timemarked)."</font>";
        //}
        echo $gradebookgradestr;
        
        // Feedback text
       /*
 echo "<br /><textarea name=\"c$entry->id\" rows=\"12\" cols=\"60\" wrap=\"virtual\" $feedbackdisabledstr>";
        p($feedbacktext);
        echo "</textarea><br />";
        
        if ($feedbackdisabledstr != '') {
            echo '<input type="hidden" name="c'.$entry->id.'" value="'.$feedbacktext.'"/>';
        }
*/
        //$nid = $notepad->id;

		$notepad  = $DB->get_record('notepad', array('id' => $entry->notepad), '*', MUST_EXIST);
		$sessions = $DB->get_records('notepad_sessions', array('nid' => $notepad->id));
		if ($sessions) {
			notepad_print($notepad, $sessions, $user);
		}
        echo "</td></tr>";
    }
    echo "</table><br clear=\"all\" />\n";
    
}

function notepad_print($notepad, $sessions,$user)  {
  
  global  $DB, $CFG, $OUTPUT;
  //require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
  //require_once(dirname(__FILE__).'/lib.php');

  echo "<div class='notepad-session-wrapper'>";
  if (true) { // Conditions to show the intro can change to look for own settings or whatever
    $course = $DB->get_record('course', array('id' => $notepad->course));
    if ($course->id) {
	    $cm = get_coursemodule_from_instance('notepad', $notepad->id, $course->id, false, MUST_EXIST);
	    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
	    }
	else {
		error('Could not find the course!');
	}
    echo $OUTPUT->box(format_module_intro('notepad', $notepad, $cm->id), 'generalbox mod_introbox', 'notepadintro');
  }
  echo "<div class='notepad-print'>";
  foreach ($sessions as $session) {
        
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
		$prev_probe_responses = $DB->get_records_select('notepad_probe_responses', "uid = $user->id AND pid IN (" . implode(",",$pids) . ") ");
	} 
	
	if ($aids) {
		$prev_activity_responses = $DB->get_records_select('notepad_activity_responses', "uid = $user->id AND aid IN (" . implode(",",$aids) . ") ");
	} 
	
	if ($qids)  {
		$prev_question_responses = $DB->get_records_select('notepad_question_responses', "uid = $user->id AND qid IN (" . implode(",",$qids) . ") ");
	}
	
	if ($prev_probe_responses or $prev_activity_responses or $prev_question_responses) {
	 echo '<div class="session"><h3><a class="sessiontoggleLink" href="#">' . $session->name . '</a></h3>';
    } else {
     echo '<div class="session"><h3>' . $session->name . '</a></h3>';
    }
    
    echo '<div class="toggle sessiontoggle">';
	echo "<ol>";
	
    foreach ($questions as $question) {
       foreach ($prev_question_responses as $response) {
    		if ($response->qid == $question->id)  {
    			echo "<li><p>" . $question->question . "</p>";
    			echo "<p class='question-response'>" . $response->response . "</p></li>";
    		}
       }
    }
	
	if ($prev_probe_responses) {
    	echo "<li>Ideas for using the probes:";  
    	echo "<table class='print'>";
    	echo "<tr><th>Probe</th><th>Use</th><th>Plans</th></tr>";
    	foreach ($prev_probe_responses as $response) {
    		
    		echo "<tr>";
    		echo "<td class='name'>" . $probes[$response->pid]->name . "</td>";
    		echo "<td class='use'>$response->useradio</td>";
    		echo "<td class='plans'>$response->plans</td>";
    		echo "</tr>";
    	}
    	echo "</table>";   	
    }
    echo "</li>";
    
        
    if ($prev_activity_responses) {
    	echo "<li>Ideas for using the activities:";
    	echo "<table class='print'>";
    	echo "<tr><th>Activity</th><th>Use</th><th>Plans</th></tr>";
    	foreach ($prev_activity_responses as $response) {
    		echo "<tr>";
    		echo "<td class='name'>" . $activities[$response->aid]->name . "</td>";
    		echo "<td class='use'>$response->useradio</td>";
    		echo "<td class='plans'>$response->plans</td>";
    		echo "</tr>";
    	}
    	echo "</table>";   	
    }
    echo "</li>";
    
  	echo "</ol>";
  	
    // get the reflection field for the session
    $reflections = file_rewrite_pluginfile_urls($session->textfield, 'pluginfile.php', $context->id, 'mod_notepad', 'notepad', $session->id);
    echo "<div class='reflections'>" . $reflections . "</div>";

  	echo "</div>";
  	echo "</div>";
  }
  
  echo "</div>";
 
  echo "</div>";

}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function notepad_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for notepad file areas
 *
 * @package mod_notepad
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function notepad_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the notepad file areas
 *
 * @package mod_notepad
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the notepad's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function notepad_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
       
    }
    require_login($course, true, $cm);
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_notepad/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
		send_file_not_found();
    }
    // finally send the file
    send_stored_file($file, 0, 0, false); // download MUST be forced - security
   
}

////////////////////////////////////////////////////////////////////////////////
// Navigation API                                                             //
////////////////////////////////////////////////////////////////////////////////

/**
 * Extends the global navigation tree by adding notepad nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the notepad module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function notepad_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the notepad settings
 *
 * This function is called when the context for the page is a notepad module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $notepadnode {@link navigation_node}
 */
function notepad_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $notepadnode=null) {
}


////////////////////////////////////////////////////////////////////////////////
// Custom Functions                                                           //
////////////////////////////////////////////////////////////////////////////////

function notepad_add_to_form($item, &$mform, $index, $type) {
  
  $mform->addElement('html',"<tr class='$type'>");
  
  $mform->addElement('html',"<td class='$type-name'>$item->name</td>");

  $mform->addElement('html',"<td class='$type-usage'>");
  $usage=array();
  $usage[] = &MoodleQuickForm::createElement('radio', "$type-useradio-$item->id", '', get_string('yes'), 'y');
  $usage[] = &MoodleQuickForm::createElement('radio', "$type-useradio-$item->id", '', get_string('no'), 'n');
  $usage[] = &MoodleQuickForm::createElement('radio', "$type-useradio-$item->id", '', '?', 'm');
  $mform->addGroup($usage, "$type-useradio-$item->id", '', array(' '), false);
  $mform->addElement('html','</td>');

  $mform->addElement('html',"<td class='$type-plan'>");
  $mform->addElement('textarea', "$type-plans-$item->id", '', 'wrap="virtual" rows="3" cols="50"', array('class'=> 'plans'));
  $mform->addElement('html','</td');
  
  $mform->addElement('html','</tr>');
  
}

function notepad_add_question_to_form($item, &$mform, $index, $type) {
  
  $mform->addElement('html',"<li>");
  
  $mform->addElement('html',"$item->question");

  $mform->addElement('textarea', "$type-response-$item->id", '', 'wrap="virtual" rows="3" cols="100"', array('class'=> 'question'));
  
  $mform->addElement('html','</li>');
  
}

/**
 * Returns the notepad instance course_module id
 * 
 * @param integer $notepadid
 * @return object 
 */
function notepad_get_coursemodule($notepadid) {

    global $DB;
    
    return $DB->get_record_sql("SELECT cm.id FROM {course_modules} cm 
                                JOIN {modules} m ON m.id = cm.module
                                WHERE cm.instance = '$notepadid' AND m.name = 'notepad'");
}


/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function notepad_get_coursemodule_info($coursemodule) {
    global $CFG;
    global $DB;
    require_once("$CFG->libdir/resourcelib.php");

 if (!$notepad = $DB->get_record('notepad', array('id'=>$coursemodule->instance))) {    
    return NULL;
 }
    
    $info = new stdClass();
    $info->name = $notepad->name;
	
    if ($notepad->display != RESOURCELIB_DISPLAY_POPUP) {
        return $info;
    }
    
    $fullurl = "$CFG->wwwroot/mod/notepad/view.php?id=$coursemodule->id&amp;inpopup=1";
    $width  = empty($notepad->popupwidth)  ? 620 : $notepad->popupwidth;
    $height = empty($notepad->popupheight) ? 450 : $notepad->popupheight;
    $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
    $info->extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";
    return $info;
}


function notepad_set_display_type($notepad) {
  global $CFG;
  require_once("$CFG->libdir/resourcelib.php");
  
  switch ($notepad->display) {
    case RESOURCELIB_DISPLAY_EMBED:
      break;
    default:
      global $PAGE;
      $PAGE->set_pagelayout('popup');
      break;
  }
} 

/**
 * Debugger. If you don't see the output, break;
 */
function notepad_debug($variables) {
  
  echo "<pre>" . var_export($variables,TRUE) . "</pre>";
} 
