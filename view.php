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

$sessions = $DB->get_records('notepad_sessions', array('nid' => $nid), 'id');

if ($sessions) {
  echo $OUTPUT->heading($notepad->name);
  echo "<div class='notepad-session-wrapper'>";
  if ($notepad->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('notepad', $notepad, $cm->id), 'generalbox mod_introbox', 'notepadintro');
  }
  echo "<div class='notepad-session-main'>";
  echo "<h4>Select a session:</h4>";
  echo "<ul>";
  $row = true;
  foreach ($sessions as $session) {
     (($c = !$c) ?  $row_class= "odd" : $row_class="even");
     if (has_capability('mod/notepad:edit', $context)) {
      echo '<li class="'. $row_class. '"><span class="session-name"><a href="' . $CFG->wwwroot . '/mod/notepad/session.php?id=' . $session->id . '">' . $session->name . '</a></span>';
      echo " &nbsp;&nbsp;<a href='editquestions.php?sid=$session->id'> questions <img src='" . $OUTPUT->pix_url('t/edit') . "' alt='edit' /></a>";
      echo " &nbsp;&nbsp;<a href='editprobes.php?sid=$session->id'> probes <img src='" . $OUTPUT->pix_url('t/edit') . "' alt='edit' /></a>";
      echo " &nbsp;&nbsp;<a href='editactivities.php?sid=$session->id'> activities <img src='" . $OUTPUT->pix_url('t/edit') . "' alt='edit' /></a>";
      echo " &nbsp;&nbsp;<a href='editsession.php?sid=$session->id&nid=$nid'> session <img src='" . $OUTPUT->pix_url('t/edit') . "' alt='edit' /></a>";
      echo " &nbsp;&nbsp;<a href='deletesession.php?sid=$session->id'> delete <img src='" . $OUTPUT->pix_url('t/delete') . "' alt='delete' /></a>";
    } else {
    	echo '<li class="'.$row_class. '"><span class="session"><a href="' . $CFG->wwwroot . '/mod/notepad/session.php?id=' . $session->id . '">' . $session->name . '</a></span>';
    }
    
    echo '</li>';
  }
  echo "</ul>";
  echo "</div>";
 
  echo "</div>";
}
else {
  echo $OUTPUT->heading('There are no sessions in this notepad.');
}

echo "<div class='notepad-action-links'>";
	if (has_capability('mod/notepad:edit', $context)) echo "<span class='notepad-add-session-link-box'><a href='" . $CFG->wwwroot . '/mod/notepad/newsession.php?nid=' . $nid . "'>Add a new session</a></span>";
echo "</div>";
// Finish the page
echo $OUTPUT->footer();
