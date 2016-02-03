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
 * Creates new pieces of student work and edits existing ones.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2012 EdTech Leaders Online
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Pull the parameters
$sid = optional_param('sid', 0, PARAM_INT); // session id
$qid = optional_param('qid', 0, PARAM_INT); // question id
$confirm = optional_param('confirm', 0, PARAM_INT); // 

// Get the problem from the pid
$question = $DB->get_record('notepad_comparisons', array('id' => $qid));
if (!$question) {
  print_error('That question does not exist.  It cannot be deleted');
}

$session = $DB->get_record('notepad_sessions', array('id' => $sid));
$notepad = $DB->get_record('notepad', array('id' => $session->nid));
$course = $DB->get_record('course', array('id' => $notepad->course));
if ($course->id) {
  $cm = get_coursemodule_from_instance('notepad', $notepad->id, $course->id, false, MUST_EXIST);
}
else {
  error('Could not find the notepad activity!');
}

// Moodley goodness.
require_login($course, true, $cm);
//$context = get_context_instance(CONTEXT_MODULE, $cm->id);
$context = context_module::instance($cm->id);
add_to_log($course->id, 'notepad', 'view', "deletequestion.php?qid=$qid", "Deleting question", $cm->id);


// Only editors can see this page.
require_capability('mod/notepad:edit', $context);


if ($confirm && $qid) {
  $responses = $DB->get_records('notepad_comparison_responses',array('cid' => $qid));
  if ($responses) {
    $DB->delete_records_list('notepad_comparison_responses', 'id', array_keys($responses));
  }
  $DB->delete_records('notepad_comparisons', array('id' => $qid));
  
  redirect("editcomparisons.php?sid=$sid");
}

// Set the page header.
$PAGE->set_url('/mod/notepad/deletecomparison.php', array('qid' => $qid, 'sid' => $sid));
$PAGE->set_title(format_string("Delete Comparison Question."));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('notepad-delete-session');

// Sort CSS styles.
$PAGE->requires->css('/mod/notepad/css/notepad.css');

notepad_set_display_type($notepad);

// Begin page output
echo $OUTPUT->header();



echo $OUTPUT->confirm("Are you sure you want to delete this comparison question?  Any responses will be lost.","deletecomparison.php?qid=$qid&sid=$sid&confirm=1","view.php?n=$notepad->id");

echo $OUTPUT->footer();










