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
 * Prints a particular instance of notepad
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace notepad with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // notepad instance ID - it should be named as the first character of the module
//$sid = optional_param('sid', 0, PARAM_INT); // the session where the notepad is being printed from
$s = optional_param('s', 0, PARAM_INT); // the session to print

if ($id) {
    $cm         = get_coursemodule_from_id('notepad', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $notepad  = $DB->get_record('notepad', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $notepad  = $DB->get_record('notepad', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $notepad->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('notepad', $notepad->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}



require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);


add_to_log($course->id, 'notepad', 'view', "view.php?id={$cm->id}", $notepad->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/notepad/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($notepad->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->css('/mod/notepad/css/notepad.css');

notepad_set_display_type($notepad);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('notepad-'.$somevar);

// Output starts here
echo $OUTPUT->header();

$nid = $notepad->id;

$sort = 'weight';
$sessions = $DB->get_records('notepad_sessions', array('nid' => $nid), $sort);

$i = 0;
$num_sessions = count($sessions);
echo "<div class='notepad-session-list'>";
echo "<form>";
echo "<select onchange='window.location.href=this.options[this.selectedIndex].value'>";
echo "<option value=''>Go to..</option>";
foreach ($sessions as $session) {
  echo '<option value="'. $CFG->wwwroot . '/mod/notepad/session.php?id=' . $session->id . '">' . $session->name . '</option>';
}
echo '<option value="'. $CFG->wwwroot . '/mod/notepad/print.php?n=' . $notepad->id . '">Print my notebook</option>';
echo "</select>";
echo "</form>";

echo "</div>";

echo "<div class='notepad-session-list'>";
echo "<form>";
echo "<select onchange='window.location.href=this.options[this.selectedIndex].value'>";
echo "<option value=''>Show session..</option>";
foreach ($sessions as $notepad_session) {
	echo '<option value="'. $CFG->wwwroot . '/mod/notepad/print.php?n=' . $notepad->id . '&amp;s=' . $notepad_session->id . '">' . $notepad_session->name . '</option>';
}
echo '<option value="'. $CFG->wwwroot . '/mod/notepad/print.php?n=' . $notepad->id  . '">All sessions</option>';
echo "</select>";
echo "</form>";

echo "</div>";

echo "<h2>$notepad->name</h2>";

if ($sessions) {
  if ($s) {
    // we are just printing one session
	$session = $DB->get_records('notepad_sessions', array('nid' => $nid, 'id' => $s));
	notepad_print($notepad, $session, $USER);
  } else {
  	//echo $OUTPUT->heading($notepad->name);
  	notepad_print($notepad, $sessions, $USER); 
  }
}
else {
  echo $OUTPUT->heading('There are no sessions in this notepad.');
}

// Finish the page
echo $OUTPUT->footer();
