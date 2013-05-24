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
 * Creates new activities and edits existing ones.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2012 EdTech Leaders Online
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Include the edit form.
require_once(dirname(__FILE__) . '/notepad_edit_activity_form.php');

// Pull the sid and/or aid from the url.
$sid = optional_param('sid', 0, PARAM_INT); // session ID
$aid = optional_param('aid', 0, PARAM_INT); // activity ID
// Get the session from the sid.
$session = $DB->get_record('notepad_sessions', array('id' => $sid));
if (!$session) {
  print_error('That session does not exist!');
}

// Get the notepad activity, course, etc from the problem.
$notepad = $DB->get_record('notepad', array('id' => $session->nid));
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
add_to_log($course->id, 'notepad', 'view', "editactivities.php?sid=$sid", $session->name, $cm->id);


// Only editors can see this page.
require_capability('mod/notepad:edit', $context);

// Set the page header. Needs to happen before the form code in order to stick, but I'm not sure why - CR
$PAGE->set_url('/mod/notepad/editactivities.php', array('sid' => $sid, 'aid' => $aid));
$PAGE->set_title(format_string("Editing Activities."));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('notepad-activity-edit-form');

// Sort CSS styles.
$PAGE->requires->css('/mod/notepad/css/notepad.css');


notepad_set_display_type($notepad);

// All activities for the session.
$activities = $DB->get_records('notepad_activities', array('sid' => $session->id));

$activity = NULL;

// If there's a aid in the url, we're editing an exisitng activity
if ($aid != 0) {
  // Get a activity to load 
  $activity = $DB->get_record('notepad_activities', array('sid' => $sid, 'id' => $aid));
  // If there are no activities, the aid is funky.
  if (!$activity) {
    print_error('Can not find any activities');
  }
  // This helps with the form.  activityname is the form element's name
  $activity->activiyname = $activity->name;
}


// Load the form.
$mform = new notepad_edit_activity_form("/mod/notepad/editactivities.php?sid=$sid&aid=$aid", array('activities' => $activities, 'this_activity' => $activity));

// If the form was cancelled, redirect.
if ($mform->is_cancelled()) {
  redirect("session.php?id=$sid");
}
else {

  
  if ($activity) {
  //Set up the draft area.
  
    // Put the existing data into the form.
  $mform->set_data($activity);
  }
  // If there's data in the form...
  if ($results = $mform->get_data()) {
    
    // If the the data is for a new activity...
    if ($aid == 0) {
      // Save the activity as a new record.
      $activity->sid = $sid;
      $activity->name = $results->name;
      $new_record = $DB->insert_record('notepad_activities', $activity);
    }
    else {
      // We're updaing an existing activity.
      $activity->name = $results->name;
      $updated_record = $DB->update_record('notepad_activities', $activity);
    }
    // Now redirect back to the problem page with the new / updated data.
    redirect("editactivities.php?sid=$sid");
  }
}

// Begin page output
echo $OUTPUT->header();
echo $OUTPUT->heading("Manage Activities for {$session->name}");

echo "<div class='notepad-activity-wrapper'>";

echo "<div class='notepad-activity-pager'>";
echo "<h4>Select an activityto edit,<br /> or click \"Add New\" to create a new activity.</h4>";
echo "<ul>";
foreach ($activities as $activity) {
  $class = ($aid == $activity->id) ? "class=\"notepad-pager-current\"" : ""; 
  echo '<li ' . $class . '><a href="' . $CFG->wwwroot . '/mod/notepad/editactivities.php?sid=' . $activity->sid . '&amp;aid=' . $activity->id . '">' . $activity->name . '</a></li>';
}
$class = (!$aid) ? ' class="notepad-pager-current" ' : "";
echo '<li' . $class . '><a href="' . $CFG->wwwroot . '/mod/notepad/editactivities.php?sid=' . $session->id . '">Add New</a></li>';
echo "</ul>";
echo "</div>";

echo "<div class='notepad-manage-form-wrapper'>";
if ($aid) echo "<p class='notepad-delete-link'><a href='deleteactivity.php?aid=$aid'>Delete this sample</a></p>";
if ($aid) echo "<h4>Editing $activity->name</h4>";
else echo "<h4>Adding a new activity</h4>";

//displays the form
$mform->display();


echo "</div>";
echo "<div class='notepad-action-links'>";
echo '<span class="notepad-back-link-box"><a href="view.php?id=' . $cm->id . '">Back to the notepad</a></span>';
echo "</div>";
echo "</div>";

// Finish the page
echo $OUTPUT->footer();









