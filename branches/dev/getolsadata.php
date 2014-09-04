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

require_login();

$id = required_param('assetid', PARAM_TEXT);       // AssetID

if (empty($id)) {
	error('A required parameter is missing');
}

if (strtolower($id) != 'sso') {
	$response = AI_GetXmlAssetMetaData($id);
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
<title><?php echo get_string('skillsoft_metadatatitle', 'skillsoft');?></title>
<script type="text/javascript">
	//<![CDATA[
	function doit() {
		<?php
		if (strtolower($id) != 'sso') {
		 	if ($response->success) {
				print "window.opener.document.getElementById('id_name').value=".'"'.olsadatatohtml($response->result->title->_).'";';
				print "window.opener.document.getElementById('id_launch').value=".'"'.$response->result->launchurl->_.'";';
				print "window.opener.document.getElementById('id_duration').value=".'"'.$response->result->duration.'";';
				print "window.opener.setTextArea(window.opener,'introeditor',".'"'.olsadatatohtml($response->result->description->_).'");';
				print "window.opener.setTextArea(window.opener,'audience',".'"'.olsadatatohtml($response->result->audience).'");';
				print "window.opener.setTextArea(window.opener,'prereq',".'"'.olsadatatohtml($response->result->prerequisites).'");';
				print "window.close();";
			} else {
				//error($response->errormessage);
				print "document.getElementById('waitingmessage').style.display = 'none';";
				print "document.getElementById('errormessage').style.display = 'block';";
			}
		} else {
			if (!$CFG->skillsoft_trackingmode == TRACK_TO_LMS) {
				//print '<p>'.get_string('skillsoft_ssoerror', 'skillsoft').'</p>';
				print "window.opener.document.getElementById('id_name').value=".'"'.get_string('skillsoft_ssoassettitle', 'skillsoft').'";';
				print "window.opener.document.getElementById('id_launch').value=".'"'.$CFG->skillsoft_ssourl.'";';
				print "window.opener.document.getElementById('id_duration').value='0';";
				print "window.opener.setTextArea(window.opener,'introeditor',".'"'.get_string('skillsoft_ssoassetsummary', 'skillsoft').'");';
				print "window.opener.setTextArea(window.opener,'audience','');";
				print "window.opener.setTextArea(window.opener,'prereq','');";
				print "window.close();";
			} else {
				print "document.getElementById('waitingmessage').style.display = 'none';";
				print "document.getElementById('errormessage').style.display = 'block';";
			}
		}

		?>

	}

	//]]>
</script>
</head>
<body onload="doit();">
<div id="waitingmessage"><p><?php echo get_string('skillsoft_metadatasetting', 'skillsoft');?></p></div>
<div id="errormessage" style="display:none;"><p>
<?php
//$OUTPUT->close_window_button();
if (strtolower($id) != 'sso') {
	print '<p>'.get_string('skillsoft_metadataerror', 'skillsoft').'</p>';
	print '<p>'.$response->errormessage.'</p>';
} else {
	if ($CFG->skillsoft_trackingmode == TRACK_TO_LMS) {
		print '<p>'.get_string('skillsoft_ssomodeerror', 'skillsoft').'</p>';
	}
}
?>
</p></div>
</body>
</html>
