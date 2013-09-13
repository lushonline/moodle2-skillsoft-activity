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
 * @copyright 2009-2011 Martin Holden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

$contextmodule = get_context_instance(CONTEXT_MODULE, $cm->id);

$row = array();
$tabs  = array();
$inactive = array();
$activated = array();

$infourl = new moodle_url('/mod/skillsoft/view.php', array('id'=>$cm->id));
$row[] = new tabobject('info', $infourl, get_string('skillsoft_info', 'skillsoft'));

$reporturl = new moodle_url('/mod/skillsoft/report.php', array('id'=>$skillsoft->id, 'user'=>'true', 'attempt'=>$attempt));
$row[] = new tabobject('reports', $reporturl, get_string('skillsoft_results', 'skillsoft'));

if (has_capability('mod/skillsoft:viewreport', $contextmodule)) {
	$reportallurl = new moodle_url('/mod/skillsoft/report.php', array('id'=>$skillsoft->id, 'attempt'=>$attempt));
    $row[] = new tabobject('allreports', $reportallurl, get_string('skillsoft_allresults', 'skillsoft'));
}
$tabs[] = $row;

print_tabs($tabs, $currenttab, $inactive, $activated);