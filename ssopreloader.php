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

$a = required_param('a', PARAM_INT); //activity id
if (!empty($a)) {
	if (! $skillsoft = $DB->get_record('skillsoft', array('id'=> $a))) {
		print_error('Skillsoft asset is incorrect');
	}
	if (! $course = $DB->get_record('course', array('id'=> $skillsoft->course))) {
		print_error('Course is misconfigured');
	}
	if (! $cm = get_coursemodule_from_instance('skillsoft', $skillsoft->id, $course->id)) {
		print_error('Course Module ID was incorrect');
	}
} else {
	print_error('A required parameter is missing');
}

require_login($course->id, false, $cm);



$target = new moodle_url('/mod/skillsoft/sso.php', array('a'=>$a));
$htmlbody = '<p>'.get_string('skillsoft_ssoloading', 'skillsoft').'&nbsp';
$htmlbody .= '<img src="'. $OUTPUT->pix_url('wait', 'skillsoft').'" class="icon" alt="'.get_string('skillsoft_waitingalt','skillsoft').'" /><br/>';
$htmlbody .= '</p>';


//$target = $CFG->wwwroot.'/mod/skillsoft/sso.php?a='.$a;
//$skillsoftpixdir = $CFG->modpixpath.'/skillsoft/pix';
?>
<html>
<head>
<title><?php echo get_string('skillsoft_ssotitle', 'skillsoft');?></title>
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
<p><?php echo get_string('skillsoft_ssoloading', 'skillsoft');?></p>
</body>
</html>