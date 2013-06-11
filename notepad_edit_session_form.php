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
        
        $range = range(-50,50);
        $options = array_combine($range,$range);
        
        $mform->addElement('select','weight','Weight',$options);
        $mform->setDefault('weight', 0);        //Default value
          
        $this->add_action_buttons(false);
    }                           
}                               