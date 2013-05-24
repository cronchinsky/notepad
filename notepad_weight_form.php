<?php



require_once($CFG->libdir . "/formslib.php");

class notepad_weight_form extends moodleform {
 
    function definition() {
         global $CFG;
        
        $mform =& $this->_form; // Don't forget the underscore! 
        $probes = $this->_customdata['probes'];
        
        
        $range = range(-50,50);
        $options = array_combine($range,$range);
        
        if (!empty($probes)) {
          $index = 1;
          foreach ($probes as $probe) {
            notepad_add_probe_to_form($probe,$mform, $index);
            $mform->addElement('html',"<div class='annotate-question-edit-actions'><a href='editprobes.php?deleteid=$probe->id&aid=$probe->aid'>Delete</a> | <a href='editquestion.php?qid=$probe->id&aid=$probe->aid'>Edit</a>");
            $mform->setDefault("weight-$probe>id",$probe->weight);
            $mform->addElement('select',"weight-$probe->id",'Weight',$options);
            $mform->addElement('html',"</div>");
            $index++;
          }
          $this->add_action_buttons(false, "Update Weights");
        }
    }                           
}                               
