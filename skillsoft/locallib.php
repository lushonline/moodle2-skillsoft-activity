<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/mod/skillsoft/lib.php');
require_once($CFG->dirroot.'/mod/skillsoft/aiccmodel.php');
require_once($CFG->dirroot.'/mod/skillsoft/aicclib.php');

defined('MOODLE_INTERNAL') || die();

/// Constants and settings for module skillsoft
define('TRACK_TO_LMS', '0');
define('TRACK_TO_OLSA', '1');
define('TRACK_TO_OLSA_CUSTOMREPORT', '2');

/// Constants and settings for module skillsoft
define('IDENTIFIER_USERID', 'id');
define('IDENTIFIER_USERNAME', 'username');

/// Constants and settings for module skillsoft
/// SSO actiontype for assets
define('SSO_ASSET_ACTIONTYPE_LAUNCH', 'launch');
define('SSO_ASSET_ACTIONTYPE_SUMMARY', 'summary');

define('SKILLSOFT_EVENT_ACTIVITY_VIEWED',0);
define('SKILLSOFT_EVENT_ACTIVITY_MANAGEMENT_VIEWED',1);
define('SKILLSOFT_EVENT_REPORT_VIEWED',2);

/**
 * Returns an array of the array of what grade options
 *
 * @return array an array of OLSA Tracking Options
 */
function skillsoft_get_tracking_method_array(){
	return array (TRACK_TO_LMS => get_string('skillsoft_tracktolms', 'skillsoft'),
	TRACK_TO_OLSA => get_string('skillsoft_tracktoolsa', 'skillsoft'),
	TRACK_TO_OLSA_CUSTOMREPORT => get_string('skillsoft_tracktoolsacustomreport', 'skillsoft'),
	);
}

/**
 * Returns an array
 *
 * @return array an array of fileds to choose for tracking
 */
function skillsoft_get_user_identifier_array(){
	return array (IDENTIFIER_USERID => get_string('skillsoft_userid_identifier', 'skillsoft'),
	IDENTIFIER_USERNAME => get_string('skillsoft_username_identifier', 'skillsoft'),
	);
}

/**
 * Returns an array
 *
 * @return array an array of fileds to choose for sso asset action type
 */
function skillsoft_get_sso_asset_actiontype_array(){
	return array (SSO_ASSET_ACTIONTYPE_LAUNCH => get_string('skillsoft_sso_actiontype_launch', 'skillsoft'),
	SSO_ASSET_ACTIONTYPE_SUMMARY => get_string('skillsoft_sso_actiontype_summary', 'skillsoft'),
	);
}

/**
 * Allows re-processing of processed assets during the next cycle
  * by setting skillsoft_report_results processed filed to 0
 * @param string $assetid the skillsoft assetid
 * @return boolean
 */
function skillsoft_reset_processed($assetid) {
    global $DB;
    $conditions = array("assetid"=>$assetid, "processed"=>1);
    return $DB->set_field('skillsoft_report_results', 'processed', 0, $conditions);
}


/**
 * Creates a new sessionid key.
 * @param int $userid
 * @param int $skillsoftid
 * @return string access key value
 */
function skillsoft_create_sessionid($userid, $skillsoftid) {
	global $DB;

	$key = new stdClass();
	$key->skillsoftid      = $skillsoftid;
	$key->userid        = $userid;
	$key->timecreated   = time();

	$key->sessionid = md5($skillsoftid.'_'.$userid.'_'.$key->timecreated.random_string(40)); // something long and unique
	$conditions = array('sessionid'=>$key->sessionid);
	while ($DB->record_exists('skillsoft_session_track', $conditions)) {
		// must be unique
		$key->sessionid     = md5($skillsoftid.'_'.$userid.'_'.$key->timecreated.random_string(40));
	}

	if (!$DB->insert_record('skillsoft_session_track', $key)) {
		error('Can not insert new sessionid');
	}

	return $key->sessionid;
}

/**
 * Checks a sessionid key.
 * @param string $sessionid the skillsoft session_id
 * @return object $key
 */

function skillsoft_check_sessionid($sessionid) {
	global $DB;

	$conditions = array('sessionid'=>$sessionid);
	$key = $DB->get_record('skillsoft_session_track', $conditions);

	return $key;
}

/**
 * Given an skillsoft object this will return
 * the HTML snippet for displaying the Launch Button
 * or output the HTML based on value of $return
 * @param object $skillsoft
 * @param boolean $return
 * @return string $output or null
 */
function skillsoft_view_display($skillsoft, $user, $return=false) {
	global $CFG;
	if (stripos($skillsoft->launch,'?') !== false) {
		$connector = '&';
	} else {
		$connector = '?';
	}

	$element = "";

	/* We need logic here that if SSO url defined we use this */
	if (!$CFG->skillsoft_usesso && strtolower($skillsoft->assetid) != 'sso') {
		//skillsoft_ssourl is not defined so do AICC
		$newkey = skillsoft_create_sessionid($user->id, $skillsoft->id);
		
		$launcher = $skillsoft->launch;
		//Section 508 Enhancement - add x508 value of $user->screenreader
		if (isset($user->screenreader) && $user->screenreader == 1) {
			$launcher .= $connector.'x508=1';
		}
		$launcher .= $connector.'aicc_sid='.$newkey.'&aicc_url='.$CFG->wwwroot.'/mod/skillsoft/aicchandler.php';
		
		//$options = "'".$CFG->skillsoft_aiccwindowsettings."'";
		$options = "'".$skillsoft->aiccwindowsettings."'";

		/*
	 	* TODO: WE NEED LOGIC HERE TO HANDLE ADDING A NEW ATTEMPT WHEN USING TRACK TO LMS
	 	*/
		if ($CFG->skillsoft_trackingmode == TRACK_TO_LMS) {
			//Get last attempt
			if ($lastsession = skillsoft_get_tracks($skillsoft->id, $user->id)) {
				//Add the last $attempt as a hidden field
				$element.= "<input type=\"hidden\" name=\"attempt\" id=\"attempt\" value=\"".$lastsession->attempt."\" >";
				//Get completed status of lastattempt
				$completed = isset($lastsession->{'[SUMMARY]completed'}) ? userdate($lastsession->{'[SUMMARY]completed'}):null;
				if (!is_null($completed)) {
					//Overwrite the $element taht was hidden input with a checkbox option
					$element.= "<div id=\"restart\" name=\"restart\"><input type=\"checkbox\" name=\"startover\" id=\"startover\" value=\"".($lastsession->attempt+1)."\" >".get_string('skillsoft_newattempt','skillsoft')."<br/></div>";
				}
			} else {
				$element.= "<input type=\"hidden\" name=\"attempt\" id=\"attempt\" value=\"1\" >";
			}
		} else {
			$element.= "<input type=\"hidden\" name=\"attempt\" id=\"attempt\" value=\"1\" >";
		}
	} else {
		//we have skillsoft_ssourl so we replace {0} with $skillsoft->id
		//$launcher = sprintf($CFG->skillsoft_ssourl,$skillsoft->assetid);
		$launcher = sprintf($CFG->skillsoft_ssourl,$skillsoft->id);
		$options = "''";
		$element.= "<input type=\"hidden\" name=\"attempt\" id=\"attempt\" value=\"1\" >";
	}
	//Should look at making this call a JavaScript, that we include in the page
	$element.= "<input type=\"button\" value=\"". get_string('skillsoft_enter','skillsoft') ."\" onclick=\"return openAICCWindow('$launcher', 'courseWindow',$options, false);\" />";

	if ($return) {
		return $element;
	} else {
		echo $element;
	}
}

/**
 * Insert values into the skillsoft_au_track table
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $element
 * @param $value
 * @return bool true if succesful
 */
function skillsoft_insert_track($userid,$skillsoftid,$attempt,$element,$value) {
	global $DB;
	$id = null;

	//Work to support multiple attempts
	//$attempt = 1;

	/* 13-SEP-2013
	 * Added error trap to convert $value=NULL to null string ""
	 */
	if ($value===NULL) {
		$value = "";
	}
	
	$params = array($userid,$skillsoftid,$attempt,$element);
	if ($track = $DB->get_record_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=? AND element=?",$params)) {
	
		$track->value = $value;
		$track->timemodified = time();
		$id = $DB->update_record('skillsoft_au_track',$track);
	} else {
		$track = new stdClass();
		$track->userid = $userid;
		$track->skillsoftid = $skillsoftid;
		$track->attempt = $attempt;
		$track->element = $element;
		//$track->value = addslashes($value);
		$track->value = $value;
		$track->timemodified = time();
		$id = $DB->insert_record('skillsoft_au_track',$track);
	}

	//if we have a best score OR we have passed/completed status then update the gradebook
	if ( strstr($element, ']bestscore') ||
	(strstr($element,']lesson_status') && (substr($track->value,0,1) == 'c' || substr($track->value,0,1) == 'p'))
	) {
		$conditions = array('id'=> $skillsoftid);
		$skillsoft = $DB->get_record('skillsoft', $conditions);
		include_once('lib.php');
		skillsoft_update_grades($skillsoft, $userid);
	}
	//print_object($track);
	return $id;
}

/**
 * setFirstAccessDate
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $time
 * @return bool true if succesful
 */
function skillsoft_setFirstAccessDate($userid,$skillsoftid,$attempt,$time) {
	global $DB;
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$element = '[SUMMARY]firstaccess';
	$params = array($userid,$skillsoftid,$attempt,$element);
	if ($track = $DB->get_record_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=? AND element=?",$params)) {
		//We have value so do nothing
	} else {
		$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, '[SUMMARY]firstaccess', $time);
	}
	return $id;
}

/**
 * setLastAccessDate
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $time
 * @return bool true if succesful
 */
function skillsoft_setLastAccessDate($userid,$skillsoftid,$attempt,$time) {
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, '[SUMMARY]lastaccess', $time);
	return $id;
}

/**
 * setCompletedDate
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $time
 * @return bool true if succesful
 */
function skillsoft_setCompletedDate($userid,$skillsoftid,$attempt,$time) {
	global $DB;
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$element = '[SUMMARY]completed';
	$params = array($userid,$skillsoftid,$attempt,$element);
	if ($track = $DB->get_record_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=? AND element=?",$params)) {
		//We have value so do nothing
	} else {
		$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, '[SUMMARY]completed', $time);
	}
	return $id;
}


/**
 * setAccessCount
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @return bool true if succesful
 */
function skillsoft_setAccessCount($userid,$skillsoftid,$attempt,$value=0) {
	global $DB;
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$element = '[SUMMARY]accesscount';
	if ($value == 0 ) {
			
		$params = array($userid,$skillsoftid,$attempt,$element);
		if ($track = $DB->get_record_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=? AND element=?",$params)) {
			//We have value so increment it
			$accesscount = $track->value;
			$accesscount++;
			$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, $accesscount);
		} else {
			$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, 1);
		}
	} else {
		$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, $value);
	}
	return $id;
}


/**
 * setFirstScore
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $score
 * @return bool true if succesful
 */
function skillsoft_setFirstScore($userid,$skillsoftid,$attempt,$score) {
	global $DB;
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$element = '[SUMMARY]firstscore';
	if ($score != 0) {
		$params = array($userid,$skillsoftid,$attempt,$element);
		if ($track = $DB->get_record_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=? AND element=?",$params)) {
			//We have value so do nothing
		} else {
			$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, $score);
		}
	}
	return $id;
}

/**
 * setCurrentScore
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $score
 * @return bool true if succesful
 */
function skillsoft_setCurrentScore($userid,$skillsoftid,$attempt,$score) {
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$element = '[SUMMARY]currentscore';
	if ($score != 0) {
		$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, $score);
	}
	return $id;
}

/**
 * setBestScore
 *
 * @param $userid
 * @param $skillsoftid
 * @param $attempt
 * @param $score
 * @return bool true if succesful
 */
function skillsoft_setBestScore($userid,$skillsoftid,$attempt,$score) {
	global $DB;
	$id = null;
	//Work to support multiple attempts
	//$attempt = 1;
	$element = '[SUMMARY]bestscore';
	
	if ($score != 0) {
		$params = array($userid,$skillsoftid,$attempt,$element);
		if ($track = $DB->get_record_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=? AND element=?",$params)) {
			//We this score is higher
			$currentscore =  $track->value;
			if ($score > $currentscore) {
				$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, $score);
			}
		} else {
			$id = skillsoft_insert_track($userid, $skillsoftid, $attempt, $element, $score);
		}
	}
	return $id;
}

/**
 * @param $skillsoftid
 * @param $userid
 * @return value representing last attempt by user for asset
 */
function skillsoft_get_last_attempt($skillsoftid, $userid) {
	global $DB;
	/// Find the last attempt number for the given user id and scorm id
	$conditions = array('userid'=> $userid, 'skillsoftid'=> $skillsoftid);

	if ($lastattempt = $DB->get_record('skillsoft_au_track', $conditions, 'max(attempt) as a')) {
		if (empty($lastattempt->a)) {
			return '0';
		} else {
			return $lastattempt->a;
		}
	}
}

/**
 * @param $skillsoftid
 * @param $userid
 * @param $attempt
 * @return object representing all values for user and skillsoft activity in skillsoft_au_track
 */
function skillsoft_get_tracks($skillsoftid,$userid,$attempt='') {
	/// Gets all tracks of specified sco and user
	global $CFG,$DB;

	//Work to support multiple attempts
	//$attempt = 1;

	if (empty($attempt)) {
		$attempt = skillsoft_get_last_attempt($skillsoftid,$userid);
		if ($attempt == 0) {
			$attempt = 1;
		}
	}

	$params=array($userid,$skillsoftid,$attempt);
	if ($tracks = $DB->get_records_select('skillsoft_au_track',"userid=? AND skillsoftid=? AND attempt=?",$params,'element ASC')) {
		$usertrack = new stdClass();
		$usertrack->userid = $userid;
		$usertrack->skillsoftid = $skillsoftid;
		$usertrack->score_raw = '';
		$usertrack->status = '';
		$usertrack->total_time = '00:00:00';
		$usertrack->session_time = '00:00:00';
		$usertrack->timemodified = 0;
		$usertrack->attempt = $attempt;
		foreach ($tracks as $track) {
			$element = $track->element;
			$usertrack->{$element} = $track->value;
			if (isset($track->timemodified) && ($track->timemodified > $usertrack->timemodified)) {
				$usertrack->timemodified = $track->timemodified;
			}
		}
		if (is_array($usertrack)) {
			ksort($usertrack);
		}
		return $usertrack;
	} else {
		return false;
	}
}



/**
 * @param object $skillsoft
 * @param int $userid
 * @param int $attempt
 * @return object
 */
function skillsoft_grade_user($skillsoft, $userid, $attempt='') {
	$result = new stdClass();
	$result->score = 0;
	$result->time = 0;

	//We need to get last attempt to grade
	if (empty($attempt)) {
		$attempt = skillsoft_get_last_attempt($skillsoft->id,$userid);
		if ($attempt == 0) {
			$attempt = 1;
		}
	}

	if ($userdata = skillsoft_get_tracks($skillsoft->id, $userid, $attempt)) {
		if (isset($userdata->{'[SUMMARY]bestscore'})) {
			$result->score = $userdata->{'[SUMMARY]bestscore'};
			$result->time = $userdata->{'[SUMMARY]lastaccess'};
		} else {
			$result = NULL;
		}
	}
	return $result;
}


/*************************************************************
 * ODC Functions
 */

/**
 * Insert raw tdr into the skillsoft_tdr
 *
 * @param $tdr
 * @return bool true if succesful
 */
function skillsoft_insert_tdr($rawtdr) {
	global $CFG, $DB;

	//We get a raw SkillSoft TDR which we need to manipluate to fit into
	//Moodle database limits

	$tdr = new stdClass();
	//Set TDRID
	$tdr->tdrid = $rawtdr->id;

	//Convert TimeStamp
	//Define variables that are passed to sscanf
	$year;
	$month;
	$day;
	$hour;
	$min;
	$sec;
	sscanf($rawtdr->timestamp,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
	$tdr->timestamp = mktime($hour,$min,$sec,$month,$day,$year);

	//20110114-Use the new skillsoft_getusername_from_loginname() function
	//This allows us to centralise the "translation" from SkillPort Username
	//to Moodle USERID

	$tdr->userid = skillsoft_getusername_from_loginname($rawtdr->userid);

	$tdr->username = $rawtdr->userid;

	$tdr->assetid = $rawtdr->assetid;

	$tdr->reset = $rawtdr->reset;

	//Addslashes
	$tdr->format = $rawtdr->format;
	$tdr->data = $rawtdr->data;
	$tdr->context = $rawtdr->context;

	$params = array($tdr->tdrid);
	if ($updatetdr = $DB->get_record_select('skillsoft_tdr','tdrid=?',$params)) {
		$id = $DB->update_record('skillsoft_tdr',$tdr);
	} else {
		$id = $DB->insert_record('skillsoft_tdr',$tdr);
	}
	return $id;
}



/**
 * Processes all the TDRs in the datbase updating skillsoft_au_track and gradebook
 *
 * @param $trace false default, flag to indicate if mtrace messages should be sent
 * @return unknown_type
 */
function skillsoft_process_received_tdrs($trace=false) {
	global $CFG,$DB;
	if ($trace) {
		mtrace(get_string('skillsoft_odcprocessinginit','skillsoft'));
	}
	if ($unmatchedtdrs = $DB->get_records_select('skillsoft_tdr','userid=0',null,'tdrid ASC')) {
		foreach ($unmatchedtdrs as $tdr) {
			$tdr->userid = skillsoft_getusername_from_loginname($tdr->username);
			if ($tdr->userid != 0)
			{
				$id = update_record('skillsoft_tdr',$tdr);
			}
		}
	}


	//Select all the unprocessed TDR's
	//We do it this way so that if we create a new Moodle SkillSoft activity for an asset we
	//have TDR's for already we can "catch up"
	$sql  = "SELECT t.id as id, s.id AS skillsoftid, u.id AS userid, t.tdrid, t.timestamp, t.reset, t.format, t.data, t.context, t.processed ";
	$sql .= "FROM {skillsoft_tdr} t INNER JOIN {user} u ON u.id = t.userid INNER JOIN {skillsoft} s ON t.assetid = s.assetid ";
	$sql .= "WHERE t.processed=0 ";
	$sql .= "ORDER BY s.id,u.id,t.tdrid ";

	$attempt=1;
	$lasttdr = new stdClass();
	$lasttdr->skillsoftid = NULL;
	$lasttdr->userid = NULL;

	$rs = $DB->get_recordset_sql($sql);
	if ($rs->valid()) {
		foreach ($rs as $processedtdr) {
			if ($trace) {
				mtrace(get_string('skillsoft_odcprocessretrievedtdr','skillsoft',$processedtdr));
			}
			if ($processedtdr->skillsoftid != $lasttdr->skillsoftid || $processedtdr->userid != $lasttdr->userid) {
				$conditions = array('id'=> $processedtdr->skillsoftid);
				$skillsoft = $DB->get_record('skillsoft',$conditions);
				$conditions2 = array('id'=> $processedtdr->userid);
				$user = $DB->get_record('user',$conditions2);
				$handler = new aicchandler($user,$skillsoft,$attempt,$CFG->skillsoft_strictaiccstudentid);
			}

			
			//Process the TDR as AICC Data
			if ($skillsoft->completable) {
				$handler->processtdr($processedtdr, $attempt);
			} else {
				$handler->processtdr($processedtdr, 1);
			}
			$processedtdr->processed = 1;
			$id = $DB->update_record('skillsoft_tdr',$processedtdr);
			$lasttdr = $processedtdr;
		}
	}
	$rs->close();

	if ($trace) {
		mtrace(get_string('skillsoft_odcprocessingend','skillsoft'));
	}
}


/**
 * This function is is the key to importing usage data from SkillPort
 * It will attempt to convert the SkillPort username to the equivalent
 * Moodle $user->id, if it fails the is is returned as 0
 *
 * @param $skillport_loginname
 * @return $moodle_userid
 */
function skillsoft_getusername_from_loginname($skillport_loginname) {
	global $CFG, $DB;

	//If the PREFIX is configured we strip this from the skillport loginname
	//Before we attempt to match it
	if ($CFG->skillsoft_accountprefix != '') {
		//Check we have the prefix in the username
		$pos = stripos($skillport_loginname, $CFG->skillsoft_accountprefix);
		if ($pos !== false && $pos == 0) {
			$skillport_loginname = substr($skillport_loginname,strlen($CFG->skillsoft_accountprefix));
		}
	}

	//We check if we are using the IDENTIFIER_USERID that the
	//SkillPort loginname is numeric before we attempt to match it
	if ($CFG->skillsoft_useridentifier == IDENTIFIER_USERID) {
		if (!is_numeric($skillport_loginname) ) {
			return 0;
			break;
		}
	}

	//Now we attempt to get the Moodle userid by looking up the user
	//We return the Moodle USERID or 0 if no match
	$conditions = array($CFG->skillsoft_useridentifier=>$skillport_loginname);
	if ($user = $DB->get_record('user',$conditions)) {
		return $user->id;
	} else {
		return 0;
	}
}


/*
 * CUSTOM REPORT HANDLING FUNCTIONS
 */


/**
 * This function will convert numeric byte to KB, MB etc
 *
 * @param int $bytes - The numeric bytes
 * @return string Formatted String representation
 */
function byte_convert($bytes)
{
	$symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$exp = 0;
	$converted_value = 0;
	if( $bytes > 0 )
	{
		$exp = floor( log($bytes)/log(1024) );
		$converted_value = ( $bytes/pow(1024,floor($exp)) );
	}
	return sprintf( '%.2f '.$symbol[$exp], $converted_value );
}

/**
 * Insert values into the skillsoft_report_track table
 *
 * @return bool true if succesful
 */
function skillsoft_insert_customreport_requested($handle,$startdate='',$enddate='') {
	global $DB;
	$id = null;
	$report = new stdClass();
	$report->handle = $handle;
	$report->startdate = $startdate;
	$report->enddate = $enddate;
	$report->timerequested = time();
	$id = $DB->insert_record('skillsoft_report_track',$report);
	return $id;
}

/**
 * Record report ready
 *
 * @return bool true if succesful
 */
function skillsoft_update_customreport_polled($handle,$url) {
	global $DB;
	$id = null;
	$params = array($handle);
	if ($report = $DB->get_record_select('skillsoft_report_track','handle=?',$params)) {
		$report->url = $url;
		$report->polled = true;
		$report->timepolled = time();
		$id = $DB->update_record('skillsoft_report_track',$report);
	}
	return $id;
}

/**
 * Record report downloaded
 *
 * @return bool true if succesful
 */
function skillsoft_update_customreport_downloaded($handle,$localpath) {
	global $DB;
	$id = null;
	$params = array($handle);
	if ($report = $DB->get_record_select('skillsoft_report_track','handle=?',$params)) {
		$report->localpath = $localpath;
		$report->downloaded = true;
		$report->timedownloaded = time();
		$id = $DB->update_record('skillsoft_report_track',$report);
	}
	return $id;
}

/**
 * Record report imported
 *
 * @return bool true if succesful
 */
function skillsoft_update_customreport_imported($handle) {
	global $DB;
	$id = null;
	$params = array($handle);
	if ($report = $DB->get_record_select('skillsoft_report_track','handle=?',$params)) {
		$report->imported = true;
		$report->timeimported = time();
		$id = $DB->update_record('skillsoft_report_track',$report);
	}
	return $id;
}

/**
 * Record report processed
 *
 * @return bool true if succesful
 */
function skillsoft_update_customreport_processed($handle) {
	global $DB;
	$id = null;
	$params = array($handle);
	if ($report = $DB->get_record_select('skillsoft_report_track','handle=?',$params)) {
		$report->processed = true;
		$report->timeprocessed = time();
		$id = $DB->update_record('skillsoft_report_track',$report);
	}
	return $id;
}

/**
 * Delete the entry in skillsoft_report_track table
 *
 * @return bool true if succesful
 */
function skillsoft_delete_customreport($handle) {
	global $DB;

	$id = null;
	$conditions = array('handle'=>$handle);
	$id = $DB->delete_records('skillsoft_report_track',$conditions);
	return $id;
}

/**
 * Converts the CSV data from a row in the custom report which is in
 * array format into an object for easy insert into database
 *
 * It also performs any necessary conversions and validations
 * such as dates to timestamps
 *
 * @param $arraykey		Array of key names
 * @param $arrayvalue	Array of the values
 * @return $object
 */
function ConvertCSVRowToReportResults($arraykey, $arrayvalue) {
	$object = new stdClass();
	//Need to consider if using column position is better rather than heading
	$count = count($arraykey);
	for ($i = 0; $i < $count; $i++) {
		$cleankey = trim(strtolower($arraykey[$i]));

		//Do we have a value or is it null
		if ($arrayvalue[$i])
		{
			$hour=0;
			$min=0;
			$sec=0;
			$month=0;
			$day=0;
			$year=0;

			switch ($cleankey) {
				case 'duration';
				if ( $arrayvalue[$i] > 0 ) {
					$object->duration = $arrayvalue[$i];
				} else {
					$object->duration = 0;
				}
				break;
				case 'courseid';
				$object->assetid = $arrayvalue[$i];
				break;
				case 'firstaccessdate';
				case 'lastaccessdate';
				//Convert TimeStamp 2010-01-29 18:09:18
				sscanf($arrayvalue[$i],"%u-%u-%u %u:%u:%u",$year,$month,$day,$hour,$min,$sec);
				$object->$cleankey = mktime($hour,$min,$sec,$month,$day,$year);
				break;
				case 'completiondate';
				//Convert TimeStamp 2010-01-29 18:09:18
				sscanf($arrayvalue[$i],"%u-%u-%u %u:%u:%u",$year,$month,$day,$hour,$min,$sec);
				$object->completeddate = mktime($hour,$min,$sec,$month,$day,$year);
				break;
				case 'timesaccessed';
				$object->accesscount = $arrayvalue[$i];
				break;
				case 'overallpreassess';
				$object->firstscore = $arrayvalue[$i];
				break;
				case 'overallhigh';
				$object->bestscore = $arrayvalue[$i];
				break;
				case 'overallcurrent';
				$object->currentscore = $arrayvalue[$i];
				break;
				case 'coursestatus';
				switch (strtolower($arrayvalue[$i]))
				{
					case 'started';
					$object->lessonstatus = 'incomplete';
					break;
					case 'completed';
					$object->lessonstatus = 'completed';
					break;
				}
				break;
				default:
					//Here we apply custom logic
					$object->$cleankey = $arrayvalue[$i];
			}
		}
	}
	return $object;
}

/**
 * Insert report_results into the skillsoft_report_results
 *
 * @param $report_results
 * @return bool true if succesful
 */
function skillsoft_insert_report_results($report_results) {
	global $CFG, $DB;
	$success = null;

	//Need to determine the moodle userid based on loginname
	//$report_results->userid = skillsoft_getusername_from_loginname($report_results->loginname);
	$report_results->userid = 0;

	//Update to insert unique records BY loginname, assetid and firstaccessdate to handle multiple completions
	//if ($update_results = get_record_select('skillsoft_report_results',"loginname='$report_results->loginname' and assetid='$report_results->assetid'")) {
	
	$params=array($report_results->loginname,$report_results->assetid,$report_results->firstaccessdate);
	if ($update_results = $DB->get_record_select('skillsoft_report_results','loginname=? and assetid=? and firstaccessdate=?',$params, 'id')) {
		$report_results->id = $update_results->id;
		$report_results->processed = 0;
		$success = $DB->update_record('skillsoft_report_results',$report_results);
	} else {
		$success = $DB->insert_record('skillsoft_report_results',$report_results);
	}
	return $success;
}

/*
 * This function will use OLSA to run a custom report
 *
 * @param bool $trace - Do we output tracing info.
 * @param string $prefix - The string to prefix all mtrace reports with
 * @param string $includetoday - Include todays report, used as a debugging aid
 * @return string $handle - The report handle
 */
function skillsoft_run_customreport($trace=false, $prefix='    ', $includetoday=false) {
	global $CFG;

	$mprefix = is_null($prefix) ? "    " : $prefix;
	$starttime = microtime(true);
	$handle = '';

	if ($trace){
		mtrace($mprefix.get_string('skillsoft_customreport_run_start','skillsoft'));
	}

	$startdate=$CFG->skillsoft_reportstartdate;

	if ($startdate == '' || $startdate==strtotime(date("d-M-Y"))) {
		$startdate = "01-Jan-2000";
		set_config('skillsoft_reportstartdate', $startdate);
	}
	$startdateticks = strtotime($startdate);

	if ($includetoday) {
		//End date is "today"
		$enddateticks = strtotime(date("d-M-Y"));
	} else {
		//End date is "yesterday"
		$enddateticks = strtotime(date("d-M-Y") . " -1 day");
	}
	$enddate = date("d-M-Y",$enddateticks);

	mtrace($mprefix.get_string('skillsoft_customreport_run_startdate','skillsoft', date("c",$startdateticks)));
	mtrace($mprefix.get_string('skillsoft_customreport_run_enddate','skillsoft', date("c",$enddateticks)));

	if (($startdateticks == $enddateticks) && !$includetoday) {
		//The enddate has already been retrieved so do nothing
		mtrace($mprefix.get_string('skillsoft_customreport_run_alreadyrun','skillsoft'));
	} else {
		//$initresponse = UD_InitiateCustomReportByUserGroups('skillsoft',$startdate,$enddate);
		$initresponse = UD_InitiateCustomReportByUsers('',$startdate,$enddate);
		
		if ($initresponse->success) {
			$handle = $initresponse->result->handle;
			$id=skillsoft_insert_customreport_requested($handle,$startdate,$enddate);
			mtrace($mprefix.get_string('skillsoft_customreport_run_response','skillsoft',$initresponse->result->handle));
		} else {
			mtrace($mprefix.get_string('skillsoft_customreport_run_initerror','skillsoft',$initresponse->errormessage));
		}
	}
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	if ($trace){
		mtrace($mprefix.get_string('skillsoft_customreport_run_end','skillsoft').' (took '.$duration.' seconds)');
	}
	return $handle;
}

/*
 * This function will use OLSA to poll if the report is ready
 *
 * @param string $handle - The report handle to poll for
 * @param bool $trace - Do we output tracing info.
 * @param string $prefix - The string to prefix all mtrace reports with
 * @return string The URL of the report or ""
 */
function skillsoft_poll_customreport($handle, $trace=false, $prefix='    ') {
	global $CFG;
	$starttime = microtime(true);
	$reporturl = '';

	if ($trace){
		mtrace($prefix.get_string('skillsoft_customreport_poll_start','skillsoft'));
		mtrace($prefix.$prefix.get_string('skillsoft_customreport_poll_polling','skillsoft',$handle));
	}
	$pollresponse = UTIL_PollForReport($handle);
	if ($pollresponse->success) {
		if ($trace) {
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_poll_ready','skillsoft'));
		}
		$reporturl = $pollresponse->result->olsaURL;
		//Update skillsoft_report_track table
		skillsoft_update_customreport_polled($handle,$reporturl);
	} else if ($pollresponse->errormessage == get_string('skillsoft_olsassoapreportnotready','skillsoft')) {
		//The report is not ready
		if ($trace) {
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_poll_notready','skillsoft'));
		}
	} else if ($pollresponse->errormessage == get_string('skillsoft_olsassoapreportnotvalid','skillsoft',$report->handle)) {
		//The report does not exist so we need to delete this row in report track table
		if ($trace) {
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_poll_doesnotexist','skillsoft'));
		}
		$id=skillsoft_delete_report($report->handle);
	}
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	if ($trace){
		mtrace($prefix.get_string('skillsoft_customreport_poll_end','skillsoft').' (took '.$duration.' seconds)');
	}
	return $reporturl;
}

/**
 * This function will use CURL to download a file
 *
 * @param string $url - The URL we want to download
 * @param string $folder - The folder where we will save it. DEFAULT = temp
 * @param bool $trace - Do we output tracing info.
 * @param string $prefix - The string to prefix all mtrace reports with
 * @return string localpath or NULL on error
 */
function skillsoft_download_customreport($handle, $url, $folder=NULL, $trace=false, $prefix='    ') {
	global $CFG;

	set_time_limit(0);
	$starttime = microtime(true);
	if ($trace) {
		mtrace($prefix.get_string('skillsoft_customreport_download_start', 'skillsoft'));
		mtrace($prefix.$prefix.get_string('skillsoft_customreport_download_url', 'skillsoft',$url));
	}

	$basefolder = str_replace('\\','/', $CFG->tempdir);
	$downloadedfile=NULL;

	if ($folder==NULL) {
		$folder='reports';
	}

	/// Create temp directory if necesary
	if (!make_temp_directory($folder, false)) {
		//Couldn't create temp folder
		if ($trace) {
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_download_createdirectoryfailed', 'skillsoft', $basefolder.'/'.$folder));
		}
		return NULL;
	}

	$filename = basename($url);

	$fp = fopen($basefolder.'/'.$folder.'/'.$filename, 'wb');

	if (!extension_loaded('curl') or ($ch = curl_init($url)) === false) {
		//Error no CURL
		if ($trace) {
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_download_curlnotavailable', 'skillsoft'));
		}
	} else {
		$ch = curl_init($url);
		//Ignore SSL errors
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		
		//Force SSLv3 to workaround Openssl 1.0.1 issue
		//See https://bugs.launchpad.net/ubuntu/+source/curl/+bug/595415
		//curl_setopt($ch, CURLOPT_SSLVERSION, 3); 

		//Force CURL to use TLSv1 or later as SSLv3 deprecated on Skillsoft servers
		//Bug Fix - http://code.google.com/p/moodle2-skillsoft-activity/issues/detail?id=17
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		
		//Setup Proxy Connection

		if (!empty($CFG->proxyhost)) {
			// SOCKS supported in PHP5 only
			if (!empty($CFG->proxytype) and ($CFG->proxytype == 'SOCKS5')) {
				if (defined('CURLPROXY_SOCKS5')) {
					curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				} else {
					curl_close($ch);
					if ($trace) {
						mtrace($prefix.$prefix.get_string('skillsoft_customreport_download_socksproxyerror', 'skillsoft'));
					}
					return NULL;
				}
			}

			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, false);

			if (empty($CFG->proxyport)) {
				curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost);
			} else {
				curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
			}

			if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypassword);
				if (defined('CURLOPT_PROXYAUTH')) {
					// any proxy authentication if PHP 5.1
					curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
				}
			}
		}

		curl_exec($ch);

		// Check if any error occured
		if(!curl_errno($ch))
		{
			$downloadresult = new stdClass();
			$downloadresult->bytes = byte_convert(curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD));
			$downloadresult->total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
			$downloadresult->filepath = $basefolder.'/'.$folder.'/'.$filename;
			fclose($fp);
			if ($trace) {
				mtrace($prefix.$prefix.get_string('skillsoft_customreport_download_result', 'skillsoft' , $downloadresult));
			}
			$downloadedfile = $downloadresult->filepath;
			//Update skillsoft_report_track table
			skillsoft_update_customreport_downloaded($handle,$downloadedfile);
		} else {
			fclose($fp);
			if ($trace) {
				mtrace($prefix.$prefix.get_string('skillsoft_customreport_download_error', 'skillsoft' , curl_error($ch)));
			}
			return NULL;
		}
	}
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	if ($trace) {
		mtrace($prefix.get_string('skillsoft_customreport_download_end', 'skillsoft').' (took '.$duration.' seconds)');
	}
	return $downloadedfile;
}

/*
 * This function will read the downloaded CSV report and import into
 * the 'skillsoft_report_results' table
 *
 * @param string $importfile - The fulpath to the file to import
 * @param bool $trace - Do we output tracing info.
 * @param string $prefix - The string to prefix all mtrace reports with
 * @return bool true for successful import
 */
function skillsoft_import_customreport($handle, $importfile, $trace=false, $prefix='    ') {
	global $CFG,$DB;

	set_time_limit(0);
	$starttime = microtime(true);
	if ($trace){
		mtrace($prefix.get_string('skillsoft_customreport_import_start','skillsoft'));
	}
	$file = new SplFileObject($importfile);
	$file->setFlags(SplFileObject::READ_CSV);
	$rowcounter = -1;
	$insertokay = true;

	$transaction = $DB->start_delegated_transaction();
	do {
		$row = $file->fgetcsv();
		if ($rowcounter == -1) {
			//This is the header row
			$headerrowarray = $row;
		} else {
			if($row[0])
			{
				$report_results = ConvertCSVRowToReportResults($headerrowarray, $row);
				$insertokay = skillsoft_insert_report_results($report_results);
			}
			if ($trace) {
				if (($rowcounter % 1000) == 0) {
					//Output message every 1000 entries
					mtrace($prefix.$prefix.get_string('skillsoft_customreport_import_rowcount','skillsoft', $rowcounter));
				}
			}
		}
		$file->next();
		$rowcounter++;
	} while ($file->valid() && $insertokay);
	$transaction->allow_commit();
	
	if ($insertokay) {
		if ($trace){
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_import_totalrow','skillsoft', $rowcounter));
		}
		unset($file);
		skillsoft_update_customreport_imported($handle);
	} else {
		if ($trace){
			mtrace($prefix.$prefix.get_string('skillsoft_customreport_import_errorrow','skillsoft', $rowcounter));
		}
	}
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	if ($trace){
		mtrace($prefix.get_string('skillsoft_customreport_import_end','skillsoft').' (took '.$duration.' seconds)');
	}
	return $insertokay;
}


/**
 * Processes all the entries imported from custom report in the datbase
 * updating skillsoft_au_track and gradebook
 *
 * @param $trace false default, flag to indicate if mtrace messages should be sent
 * @param string $prefix - The string to prefix all mtrace reports with
 * @return unknown_type
 */
function skillsoft_process_received_customreport($handle, $trace=false, $prefix='    ') {
	global $CFG, $DB;

	set_time_limit(0);
	$starttime = microtime(true);
	if ($trace) {
		mtrace($prefix.get_string('skillsoft_customreport_process_start','skillsoft'));
	}

	//Get a count of records and process in batches
	$conditions = array('userid'=>'0');
	$countofunprocessed = $DB->count_records('skillsoft_report_results',$conditions);

	if ($trace) {
		mtrace($prefix.get_string('skillsoft_customreport_process_totalrecords','skillsoft',$countofunprocessed));
	}

//	$limitfrom=0;
//	$limitnum=1000;
//
//	do {
//		if ($trace) {
//			mtrace($prefix.get_string('skillsoft_customreport_process_batch','skillsoft',$limitfrom));
//		}
//		if ($unmatchedreportresults = $DB->get_records_select('skillsoft_report_results','userid=0',null,'id ASC','*',$limitfrom,$limitnum)) {
//			foreach ($unmatchedreportresults as $reportresults) {
//				$reportresults->userid = skillsoft_getusername_from_loginname($reportresults->loginname);
//				if ($reportresults->userid != 0)
//				{
//					$id = $DB->update_record('skillsoft_report_results',$reportresults);
//				}
//			}
//		}
//		$limitfrom += 1000;
//	} while (($unmatchedreportresults != false) && ($limitfrom < $countofunprocessed));

	//Perform the match of userid using SQL alone
	$sql = "UPDATE {skillsoft_report_results} ";
	$sql .= "SET userid = ";
	$sql .= "(SELECT id FROM {user} WHERE ";
	$sql .= $DB->sql_concat("'".$CFG->skillsoft_accountprefix."'", "{user}.".$CFG->skillsoft_useridentifier);
	$sql .= " = {skillsoft_report_results}.loginname) ";
	$sql .= "WHERE EXISTS ";
	$sql .= "(SELECT id FROM {user} WHERE ";
	$sql .= $DB->sql_concat("'".$CFG->skillsoft_accountprefix."'", "{user}.".$CFG->skillsoft_useridentifier);
	$sql .= " = {skillsoft_report_results}.loginname) ";
	
	$DB->execute($sql);
	
	//Select all the unprocessed Custom Report Results's
	//We do it this way so that if we create a new Moodle SkillSoft activity for an asset we
	//have TDR's for already we can "catch up"
	$sql  = "SELECT t.id as id, s.id AS skillsoftid, u.id AS userid, t.firstaccessdate, t.lastaccessdate, t.completeddate, t.firstscore, t.currentscore, t.bestscore, t.lessonstatus, t.duration, t.accesscount, t.processed, t.attempt ";
	$sql .= "FROM {skillsoft_report_results} t ";
	$sql .= "INNER JOIN {user} u ON u.id = t.userid ";
	$sql .= "INNER JOIN {skillsoft} s ON t.assetid = s.assetid ";
	$sql .= "WHERE t.processed=0 ";
	$sql .= "ORDER BY s.id,u.id,t.firstaccessdate";


	$lastreportresults = new stdClass();
	$lastreportresults->skillsoftid = NULL;
	$lastreportresults->userid = NULL;

	$rs = $DB->get_recordset_sql($sql);
	if ($rs->valid()) {
		foreach ($rs as $reportresults) {
			if ($trace) {
				mtrace($prefix.$prefix.get_string('skillsoft_customreport_process_retrievedresults','skillsoft',$reportresults));
			}

			if ($reportresults->attempt != 0)
			{
				$attempt=$reportresults->attempt;
			}  else {
				$attempt=skillsoft_get_last_attempt($reportresults->skillsoftid , $reportresults->userid );
				//Check if "last attempt" is first attempt if not increment
				if ($attempt == 0) {
					$attempt = 1;
				} else {
					$attempt = $attempt + 1;
				}
			}

			if ($reportresults->skillsoftid != $lastreportresults->skillsoftid || $reportresults->userid != $lastreportresults->userid) {
				$skillsoft = $DB->get_record('skillsoft',array('id'=>$reportresults->skillsoftid));
				$user = $DB->get_record('user',array('id'=>$reportresults->userid));
				$handler = new aicchandler($user,$skillsoft,$attempt,$CFG->skillsoft_strictaiccstudentid);
			}

			//Process the ReportResults as AICC Data
			if ($skillsoft->completable) {
				$handler->processreportresults($reportresults,$attempt);
			} else {
				//Only update attempt 1
				$handler->processreportresults($reportresults,1);
			}
			$reportresults->processed = 1;
			$reportresults->attempt = $attempt;
			$lastreportresults = $reportresults;

			$gradeupdate=skillsoft_update_grades($skillsoft, $user->id);

			$id = $DB->update_record('skillsoft_report_results',$reportresults);
		}
	}
	$rs->close();

	//Update the skillsoft_report_track
	skillsoft_update_customreport_processed($handle);
	
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	if ($trace) {
		mtrace($prefix.get_string('skillsoft_customreport_process_end','skillsoft').' (took '.$duration.' seconds)');
	}
}

function skillsoft_get_moodle_version_major() { 
     global $CFG; 
  
     $version_array = explode('.', $CFG->version); 
     return $version_array[0]; 
} 

function skillsoft_event_log_standard($event_type, $skillsoft, $context, $cm) {
    $context = context_module::instance($cm->id);
    $event_properties = array('context' => $context, 'objectid' => $skillsoft->id);

    switch ($event_type) {
        case SKILLSOFT_EVENT_ACTIVITY_VIEWED:
            $event = \mod_skillsoft\event\course_module_viewed::create($event_properties);
            break;
        case SKILLSOFT_EVENT_ACTIVITY_MANAGEMENT_VIEWED:
            $event = \mod_skillsoft\event\course_module_instance_list_viewed::create($event_properties);
            break;
        case SKILLSOFT_EVENT_REPORT_VIEWED:
            $event = \mod_skillsoft\event\report_viewed::create($event_properties);
            break;            
    }
    $event->trigger();
}

function skillsoft_event_log_legacy($event_type, $skillsoft, $context, $cm) {
    global $DB;

    switch ($event_type) {
        case SKILSOFT_EVENT_ACTIVITY_VIEWED:
            $event = 'view';
            break;
        case SKILLSOFT_EVENT_ACTIVITY_MANAGEMENT_VIEWED:
            $event = 'view all';
            break;
        case constant(SKILLSOFT_EVENT_REPORT_VIEWED) :    
        	$event = "view report";
        	break;
        default:
            return;
    }
    $course = $DB->get_record('course', array('id' => $skillsoft->course), '*', MUST_EXIST);

    add_to_log($course->id, 'skillsoft', $event, '', $skillsoft->name, $cm->id);
}

function skillsoft_event_log($event_type, $skillsoft, $context, $cm) {
    global $CFG;

    $version_major = skillsoft_get_moodle_version_major();
    if ( $version_major < '2014051200' ) {
        //This is valid before v2.7
        skillsoft_event_log_legacy($event_type, $skillsoft, $context, $cm);

    } else {
        //This is valid after v2.7
        skillsoft_event_log_standard($event_type, $skillsoft, $context, $cm);
    }
}