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
 * Main file for skillsoft_customreportlog report
 *
 * Simple diagnistics report to show the custom report requests issued.
 *
 * @package    report_skillsoft_customreportlog
 * @copyright  2013 onwards Martin Holden}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'handle', PARAM_ALPHA);
$dir     = optional_param('dir', 'ASC', PARAM_ALPHA);

$PAGE->set_url('/report/skillsoft_customreportlog/index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
$PAGE->set_pagelayout('report');

require_login();
require_capability('report/skillsoft_customreportlog:view', context_system::instance());

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_skillsoft_customreportlog'));

$changescount = $DB->count_records('skillsoft_report_track');
echo $OUTPUT->box_start();     // The forms section at the top
echo get_string('skillsoft_customreportlog_recordcount', 'report_skillsoft_customreportlog').' '.$changescount;
echo $OUTPUT->box_end();



if ($changescount >0) {

	$columns = array(
		'startdate' => get_string('skillsoft_customreportlog_startdate', 'report_skillsoft_customreportlog'),
		'enddate' => get_string('skillsoft_customreportlog_enddate', 'report_skillsoft_customreportlog'),
		'handle' => get_string('skillsoft_customreportlog_handle', 'report_skillsoft_customreportlog'),
		'timerequested' => get_string('skillsoft_customreportlog_timerequested', 'report_skillsoft_customreportlog'),
		'timedownloaded' => get_string('skillsoft_customreportlog_timedownloaded', 'report_skillsoft_customreportlog'),
		'timeimported' => get_string('skillsoft_customreportlog_timeimported', 'report_skillsoft_customreportlog'),
		'timeprocessed' => get_string('skillsoft_customreportlog_timeprocessed', 'report_skillsoft_customreportlog'),
		'url' => get_string('skillsoft_customreportlog_url', 'report_skillsoft_customreportlog'),
	);
	$hcolumns = array();


	if (!isset($columns[$sort])) {
		$sort = 'handle';
	}

	foreach ($columns as $column=>$strcolumn) {
		if ($column != 'url') {
			if ($sort != $column) {
				$columnicon = '';
				$columndir = 'ASC';
			} else {
				$columndir = $dir == 'ASC' ? 'DESC':'ASC';
				$columnicon = $dir == 'ASC' ? 'down':'up';
				$columnicon = " <img src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";

			}
			//If we sort revert to page 0
			$hcolumns[$column] = "<a href=\"index.php?sort=$column&amp;dir=$columndir&amp;perpage=$perpage\">".$strcolumn."</a>$columnicon";
		} else {
			$hcolumns[$column] = $strcolumn;
		}
	}

	$baseurl = new moodle_url('index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
	echo $OUTPUT->paging_bar($changescount, $page, $perpage, $baseurl);

	$table = new html_table();
	$table->head  = array(
	$hcolumns['startdate'], $hcolumns['enddate'], $hcolumns['handle'],
	$hcolumns['timerequested'], $hcolumns['timedownloaded'], $hcolumns['timeimported'],
	$hcolumns['timeprocessed'], $hcolumns['url'],
	);
	$table->width = '95%';
	$table->data  = array();

	$orderby = "$sort $dir";

	$reports = $DB->get_recordset('skillsoft_report_track', array(), $orderby, '*', $page*$perpage, $perpage);

	$strurldownload = get_string('skillsoft_customreportlog_urldownload', 'report_skillsoft_customreportlog');
	
	foreach ($reports as $rec) {
		$row = array();
		$row[] = $rec->startdate;
		$row[] = $rec->enddate;
		$row[] = $rec->handle;
		$row[] = userdate($rec->timerequested);
		$row[] = $rec->downloaded == 1? userdate($rec->timedownloaded):'';
		$row[] = $rec->imported == 1 ? userdate($rec->timeimported):'';
		$row[] = $rec->processed == 1 ? userdate($rec->timeprocessed):'';
		$row[] = $rec->downloaded == 1 ? '<a href="'.new moodle_url($rec->url).'" target="_blank">'.$strurldownload.'</a>' :'';
		$table->data[] = $row;
	}
	$reports->close();

	echo html_writer::table($table);

}
echo $OUTPUT->footer();
