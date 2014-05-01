<?php

// This script uses installed report plugins to print notepad reports

    require_once('../../config.php');
    require_once($CFG->dirroot.'/mod/notepad/locallib.php');
    //require_once($CFG->dirroot.'/mod/notepad/report/reportlib.php');

    $id = optional_param('id',0,PARAM_INT);    // Course Module ID, or
    $n = optional_param('n',0,PARAM_INT);     // notepad ID

    $mode = optional_param('mode', '', PARAM_ALPHA);        // Report mode

    if ($id) {
        if (! $cm = get_coursemodule_from_id('notepad', $id)) {
            print_error('invalidcoursemodule');
        }

        if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }

        if (! $notepad = $DB->get_record('notepad', array('id' => $cm->instance))) {
            print_error('invalidcoursemodule');
        }

    } else {
        if (! $notepad = $DB->get_record('notepad', array('id' => $n))) {
            print_error('invalidnotepadid', 'notepad');
        }
        if (! $course = $DB->get_record('course', array('id' => $notepad->course))) {
            print_error('invalidcourseid');
        }
        if (! $cm = get_coursemodule_from_instance("notepad", $notepad->id, $course->id)) {
            print_error('invalidcoursemodule');
        }
    }
	
	$url = new moodle_url('/mod/notepad/report.php', array('id' => $cm->id));
    if ($mode !== '') {
        $url->param('mode', $mode);
    }
    $PAGE->set_url($url);

    require_login($course, false, $cm);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    $PAGE->set_pagelayout('report');
    $PAGE->requires->css('/mod/notepad/css/notepad.css');
    $PAGE->requires->js('/mod/notepad/scripts/jquery.min.js');
    $PAGE->requires->js('/mod/notepad/scripts/notepad.js');
    
    add_to_log($course->id, "notepad", "report", "report.php?id=$cm->id", "$notepad->id", "$cm->id");

	echo $OUTPUT->header();
	echo $OUTPUT->heading('notepad grade report');	
	
	// make some easy ways to access the entries.
	if ( $notepad_entries = $DB->get_records("notepad_entries", array("notepad" => $notepad->id))) {
	    foreach ($notepad_entries as $entry) {
	        $entrybyuser[$entry->uid] = $entry;
	        $entrybyentry[$entry->id]  = $entry;
	    }
	
	} else {
	    $entrybyuser  = array () ;
	    $entrybyentry = array () ;
	}
	
	// Group mode
	$groupmode = groups_get_activity_groupmode($cm);
	$currentgroup = groups_get_activity_group($cm, true);
	

	add_to_log($course->id, "notepad", "view responses", "report.php?id=$cm->id", "$notepad->id", $cm->id);
	
	/// Print out the notepad entries
	

	if ($currentgroup) {
	    $groups = $currentgroup;
	} else {
	    $groups = '';
	}

	$users = get_users_by_capability($context, 'mod/notepad:addentries', '', '', '', '', $groups);
	
	if (!$users) {
		echo $OUTPUT->heading(get_string("nousersyet"));
	
	} else {
	    
	    groups_print_activity_menu($cm, $CFG->wwwroot . "/mod/notepad/report.php?id=$cm->id");
	
	    $grades = make_grades_menu($notepad->grade);
	    if (!$teachers = get_users_by_capability($context, 'mod/notepad:edit')) {
	        print_error('noentriesmanagers', 'journal');
	    }
	
	    $allowedtograde = (groups_get_activity_groupmode($cm) != VISIBLEGROUPS OR groups_is_member($currentgroup));
	
	    /*
if ($allowedtograde) {
	        echo '<form action="report.php" method="post">';
	    }
*/
	    echo '<div id="toggleall"><a class="alltoggleLink" href="#">Show All</a></div>';
	    if ($usersdone = notepad_get_users_done($notepad, $currentgroup)) {
	        foreach ($usersdone as $user) {
	            notepad_print_user_entry($course, $user, $entrybyuser[$user->id], $teachers, $grades);
	            unset($users[$user->id]);
	        }
	    }
	
	    foreach ($users as $user) {       // Remaining users
	        notepad_print_user_entry($course, $user, NULL, $teachers, $grades);
	    }
/*
if ($allowedtograde) {
	        echo "<center>";
	        echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\" />";
	        echo "<input type=\"submit\" value=\"".get_string("saveallfeedback", "notepad")."\" />";
	        echo "</center>";
	        echo "</form>";
	    }
*/
	}
		

/// Print footer

    echo $OUTPUT->footer();


