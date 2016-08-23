<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (empty($skillsoft)) {
    error('You cannot call this script in that way');
}
if (!isset($currenttab)) {
    $currenttab = '';
}

if (!isset($cm)) {
    $cm = get_coursemodule_from_instance('skillsoft', $skillsoft->id);
}

//$contextmodule = get_context_instance(CONTEXT_MODULE, $cm->id);
$contextmodule = context_MODULE::instance($cm->id);

$row = array();
$tabs  = array();
$inactive = array();
$activated = array();

$infourl = new moodle_url('/mod/skillsoft/view.php', array('id'=>$cm->id));
$row[] = new tabobject('info', $infourl, get_string('skillsoft_info', 'skillsoft'));

$reporturl = new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id, 'user'=>'true'));
$row[] = new tabobject('reports', $reporturl, get_string('skillsoft_results', 'skillsoft'));

if (has_capability('mod/skillsoft:viewreport', $contextmodule)) {
	$reportallurl = new moodle_url('/mod/skillsoft/report.php', array('id'=>$cm->id));
    $row[] = new tabobject('allreports', $reportallurl, get_string('skillsoft_allresults', 'skillsoft'));
}
$tabs[] = $row;

print_tabs($tabs, $currenttab, $inactive, $activated);