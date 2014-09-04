<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
require_once($CFG->dirroot.'/mod/skillsoft/aiccmodel.php');
require_once($CFG->dirroot.'/mod/skillsoft/olsalib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * This function checks if the passed $Haystack starts with $Needle
 *
 * @param string $Haystack the string we are checking starts with
 * @param string $Needle the string it should starts with
 */
function startsWith($Haystack, $Needle){
	// Recommended version, using strpos
	return strpos($Haystack, $Needle) === 0;
}

/**
 * Simulates Java StringTokenizer
 */
class StringTokenizer {
	private $token;
	private $delim;

	/**
	 * Constructs a string tokenizer for the specified string
	 * @param string $str String to tokenize
	 * @param string $delim The set of delimiters (the characters that separate tokens)
	 * specified at creation time, default to ' '
	 */
	public function __construct($str, $delim=' ')
	{
		$this->token = strtok($str, $delim);
		$this->delim = $delim;
	}

	/**
	 * @return unknown_type
	 */
	public function __destruct()
	{
		unset($this);
	}

	/**
	 * Tests if there are more tokens available from this tokenizer's string. It
	 * does not move the internal pointer in any way. To move the internal pointer
	 * to the next element call nextToken()
	 * @return boolean - true if has more tokens, false otherwise
	 */
	public function hasMoreTokens()
	{
		return ($this->token !== false);
	}

	/**
	 * Returns the next token from this string tokenizer and advances the internal
	 * pointer by one.
	 * @return string - next element in the tokenized string
	 */
	public function nextToken()
	{
		$current = $this->token;
		$this->token = strtok($this->delim);
		return $current;
	}
}

class aicchandler {
	public $cmi;
	public $stokenizer;

	private $key;
	private $block;
	private $value;

	private $user;
	private $skillsoft;
	private $attempt;

	public function __construct($user, $skillsoft, $attempt=1, $enforcestrictstudentid=0)
	{
		$this->cmi = new cmi($enforcestrictstudentid);
		$this->user = $user;
		$this->skillsoft = $skillsoft;
		$this->attempt = $attempt;
		$this->getdata();
	}

	/**
	 * Return a UNIX time (epoch) value from the ISO8601 formatted string
	 *
	 * @param string $tstamp iso8601 formattted string
	 * @return number
	 */
	function isotoepoch($tstamp) {
		//converts ISODATE to unix date
		//1984-09-01T14:21:31Z
		sscanf($tstamp,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
		$newtstamp=mktime($hour,$min,$sec,$month,$day,$year);
		return $newtstamp;
	}

	/**
	 * Convert the supplied seconds value into a CMITimeSpan representation
	 * hh:mm:ss
	 *
	 * @param int $time
	 * @return string
	 */
	function Sec2Time($time){
		$value = array(
	      "hours" => 0,
	      "minutes" => 0, "seconds" => 0,
		);
		if($time >= 3600){
			$value["hours"] = floor($time/3600);
			$time = ($time%3600);
		}
		if($time >= 60){
			$value["minutes"] = floor($time/60);
			$time = ($time%60);
		}
		$value["seconds"] = floor($time);

		if ($value["hours"] < 10) {
			$hours = '0' . $value["hours"];
		} else {
			$hours = '' . $value["hours"];
		}
	  
		if ($value["minutes"] < 10) {
			$mins = '0' .  $value["minutes"];
		} else {
			$mins = '' . $value["minutes"];
		}

		if ($value["seconds"] < 10) {
			$secs = '0'. $value["seconds"];
		} else {
			$secs = '' . $value["seconds"];
		}

		return $hours . ":" . $mins . ":" . $secs;
	}

	/**
	 * Add two CMITimeSpan values
	 *
	 * Adds together two CMITimeSpan (HH:MM:SS.SS) values
	 *
	 * @param CMITimeSpan $a
	 * @param CMITimeSpan $b
	 * @return CMITimeSpan
	 */
	function add_time($a, $b) {
		$aes = explode(':',$a);
		$bes = explode(':',$b);
		$aseconds = explode('.',$aes[2]);
		$bseconds = explode('.',$bes[2]);
		$change = 0;

		$acents = 0;  //Cents
		if (count($aseconds) > 1) {
			$acents = $aseconds[1];
		}
		$bcents = 0;
		if (count($bseconds) > 1) {
			$bcents = $bseconds[1];
		}
		$cents = $acents + $bcents;
		$change = floor($cents / 100);
		$cents = $cents - ($change * 100);
		if (floor($cents) < 10) {
			$cents = '0'. $cents;
		}

		$secs = $aseconds[0] + $bseconds[0] + $change;  //Seconds
		$change = floor($secs / 60);
		$secs = $secs - ($change * 60);
		if (floor($secs) < 10) {
			$secs = '0'. $secs;
		}

		$mins = $aes[1] + $bes[1] + $change;   //Minutes
		$change = floor($mins / 60);
		$mins = $mins - ($change * 60);
		if ($mins < 10) {
			$mins = '0' .  $mins;
		}

		$hours = $aes[0] + $bes[0] + $change;  //Hours
		if ($hours < 10) {
			$hours = '0' . $hours;
		}

		if ($cents != '0') {
			return $hours . ":" . $mins . ":" . $secs . '.' . $cents;
		} else {
			return $hours . ":" . $mins . ":" . $secs;
		}
	}

	/**
	 * This function populates the elements from the AICC Core section
	 * we have parsed into the appropriate datamodel elements
	 *
	 * @uses $this->key
	 * @uses $this->value
	 * @return bool if the value was succseffully copied to datamodel.
	 */
	private function setAICCCore()
	{
		switch($this->key) {
			case 'student_id':
				$this->cmi->core->student_id = $this->value;
				return true;
				break;
			case 'student_name':
				$this->cmi->core->student_name = $this->value;
				return true;
				break;
			case 'lesson_location':
				$this->cmi->core->lesson_location = $this->value;
				return true;
				break;
			case 'credit':
				$this->cmi->core->credit = $this->value;
				return true;
				break;
			case 'lesson_status':
				//We still need to parse it for EXIT flag
				$this->cmi->core->lesson_status = $this->value;
				return true;
				break;
			case 'time':
				//We need logic on how we handle this but for now just do as session_time
				$this->cmi->core->session_time = $this->value;
				return true;
				break;
			case 'score':
				//We need logic on how we handle this for min/max but for now just do as score raw
				$this->cmi->core->score->raw = $this->value;
				return true;
				break;
		}
		return false;
	}

	/**
	 * This function populates the elements from the AICC CoreLesson section
	 * we have parsed into the appropriate datamodel elements
	 *
	 * @return bool if the value was succseffully copied to datamodel.
	 */
	private function setAICCCoreLesson()
	{
		if(($this->block != NULL) && ($this->block == "[core_lesson]"))
		{
			if ($this->cmi->core->core_lesson != "") {
				$this->cmi->core->core_lesson = $this->cmi->core->core_lesson."\r\n".$this->value;
			} else {
				$this->cmi->core->core_lesson = $this->value;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * NOT IMPLEMENTED - PlaceHolder for future implementation
	 * This function populates the elements from the AICC Objectives Status section
	 * we have parsed into the appropriate datamodel elements
	 *
	 * @return bool if the value was succseffully copied to datamodel.
	 */
	private function setAICCObjectivesStatus() {
		return false;
	}

	/**
	 * NOT IMPLEMENTED - PlaceHolder for future implementation
	 * This function populates the elements from the AICC Comments section
	 * we have parsed into the appropriate datamodel elements
	 *
	 * @return bool if the value was succseffully copied to datamodel.
	 */
	private function setAICCComments() {
		if(($this->block != NULL) && ($this->block == "[comments]"))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * NOT IMPLEMENTED - PlaceHolder for future implementation
	 * This function populates the elements from the AICC Student_Data section
	 * we have parsed into the appropriate datamodel elements
	 *
	 * @return bool if the value was succseffully copied to datamodel.
	 */
	private function setAICCStudentData() {
		if(($this->block != NULL) && ($this->block == "[student_data]"))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * NOT IMPLEMENTED - PlaceHolder for future implementation
	 * This function populates the elements from the AICC Student_Data section
	 * we have parsed into the appropriate datamodel elements
	 *
	 * @return bool if the value was succseffully copied to datamodel.
	 */
	private function setAICCStudentPreferences() {
		if(($this->block != NULL) && ($this->block == "[student_preferences]"))
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Clear the CMI datamodel
	 *
	 * This function clears the CMI object
	 */
	public function cleardata() {
		$this->cmi = new cmi();
	}

	/**
	 * Parse AICC HACP
	 *
	 * This function parses an AICC HACP putparam
	 * @param string $aiccdata - The contents of an AICC putparam
	 */
	public function parsehacp($aiccdata)
	{

		$AICCTokens = new StringTokenizer($aiccdata, "\r\n");
		$AICCToken = "";

		while ($AICCTokens->hasMoreTokens()) {
			if(!startsWith($AICCToken, "[")) {
				$AICCToken = trim($AICCTokens->nextToken());
			}
			if(!$AICCToken == "")
			{
				//Find block and set
				if(startsWith($AICCToken, "["))
				{
					$this->key = strtolower($AICCToken);
					$this->block = strtolower($AICCToken);
					if($AICCTokens->hasMoreTokens()) {
						$AICCToken = trim($AICCTokens->nextToken());
					} else {
						return;
					}
				}

				if ($this->block == "[core_lesson]" || $this->block == "[comments]")
				{
					if(!startsWith($AICCToken,"[") && (!$AICCToken ==""))
					{
						$this->value = $AICCToken;
						if ($this->setAICCCoreLesson() || !$this->setAICCComments())
						{

						};
					}
				} else {
					$equal = strpos($AICCToken, '=');
					if ($equal != 0) {
						$this->key = strtolower(trim(substr($AICCToken,0,$equal)));
						$this->value = trim(substr($AICCToken,$equal+1));
					}
					//&& !setAICCEvaluation() && !setAICCObjectivesStatus() && !setAICCStudentData() && !setAICCStudentDemographics()
						
					if ( !$this->setAICCCore() && !$this->setAICCCoreLesson() && !$this->setAICCObjectivesStatus() && !$this->setAICCStudentData()){
						if (!$this->setAICCStudentPreferences()){
								
						}
					}
				}
			}
		}
	}

	/**
	 * Retrieve existing data
	 *
	 * This function retrieves existing data from database and populates
	 * the CMI model
	 */
	public function getdata()
	{
		global $CFG;
		
		//Get data from database
		$userdata=skillsoft_get_tracks($this->skillsoft->id,$this->user->id,$this->attempt);

		//Populate the cmi datamodel

		//Set the student ID based on


		//Need to include logic for "prefix" here

		$this->cmi->core->student_id = $CFG->skillsoft_accountprefix.$this->user->{$CFG->skillsoft_useridentifier};

		$this->cmi->core->student_name = $this->user->lastname .', '. $this->user->firstname;

		if ($this->cmi->core->lesson_mode == 'normal') {
			$this->cmi->core->credit = 'credit';
		} else {
			$this->cmi->core->credit = 'no-credit';;
		}

		if (isset($userdata->{'[CORE]lesson_location'})) {
			$this->cmi->core->lesson_location = $userdata->{'[CORE]lesson_location'};
		} else {
			$this->cmi->core->lesson_location = '';
		}

		if (isset($userdata->status)) {
			if ($userdata->status == '') {
				$this->cmi->core->lesson_status_entry = 'ab initio';
			} else {
				if (isset($userdata->{'[CORE]lesson_status flag'}) && ($userdata->{'[CORE]lesson_status flag'} == 'suspend')) {
					$this->cmi->core->lesson_status_entry = 'resume';
				} else {
					$this->cmi->core->lesson_status_entry = NULL;
				}
			}
		}

		if (isset($userdata->{'[CORE]lesson_status'})) {
			$this->cmi->core->lesson_status = $userdata->{'[CORE]lesson_status'};
		} else {
			$this->cmi->core->lesson_status = 'not attempted';
		}

		if (isset($userdata->{'[CORE]score'})) {
			$this->cmi->core->score->raw = $userdata->{'[CORE]score'};
		} else {
			$this->cmi->core->score->raw = '';
		}

		$this->cmi->core->student_data->mastery_score = isset($this->skillsoft->mastery)?$this->skillsoft->mastery:'';

		if (isset($userdata->{'[CORE]time'})) {
			$this->cmi->core->time = $userdata->{'[CORE]time'};
		} else {
			$this->cmi->core->time = '00:00:00';
		}

		if (isset($userdata->{'[CORE_LESSON]'})) {
			$this->cmi->core->core_lesson = stripslashes($userdata->{'[CORE_LESSON]'});
		} else {
			$this->cmi->core->core_lesson = '';
		}

		if (isset($userdata->{'[CORE]session_time'})) {
			$this->cmi->core->session_time = $userdata->{'[CORE]session_time'};
		} else {
			$this->cmi->core->session_time = '00:00:00';
		}
	}

	/**
	 * Process the AICC GetParam request
	 *
	 * @param bool $return Immediately output the value or return as a string
	 * @return string
	 */
	public function getparam($return=false)
	{
		//Make sure to get latest data
		$this->getdata();

		$response='';
		$response .= 'error=0'."\r\n";
		$response .= 'error_text=Successful'."\r\n";
		$response .= 'aicc_data=[Core]'."\r\n";
		$response .= 'student_id='.$this->cmi->core->student_id."\r\n";
		$response .= 'student_name='.$this->cmi->core->student_name."\r\n";
		$response .= 'lesson_location='.$this->cmi->core->lesson_location."\r\n";
		$response .= 'credit='.$this->cmi->core->credit."\r\n";

		$response .= 'lesson_status='.$this->cmi->core->lesson_status;
		if (isset($this->cmi->core->lesson_status_entry))
		{
			$response .= ', '.$this->cmi->core->lesson_status_entry."\r\n";
		} else {
			$response .= "\r\n";
		}
		$response .= 'score='.$this->cmi->core->score->raw."\r\n";
		$response .= 'time='.$this->cmi->core->time."\r\n";
		$response .= 'lesson_mode='.$this->cmi->core->lesson_mode."\r\n";
		$response .= '[Core_Lesson]'."\r\n";
		$response .= $this->cmi->core->core_lesson."\r\n";
		$response .= '[Student_Data]'."\r\n";
		$response .= 'mastery_score='.$this->cmi->core->student_data->mastery_score."\r\n";

		if ($return) {
			return $response;
		} else {
			echo $response;
		}

	}

	/**
	 * Process the AICC PutParam request
	 *
	 * @param string $aiccdata The data sent to the AICC handler by the CMI
	 * @param bool $return Immediately output the value or return as a string
	 * @return string
	 */
	public function putparam($aiccdata,$return=false)
	{
		global $CFG;
		//Lets parse the response
		$this->cleardata();
		$this->parsehacp($aiccdata);
		
		//If we are track to LMS perform the writes to DB
		if ($CFG->skillsoft_trackingmode == TRACK_TO_LMS) {
			//Persist the data
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]lesson_location', $this->cmi->core->lesson_location);
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]lesson_status', strtolower($this->cmi->core->lesson_status));
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]lesson_status flag', strtolower($this->cmi->core->lesson_status_exit));
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]score', $this->cmi->core->score->raw);
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]session_time', $this->cmi->core->session_time);
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE_LESSON]', $this->cmi->core->core_lesson);
	
			//If the launch url contains HACP=0 we know the asset is none completable so call ExitAu here
			$nonetrackable = stripos($this->skillsoft->launch, 'HACP=0');
			if ($nonetrackable !== false) {
				$exitaustr = $this->exitau(true);
			}
		}
		$response = '';
		$response .= 'error=0'."\r\n";
		$response .= 'error_text=Successful'."\r\n";		
		
		if ($return) {
			return $response;
		} else {
			echo $response;
		}
	}

	
	
	
	
	/**
	 * Process the TDR
	 *
	 * @param string $tdr The OLSA TDR
	 * @return null
	 */
	public function processtdr($tdr,$attempt=1)
	{
		//Lets parse the response
		$this->cleardata();
		
		$this->attempt = $attempt;
		
		$this->getdata();
		
		//Convert data value into XML
		$cmixml = simplexml_load_string($tdr->data);

		//Populate the CMI datamodel
		$this->cmi->core->lesson_location = (string)$cmixml->core->lessonLocation;
		$this->cmi->core->lesson_status = (string)$cmixml->core->lessonStatus;
		$this->cmi->core->session_time = (string)$cmixml->core->sessionTime;
		$this->cmi->core->score->raw = (string)$cmixml->core->score->raw;
		$this->cmi->core->core_lesson = (string)$cmixml->suspendData;

		//Persist the data
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]lesson_location', $this->cmi->core->lesson_location);
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]lesson_status', strtolower($this->cmi->core->lesson_status));
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]score', $this->cmi->core->score->raw);
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]session_time', $this->cmi->core->session_time);
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE_LESSON]', $this->cmi->core->core_lesson);
		
		//Now we do the EXITAU part
		$value = $this->add_time($this->cmi->core->session_time, $this->cmi->core->time);
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]time', $value);
		
		$id = skillsoft_setFirstAccessDate($this->user->id, $this->skillsoft->id, $this->attempt, $tdr->timestamp);
		$id = skillsoft_setLastAccessDate($this->user->id, $this->skillsoft->id, $this->attempt, $tdr->timestamp);
		
		if ( (substr($this->cmi->core->lesson_status,0,1) == 'c' || substr($this->cmi->core->lesson_status,0,1) == 'p'))
		{
			$id = skillsoft_setCompletedDate($this->user->id, $this->skillsoft->id, $this->attempt, $tdr->timestamp);
		}

		$id = skillsoft_setAccessCount($this->user->id, $this->skillsoft->id, $this->attempt);

		$id = skillsoft_setFirstScore($this->user->id, $this->skillsoft->id, $this->attempt, $this->cmi->core->score->raw);
		$id = skillsoft_setCurrentScore($this->user->id, $this->skillsoft->id, $this->attempt, $this->cmi->core->score->raw);
		$id = skillsoft_setBestScore($this->user->id, $this->skillsoft->id, $this->attempt, $this->cmi->core->score->raw);
		
	}	
	
	/**
	 * Process the AICC ExitAU request
	 *
	 * @param bool $return Immediately output the value or return as a string
	 * @return string
	 */
	public function exitau($return=false) {
		global $CFG;

		//If we are track to LMS perform the writes to DB
		if ($CFG->skillsoft_trackingmode == TRACK_TO_LMS) {
			$nonetrackable = stripos($this->skillsoft->launch, 'HACP=0');
	
			$this->getdata();
			//Calculate Overall Time
			$value = $this->add_time($this->cmi->core->session_time, $this->cmi->core->time);
			$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]time', $value);
	
			//Submit the summary data
			$now = time();
			$id = skillsoft_setFirstAccessDate($this->user->id, $this->skillsoft->id, $this->attempt, $now);
			$id = skillsoft_setLastAccessDate($this->user->id, $this->skillsoft->id, $this->attempt, $now);
	
			if ( (substr($this->cmi->core->lesson_status,0,1) == 'c' || substr($this->cmi->core->lesson_status,0,1) == 'p'))
			{
				$id = skillsoft_setCompletedDate($this->user->id, $this->skillsoft->id, $this->attempt, $now);
			}
	
			$id = skillsoft_setAccessCount($this->user->id, $this->skillsoft->id, $this->attempt);
	
			$id = skillsoft_setFirstScore($this->user->id, $this->skillsoft->id, $this->attempt, $this->cmi->core->score->raw);
			$id = skillsoft_setCurrentScore($this->user->id, $this->skillsoft->id, $this->attempt, $this->cmi->core->score->raw);
			$id = skillsoft_setBestScore($this->user->id, $this->skillsoft->id, $this->attempt, $this->cmi->core->score->raw);
		}
		$response = '';
		$response .= 'error=0'."\r\n";
		$response .= 'error_text=Successful'."\r\n";

		if ($return) {
			return $response;
		} else {
			echo $response;
		}
	}

	/**
	 * Process the ReportResults
	 *
	 * @param object $reportresults The ReportResults
	 * @return null
	 */
	public function processreportresults($reportresults, $attempt=1)
	{
		
		//Lets parse the response
		$this->cleardata();
		
		$this->attempt = $attempt;
		$this->getdata();
		
		
		$this->cmi->core->lesson_status = strtolower($reportresults->lessonstatus);
		
		if ($reportresults->currentscore != 0) {
			$this->cmi->core->score->raw = $reportresults->currentscore;
		}
		
		
		//Now we do the EXITAU part
		//Duration
		$value = $this->Sec2Time($reportresults->duration);
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]time', $value);

		
		$id = skillsoft_setFirstAccessDate($this->user->id, $this->skillsoft->id, $this->attempt, $reportresults->firstaccessdate);
		$id = skillsoft_setLastAccessDate($this->user->id, $this->skillsoft->id, $this->attempt, $reportresults->lastaccessdate);

		if ( (substr($this->cmi->core->lesson_status,0,1) == 'c' || substr($this->cmi->core->lesson_status,0,1) == 'p'))
		{
			$id = skillsoft_setCompletedDate($this->user->id, $this->skillsoft->id, $this->attempt, $reportresults->completeddate);
		}

		$id = skillsoft_setAccessCount($this->user->id, $this->skillsoft->id, $this->attempt,$reportresults->accesscount);
		$id = skillsoft_setFirstScore($this->user->id, $this->skillsoft->id, $this->attempt, $reportresults->firstscore);
		$id = skillsoft_setCurrentScore($this->user->id, $this->skillsoft->id, $this->attempt, $reportresults->currentscore);
		$id = skillsoft_setBestScore($this->user->id, $this->skillsoft->id, $this->attempt, $reportresults->bestscore);
		
		//Need to do these last to ensure grades correctly entered
		//Persist the data
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]lesson_status', $this->cmi->core->lesson_status);
		$id = skillsoft_insert_track($this->user->id, $this->skillsoft->id, $this->attempt, '[CORE]score', $this->cmi->core->score->raw);
		
		
	}
}

