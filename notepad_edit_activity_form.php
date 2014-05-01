
<?php

require_once($CFG->libdir . "/formslib.php");

class notepad_edit_activity_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 
        
        //$activities = $this->_customdata['activities'];
        //$this_activity = $this->_customdata['this_activity'];
        

        $mform->addElement('text', 'name', 'Name');
        $mform->addRule('name', 'This field is required', 'required');
        
        $this->add_action_buttons(false);    
    }                           
}                               
