
<?php

require_once($CFG->libdir . "/formslib.php");

class notepad_edit_form extends moodleform {
 
    function definition() {
         global $CFG;
        
        $mform =& $this->_form; // Don't forget the underscore! 
        $probes = $this->_customdata['probes'];
        $activities = $this->_customdata['activities'];
        $questions = $this->_customdata['questions'];
        
        $context = $this->_customdata['context'];

        $session = $this->_customdata['session'];

          $mform->addElement('header',"session-notepad",$session->name);
          $mform->addElement('html', "<div id='notepad-img'></div>");
          $mform->addElement('html', '<ol class="notepad">');
          //notepad_debug($questions);
          if ($questions)  {
	          $index = 1;
	          foreach ($questions as $question)  {
		        notepad_add_question_to_form($question,$mform, $index, 'question');
	            $index++;		          
	          }	          
          }	  
		  
		  if ($probes)  {
			  $mform->addElement('html', '<li><p>Check any <strong>formative assessment probes</strong> that you would like to try with students.</p>');
			  
	
			  $mform->addElement('html','<table class="probes">');
	  		  $mform->addElement('html',"<tr><th>Probes</th>");
	  		  $mform->addElement('html',"<th>Would you use this probe?</th>");
	  		  $mform->addElement('html',"<th>Optional: Write Plans for Using the Probe</th>");
	  		  $mform->addElement('html','</tr>');
	  		    
	       
	          $index = 1;
	          foreach ($probes as $probe) {
	            notepad_add_to_form($probe,$mform, $index, 'probe');
	            $index++;
	          }
	          
	          $mform->addElement('html','</table>');
	          $mform->addElement('html', '</li>');
	      }
          
          if ($activities)  {
	          $mform->addElement('html', '<li><p>Check the <strong>activities/instructional practices</strong> that you would like to try with students.</p>');
	          $mform->addElement('html','<table class="activities">');
	  		  $mform->addElement('html',"<tr><th> Activities</th>");
	  		  $mform->addElement('html',"<th>Would you use this activity?</th>");
	  		  $mform->addElement('html',"<th>Optional: Write Plans for Using the Activity</th>");
	  		  $mform->addElement('html','</tr>');
	          
	           $index = 1;
	           foreach ($activities as $activity) {
	            notepad_add_to_form($activity, $mform, $index, 'activity');
	            $index++;
	          }	
	          
	          $mform->addElement('html','</table>');          
	          $mform->addElement('html', '</li>');
          }
		  	
		  $mform->addElement('html','</ul>'); 
		            
          $mform->addElement('html', '</ol>');
          
          if ($session->wysiwyg) {
            $maxfiles = 99;             // TODO: add some setting
		    $maxbytes = $CFG->maxbytes; // TODO: add some setting
		    $mform->addElement('html','<div class="wysiwyg">');
            $mform->addElement('editor','textfield_editor', '' ,null,array('maxfiles'=> EDITOR_UNLIMITED_FILES, 'maxbytes' => $maxbytes));	
            $mform->addElement('html','</div>');	  
          }
          
          $mform->addElement('checkbox', 'question-submit_session','Ready for facilitators');
         
          $this->add_action_buttons(false, "Save");
          /* $buttonarray=array();
		  $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		  $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
		  $buttonarray[] = &$mform->createElement('cancel');
		  $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		  $mform->closeHeaderBefore('buttonar');
		  */
          $this->set_data($session);  
    }   
                         
}                               
