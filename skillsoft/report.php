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

$id = required_param('id', PARAM_INT);    // skillsoft ID, or
$user = optional_param('user', '', PARAM_BOOL);  // User report
$attempt = optional_param('attempt', '', PARAM_INT);  // attempt number


if (!empty($id)) {
	if (! $cm = get_coursemodule_from_id('skillsoft', $id)) {
        print_error('Course Module ID was incorrect');
    }
    if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
        print_error('Course is misconfigured');
    }
    if (! $skillsoft = $DB->get_record('skillsoft', array('id'=>$cm->instance))) {
        print_error('Course module is incorrect');
    }
	
//	if (! $skillsoft = $DB->get_record('skillsoft', array('id'=>$id))) {
//		print_error('Course module is incorrect');
//	}
//	if (! $course = $DB->get_record('course', array('id'=> $skillsoft->course))) {
//		print_error('Course is misconfigured');
//	}
//	if (! $cm = get_coursemodule_from_instance('skillsoft', $skillsoft->id, $course->id)) {
//		print_error('Course Module ID was incorrect');
//	}
} else {
	print_error('A required parameter is missing');
}
$url = new moodle_url('/mod/skillsoft/report.php',array('id'=>$CM->id));

$PAGE->set_url($url);

require_login($course->id, false, $cm);
//require_course_login($course);

//$PAGE->set_context(CONTEXT_MODULE, $cm->id);

//$contextmodule = get_context_instance(CONTEXT_MODULE,$cm->id);
$contextmodule = context_MODULE::instance($cm->id);


//Retrieve the localisation strings
$strskillsoft = get_string('modulename', 'skillsoft');
$strskillsofts = get_string('modulenameplural', 'skillsoft');
$strskillsoftid = get_string('skillsoft_assetid', 'skillsoft');
$strskillsoftsummary = get_string('skillsoft_summary', 'skillsoft');
$strlastmodified = get_string('lastmodified');

$strreport  = get_string('skillsoft_report', 'skillsoft');
$strattempt  = get_string('skillsoft_attempt', 'skillsoft');
$strallattempt  = get_string('skillsoft_allattempt', 'skillsoft');

//Navigation Links
$PAGE->set_title("$course->shortname: ".format_string($skillsoft->name));
$PAGE->set_heading($course->fullname);

//If user has viewreport permission enable "Report" link allowing viewing all usage of asset
if (has_capability('mod/skillsoft:viewreport', $contextmodule)) {
	$PAGE->navbar->add($strreport, new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id)));
} else {
	$PAGE->navbar->add($strreport);
}

if ($user) {
	if (empty($attempt)) {
		$PAGE->navbar->add($strallattempt, new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id,'user'=>'true')));
	} else {
		$PAGE->navbar->add($strallattempt, new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id,'user'=>'true')));
		$PAGE->navbar->add($strattempt.' '.$attempt);
	}
}
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($skillsoft->name));

if ($user) {
	$currenttab = 'reports';
	require($CFG->dirroot . '/mod/skillsoft/tabs.php');
	//Print User Specific Data
	// Print general score data
	$table = new html_table();
	$table->tablealign='center';
	$table->head = array(
	$strattempt,
	get_string('skillsoft_firstaccess','skillsoft'),
	get_string('skillsoft_lastaccess','skillsoft'),
	get_string('skillsoft_completed','skillsoft'),
	get_string('skillsoft_lessonstatus','skillsoft'),
	get_string('skillsoft_totaltime','skillsoft'),
	get_string('skillsoft_firstscore','skillsoft'),
	get_string('skillsoft_currentscore','skillsoft'),
	get_string('skillsoft_bestscore','skillsoft'),
	get_string('skillsoft_accesscount','skillsoft'),
	);
	$table->align = array('left','left', 'left', 'left', 'center','center','right','right','right','right');
	$table->wrap = array('', '', '','','nowrap','nowrap','nowrap','nowrap','nowrap','nowrap');
	$table->width = '80%';
	$table->size = array('*', '*', '*', '*', '*', '*', '*', '*', '*', '*');


	if (empty($attempt)) {

		//Show all attempts
		//add_to_log($course->id, 'skillsoft', 'view report', 'report.php?id='.$cm->id."&user=".($user ? 'true' : 'false')."&attempt=".$attempt, 'View report for Asset: '.$skillsoft->name);

		skillsoft_event_log(SKILLSOFT_EVENT_REPORT_VIEWED, $skillsoft, $contextmodule, $cm);
		
		$maxattempts = skillsoft_get_last_attempt($skillsoft->id,$USER->id);
		if ($maxattempts == 0) {
			$maxattempts = 1;
		}

		for ($a = $maxattempts; $a > 0; $a--) {
			$row = array();
			$score = '&nbsp;';
			if ($trackdata = skillsoft_get_tracks($skillsoft->id,$USER->id,$a)) {
				$row[] = '<a href="'.new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id,'user'=>'true','attempt'=>$trackdata->attempt)).'">'.$trackdata->attempt.'</a>';
				$row[] = isset($trackdata->{'[SUMMARY]firstaccess'}) ? userdate($trackdata->{'[SUMMARY]firstaccess'}):'';
				$row[] = isset($trackdata->{'[SUMMARY]lastaccess'}) ? userdate($trackdata->{'[SUMMARY]lastaccess'}):'';
				if ($skillsoft->completable == true) {
					$row[] = isset($trackdata->{'[SUMMARY]completed'}) ? userdate($trackdata->{'[SUMMARY]completed'}):'';
					$row[] = isset($trackdata->{'[CORE]lesson_status'}) ? $trackdata->{'[CORE]lesson_status'}:'';
					$row[] = isset($trackdata->{'[CORE]time'}) ? $trackdata->{'[CORE]time'}:'';
					$row[] = isset($trackdata->{'[SUMMARY]firstscore'}) ? $trackdata->{'[SUMMARY]firstscore'}:'';
					$row[] = isset($trackdata->{'[SUMMARY]currentscore'}) ? $trackdata->{'[SUMMARY]currentscore'}:'';
					$row[] = isset($trackdata->{'[SUMMARY]bestscore'}) ? $trackdata->{'[SUMMARY]bestscore'}:'';
				} else {
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
				}
				$row[] = isset($trackdata->{'[SUMMARY]accesscount'}) ? $trackdata->{'[SUMMARY]accesscount'} :'';
				$table->data[] = $row;
			}
		}

	} else {
		//add_to_log($course->id, 'skillsoft', 'view report', 'report.php?id='.$cm->id."&user=".($user ? 'true' : 'false')."&attempt=".$attempt, 'View report for Asset: '.$skillsoft->name);		$row = array();
		skillsoft_event_log(SKILLSOFT_EVENT_REPORT_VIEWED, $skillsoft, $contextmodule, $cm);
		$score = '&nbsp;';
		if ($trackdata = skillsoft_get_tracks($skillsoft->id,$USER->id,$attempt)) {
			$row[] = '<a href="'.new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id,'user'=>'true','attempt'=>$trackdata->attempt)).'">'.$trackdata->attempt.'</a>';
			$row[] = isset($trackdata->{'[SUMMARY]firstaccess'}) ? userdate($trackdata->{'[SUMMARY]firstaccess'}):'';
			$row[] = isset($trackdata->{'[SUMMARY]lastaccess'}) ? userdate($trackdata->{'[SUMMARY]lastaccess'}):'';
			if ($skillsoft->completable == true) {
				$row[] = isset($trackdata->{'[SUMMARY]completed'}) ? userdate($trackdata->{'[SUMMARY]completed'}):'';
				$row[] = isset($trackdata->{'[CORE]lesson_status'}) ? $trackdata->{'[CORE]lesson_status'}:'';
				$row[] = isset($trackdata->{'[CORE]time'}) ? $trackdata->{'[CORE]time'}:'';
				$row[] = isset($trackdata->{'[SUMMARY]firstscore'}) ? $trackdata->{'[SUMMARY]firstscore'}:'';
				$row[] = isset($trackdata->{'[SUMMARY]currentscore'}) ? $trackdata->{'[SUMMARY]currentscore'}:'';
				$row[] = isset($trackdata->{'[SUMMARY]bestscore'}) ? $trackdata->{'[SUMMARY]bestscore'}:'';
			} else {
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
			}
			$row[] = isset($trackdata->{'[SUMMARY]accesscount'}) ? $trackdata->{'[SUMMARY]accesscount'} :'';
			$table->data[] = $row;
		}
	}
} else {
	require_capability('mod/skillsoft:viewreport', $contextmodule);
	//add_to_log($course->id, 'skillsoft', 'view all report', 'report.php?id='.$cm->id."&user=".($user ? 'true' : 'false')."&attempt=".$attempt, 'View all users report for Asset: '.$skillsoft->name);
	skillsoft_event_log(SKILLSOFT_EVENT_REPORT_VIEWED, $skillsoft, $contextmodule, $cm);
	

	$currenttab = 'allreports';
	require($CFG->dirroot . '/mod/skillsoft/tabs.php');

	//Just report on the activity
	//SQL to get all get all userid/skillsoftid records
	$sql = "SELECT ai.userid, ai.skillsoftid
                        FROM {skillsoft_au_track} ai
                        WHERE ai.skillsoftid = ?
                        GROUP BY ai.userid,ai.skillsoftid";
	$params = array($skillsoft->id);


	$table = new html_table();
	$table->tablealign = 'center';
	$table->head = array(
	get_string('name'),
	$strattempt,
	get_string('skillsoft_firstaccess','skillsoft'),
	get_string('skillsoft_lastaccess','skillsoft'),
	get_string('skillsoft_completed','skillsoft'),
	get_string('skillsoft_lessonstatus','skillsoft'),
	get_string('skillsoft_totaltime','skillsoft'),
	get_string('skillsoft_firstscore','skillsoft'),
	get_string('skillsoft_currentscore','skillsoft'),
	get_string('skillsoft_bestscore','skillsoft'),
	get_string('skillsoft_accesscount','skillsoft'),
	);
	$table->align = array('left','left','left', 'left', 'left', 'center','center','right','right','right','right');
	$table->wrap = array('','','', '','','nowrap','nowrap','nowrap','nowrap','nowrap','nowrap');
	$table->width = '80%';
	$table->size = array('*','*','*', '*', '*', '*', '*', '*', '*', '*', '*');

	if ($skillsoftusers=$DB->get_records_sql($sql,$params))
	{
		foreach($skillsoftusers as $skillsoftuser){
				
			$maxattempts = skillsoft_get_last_attempt($skillsoft->id,$skillsoftuser->userid);
			if ($maxattempts == 0) {
				$maxattempts = 1;
			}

			for ($a = $maxattempts; $a > 0; $a--) {
				$row = array();
				$userdata = $DB->get_record('user',array('id'=>$skillsoftuser->userid),'id, firstname, lastname, picture, imagealt, email');

				$row[] = $OUTPUT->user_picture($userdata, array('courseid'=>$course->id)).' '.'<a href="'.$CFG->wwwroot.'/user/view.php?id='.$skillsoftuser->userid.'&amp;course='.$course->id.'">'.fullname($userdata).'</a>';

				$score = '&nbsp;';
				if ($trackdata = skillsoft_get_tracks($skillsoftuser->skillsoftid,$skillsoftuser->userid,$a)) {
					$row[] = $trackdata->attempt;
					$row[] = isset($trackdata->{'[SUMMARY]firstaccess'}) ? userdate($trackdata->{'[SUMMARY]firstaccess'}):'';
					$row[] = isset($trackdata->{'[SUMMARY]lastaccess'}) ? userdate($trackdata->{'[SUMMARY]lastaccess'}):'';
					if ($skillsoft->completable == true) {
						$row[] = isset($trackdata->{'[SUMMARY]completed'}) ? userdate($trackdata->{'[SUMMARY]completed'}):'';
						$row[] = isset($trackdata->{'[CORE]lesson_status'}) ? $trackdata->{'[CORE]lesson_status'}:'';
						$row[] = isset($trackdata->{'[CORE]time'}) ? $trackdata->{'[CORE]time'}:'';
						$row[] = isset($trackdata->{'[SUMMARY]firstscore'}) ? $trackdata->{'[SUMMARY]firstscore'}:'';
						$row[] = isset($trackdata->{'[SUMMARY]currentscore'}) ? $trackdata->{'[SUMMARY]currentscore'}:'';
						$row[] = isset($trackdata->{'[SUMMARY]bestscore'}) ? $trackdata->{'[SUMMARY]bestscore'}:'';
					} else {
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
					}
					$row[] = isset($trackdata->{'[SUMMARY]accesscount'}) ? $trackdata->{'[SUMMARY]accesscount'} :'';
				} else {
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
				}
				$table->data[] = $row;
			}
		}
	}
}
echo $OUTPUT->box_start('generalbox boxaligncenter');
echo html_writer::table($table);
echo $OUTPUT->box_end();

if (empty($noheader)) {
	echo $OUTPUT->footer();
}