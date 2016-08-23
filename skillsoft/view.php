<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');


//TODO: Find how to do this in Moodle2
 
//require_js($CFG->wwwroot . '/mod/skillsoft/skillsoft.js');
$PAGE->requires->js('/mod/skillsoft/skillsoft.js');
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // skillsoft asset instance ID - it should be named as the first character of the module

if (!empty($id)) {
	if (! $cm = get_coursemodule_from_id('skillsoft', $id)) {
		print_error('Course Module ID was incorrect');
	}
	if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
		print_error('Course is misconfigured');
	}
	if (! $skillsoft = $DB->get_record('skillsoft', array('id'=> $cm->instance))) {
		print_error('Course module is incorrect');
	}
} else if (!empty($a)) {
	if (! $skillsoft = $DB->get_record('skillsoft', array('id'=> $a))) {
		print_error('Course module is incorrect');
	}
	if (! $course = $DB->get_record('course', array('id'=> $skillsoft->course))) {
		print_error('Course is misconfigured');
	}
	if (! $cm = get_coursemodule_from_instance('skillsoft', $skillsoft->id, $course->id)) {
		print_error('Course Module ID was incorrect');
	}
} else {
	print_error('A required parameter is missing');
}
$url = new moodle_url('/mod/skillsoft/view.php', array('id'=>$cm->id));

require_login($course->id, false, $cm);

//$context = get_context_instance(CONTEXT_COURSE, $course->id);

$context = context_COURSE::instance($course->id);

$strskillsofts = get_string('modulenameplural', 'skillsoft');
$strskillsoft  = get_string('modulename', 'skillsoft');

if (isset($SESSION->skillsoft_id)) {
	unset($SESSION->skillsoft_id);
}

$SESSION->skillsoft_id = $skillsoft->id;
$SESSION->skillsoft_status = 'Not Initialized';
$SESSION->skillsoft_mode = 'normal';
$SESSION->skillsoft_attempt = 1;

$pagetitle = strip_tags($course->shortname.': '.format_string($skillsoft->name).' ('.format_string($skillsoft->assetid).')');
//add_to_log($course->id, 'skillsoft', 'view activity', 'view.php?id='.$cm->id, 'View SkillSoft Asset: '.$skillsoft->name, $cm->id);

skillsoft_event_log(SKILLSOFT_EVENT_ACTIVITY_VIEWED, $skillsoft, $context, $cm);



$PAGE->set_url($url);
//
// Print the page header
//
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

$attempt = skillsoft_get_last_attempt($skillsoft->id, $USER->id);
if ($attempt == 0) {
	$attempt = 1;
}
$currenttab = 'info';
require($CFG->dirroot . '/mod/skillsoft/tabs.php');
$viewreport=get_string('skillsoft_viewreport','skillsoft');



//echo '<div class="reportlink"><a href="'.new moodle_url('/mod/skillsoft/report.php', array('id'=>$skillsoft->id, 'user'=>'true', 'attempt'=>$attempt)).'">'.$viewreport.'</a></div>';
// Print the main part of the page
//print_heading(format_string($skillsoft->name).' ('.format_string($skillsoft->assetid).')');

if (!empty($skillsoft->intro)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_summary', 'skillsoft').'</h3>'.format_text($skillsoft->intro), 'generalbox boxaligncenter boxwidthwide', 'summary');
}
if (!empty($skillsoft->audience)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_audience', 'skillsoft').'</h3>'.format_text($skillsoft->audience), 'generalbox boxaligncenter boxwidthwide', 'audience');
}
if (!empty($skillsoft->prereq)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_prereq', 'skillsoft').'</h3>'.format_text($skillsoft->prereq), 'generalbox boxaligncenter boxwidthwide', 'prereq');
}
if (!empty($skillsoft->duration)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_duration', 'skillsoft').'</h3>'.format_text($skillsoft->duration), 'generalbox boxaligncenter boxwidthwide', 'duration');
}
echo $OUTPUT->box(skillsoft_view_display($skillsoft, $USER, true), 'generalbox boxaligncenter boxwidthwide', 'courselaunch');

echo $OUTPUT->footer();