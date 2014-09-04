<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//score
class score {
	private $raw;

	public function __construct()
	{
		$this->raw = NULL;
	}

	private function validate_raw($value) {
		if ( (is_numeric($value) && ($value >= 0) && ($value <= 100)) || ($value == '') ) {
			return true;
		} else {
			return false;
		}
	}


	public function __set($var, $value) {
		switch($var)
		{
			case 'raw':
				if ($this->validate_raw($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('score->'.$var.' is not valid value.');
				}
				break;
			default:
				throw new Exception('Invalid element.');

		}
	}

	public function __get($var) {
		return $this->$var;
	}
}

class student_data {
	private $mastery_score;

	public function __construct()
	{
		$this->mastery_score = NULL;
	}

	private function validate_masteryscore($value) {
		if ( (is_numeric($value) && ($value >= 0) && ($value <= 100)) || ($value == '') ) {
			return true;
		} else {
			return false;
		}
	}

	public function __set($var, $value) {
		switch($var)
		{
			case 'mastery_score':
				if ($this->validate_masteryscore($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('student_data->'.$var.' is not valid value.');
				}
				break;
			default:
				throw new Exception('Invalid element.');

		}
	}

	public function __get($var) {
		return $this->$var;
	}

}

//aicc hacp core class
class core {
	private $student_id;
	private $student_name;
	private $lesson_location;
	private $credit;

	private $lesson_mode;

	private $lesson_status;
	private $lesson_status_entry;
	private $lesson_status_exit;

	private $score;
	private $time;

	private $student_data;

	private $core_lesson;
	private $core_vendor;

	//AU to CMI
	private $session_time;

	
	//MAY-2013 (2013051400)
	//true we apply the regex to confirm the studentid is AICC 2.2 conformant
	private $enforcestrictstudentid;
	
	private function IsCmiString255($value)
	{
		return strlen($value) <= 255;
	}

	private function IsCmiString4096($value)
	{
		return strlen($value) <= 4096;
	}

	private function IsCmiIdentifier($value)
	{
		if (strlen($value) > 255) {
			return false;
		}
		if ($this->enforcestrictstudentid) {
				return preg_match("/^[A-Za-z0-9\-_:]+$/", $value);
		} else {
			return true;
		}
	}


	private function IsCmiCredit($value)
	{
		$vocabulary = array(
		               'credit' => 'credit',
		               'no-credit' => 'no-credit',
		               'c' => 'credit',
		               'n' => 'no-credit',
		);
		if (isset($vocabulary[strtolower($value)])) {
			return true;
		} else {
			return false;
		}
	}

	private function IsCmiLessonMode($value)
	{
		$vocabulary = array(
		               'browse' => 'browse',
		               'normal' => 'normal',
		               'review' => 'review',
		               'b' => 'browse',
		               'n' => 'normal',
		               'r' => 'review',
		);
		if (isset($vocabulary[strtolower($value)])) {
			return true;
		} else {
			return false;
		}
	}


	private function IsCmiStatus($value)
	{
		$vocabulary = array(
	                   'passed' => 'passed',
	                   'completed' => 'completed',
	                   'failed' => 'failed',
	                   'incomplete' => 'incomplete',
	                   'browsed' => 'browsed',
	                   'not attempted' => 'not attempted',
	                   'p' => 'passed',
	                   'c' => 'completed',
	                   'f' => 'failed',
	                   'i' => 'incomplete',
	                   'b' => 'browsed',
	                   'n' => 'not attempted'
	                   );
	                   if (isset($vocabulary[strtolower($value)])) {
	                   	return true;
	                   } else {
	                   	return false;
	                   }
	}

	private function IsCmiExit($value)
	{
		$vocabulary = array(
	                   'logout' => 'logout',
	                   'time-out' => 'time-out',
	                   'suspend' => 'suspend',
	                   'l' => 'logout',
	                   't' => 'time-out',
	                   's' => 'suspend',
		);
		if (isset($vocabulary[strtolower($value)])) {
			return true;
		} else {
			return false;
		}
	}

	private function IsCmiEntry($value)
	{
		$vocabulary = array(
						'ab initio' => 'ab initio',
						'resume' => 'resume',
						'a' => 'a',
						'r' => 'r',
		);
		if (isset($vocabulary[strtolower($value)])) {
			return true;
		} else {
			return false;
		}
	}

	private function IsCmiTimespan($value)
	{
		// HHHH:MM:SS.SS
		// Hour: 2-4 digits. The decimal part of seconds is optional (0-2 digits).
		return preg_match("/^\d{2,4}:\d\d:\d\d(\.\d{1,2})?$/", $value);
	}


	private function setdefaults() {
		$this->student_id = NULL;
		$this->student_name = NULL;
		$this->lesson_location = NULL;
		$this->credit = 'credit';
		$this->lesson_mode = 'normal';
		$this->lesson_status = 'not attempted';
		$this->lesson_status_entry = 'ab initio';
		$this->time = '00:00:00';
		$this->core_lesson = NULL;
		$this->core_vendor = NULL;

		$this->session_time = '00:00:00';
		$this->lesson_status_exit = NULL;
	}

	public function __construct($enforcestrictstudentid=0)
	{
		$this->setdefaults();
		$this->score = new score();
		$this->student_data = new student_data();
		$this->enforcestrictstudentid=$enforcestrictstudentid;
	}

	public function __set($var, $value) {
		switch($var)
		{
			case 'student_id':
				if ($this->IsCmiIdentifier($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'student_name':
				if ($this->IsCmiString255($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'lesson_location':
				if ($this->IsCmiString255($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'credit':
				if ($this->IsCmiCredit($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'lesson_mode':
				if ($this->IsCmiLessonMode($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'lesson_status':
				if ($this->IsCmiStatus($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'lesson_status_entry':
				if ($this->IsCmiEntry($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'time':
				if ($this->IsCmiTimespan($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'core_lesson':
				if ($this->IsCmiString4096($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'core_vendor':
				if ($this->IsCmiString4096($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'session_time':
				if ($this->IsCmiTimespan($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			case 'lesson_status_exit':
				if ($this->IsCmiExit($value)) {
					$this->$var = $value;
				} else {
					throw new Exception('core->'.$var.' is not valid value.');
				}
				break;
			default:
				throw new Exception('Invalid element.');

		}
	}

	public function __get($var) {
		return $this->$var;
	}


}

class cmi {
	public $core;

	public function __construct($enforcestrictstudentid=0)
	{
		$this->core = new core($enforcestrictstudentid);
	}
}