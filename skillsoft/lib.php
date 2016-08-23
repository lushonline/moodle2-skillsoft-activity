<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Given an object containing all the necessary data,
 * determine if the item is completable..
 *
 * @param object $skillsoft An object from the form in mod_form.php
 * @return bool true if the item is completable
*/
function skillsoft_iscompletable($skillsoft) {
	if (strcasecmp(substr($skillsoft->assetid, 0, 9),"_scorm12_")===0) {
		//SCORM content hosted on SkillPort will not have hacp=0 in the
		//URL so we look at course code and if _scorm12_* then mark as completable
		return true;
	} else if (stripos($skillsoft->launch,'hacp=0')) {
		return false;
	} else if (strtolower($skillsoft->assetid) == 'sso') {
		return false;
	} else {
		return true;
	}
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $skillsoft An object from the form in mod_form.php
 * @return int The id of the newly inserted skillsoft record
 */
function skillsoft_add_instance($skillsoft) {
	global $CFG, $DB;

	$skillsoft->timecreated = time();
	$skillsoft->timemodified = time();
	$skillsoft->completable = skillsoft_iscompletable($skillsoft);

	if ($result = $DB->insert_record('skillsoft', $skillsoft)) {
		$skillsoft->id = $result;
		//$newskillsoft = get_record('skillsoft', 'id' , $result);
		//skillsoft_grade_item_update(stripslashes_recursive($skillsoft),NULL);
		skillsoft_grade_item_update($skillsoft,NULL);
	}

	//Only if completable reprocess imported data
	if ($skillsoft->completable) {
		//We have added an instance so now we need to unset the processed flag
		//in the ODC/CustomReport data so that this new "instance" of a course
		//gets the data updated next time CRON runs
		if ($CFG->skillsoft_trackingmode == TRACK_TO_OLSA_CUSTOMREPORT) {

			//Use more efficient method
			skillsoft_reset_processed($skillsoft->assetid);
		}

		if ($CFG->skillsoft_trackingmode == TRACK_TO_OLSA) {
			//Use more efficient method
			skillsoft_reset_processed($skillsoft->assetid);
		}
	}

	return $result;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $skillsoft An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function skillsoft_update_instance($skillsoft) {
	global $DB;
	$skillsoft->timemodified = time();
	$skillsoft->id = $skillsoft->instance;

	$skillsoft->completable = skillsoft_iscompletable($skillsoft);

	if ($result = $DB->update_record('skillsoft', $skillsoft)) {
		skillsoft_grade_item_update($skillsoft,NULL);
	}

	return $result;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function skillsoft_delete_instance($id) {
	global $DB;

	$result = true;

	//Does the record exist
	if (! $skillsoft = $DB->get_record('skillsoft', array('id'=>$id))) {
		$result = false;
	} else {
		//Delete the grade items
		if (skillsoft_grade_item_delete($skillsoft) != 0) {
			$result = false;
		}
	}

	// Delete any dependent records, AKA the usage data

	if (! $DB->delete_records('skillsoft_au_track', array('skillsoftid'=>$skillsoft->id))) {
		$result = false;
	}

	// Delete the record
	if (! $DB->delete_records('skillsoft', array('id'=>$skillsoft->id))) {
		$result = false;
	}

	return $result;
}

/**
 * Return grade for given user or all users.
 *
 * @param int $skillsoftid id of skillsoft
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function skillsoft_get_user_grades($skillsoft, $userid=0) {
	global $CFG, $DB;
	
	require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
	//require_once('locallib.php');

	$grades = array();

	if ($skillsoft->completable == true) {
		if (empty($userid)) {
			if ($auusers = $DB->get_records_select('skillsoft_au_track',
					'skillsoftid=?',
					array($skillsoft->id),
					'userid,skillsoftid'))
			{
				foreach ($auusers as $auuser) {
					$rawgradeinfo =  skillsoft_grade_user($skillsoft, $auuser->userid);

					$grades[$auuser->userid] = new object();
					$grades[$auuser->userid]->id         = $auuser->userid;
					$grades[$auuser->userid]->userid     = $auuser->userid;
					$grades[$userid]->rawgrade = isset($rawgradeinfo->score) ? $rawgradeinfo->score : NULL;
					$grades[$userid]->dategraded = isset($rawgradeinfo->time) ? $rawgradeinfo->time : NULL;
				}
			} else {
				return false;
			}

		} else {
			if (!$DB->get_records_select('skillsoft_au_track',
					'skillsoftid=? AND userid=?',
					array($skillsoft->id, $userid),
					'userid,skillsoftid'))
			{
				return false; //no attempt yet
			}
			$rawgradeinfo =  skillsoft_grade_user($skillsoft, $userid);

			$grades[$userid] = new object();
			$grades[$userid]->id         = $userid;
			$grades[$userid]->userid     = $userid;
			$grades[$userid]->rawgrade = isset($rawgradeinfo->score) ? $rawgradeinfo->score : NULL;
			$grades[$userid]->dategraded = isset($rawgradeinfo->time) ? $rawgradeinfo->time : NULL;
		}
	}
	return $grades;
}


/**
 * Update grades in central gradebook
 *
 * @param object $skillsoft null means all skillsoftbases
 * @param int $userid specific user only, 0 mean all
 */
function skillsoft_update_grades($skillsoft=null, $userid=0, $nullifnone=true) {
	global $CFG, $DB;
	if (!function_exists('grade_update')) { //workaround for buggy PHP versions
		require_once($CFG->libdir.'/gradelib.php');
	}

	if ($skillsoft != null) {
		if ($skillsoft->completable == true) {
			if ($grades = skillsoft_get_user_grades($skillsoft, $userid)) {
				skillsoft_grade_item_update($skillsoft, $grades);
			} else if ($userid and $nullifnone) {
				$grade = new object();
				$grade->userid   = $userid;
				$grade->rawgrade = NULL;
				skillsoft_grade_item_update($skillsoft, $grade);
			} else {
				skillsoft_grade_item_update($skillsoft);
			}
		}
	} else {
		$sql = "SELECT s.*, cm.idnumber as cmidnumber
				FROM {skillsoft} s, {course_modules} cm, {modules} m
				WHERE m.name='skillsoft' AND m.id=cm.module AND cm.instance=s.id";
		$rs = $DB->get_recordset_sql($sql);
		if ($rs->valid()) {
			foreach ($rs as $skillsoft) {
				skillsoft_update_grades($skillsoft, 0, false);
			}
		}
		$rs->close();
	}
}

/**
 * Update/create grade item for given skillsoft
 *
 * @param object $skillsoft object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function skillsoft_grade_item_update($skillsoft, $grades=NULL) {
	global $CFG, $DB;


	//If the item is completable we base the grade on the SCORE which is 0-100
	//
	// MAR2011 NOTE: In some instances a course maybe completable but NOT return a score
	// we see this when for example a course can be completed by paging through all
	// screens instead of taking a test
	// We could consider making the grading a config setting in mod_form to allow
	// it to be changed per asset.
	//
	if ($skillsoft->completable == true) {
		if (!function_exists('grade_update')) { //workaround for buggy PHP versions
			require_once($CFG->libdir.'/gradelib.php');
		}

		$params = array('itemname'=>$skillsoft->name);
		if (isset($skillsoft->cmidnumber)) {
			$params['idnumber'] = $skillsoft->cmidnumber;
		}

		$params['gradetype'] = GRADE_TYPE_VALUE;
		$params['grademax']  = 100;
		$params['grademin']  = 0;

		if ($grades  === 'reset') {
			$params['reset'] = true;
			$grades = NULL;
		}

		return grade_update('mod/skillsoft', $skillsoft->course, 'mod', 'skillsoft', $skillsoft->id, 0, $grades, $params);
	} else {
		return true;
	}
}


/**
 * Delete grade item for given skillsoft
 *
 * @param object $skillsoft object
 * @return object grade_item
 */
function skillsoft_grade_item_delete($skillsoft) {
	global $CFG, $DB;
	if ($skillsoft->completable == true) {

		if (!function_exists('grade_update')) { //workaround for buggy PHP versions
			require_once($CFG->libdir.'/gradelib.php');
		}
		$params = array('deleted'=>1);
		return grade_update('mod/skillsoft', $skillsoft->course, 'mod', 'skillsoft', $skillsoft->id, 0, NULL, $params);
	} else {
		return 0;
	}
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function skillsoft_user_outline($course, $user, $mod, $skillsoft) {
	global $CFG, $DB, $OUTPUT;
	
	require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
	//require_once('locallib.php');

	if (empty($attempt)) {
		$attempt = skillsoft_get_last_attempt($skillsoft->id,$user->id);
		if ($attempt == 0) {
			$attempt = 1;
		}
	}
	$return = NULL;

	if ($userdata = skillsoft_get_tracks($skillsoft->id, $user->id, $attempt)) {
		$a = new object();
		$a->attempt = $attempt;
		if ($skillsoft->completable == true) {
			$a->duration = isset($userdata->{'[CORE]time'}) ? $userdata->{'[CORE]time'} : '-';
			$a->bestscore = isset($userdata->{'[SUMMARY]bestscore'}) ? $userdata->{'[SUMMARY]bestscore'} : '-';
		} else {
			$a->duration = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
			$a->bestscore = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoft',get_string('skillsoft_na','skillsoft'));
		}
		$a->accesscount = isset($userdata->{'[SUMMARY]accesscount'}) ? $userdata->{'[SUMMARY]accesscount'} : '-';
		$return = new object();
		$return->info = get_string("skillsoft_summarymessage", "skillsoft", $a);
		$return->time = $userdata->{'[SUMMARY]lastaccess'};
	}

	return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 * This needs to output the information
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function skillsoft_user_complete($course, $user, $mod, $skillsoft) {
	global $CFG, $DB, $OUTPUT;
	
	require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
	//require_once('locallib.php');

	$table = new html_table();
	$table->head = array(
			get_string('skillsoft_attempt', 'skillsoft'),
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
	$table->wrap = array('','', '','','nowrap','nowrap','nowrap','nowrap','nowrap','nowrap');
	$table->width = '80%';
	$table->size = array('*','*', '*', '*', '*', '*', '*', '*', '*', '*');
	$row = array();
	$score = '&nbsp;';

	
	
	$maxattempts = skillsoft_get_last_attempt($skillsoft->id,$user->id);
	if ($maxattempts == 0) {
		$maxattempts = 1;
	}
	for ($a = $maxattempts; $a > 0; $a--) {
		$row = array();
		$score = '&nbsp;';
		if ($trackdata = skillsoft_get_tracks($skillsoft->id,$user->id,$a)) {
			
			//FIXME: Get CM reference
			$row[] = '<a href="'.new moodle_url('/mod/skillsoft/report.php', array('id'=>$mod->id,'user'=>'true','attempt'=>$trackdata->attempt)).'">'.$trackdata->attempt.'</a>';
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

	echo html_writer::table($table);
	return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in skillsoft activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function skillsoft_print_recent_activity($course, $isteacher, $timestart) {
	global $CFG, $DB, $OUTPUT;


	//We need to customise this for MYSQL/MSSSQL
	//m.value in database is string so need to convert to int for > comparison
	//to work in MSSQL and MYSQL
	$sql=	"SELECT	s.id,
			s.name,
			Count(s.id) AS countlaunches
			FROM {skillsoft} s
			LEFT JOIN {skillsoft_au_track} m ON s.id = m.skillsoftid
			WHERE 	s.course=?
			AND m.element='[SUMMARY]lastaccess'
			AND CAST(CAST(m.value AS CHAR(10)) AS DECIMAL(10,0)) > ?
			GROUP BY s.id, s.name";

	$params = array($course->id,$timestart);

	if(!$records = $DB->get_records_sql($sql,$params)) {
		return false;
	}

	$names = array();
	foreach ($records as $id => $record){
		if ($cm = get_coursemodule_from_instance('skillsoft', $record->id, $course->id)) {
			//$context = get_context_instance(CONTEXT_MODULE, $cm->id);
			$context = context_MODULE::instance($cm->id);
			if (has_capability('mod/skillsoft:viewreport', $context)) {
				$name = '<a href="'.new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id)).'">'.$record->name.'</a>'.'&nbsp;';
				if ($record->countlaunches > 1) {
					$name .= " ($record->countlaunches)";
				}
				$names[] = $name;
			}
		}
	}

	if (count($names) > 0) {
		echo $OUTPUT->heading(get_string('modulenameplural', 'skillsoft').':');
		echo '<div class="head"><div class="name">'.implode('<br />', $names).'</div></div>';
		return true;
	} else {
		return false;  //  True if anything was printed, otherwise false
	}
}

function skillsoft_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid="", $userid="", $groupid="") {
	global $CFG, $COURSE, $USER, $DB;

	if ($COURSE->id == $courseid) {
		$course = $COURSE;
	} else {
		$course = $DB->get_record('course', array('id' => $courseid));
	}

	$modinfo =& get_fast_modinfo($course);

	$cm = $modinfo->cms[$cmid];
	$params = array($timestart, $cm->id);

	if ($userid) {
		$userselect = "AND u.id = ?";
		$params[] = $userid;
	} else {
		$userselect = "";
	}

	$sql = "SELECT
	a.*,
	s.name,
	s.course,
	cm.instance,
	cm.section,
	u.firstname,
	u.lastname,
	u.email,
	u.picture,
	u.imagealt
	FROM {skillsoft_au_track} AS a
	LEFT JOIN {user} AS u ON a.userid = u.id
	LEFT JOIN {skillsoft} AS s ON a.skillsoftid = s.id
	LEFT JOIN {course_modules} AS cm ON a.skillsoftid = cm.instance
	WHERE
	CAST(CAST(a.value AS CHAR(10)) AS DECIMAL(10,0)) > ?
	AND a.element = '[SUMMARY]lastaccess'
	AND cm.id = ?
	$userselect
	ORDER BY
	a.skillsoftid DESC, a.timemodified ASC";

	//$params = array($timestart, $courseid);
	$records = $DB->get_records_sql($sql,$params);

	if (!empty($records)) {
		foreach ($records as $record) {
			if (empty($groupid) || groups_is_member($groupid, $record->userid)) {

				unset($activity);


				$activity->type = "skillsoft";
				$activity->cmid = $cm->id;
				$activity->name = $record->name;
				$activity->sectionnum = $cm->sectionnum;
				$activity->timestamp = $record->timemodified;

				$activity->content = new stdClass();
				$activity->content->instance = $record->instance;
				$activity->content->attempt = $record->attempt;
				$activity->content->lastaccessdate = $record->value;

				$activity->user = new stdClass();
				$activity->user->id = $record->userid;
				$activity->user->firstname = $record->firstname;
				$activity->user->lastname  = $record->lastname;
				$activity->user->picture   = $record->picture;
				$activity->user->imagealt = $record->imagealt;
				$activity->user->email = $record->email;

				$activities[] = $activity;

				$index++;
			}
		} // end foreach
	}
}

function skillsoft_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
	/// Basically, this function prints the results of "skillsoft_get_recent_activity"

	global $CFG, $OUTPUT;

	echo '<table border="0" cellpadding="3" cellspacing="0" class="skillsoft-recent">';

	echo "<tr><td class=\"userpicture\" valign=\"top\">";
	echo $OUTPUT->user_picture($activity->user, array('courseid'=>$courseid));
	echo "</td><td>";

	echo '<div class="title">';
	if ($detail) {
		$aname = s($activity->name);
		echo "<img src=\"" . $OUTPUT->pix_url('icon', $activity->type) . "\" ".
				"class=\"icon\" alt=\"{$aname}\" />";
	}
	// link to activity
	//$skillsofthref = new moodle_url('/mod/skillsoft/report.php', array('id'=>$activity->content->instance,'user'=>'true','attempt'=>$activity->content->attempt));;

	//$skillsofthref = new moodle_url('/mod/skillsoft/view.php', array('id'=>$activity->cmid));;
	//echo '<a href="'.$skillsofthref.'">'.$activity->name.'</a> - ';
	echo $activity->name.' - ';
	echo get_string('skillsoft_attempt', 'skillsoft').' '.$activity->content->attempt;
	echo '</div>';

	echo '<div class="user">';
	$fullname = fullname($activity->user, $viewfullnames);
	$timeago = format_time(time() - $activity->timestamp);
	$userhref = new moodle_url('/user/view.php', array('id'=>$activity->user->id,'course'=>$courseid));;

	echo '<a href="'.$userhref.'">'.$fullname.'</a>';
	echo ' - ' . userdate($activity->timestamp) . ' ('.$timeago.')';
	echo '</div>';
	echo "</td></tr></table>";

	return;
}



/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function skillsoft_delete_sessions($time) {
	global $DB;
	$result = true;

	// Delete any dependent records, AKA the usage data
	if (! $DB->delete_records_select('skillsoft_session_track', 'timecreated < ?', array($time))) {
		$result = false;
	}

	return $result;
}

function skillsoft_ondemandcommunications() {
	global $CFG;
	require_once($CFG->dirroot.'/mod/skillsoft/olsalib.php');
	//require_once('olsalib.php');

	mtrace(get_string('skillsoft_odcinit','skillsoft'));
	$initresponse = OC_InitializeTrackingData();
	if ($initresponse->success) {
		//Initialise was successful
		$moreFlag = true;
		while ($moreFlag) {
			$tdrresponse = OC_GetTrackingData();
			if ($tdrresponse->success) {
				mtrace(get_string('skillsoft_odcgetdatastart','skillsoft',$tdrresponse->result->handle));

				//Handle the use case where we only get ONE tdr
				if ( is_array($tdrresponse->result->tdrs->tdr) && ! empty($tdrresponse->result->tdrs->tdr) )
				{
					foreach ( $tdrresponse->result->tdrs->tdr as $tdr) {
						mtrace(get_string('skillsoft_odcgetdataprocess','skillsoft',$tdr->id));
						$id = skillsoft_insert_tdr($tdr);
					}
				} else {
					mtrace(get_string('skillsoft_odcgetdataprocess','skillsoft',$tdrresponse->result->tdrs->tdr->id));
					$id = skillsoft_insert_tdr($tdrresponse->result->tdrs->tdr);
				}
				$moreFlag = $tdrresponse->result->moreFlag;

				$ackresponse = OC_AcknowledgeTrackingData($tdrresponse->result->handle);

				if ($tdrresponse->success) {
					mtrace(get_string('skillsoft_odcackdata','skillsoft',$tdrresponse->result->handle));
				} else {
					mtrace(get_string('skillsoft_odcackdataerror','skillsoft',$tdrresponse->errormessage));
				}
				mtrace(get_string('skillsoft_odcgetdataend','skillsoft',$tdrresponse->result->handle));
			} else {
				if ($tdrresponse->errormessage == get_string('skillsoft_odcnoresultsavailable','skillsoft')) {
					mtrace(get_string('skillsoft_odcnoresultsavailable','skillsoft'));
				} else {
					mtrace(get_string('skillsoft_odcgetdataerror','skillsoft',$tdrresponse->errormessage));
				}
				$moreFlag = false;
			}
		}
	} else {
		mtrace(get_string('skillsoft_odciniterror','skillsoft',$initresponse->errormessage));
	}
	skillsoft_process_received_tdrs(true);
}


function skillsoft_customreport($includetoday=false) {
	global $CFG;
	require_once($CFG->dirroot.'/mod/skillsoft/olsalib.php');
	//require_once('olsalib.php');


	//Constants for custom report preocessing
	define('CUSTOMREPORT_RUN', '0');
	define('CUSTOMREPORT_POLL', '1');
	define('CUSTOMREPORT_DOWNLOAD', '2');
	define('CUSTOMREPORT_IMPORT', '3');
	define('CUSTOMREPORT_PROCESS', '4');

	global $CFG, $DB;

	//Step 1 - Check if we have an outstanding report
	//Get last report where url = '' indicating report submitted BUT not ready yet
	//Should only be 1 record
	$reports = $DB->get_records_select('skillsoft_report_track', '', null, 'id desc', '*', '0', '1');
	//We have a report row now we have to decide what to do:
	if ($reports) {
		$report = end($reports);

		//If we need to reset the cycle - skillsoft_resetcustomreportcrontask = true
		//Then delete this last $report and set $state = CUSTOMREPORT_RUN
		if ($CFG->skillsoft_resetcustomreportcrontask) {
			skillsoft_delete_customreport($report->handle);
			//Now we need to reset to 0
			set_config('skillsoft_resetcustomreportcrontask', 0);
			$state = CUSTOMREPORT_RUN;
		} else {
			if ($report->polled == 0) {
				$state= CUSTOMREPORT_POLL;
			} else if ($report->downloaded == 0) {
	 		$state= CUSTOMREPORT_DOWNLOAD;
			} else if ($report->imported == 0) {
	 		$state= CUSTOMREPORT_IMPORT;
			} else if ($report->processed == 0) {
	 		$state= CUSTOMREPORT_PROCESS;
			} else {
	 		$state = CUSTOMREPORT_RUN;
			}
		}
	} else {
		$state = CUSTOMREPORT_RUN;
	}

	$tab = '    ';

	mtrace(get_string('skillsoft_customreport_init','skillsoft'));
	//Now switch based on state
	switch ($state) {
		case CUSTOMREPORT_POLL:
			skillsoft_poll_customreport($report->handle, true);
			break;
		case CUSTOMREPORT_DOWNLOAD:
			//The report is there so lets download it
			$downloadedfile=skillsoft_download_customreport($report->handle, $report->url,NULL,true);
			flush();
			break;
		case CUSTOMREPORT_IMPORT:
			//Import the CSV to the database
			$importsuccess = skillsoft_import_customreport($report->handle, $report->localpath,true);
			if ($importsuccess) {
				//Update the $CFG setting
				set_config('skillsoft_reportstartdate', $report->enddate);
				//Delete the downloaded file
				if(is_file($report->localpath)) {
					$deleteokay = unlink($report->localpath);
				}
			}
			break;
		case CUSTOMREPORT_PROCESS:
			//Convert the imported results into moodle records and gradebook
			skillsoft_process_received_customreport($report->handle, true);
			break;
		case CUSTOMREPORT_RUN:
			skillsoft_run_customreport(true,NULL,$includetoday);
			break;
	}
	mtrace(get_string('skillsoft_customreport_end','skillsoft'));
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function skillsoft_cron () {
	global $CFG, $DB;
	
	require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
	//require_once('locallib.php');

	if (!isset($CFG->skillsoft_sessionpurge)) {
		set_config('skillsoft_sessionpurge', 8);
	}

	//Purge the values from skillsoft_session_track that are older than $CFG->skillsoft_sessionpurge hours.
	//Time now - hours*60*60
	$purgetime = time() - ($CFG->skillsoft_sessionpurge * 60 * 60);
	mtrace(get_string('skillsoft_purgemessage','skillsoft',userdate($purgetime)));
	skillsoft_delete_sessions($purgetime);

	if (!$CFG->skillsoft_disableusagedatacrontask) {
		if ($CFG->skillsoft_trackingmode == TRACK_TO_OLSA) {
			//We are in "Track to OLSA" so perform ODC cycle
			skillsoft_ondemandcommunications();
		}

		if ($CFG->skillsoft_trackingmode == TRACK_TO_OLSA_CUSTOMREPORT) {
			//We are in "Track to OLSA (Custom Report)" so perform custom report cycle
			//This is where we generate custom report for last 24 hours (or catchup), download it and then import it
			//assumption is LoginName will be the value we selected here for $CFG->skillsoft_useridentifier
			skillsoft_customreport($CFG->skillsoft_reportincludetoday);
		}
	}
	return true;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of skillsoft. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $skillsoftid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function skillsoft_get_participants($skillsoftid) {
	global $CFG, $DB;

	//Get students
	$sql = "SELECT DISTINCT u.id, u.id
			FROM {user} u,
			{lesson_au_tracks} a
			WHERE a.skillsoftid = '?' and
			u.id = a.userid";
	$params = array($skillsofid);

	$students = $DB->get_records_sql($sql,$params);

	//Return students array (it contains an array of unique users)
	return ($students);


	return false;
}


// For Participantion Reports
function skillsoft_get_view_actions() {
	return array('view activity', 'view report','view all');
}

// For Participantion Reports
function skillsoft_get_post_actions() {
	return array('add','update');
}


/**
 * This function returns if a scale is being used by one skillsoft
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $skillsoftid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function skillsoft_scale_used($skillsoftid, $scaleid) {

	$return = false;

	//$rec = $DB->get_record("skillsoft", array("id" => "$skillsoftid", "scale" => "-$scaleid"));
	//
	//if (!empty($rec) && !empty($scaleid)) {
	//    $return = true;
	//}

	return $return;
}

/**
 * Checks if scale is being used by any instance of skillsoft.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any skillsoft
 */
function skillsoft_scale_used_anywhere($scaleid) {
	$return = false;
	/*
	 if ($scaleid and record_exists('skillsoft', 'grade', -$scaleid)) {
	return true;
	} else {
	return false;
	}
	*/
	return $return;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function skillsoft_uninstall() {
	return true;
}


/**
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @users FEATURE_BACKUP_MOODLE2
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function skillsoft_supports($feature) {
	switch($feature) {
		case FEATURE_MOD_INTRO:               return true;
		case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
		case FEATURE_GRADE_HAS_GRADE:         return true;
		case FEATURE_BACKUP_MOODLE2:          return true;
		default: return null;
	}
}
