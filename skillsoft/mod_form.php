<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
$PAGE->requires->js('/mod/skillsoft/skillsoft.js');
$PAGE->requires->js('/mod/skillsoft/md5.js');

class mod_skillsoft_mod_form extends moodleform_mod {

	function definition() {
		global $form, $CFG;

		$mform = $this->_form;
	
		//-------------------------------------------------------------------------------
		// Adding the "general" fieldset, where all the common settings are showed

		$mform->addElement('header', 'general', get_string('general', 'form'));

        if (empty($this->_cm)) {
			// Asset ID
			$mform->addElement('text', 'assetid', get_string('skillsoft_assetid','skillsoft'));
			$mform->setType('assetid', PARAM_TEXT);
    		$mform->addRule('assetid', null, 'required', null, 'client');
			$mform->addRule('assetid', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
			$mform->addHelpButton('assetid', 'skillsoft_assetid', 'skillsoft');
        } else {
        	$mform->addElement('hidden', 'assetid', NULL, array('id'=>'id_assetid'));
        }

		//Button to get data from OLSA
		//pass assetid to page
		$courseid=$this->current->course;
        
		$assetid="'+document.getElementById('id_assetid').value+'";
		$url = '/mod/skillsoft/preloader.php?assetid='.$assetid.'&id='.$courseid;
        $options = 'menubar=0,location=0,scrollbars,resizable,width=600,height=200';


         if (empty($this->_cm)) {
			$buttonattributes = array(
				'title'=>get_string('skillsoft_retrievemetadata', 'skillsoft'),
				'onclick'=>"return openpopup(false,{url:'$url',options:'$options'});",
			);
			$mform->addElement('button', 'getolsa', get_string('skillsoft_retrievemetadata', 'skillsoft'), $buttonattributes);
         } else {
			$buttonattributes = array(
				'title'=>get_string('skillsoft_updatemetadata', 'skillsoft'),
				'onclick'=>"return openpopup(false,{url:'$url',options:'$options'});",
			);
			$mform->addElement('button', 'getolsa', get_string('skillsoft_updatemetadata', 'skillsoft'), $buttonattributes);
         }
		$mform->addHelpButton('getolsa', 'skillsoft_retrievemetadata', 'skillsoft');
		
		// Name
		$mform->addElement('text', 'name', get_string('skillsoft_name','skillsoft'), array('size' => '75'));
		if (!empty($CFG->formatstringstriptags)) {
			$mform->setType('name', PARAM_TEXT);
		} else {
			$mform->setType('name', PARAM_CLEAN);
		}
		$mform->addRule('name', null, 'required', null, 'client');
		$mform->addHelpButton('name', 'skillsoft_name', 'skillsoft');

		$this->add_intro_editor(true, get_string('skillsoft_summary', 'skillsoft'));
		
		// Summary
		$mform->addHelpButton('introeditor', 'skillsoft_summary', 'skillsoft');

		// Audience
		$mform->addElement('htmleditor', 'audience', get_string('skillsoft_audience','skillsoft'), array('rows'=>'15', 'cols'=>'80'));
		$mform->setType('audience', PARAM_RAW);
		$mform->addHelpButton('audience', 'skillsoft_audience', 'skillsoft');

		// Pre-Requisites
		$mform->addElement('htmleditor', 'prereq', get_string('skillsoft_prereq','skillsoft'),array('rows'=>'15', 'cols'=>'80'));
		$mform->setType('prereq', PARAM_RAW);
		$mform->addHelpButton('prereq', 'skillsoft_prereq', 'skillsoft');

		// Duration
		$mform->addElement('text', 'duration', get_string('skillsoft_duration','skillsoft'));
		$mform->setType('duration', PARAM_INT);
		$mform->addHelpButton('duration', 'skillsoft_duration', 'skillsoft');

		// Asset Type
		$mform->addElement('hidden', 'assettype', null);
		$mform->setType('assettype', PARAM_TEXT);

		// Launch URL


	    if (isset($form->add)) {
			$mform->addElement('text', 'launch', get_string('skillsoft_launch','skillsoft'), array('size' => '75'));
			$mform->setType('launch', PARAM_TEXT);
			$mform->addRule('launch', null, 'required', null, 'client');
			$mform->addRule('launch', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
			//$mform->setHelpButton('launch',array('launch', get_string('skillsoft_launch', 'skillsoft'), 'skillsoft'));
			$mform->addHelpButton('launch', 'skillsoft_launch', 'skillsoft');
	    } else {
        	$mform->addElement('hidden', 'launch', NULL, array('id'=>'id_launch'));
        	$mform->setType('launch', PARAM_TEXT);
        }



		//Mastery
		//Set a NULL as first
		$mastery[''] = "No Mastery Score";
		for ($i=1; $i<=100; $i++) {
			$mastery[$i] = "$i";
		}
		$mform->addElement('select', 'mastery', get_string('skillsoft_mastery','skillsoft'), $mastery);
		$mform->setType('mastery', PARAM_INT);
		$mform->setDefault('mastery', '');
		//$mform->setHelpButton('mastery',array('mastery', get_string('skillsoft_mastery', 'skillsoft'), 'skillsoft'));
		$mform->addHelpButton('mastery', 'skillsoft_mastery', 'skillsoft');

		//2014051301
		$mform->addElement('text', 'aiccwindowsettings', get_string('skillsoft_aiccwindowsettingsform','skillsoft'), array('size' => '100'));
		$mform->setType('aiccwindowsettings', PARAM_TEXT);
		$mform->setDefault('aiccwindowsettings', $CFG->skillsoft_aiccwindowsettings);
		$mform->addHelpButton('aiccwindowsettings', 'skillsoft_aiccwindowsettingsform', 'skillsoft');
		
		
		//Time modified
		$mform->addElement('hidden', 'timemodified');
		$mform->setType('timemodified', PARAM_INT);
		
		$mform->addElement('hidden', 'timecreated');
		$mform->setType('timecreated', PARAM_INT);
		
		$mform->addElement('hidden', 'completable');
		$mform->setType('completable', PARAM_BOOL);
		
		//-------------------------------------------------------------------------------
		//-------------------------------------------------------------------------------
		$features = new stdClass;
		$features->groups = false;
		$features->groupings = true;
		$features->groupmembersonly = true;
		$this->standard_coursemodule_elements($features);

		//-------------------------------------------------------------------------------
		// add standard buttons, common to all modules
		$this->add_action_buttons();
	}


}