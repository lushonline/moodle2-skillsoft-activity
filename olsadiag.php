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
 * Retrieve the Asset metadata from the SkillSoft OLSA server
 * and update the create/edit form using Javascript.
 *
 * @package   mod-skillsoft
 * @author    Martin Holden
 * @copyright 2009-2011 Martin Holden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/olsalib.php');

global $CFG;

//Report all PHP errors
error_reporting(E_ALL);

$br = '<br/>';
$pass ='<font style="color: #008000; font-weight: bold;">Test PASSED<br />';
$fail ='<font style="color: #800000; font-weight: bold;">Test FAILED<br />';
$fontend ='</font>';

$continue = true;
$currenttest = 1;

function testheader($testnumber, $message) {
	return sprintf('<h2>Test %s : %s</h2>',$testnumber,$message);
}

/* Returns the DateTime from the HTTP Header
 *
 */
function getservertime() {
	global $CFG;

	$location = $CFG->skillsoft_olsaendpoint;

	$ch = curl_init($location);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	//	curl_setopt($ch, CURLOPT_HTTPGET, true); //this is needed to fix the issue



	if (!empty($CFG->proxyhost)) {
		// SOCKS supported in PHP5 only
		if (!empty($CFG->proxytype) and ($CFG->proxytype == 'SOCKS5')) {
			if (defined('CURLPROXY_SOCKS5')) {
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
			} else {
				curl_close($ch);
				debugging("SOCKS5 proxy is not supported in PHP4.", DEBUG_ALL);
				return false;
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
	$response = curl_exec($ch);
	curl_close($ch);


	/*  get the status code from HTTP headers */
	if(preg_match('/Date: (.*)\r/', $response, $matches)){
		$result = new stdClass();
		$result->serverdatetime = time();
		$result->olsadatetime=strtotime( $matches[1] );
		$result->diff = $result->olsadatetime - $result->serverdatetime;
		//If diff > than limit then too slow
		$result->limit = 60;
		$result->isfast = $result->serverdatetime > $result->olsadatetime;
		$result->isslow = $result->diff > $result->limit;
		return $result;
	} else {
		return false;
	}
}






echo ('<h1>Perfoming Basic OLSA Diagnostics</h1>');
echo ('<p>This page will perform some basic tests to confirm Moodle Module is able to access SkillSoft OLSA Servers</p>');

if (!empty($CFG->proxyhost)) {
	echo ('<h2>Moodle Proxy Configuration Details</h2>');
	echo ('<p>Moodle is configured to connect to the internet using the following proxy server details.</p>');
	echo ('Proxy Host: '.$CFG->proxyhost.$br);
	echo ('Proxy Port: '.$CFG->proxyport.$br);
	if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
		echo ('Proxy Authentication: true'.$br);
	}
	echo ('<hr/>'.$br);
}

if ($continue) {
	echo (testheader($currenttest,'Checking SOAP Extension is loaded').$br);
	if (!extension_loaded('soap')) {
		echo($fail);
		echo('SOAP Extension is not enabled in PHP.INI. To enable please see <a target="_blank" href="http://www.php.net/manual/en/book.soap.php">http://www.php.net/manual/en/book.soap.php</a>'.$br);
		echo('DIAGNOSTICS HALTED'.$br);
		echo($fontend);
		$continue = false;
	} else {
		echo($pass);
		echo('SOAP Extension is loaded.'.$br);
		echo($fontend);
	}
	echo ('<hr/>'.$br);
	echo ($br);
}

$currenttest++;
if ($continue) {
	echo (testheader($currenttest,'Checking cURL Extension is loaded').$br);
	if (!extension_loaded('curl')) {
		echo($fail);
		echo('cURL Extension is not enabled in PHP.INI. To enable please see <a target="_blank" href="http://www.php.net/manual/en/book.curl.php">http://www.php.net/manual/en/book.curl.php</a>'.$br);
		echo('DIAGNOSTICS HALTED'.$br);
		echo($fontend);
		$continue = false;
	} else {
		echo($pass);
		echo('cURL Extension is loaded.'.$br);
		echo($fontend);
	}
	echo ('<hr/>'.$br);
	echo ($br);
}

$currenttest++;
if ($continue) {
	echo (testheader($currenttest,'Checking OLSA Settings are configured').$br);
	if (!isolsaconfigurationset()) {
		echo($fail);
		echo('OLSA Settings are Not Configured. Please ensure you enter the OLSA settings in the module configuration settings'.$br);
		echo('DIAGNOSTICS HALTED');
		echo($fontend);
		$continue = false;
	} else {
		echo($pass);
		echo('OLSA Settings Configured.'.$br);
		echo($fontend);
		//Set local OLSA Variables
		$endpoint = $CFG->skillsoft_olsaendpoint;
		$customerId = $CFG->skillsoft_olsacustomerid;
		$sharedsecret = $CFG->skillsoft_olsasharedsecret;
	}
	echo ('<hr/>'.$br);
	echo ($br);
}

$currenttest++;
if ($continue) {
	echo (testheader($currenttest,'Is OLSA EndPoint Accessible').$br);

	if ($content = download_file_content($endpoint.'?WSDL',null,null,true,300,20,true)) {
		//Check for HTTP 200 response
		if ($content->status != 200) {
			echo($fail);
			echo('OLSA WSDL Can Not Be Accessed'.$br);
			echo('Current Value: <a target="_blank" href ="'.$endpoint.'?WSDL">'.$endpoint.'</a>'.$br);
			if ($content->headers == false) {
				if (!extension_loaded('openssl') && stripos($endpoint, 'https') === 0) {
					echo('OLSA EndPoint uses SSL'.$br);
					echo('OPENSSL Extension is not enabled in PHP.INI. To enable please see <a target="_blank" href="http://uk.php.net/manual/en/book.openssl.php">http://uk.php.net/manual/en/book.openssl.php</a>'.$br);
				} else {
					echo('No Headers Returned, this typically indicates a networking or DNS resolution issue. Please confirm connectivity and the correct URL is specified.'.$br);
					echo('Error Message returned by request ='.$content->error.$br);
				}
			} else {
				echo('Please ensure you entered the correct URL'.$br.$br);
				echo('Headers Returned'.$br);
				foreach ($http_headers as $header) {
					echo('&nbsp;&nbsp;'.$header.$br);
				}
			}

			echo('DIAGNOSTICS HALTED'.$br);
			echo($fontend);
			$continue = false;
		}
		else {
			echo($pass);
			echo('OLSA WSDL Can Be Opened.'.$br);
			echo($fontend);
		}
	}
	echo ('<hr/>'.$br);
	echo ($br);
}

$currenttest++;
if ($continue) {
	echo (testheader($currenttest,'Check Time Synchronisation').$br);
	if (!$result = getservertime()) {
		echo($fail);
		echo('Unable to retrieve OLSA server time.'.$br);
		echo('DIAGNOSTICS HALTED');
		echo($fontend);
		$continue = false;
	} else {
		//Now compare with current time.
		//Is it faster
		if ($result->isfast) {
			echo($fail);
			echo('Moodle Server Time is faster than OLSA Server. The OLSA Authentication Process will fail'.$br);
			echo('Ensure the Moodle Server Time is synchronised to a reliable time source such as an NTP server <a target="_blank" href ="http://en.wikipedia.org/wiki/Network_Time_Protocol">http://en.wikipedia.org/wiki/Network_Time_Protocol</a>'.$br);
			echo('Moodle Server Time : '.gmdate('Y-m-d\TH:i:s', $result->serverdatetime).'Z'.$br);
			echo('OLSA Server Time : '.gmdate('Y-m-d\TH:i:s', $result->olsadatetime).'Z'.$br);
			echo('Time difference (OLSA - Moodle): '.$result->diff.' seconds'.$br);
			echo('DIAGNOSTICS HALTED');
			echo($fontend);
			$continue = false;
		} else if ($result->isslow) {
			echo($fail);
			echo('Moodle Server Time is slower than OLSA Server. The OLSA Authentication Process will fail'.$br);
			echo('The Moodle Server Time can be no more than '.$result->limit.' seconds slower than OLSA server'.$br);
			echo('Ensure the Moodle Server Time is synchronised to a reliable time source such as an NTP server <a target="_blank" href ="http://en.wikipedia.org/wiki/Network_Time_Protocol">http://en.wikipedia.org/wiki/Network_Time_Protocol</a>'.$br);
			echo('Moodle Server Time : '.gmdate('Y-m-d\TH:i:s', $result->serverdatetime).'Z'.$br);
			echo('OLSA Server Time : '.gmdate('Y-m-d\TH:i:s', $result->olsadatetime).'Z'.$br);
			echo('Time difference (OLSA - Moodle): '.$result->diff.' seconds'.$br);
			echo('DIAGNOSTICS HALTED');
			echo($fontend);
			$continue = false;
		} else {
			echo($pass);
			echo('Server Times are within limits'.$br);
			echo('Moodle Server Time : '.gmdate('Y-m-d\TH:i:s', $result->serverdatetime).'Z'.$br);
			echo('OLSA Server Time : '.gmdate('Y-m-d\TH:i:s', $result->olsadatetime).'Z'.$br);
			echo('Time difference (OLSA - Moodle): '.$result->diff.' seconds'.$br);
			echo($fontend);
		}
	}
	echo ('<hr/>'.$br);
	echo ($br);
}


$currenttest++;
if ($continue) {
	echo (testheader($currenttest,'Create OLSA SOAP Client').$br);
	//Specify the SOAP Client Options
	$options = array(
		"trace"      => 1,
		"exceptions" => true,
		"soap_version"   => SOAP_1_2,
		"cache_wsdl" => WSDL_CACHE_BOTH,
		"encoding"=> "UTF-8"
		);
		try {
			//Create a new instance of the OLSA Soap Client
			$client = new olsa_soapclient($endpoint.'?WSDL',$options);
			//Create the USERNAMETOKEN
			$client->__setUsernameToken($customerId,$sharedsecret);
			echo($pass);
			echo('OLSA SOAP Client Created'.$br);
			echo($fontend);
		} catch (Exception $e) {
			echo($fail);
			echo('Exception while creating OLSA SOAP Client'.$br);
			echo('Exception Details'.$br);
			echo($e->getMessage());
			echo('DIAGNOSTICS HALTED');
			echo($fontend);
			$continue = false;
		}
		echo ('<hr/>'.$br);
		echo ($br);
}

$currenttest++;
if ($continue) {
	echo (testheader($currenttest,'Check OLSA Authentication').$br);
	$pollresponse = UTIL_PollForReport('0');
	if ($pollresponse->errormessage == get_string('skillsoft_olsassoapauthentication','skillsoft')) {
		echo($fail);
		echo('OLSA Credentials are incorrect, or Moodle Server time is incorrect. Please ensure you entered the correct values.'.$br);
		echo('DIAGNOSTICS HALTED');
		echo($fontend);
		$continue = false;
	} else {
		echo($pass);
		echo('OLSA Credentials are correct.'.$br);
		echo($fontend);
	}
	echo ('<hr/>'.$br);
	echo ($br);
}



?>
