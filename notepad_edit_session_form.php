<?php

require_once($CFG->libdir . "/formslib.php");

class notepad_edit_session_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore!
       // $session = $this->_customdata['session'];

        $mform->addElement('text', 'name', get_string('session_name', 'notepad'));
        $mform->addRule('name', 'This field is required', 'required');
        $mform->addElement('textarea', 'directions', get_string('directions', 'notepad'),'wrap="virtual" rows="3" cols="65"');
        $mform->addElement('textarea', 'prompts', get_string('session_prompts', 'notepad'),'wrap="virtual" rows="3" cols="65"');
          
        $this->add_action_buttons();
    }                           
}                               