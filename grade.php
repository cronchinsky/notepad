<?php

require_once("../../config.php");

$id   = required_param('id', PARAM_INT);          // Course module ID

$PAGE->set_url('/mod/notepad/grade.php', array('id'=>$id));
if (! $cm = get_coursemodule_from_id('notepad', $id)) {
    print_error('invalidcoursemodule');
}

if (! $notepad = $DB->get_record("notepad", array("id"=>$cm->instance))) {
    print_error('invalidid', 'notepad');
}

if (! $course = $DB->get_record("course", array("id"=>$notepad->course))) {
    print_error('coursemisconf', 'notepad');
}

require_login($course, false, $cm);

if (has_capability('mod/notepad:grade', context_module::instance($cm->id))) {
    redirect('report.php?id='.$cm->id);
} else {
    redirect('view.php?id='.$cm->id);
}