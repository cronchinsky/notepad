
<?php

require_once($CFG->libdir . "/formslib.php");

class notepad_edit_form extends moodleform {
 
    function definition() {
       global $CFG;
      
      $mform =& $this->_form; // Don't forget the underscore! 
      $probes = $this->_customdata['probes'];
      $activities = $this->_customdata['activities'];
      $questions = $this->_customdata['questions'];
      $comparisons = $this->_customdata['comparisons'];
      
      $context = $this->_customdata['context'];

      $session = $this->_customdata['session'];

      $mform->addElement('header',"session-notepad",$session->name);
      $mform->addElement('html', "<div id='notepad-img'></div>");
      $mform->addElement('html', '<ol class="notepad">');
      //notepad_debug($questions);
      //notepad_debug($comparisons);
      
      // combine questions and comparison questions 
      // and sort by weight
      $addtoform = array();            
      if ($questions)  {       
        foreach ($questions as  $key =>$question)  {          
	      	$addtoform[$question->weight]['question'][$key] = $question;	         
				}	        
      }	       
      if ($comparisons)  {
        foreach ($comparisons as $key => $comparison)  {
	      	$addtoform[$comparison->weight]['comparison'][$key] = $comparison;         
				}        
      }	       
      ksort($addtoform);
      // notepad_debug($addtoform);
			// add sorted questions and comparisons to form
      $q_index = 1;
      $c_index = 1;
      foreach ($addtoform as $items) {
	      foreach ($items as $type => $item) {
	        foreach ($item as $key => $value) {
			      if ($type == 'question') {
				      notepad_add_question_to_form($value,$mform, $q_index, 'question');
							$q_index++;	
			      }
			      if ($type == 'comparison') {
				      notepad_add_comparison_to_form($value,$mform, $c_index, 'comparison');
							$c_index++;	
			      }
		      }
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
	  	
		  	
		  if ($session->wysiwyg) {
        $maxfiles = 99;             // TODO: add some setting
		    $maxbytes = $CFG->maxbytes; // TODO: add some setting
		    $mform->addElement('html','<li><div class="wysiwyg">');
		    $mform->addElement('html', '<p class="wysiwyg-prompt">');
		    $mform->addElement('html',"$session->wysiwyg_prompt");
        $mform->addElement('editor','textfield_editor', '' ,null,array('maxfiles'=> EDITOR_UNLIMITED_FILES, 'maxbytes' => $maxbytes));	
        $mform->addElement('html', '</p>');
        $mform->addElement('html','</div></li>');	
        
        // Disable the field if adding comments is checked.
        // This should work, but it is a bug: https://tracker.moodle.org/browse/MDL-29701
        $mform->disabledIf("textfield_editor", 'notepad-addingcomments', 'checked');
  
      }
	
			$mform->addElement('html','</ul>'); 
	            
      $mform->addElement('html', '</ol>');
        
      if ($questions) {         
      	$mform->addElement('checkbox', 'question-submit_session','Ready for facilitators');
      	$mform->disabledIf("question-submit_session", 'notepad-addingcomments', 'checked');
      }
       
      $this->add_action_buttons(false, "Save");
      
      // only put the comments field for admins/teachers/facilitators
      if (has_capability('mod/notepad:edit', $context)) {
        $mform->addElement('html','<div id="notepad-comment">');
        $mform->addElement('textarea', "comments", get_string('comments', 'notepad'), 'wrap="virtual" rows="3" cols="100"', array('class'=> 'notepad-commments'));
        $mform->addElement('html','</div>');	
        $mform->addElement('checkbox', 'notepad-addingcomments','Adding comments');
        $mform->setDefault('notepad-addingcomments', true);
      }
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
                           
