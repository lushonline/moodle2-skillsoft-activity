<?php
/*
 * @package		mod-skillsoft
 * @author		$Author: martinholden1972@googlemail.com $
 * @version		SVN: $Header: https://moodle2-skillsoft-activity.googlecode.com/svn/branches/dev/preloader.php 147 2014-09-04 14:54:05Z martinholden1972@googlemail.com $
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');

$id = required_param('id', PARAM_TEXT);       // Course Module ID, or
$assetid = required_param('assetid', PARAM_TEXT);       // SkillSoft AssetID

require_login($id, false);

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
