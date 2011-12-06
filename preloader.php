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
 * Loads the AU
 *
 * @package   mod-skillsoft
 * @author    Martin Holden
 * @copyright 2009-20111 Martin Holden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_TEXT);       // Course Module ID, or
$assetid = required_param('assetid', PARAM_TEXT);       // SkillSoft AssetID

//$PAGE->set_context(CONTEXT_COURSE, $id);

$url = new moodle_url('/mod/skillsoft/getolsadata.php', array('id'=>$id,'assetid'=>$assetid));
$target = $url->out(false,array(),false);
$htmlbody = '<p>'.get_string('skillsoft_metadataloading', 'skillsoft').'&nbsp';
$htmlbody .= '<img src="'. $OUTPUT->pix_url('wait', 'skillsoft').'" class="icon" alt="'.get_string('skillsoft_waitingalt','skillsoft').'" /><br/>';
$htmlbody .= '</p>';

?>
<html>
<head>
<title><?php echo get_string('skillsoft_metadatatitle', 'skillsoft');?></title>
<script type="text/javascript">
	//<![CDATA[
        function doredirect() {
                document.body.innerHTML = '<?php echo $htmlbody ?>';
                document.location = '<?php echo $target ?>';
        }
      //]]>
        </script>
</head>
<body onload="doredirect();">
<p><?php echo get_string('skillsoft_metadataloading', 'skillsoft');?></p>
</body>
</html>
