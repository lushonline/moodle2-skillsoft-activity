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
