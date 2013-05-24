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
 * Creates new probes and edits existing ones.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2012 EdTech Leaders Online
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Include the edit form.
require_once(dirname(__FILE__) . '/notepad_edit_session_form.php');

// Pull the sid and/or nid from the url.
$sid = optional_param('sid', 0, PARAM_INT); // session ID
$nid = optional_param('nid', 0, PARAM_INT); // notepad ID
// Get the session from the sid.
$session = $DB->get_record('notepad_sessions', array('id' => $sid));
if (!$session) {
  print_error('That session does not exist!');
}

// Get the notepad activity, course, etc from the problem.
$notepad = $DB->get_record('notepad', array('id' => $nid));
$course = $DB->get_record('course', array('id' => $notepad->course));
if ($course->id) {
  $cm = get_coursemodule_from_instance('notepad', $notepad->id, $course->id, false, MUST_EXIST);
}
else {
  error('Could not find the course / notepad activity!');
}

// Moodley goodness.
require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
add_to_log($course->id, 'notepad', 'view', "editsession.php?sid=$sid&nid=$nid", $session->name, $cm->id);


// Only editors can see this page.
require_capability('mod/notepad:edit', $context);

// Set the page header. Needs to happen before the form code in order to stick, but I'm not sure why - CR
$PAGE->set_url('/mod/notepad/editsession.php', array('sid' => $sid));
$PAGE->set_title(format_string("Editing Session $session->name"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('notepad-session-edit-form');

// Sort CSS styles.
$PAGE->requires->css('/mod/notepad/css/notepad.css');

notepad_set_display_type($notepad);

// Load the form.
$mform = new notepad_edit_session_form("/mod/notepad/editsession.php?sid=$sid&nid=$nid", array('session' => $session));

// If the form was cancelled, redirect.
if ($mform->is_cancelled()) {
  redirect("view.php?id=$nid");
}
else {
  $mform->set_data($session);
 
  // If there's data in the form...
  if ($results = $mform->get_data()) {
      $session->name = $results->name;
      $session->prompts = $results->prompts;
      $session->directions = $results->directions;
      $updated_record = $DB->update_record('notepad_sessions', $session);
      
      redirect("view.php?n=$nid");
    } 
}
// Begin page output
echo $OUTPUT->header();
echo $OUTPUT->heading("Editing {$session->name}");

//displays the form
$mform->display();


echo "<div class='notepad-action-links'>";
echo '<span class="notepad-back-link-box"><a href="view.php?id=' . $cm->id . '">Back to the notepad</a></span>';
echo "</div>";

// Finish the page
echo $OUTPUT->footer();









