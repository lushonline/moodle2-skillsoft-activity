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
 * Downloadable in various formats.
 *
 * @package    report_skillsoft_customreportlog
 * @copyright  2013 onwards Martin Holden}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/excellib.class.php');
require_once($CFG->libdir.'/odslib.class.php');

$out = optional_param('out', 'html', PARAM_TAG);   // Output (html, xls, ods, txt) defaults to html.

$PAGE->set_url('/report/skillsoft_customreportlog/index.php', array('out' => $out));
$PAGE->set_pagelayout('report');

require_login();
require_capability('report/skillsoft_customreportlog:view', context_system::instance());

//Get strings
//Global
$strreports = get_string('reports');
$strusers = get_string('users');

//Plugin Specific
$strname = get_string('pluginname', 'report_skillsoft_customreportlog');
$strfilename = get_string('skillsoft_customreport_filename', 'report_skillsoft_customreportlog');

$strstartdate = get_string('skillsoft_customreport_startdate', 'report_skillsoft_customreportlog');
$strenddate = get_string('skillsoft_customreport_enddate', 'report_skillsoft_customreportlog');
$strhandle = get_string('skillsoft_customreport_handle', 'report_skillsoft_customreportlog');
$strtimerequested = get_string('skillsoft_customreport_timerequested', 'report_skillsoft_customreportlog');
$strtimedownloaded = get_string('skillsoft_customreport_timedownloaded', 'report_skillsoft_customreportlog');
$strtimeimported = get_string('skillsoft_customreport_timeimported', 'report_skillsoft_customreportlog');
$strtimeprocessed = get_string('skillsoft_customreport_timeprocessed', 'report_skillsoft_customreportlog');
$strurl = get_string('skillsoft_customreport_url', 'report_skillsoft_customreportlog');
$strurldownload = get_string('skillsoft_customreport_urldownload', 'report_skillsoft_customreportlog');

//Get all the report records
$reports = $DB->get_records_select('skillsoft_report_track', '', null, 'id desc', '*');

// Content output (different based on $out).

if ($out == 'xls') { // XLS output.
    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($strfilename . '.xls');
    $worksheet = $workbook->add_worksheet($strfilename);

    //Output header row
    $row = 0;
    $worksheet->write($row, 0, $strstartdate);
    $worksheet->write($row, 1, $strenddate);
    $worksheet->write($row, 2, $strhandle);
    $worksheet->write($row, 3, $strurl);
    $worksheet->write($row, 4, $strtimerequested);
    $worksheet->write($row, 5, $strtimedownloaded);
    $worksheet->write($row, 6, $strtimeimported);
    $worksheet->write($row, 7, $strtimeprocessed);
    
    //Move to first row of data
    $row++;

    foreach ($reports as $rec) {
        $worksheet->write($row, 0, $rec->startdate);
        $worksheet->write($row, 1, $rec->enddate);
        $worksheet->write($row, 2, $rec->handle);
        $worksheet->write($row, 3, $rec->downloaded == 1 ? $rec->url :'');
        $worksheet->write($row, 4, date('c',$rec->timerequested));
        $worksheet->write($row, 5, $rec->downloaded == 1? date('c',$rec->timedownloaded):'');
        $worksheet->write($row, 6, $rec->imported == 1 ? date('c',$rec->timeimported):'');
        $worksheet->write($row, 7, $rec->processed == 1 ? date('c',$rec->timeprocessed):'');
        $row++;
    }
    $workbook->close();

} else if ($out == 'ods') { // ODS output.
	$workbook = new MoodleODSWorkbook('-');
    $workbook->send($strfilename . '.ods');
    $worksheet = $workbook->add_worksheet($strfilename);

    //Output header row
    $row = 0;
    $worksheet->write($row, 0, $strstartdate);
    $worksheet->write($row, 1, $strenddate);
    $worksheet->write($row, 2, $strhandle);
    $worksheet->write($row, 3, $strurl);
    $worksheet->write($row, 4, $strtimerequested);
    $worksheet->write($row, 5, $strtimedownloaded);
    $worksheet->write($row, 6, $strtimeimported);
    $worksheet->write($row, 7, $strtimeprocessed);
    
    //Move to first row of data
    $row++;

    foreach ($reports as $rec) {
        $worksheet->write($row, 0, $rec->startdate);
        $worksheet->write($row, 1, $rec->enddate);
        $worksheet->write($row, 2, $rec->handle);
        $worksheet->write($row, 3, $rec->downloaded == 1 ? $rec->url :'');
        $worksheet->write($row, 4, date('c',$rec->timerequested));
        $worksheet->write($row, 5, $rec->downloaded == 1? date('c',$rec->timedownloaded):'');
        $worksheet->write($row, 6, $rec->imported == 1 ? date('c',$rec->timeimported):'');
        $worksheet->write($row, 7, $rec->processed == 1 ? date('c',$rec->timeprocessed):'');
        $row++;
    }
    $workbook->close();
} else if ($out == 'txt') { // CSV output.
    header("Content-Type: application/download\n");
    header("Content-Disposition: attachment; filename={$strfilename}.txt");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    //Header
    echo '"'.$strstartdate.'",';
    echo '"'.$strenddate.'",';
    echo '"'.$strhandle.'",';
    echo '"'.$strurl.'",';
    echo '"'.$strtimerequested.'",';
    echo '"'.$strtimedownloaded.'",';
    echo '"'.$strtimeimported.'",';
    echo '"'.$strtimeprocessed.'"';
    echo "\r\n";

    foreach ($reports as $rec) {
        echo '"'.$rec->startdate.'",';
        echo '"'.$rec->enddate.'",';
        echo '"'.$rec->handle.'",';
        echo '"'.($rec->downloaded == 1 ? $rec->url :'').'",';
        echo '"'.date('c',$rec->timerequested).'",';
        echo '"'.($rec->downloaded == 1? date('c',$rec->timedownloaded):'').'",';
        echo '"'.($rec->imported == 1 ? date('c',$rec->timeimported):'').'",';
        echo '"'.($rec->processed == 1 ? date('c',$rec->timeprocessed):'').'"';
        echo "\r\n";
    }

} else { // HTML output.
	echo $OUTPUT->header();
	echo $OUTPUT->heading(get_string('pluginname', 'report_skillsoft_customreportlog'));

    // Print download selector.
    $options = array('xls' => get_string('downloadexcel'),
                     'ods' => get_string('downloadods'),
                     'txt' => get_string('downloadtext'));
    echo $OUTPUT->single_select(new moodle_url('/report/skillsoft_customreportlog/index.php'),'out', $options);

    echo '<div class="clearer">&nbsp;</div>';

    $table = new html_table();
    $table->width = '90%';
    $table->head = array($strstartdate,$strenddate,$strhandle,$strurl,$strtimerequested,$strtimedownloaded,$strtimeimported,$strtimeprocessed);
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
    foreach ($reports as $rec) {
        $table->data[] = new html_table_row(array(
	        $rec->startdate,
	        $rec->enddate,
	        $rec->handle,
	        $rec->downloaded == 1 ? '<a href="'.new moodle_url($rec->url).'" target="_blank">'.$strurldownload.'</a>' :'',
	        userdate($rec->timerequested),
	        $rec->downloaded == 1? userdate($rec->timedownloaded):'',
	        $rec->imported == 1 ? userdate($rec->timeimported):'',
	        $rec->processed == 1 ? userdate($rec->timeprocessed):''
           ));
    }
    echo html_writer::table($table);

    echo $OUTPUT->footer();
}

// Close the recordset (common for all outputs).
$reports->close();
