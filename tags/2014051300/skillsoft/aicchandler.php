<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * AICC Call Back Page for module aicc
 *
 * All the aicc handler functions
 *
 * @package   mod-skillsoft
 * @author 	  Martin Holden
 * @copyright 2009-2013 Martin Holden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");
header("ETag: PUB" . time());

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

foreach ($_POST as $key => $value)
{
	$tempkey = strtolower($key);
	$_POST[$tempkey] = $value;
}

$command = required_param('command', PARAM_ALPHA);
$sessionid = required_param('session_id', PARAM_ALPHANUM);
$aiccdata = optional_param('aicc_data', '', PARAM_RAW);
$version = optional_param('version', '', PARAM_RAW);
$attempt = optional_param('attempt', '1', PARAM_INT);

if (!empty($command) && ($skillsoftsession=skillsoft_check_sessionid($sessionid))) {
	$skillsoftid = $skillsoftsession->skillsoftid;
	$userid = $skillsoftsession->userid;
	//$attempt = 1;

	
	if ($skillsoft = $DB->get_record('skillsoft',array('id'=>$skillsoftid))) {
		$user = $DB->get_record('user',array('id'=>$userid));
		$handler = new aicchandler($user,$skillsoft,$attempt,$CFG->skillsoft_strictaiccstudentid);

		switch (strtolower($command)) {
			case 'getparam':
				$handler->getparam();
				break;
			case 'putparam':
				$handler->putparam($aiccdata);
				break;
			case 'exitau':
				$handler->exitau();
				break;
			case 'putcomments':
			case 'putinteractions':
			case 'putobjectives':
			case 'putpath':
			case 'putperformance':
				echo "error=0\r\nerror_text=Successful\r\n";
				break;
			default:
				echo "error=1\r\nerror_text=Invalid Command\r\n";
				break;
		}
	}
} else {
	if (empty($command)) {
		echo "error=1\r\nerror_text=Invalid Command\r\n";
	} else {
		echo "error=3\r\nerror_text=Invalid Session ID\r\n";
	}
}
?>
