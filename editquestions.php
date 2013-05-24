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
 * Creates new questions and edits existing ones.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2012 EdTech Leaders Online
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

// Include the edit form.
require_once(dirname(__FILE__) . '/notepad_edit_question_form.php');

// Pull the sid and/or qid from the url.
$sid = optional_param('sid', 0, PARAM_INT); // session ID
$qid = optional_param('qid', 0, PARAM_INT); // question ID
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
add_to_log($course->id, 'notepad', 'view', "editquestions.php?sid=$sid", $session->name, $cm->id);


// Only editors can see this page.
require_capability('mod/notepad:edit', $context);

// Set the page header. Needs to happen before the form code in order to stick, but I'm not sure why - CR
$PAGE->set_url('/mod/notepad/editquestions.php', array('sid' => $sid, 'qid' => $qid));
$PAGE->set_title(format_string("Editing questions."));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('notepad-question-edit-form');

// Sort CSS styles.
$PAGE->requires->css('/mod/notepad/css/notepad.css');

notepad_set_display_type($notepad);

// All question for the session.
$questions = $DB->get_records('notepad_questions', array('sid' => $session->id));

$question = NULL;

// If there's a qid in the url, we're editing an exisitng question
if ($qid != 0) {
  // Get a question to load 
  $question = $DB->get_record('notepad_questions', array('id' => $qid));
  // If there are no questions, the qid is funky.
  if (!$question) {
    print_error('Can not find any questions');
  }
  // This helps with the form.  questionname is the form element's name
  $question->questionname = $question->question;
  $questionname = $question->question;
}


// Load the form.
$mform = new notepad_edit_question_form("/mod/notepad/editquestions.php?sid=$sid&qid=$qid", array('questions' => $questions, 'this_question' => $question));

// If the form was cancelled, redirect.
if ($mform->is_cancelled()) {
  redirect("session.php?id=$sid");
}
else {

  
  if ($question) {
  //Set up the draft area.
  
    // Put the existing data into the form.
  $mform->set_data($question);
  }
  // If there's data in the form...
  if ($results = $mform->get_data()) {
    
    // If the the data is for a new question...
    if ($qid == 0) {
      // Save the question as a new record.
      $question->sid = $sid;
      $question->question = $results->question;
      $new_record = $DB->insert_record('notepad_questions', $question);
    }
    else {
      // We're updaing existing work.
      $question->question = $results->question;
      $updated_record = $DB->update_record('notepad_questions', $question);
    }
    // Now redirect back to the problem page with the new / updated data.
    redirect("editquestions.php?sid=$sid");
  }
}

// Begin page output
echo $OUTPUT->header();
echo $OUTPUT->heading("Manage questions for {$session->name}");

echo "<div class='notepad-question-wrapper'>";

echo "<div class='notepad-question-pager'>";
echo "<h4>Select a question to edit,<br /> or click \"Add New\" to create a new question.</h4>";
echo "<ul>";
foreach ($questions as $question) {
  $class = ($qid == $question->id) ? "class=\"notepad-pager-current\"" : ""; 
  echo '<li ' . $class . '><a href="' . $CFG->wwwroot . '/mod/notepad/editquestions.php?sid=' . $question->sid . '&amp;qid=' . $question->id . '">' . $question->question . '</a></li>';
}
$class = (!$qid) ? ' class="notepad-pager-current" ' : "";
echo '<li' . $class . '><a href="' . $CFG->wwwroot . '/mod/notepad/editquestions.php?sid=' . $session->id . '">Add New</a></li>';
echo "</ul>";
echo "</div>";

echo "<div class='notepad-manage-form-wrapper'>";
if ($qid) echo "<p class='notepad-delete-link'><a href='deletequestion.php?qid=$qid'>Delete this question [not implemented yet]</a></p>";
if ($qid) echo "<h4>Editing $questionname</h4>";
else echo "<h4>Adding a new question</h4>";

//displays the form
$mform->display();


echo "</div>";
echo "<div class='notepad-action-links'>";
echo '<span class="notepad-back-link-box"><a href="view.php?id=' . $cm->id . '">Back to the notepad</a></span>';
echo "</div>";
echo "</div>";

// Finish the page
echo $OUTPUT->footer();









