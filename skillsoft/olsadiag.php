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
require_once($CFG->dirroot.'/mod/skillsoft/olsalib.php');

global $CFG;

$url = new moodle_url('/mod/skillsoft/olsadiag.php');
//$context = get_context_instance(CONTEXT_SYSTEM);

$context = context_SYSTEM::instance();


require_login();
require_capability('moodle/site:config', $context);

//Display the page header
$pagetitle = 'OLSA Diagnostics';
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add($pagetitle);
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

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

	//Force CURL to use TLSv1 or later as SSLv3 deprecated on Skillsoft servers
	//Bug Fix - http://code.google.com/p/moodle2-skillsoft-activity/issues/detail?id=17
	curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

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

echo $OUTPUT->box('This page will perform some basic tests to confirm Moodle Module is able to access SkillSoft OLSA Servers', 'generalbox boxaligncenter boxwidthwide', 'summary');


if (!empty($CFG->proxyhost)) {
	$html = '';
	$html .= 'Moodle is configured to connect to the internet using the following proxy server details.';
	$html .= $br;
	$html .='Proxy Host: '.$CFG->proxyhost;
	$html .= $br;
	$html .= 'Proxy Port: '.$CFG->proxyport;
	$html .= $br;
	if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
		$html .= 'Proxy Authentication: true';
		$html .= $br;
	}
	echo $OUTPUT->box('<h2>'.'Moodle Proxy Configuration Details'.'</h2>'.$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

if ($continue) {
	$html = '';
	if (!extension_loaded('soap')) {
		$continue = false;
		$html .= $fail;
		$html .= 'SOAP Extension is not enabled in PHP.INI. To enable please see <a target="_blank" href="http://www.php.net/manual/en/book.soap.php">http://www.php.net/manual/en/book.soap.php</a>';
		$html .= $br;
		$html .= 'DIAGNOSTICS HALTED';
		$html .= $br;
		$html .= $fontend;
	} else {
		$html .= $pass;
		$html .= 'SOAP Extension is loaded.';
		$html .= $br;
		$html .= $fontend;
	}
	echo $OUTPUT->box(testheader($currenttest,'Checking SOAP Extension is loaded').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

$currenttest++;
if ($continue) {
	$html = '';
	if (!extension_loaded('curl')) {
		$continue = false;
		$html .= $fail;
		$html .= 'cURL Extension is not enabled in PHP.INI. To enable please see <a target="_blank" href="http://www.php.net/manual/en/book.curl.php">http://www.php.net/manual/en/book.curl.php</a>';
		$html .= $br;
		$html .= 'DIAGNOSTICS HALTED';
		$html .= $br;
		$html .= $fontend;
	} else {
		$html .= $pass;
		$html .= 'cURL Extension is loaded.';
		$html .= $br;
		$html .= $fontend;
	}
	echo $OUTPUT->box(testheader($currenttest,'Checking cURL Extension is loaded').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

$currenttest++;
if ($continue) {
	$html = '';
	if (!isolsaconfigurationset()) {
		$continue = false;
		$html .= $fail;
		$html .= 'OLSA Settings are Not Configured. Please ensure you enter the OLSA settings in the module configuration settings';
		$html .= $br;
		$html .= 'DIAGNOSTICS HALTED';
		$html .= $br;
		$html .= $fontend;
	} else {
		$html .= $pass;
		$html .= 'OLSA Settings Configured.';
		$html .= $br;
		$html .= $fontend;
		//Set local OLSA Variables used for subsequent tests
		$endpoint = $CFG->skillsoft_olsaendpoint;
		$customerId = $CFG->skillsoft_olsacustomerid;
		$sharedsecret = $CFG->skillsoft_olsasharedsecret;
	}
	echo $OUTPUT->box(testheader($currenttest,'Checking OLSA Settings are configured').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

$currenttest++;
if ($continue) {
	$html = '';
	if ($content = download_file_content($endpoint.'?WSDL',null,null,true,300,20,true)) {
		//Check for HTTP 200 response
		if ($content->status != 200) {
			$continue = false;
			$html .= $fail;
			$html .= 'OLSA WSDL Can Not Be Accessed';
			$html .= $br;
			$html .= 'Current Value: <a target="_blank" href ="'.$endpoint.'?WSDL">'.$endpoint.'</a>';
			$html .= $br;
			if ($content->headers == false) {
				if (!extension_loaded('openssl') && stripos($endpoint, 'https') === 0) {
					$html .= 'OLSA EndPoint uses SSL';
					$html .= $br;
					$html .= 'OPENSSL Extension is not enabled in PHP.INI. To enable please see <a target="_blank" href="http://uk.php.net/manual/en/book.openssl.php">http://uk.php.net/manual/en/book.openssl.php</a>';
					$html .= $br;
				} else {
					$html .= 'No Headers Returned, this typically indicates a networking or DNS resolution issue. Please confirm connectivity and the correct URL is specified.';
					$html .= $br;
					$html .= 'Error Message returned by request ='.$content->error;
					$html .= $br;
				}
			} else {
				$html .= 'Please ensure you entered the correct URL';
				$html .= $br;
				$html .= $br;
				$html .= 'Headers Returned';
				$html .= $br;
				foreach ($http_headers as $header) {
					$html .= '&nbsp;&nbsp;'.$header;
					$html .= $br;
				}
			}
			$html .= 'DIAGNOSTICS HALTED';
			$html .= $br;
			$html .= $fontend;
			$continue = false;
		} else {
			$html .= $pass;
			$html .= 'OLSA WSDL Can Be Opened.';
			$html .= $br;
			$html .= $fontend;
		}
	}
	echo $OUTPUT->box(testheader($currenttest,'Is OLSA EndPoint Accessible').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

$currenttest++;
if ($continue) {
	$html = '';
	if (!$result = getservertime()) {
		$continue = false;
		$html .= $fail;
		$html .= 'Unable to retrieve OLSA server time.';
		$html .= $br;
		$html .= 'DIAGNOSTICS HALTED';
		$html .= $br;
		$html .= $fontend;
	} else {
		//Now compare with current time.
		//Is it faster
		if ($result->isfast && $result->diff > 1) {
			$continue = false;
			$html .= $fail;
			$html .= 'Moodle Server Time is faster than OLSA Server. The OLSA Authentication Process will fail';
			$html .= $br;
			$html .= 'Ensure the Moodle Server Time is synchronised to a reliable time source such as an NTP server <a target="_blank" href ="http://en.wikipedia.org/wiki/Network_Time_Protocol">http://en.wikipedia.org/wiki/Network_Time_Protocol</a>';
			$html .= $br;
			$html .= 'Moodle Server Time : '.gmdate('Y-m-d\TH:i:s', $result->serverdatetime).'Z';
			$html .= $br;
			$html .= 'OLSA Server Time : '.gmdate('Y-m-d\TH:i:s', $result->olsadatetime).'Z';
			$html .= $br;
			$html .= 'Time difference (OLSA - Moodle): '.$result->diff.' seconds';
			$html .= $br;
			$html .= 'DIAGNOSTICS HALTED';
			$html .= $br;
			$html .= $fontend;
		} else if ($result->isslow) {
			$continue = false;
			$html .= $fail;
			$html .= 'Moodle Server Time is slower than OLSA Server. The OLSA Authentication Process will fail';
			$html .= $br;
			$html .= 'The Moodle Server Time can be no more than '.$result->limit.' seconds slower than OLSA server';
			$html .= $br;
			$html .= 'Ensure the Moodle Server Time is synchronised to a reliable time source such as an NTP server <a target="_blank" href ="http://en.wikipedia.org/wiki/Network_Time_Protocol">http://en.wikipedia.org/wiki/Network_Time_Protocol</a>';
			$html .= $br;
			$html .= 'Moodle Server Time : '.gmdate('Y-m-d\TH:i:s', $result->serverdatetime).'Z';
			$html .= $br;
			$html .= 'OLSA Server Time : '.gmdate('Y-m-d\TH:i:s', $result->olsadatetime).'Z';
			$html .= $br;
			$html .= 'Time difference (OLSA - Moodle): '.$result->diff.' seconds';
			$html .= $br;
			$html .= 'DIAGNOSTICS HALTED';
			$html .= $br;
			$html .= $fontend;
		} else {
			$html .= $pass;
			$html .= 'Server Times are within limits';
			$html .= $br;
			$html .='Moodle Server Time : '.gmdate('Y-m-d\TH:i:s', $result->serverdatetime).'Z';
			$html .= $br;
			$html .='OLSA Server Time : '.gmdate('Y-m-d\TH:i:s', $result->olsadatetime).'Z';
			$html .= $br;
			$html .='Time difference (OLSA - Moodle): '.$result->diff.' seconds';
			$html .= $br;
			$html .= $fontend;
		}

	}
	echo $OUTPUT->box(testheader($currenttest,'Check Time Synchronisation').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}


$currenttest++;
if ($continue) {
	$html = '';
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
			$html .= $pass;
			$html .= 'OLSA SOAP Client Created';
			$html .= $br;
			$html .= $fontend;
		} catch (Exception $e) {
			$continue = false;
			$html .= $fail;
			$html .= 'Exception while creating OLSA SOAP Client';
			$html .= $br;
			$html .= 'Exception Details';
			$html .= $br;
			$html .= $e->getMessage();
			$html .= $br;
			$html .= 'DIAGNOSTICS HALTED';
			$html .= $fontend;
		}
		echo $OUTPUT->box(testheader($currenttest,'Create OLSA SOAP Client').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

$currenttest++;
if ($continue) {
	$html = '';
	$pollresponse = UTIL_PollForReport('0');
	if ($pollresponse->errormessage == get_string('skillsoft_olsassoapauthentication','skillsoft')) {
		$continue = false;
		$html .= $fail;
		$html .= 'OLSA Credentials are incorrect, or Moodle Server time is incorrect. Please ensure you entered the correct values.';
		$html .= $br;
		$html .= 'DIAGNOSTICS HALTED';
		$html .= $br;
		$html .= $fontend;
	} else {
		$html .= $pass;
		$html .= 'OLSA Credentials are correct.';
		$html .= $br;
		$html .= $fontend;
		//Set local OLSA Variables used for subsequent tests
		$endpoint = $CFG->skillsoft_olsaendpoint;
		$customerId = $CFG->skillsoft_olsacustomerid;
		$sharedsecret = $CFG->skillsoft_olsasharedsecret;
	}
	echo $OUTPUT->box(testheader($currenttest,'Check OLSA Authentication').$html, 'generalbox boxaligncenter boxwidthwide', 'summary');
}

echo $OUTPUT->footer();

