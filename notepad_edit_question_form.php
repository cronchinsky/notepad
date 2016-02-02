
<?php

require_once($CFG->libdir . "/formslib.php");

class notepad_edit_question_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 
        
        //$probes = $this->_customdata['probes'];
        //$this_probe = $this->_customdata['this_probe'];
        

        $mform->addElement('textarea', 'question', 'Question', 'wrap="virtual" rows="5" cols="100"');
        $mform->addRule('question', 'This field is required', 'required');
        
        $range = range(-20,20);
        $options = array_combine($range,$range);
        
        $mform->addElement('select','weight','Weight',$options);
        $mform->setDefault('weight', 0);        //Default value

        
        $this->add_action_buttons(false);    
    }                           
}                               
