<?php

require_once('../../config.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT);

if (! $cm = get_coursemodule_from_id('skillsoft', $id)) {
    print_error('Course Module ID was incorrect');
}
if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('Course is misconfigured');
}
if (! $skillsoft = $DB->get_record('skillsoft', array('id'=> $cm->instance))) {
    print_error('Course module is incorrect');
}

require_login($course->id, false, $cm);

$url = skillsoft_launch_url($skillsoft, $USER);
redirect($url);
?>
