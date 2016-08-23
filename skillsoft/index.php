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

$id = required_param('id', PARAM_INT);   // course id
$PAGE->set_url('/mod/skillsoft/index.php', array('id'=>$id));

if (!empty($id)) {
	if (! $course = $DB->get_record('course', array('id'=>$id))) {
		print_error('Course ID is incorrect');
	}
} else {
	print_error('A required parameter is missing');
}

require_course_login($course);

$PAGE->set_pagelayout('incourse');

//add_to_log($course->id, 'skillsoft', 'view all activity', 'index.php?id='.$course->id, 'View all SkillSoft Assets');
$context = context_course::instance($course->id);
skillsoft_event_log(SKILLSOFT_EVENT_ACTIVITY_MANAGEMENT_VIEWED, $skillsoft, $context, $cm);

//Retrieve the localisation strings

$strskillsoft = get_string('modulename', 'skillsoft');
$strskillsofts = get_string('modulenameplural', 'skillsoft');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strskillsoftid = get_string('skillsoft_assetid', 'skillsoft');
$strskillsoftsummary = get_string('skillsoft_summary', 'skillsoft');
$strlastmodified = get_string('lastmodified');
$strname = get_string('skillsoft_name','skillsoft');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strsummary = get_string("summary");

$PAGE->set_title($strskillsofts);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strskillsofts);
echo $OUTPUT->header();

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
	$sections = get_all_sections($course->id);
}

if ($usesections) {
	$sortorder = "cw.section ASC";
} else {
	$sortorder = "m.timemodified DESC";
}

if (! $skillsofts = get_all_instances_in_course('skillsoft', $course)) {
	notice(get_string('thereareno', 'moodle', $strskillsofts), '../../course/view.php?id=$course->id');
	exit;
}

$table = new html_table();

if ($usesections) {
	$table->head  = array ($strsectionname, $strskillsoftid, $strname, $strsummary);
	$table->align = array ("center", "left", "left", "left");
} else {
	$table->head  = array ($strlastmodified, $strskillsoftid, $strname, $strsummary);
	$table->align = array ("left", "left", "left", "left");
}

foreach ($skillsofts as $skillsoft) {

	//$context = get_context_instance(CONTEXT_MODULE,$skillsoft->coursemodule);
	$context = context_MODULE::instance($skillsoft->coursemodule);
	
	$tt = "";
	if ($usesections) {
		if ($skillsoft->section) {
			$tt = get_section_name($course, $sections[$skillsoft->section]);
		}
	} else {
		$tt = userdate($skillsoft->timemodified);
	}
	$options = (object)array('noclean'=>true);
	if (!$skillsoft->visible) {
		//Show dimmed if the mod is hidden
		$table->data[] = array ($tt, $skillsoft->assetid, '<a class="dimmed" href="view.php?id='.$skillsoft->coursemodule.'">'.format_string($skillsoft->name).'</a>', $skillsoft->intro);
	} else {
		//Show normal if the mod is visible
		$table->data[] = array ($tt, $skillsoft->assetid, '<a href="view.php?id='.$skillsoft->coursemodule.'">'.format_string($skillsoft->name).'</a>', $skillsoft->intro);
	}
}

echo "<br />";

echo html_writer::table($table);

echo $OUTPUT->footer();

