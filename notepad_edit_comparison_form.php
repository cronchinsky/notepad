
<?php

require_once($CFG->libdir . "/formslib.php");

class notepad_edit_comparison_form extends moodleform {
 
    function definition() {
        global $CFG;
 
        $mform =& $this->_form; // Don't forget the underscore! 
        
        //$probes = $this->_customdata['probes'];
        //$this_probe = $this->_customdata['this_probe'];
        

        $mform->addElement('textarea', 'question', 'Question', 'wrap="virtual" rows="5" cols="100"');
        $mform->addRule('question', 'This field is required', 'required');
        $mform->addElement('text', 'label_a', 'Label A', 'size="30"');
        $mform->setType('label_a',  PARAM_NOTAGS); 
        $mform->addRule('label_a', 'This field is required', 'required');
        $mform->addElement('text', 'label_b', 'Label B', 'size="30"');
        $mform->setType('label_b',  PARAM_NOTAGS); 
        $mform->addRule('label_b', 'This field is required', 'required');


        $range = range(-20,20);
        $options = array_combine($range,$range);
        
        $mform->addElement('select','weight','Weight',$options);
        $mform->setDefault('weight', 0);        //Default value


        $this->add_action_buttons(false);    
    }                           
}                               
