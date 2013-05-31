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
 * Main file for skillsoft_tdrreport report
 * 
 * Simple diagnostics report to show the tdr records stored in the database.
 *
 * @package    report_skillsoft_tdrreport
 * @copyright  2013 onwards Martin Holden}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'tdrid', PARAM_ALPHA);
$dir     = optional_param('dir', 'ASC', PARAM_ALPHA);
$filtervalue = optional_param('filtervalue', '', PARAM_RAW);
$filterby = optional_param('filterby', 'userid', PARAM_ALPHA);

$PAGE->set_url('/report/skillsoft_tdrreport/index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
$PAGE->set_pagelayout('report');

require_login();
require_capability('report/skillsoft_tdrreport:view', context_system::instance());

if ($filtervalue == '') {
   $where = ''; 
} else {
	switch ($filterby) {
    case "userid":
        $where = 'userid = '.$filtervalue.' '; 
        break;
    case "tdrid":
        $where = 'tdrid = '.$filtervalue.' '; 
        break;
    case "assetid":
        $where = 'assetid = "'.$filtervalue.'" '; 
        break;
	}
}

$changescount = $DB->count_records_select('skillsoft_tdr',$where);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_skillsoft_tdrreport'));

echo $OUTPUT->box_start();     // The forms section at the top





?>
<div class="mdl-align">
<form method="get" action="index.php">
  <div>
    <label for="filtervalue_el"><?php print_string('skillsoft_tdrreport_filterlabel', 'report_skillsoft_tdrreport') ?></label>
    <input type="text" name="filtervalue" id="filtervalue_el" value="<?php echo $filtervalue ?>" />
    <?php $options = array('tdrid' => get_string('skillsoft_tdrreport_tdrid', 'report_skillsoft_tdrreport'),
                     'userid' => get_string('skillsoft_tdrreport_userid', 'report_skillsoft_tdrreport'),
                     'assetid' => get_string('skillsoft_tdrreport_assetid', 'report_skillsoft_tdrreport')
    			);
     echo html_writer::select($options, 'filterby', $filterby, false);
     ?>
    <input type="submit" value="<?php print_string('skillsoft_tdrreport_applyfilter', 'report_skillsoft_tdrreport')?>" />
  </div>
</form>
</div>
<p><?php echo get_string('skillsoft_tdrreport_recordcount', 'report_skillsoft_tdrreport').' '.$changescount;?></p>
<?php
echo $OUTPUT->box_end();



$columns = array('tdrid'		=> get_string('skillsoft_tdrreport_tdrid', 'report_skillsoft_tdrreport'),
                 'timestamp'	=> get_string('skillsoft_tdrreport_timestamp', 'report_skillsoft_tdrreport'),
                 'userid' 		=> get_string('skillsoft_tdrreport_userid', 'report_skillsoft_tdrreport'),
                 'username'     => get_string('skillsoft_tdrreport_username', 'report_skillsoft_tdrreport'),
                 'assetid'      => get_string('skillsoft_tdrreport_assetid', 'report_skillsoft_tdrreport'),
                 'data'			=> get_string('skillsoft_tdrreport_data', 'report_skillsoft_tdrreport'),
                 'processed'    => get_string('skillsoft_tdrreport_processed', 'report_skillsoft_tdrreport'),
                );
$hcolumns = array();


if (!isset($columns[$sort])) {
    $sort = 'tdrid';
}

foreach ($columns as $column=>$strcolumn) {
	if ($column != 'data') {
	    if ($sort != $column) {
	        $columnicon = '';
	        $columndir = 'ASC';
	    } else {
	        $columndir = $dir == 'ASC' ? 'DESC':'ASC';
	        $columnicon = $dir == 'ASC' ? 'down':'up';
	        $columnicon = " <img src=\"" . $OUTPUT->pix_url('t/' . $columnicon) . "\" alt=\"\" />";
	
	    }
	    $hcolumns[$column] = "<a href=\"index.php?sort=$column&amp;dir=$columndir&amp;page=$page&amp;perpage=$perpage&amp;filtervalue=$filtervalue&amp;filterby=$filterby\">".$strcolumn."</a>$columnicon";
	} else {
		$hcolumns[$column] = $strcolumn;
	}
}

$baseurl = new moodle_url('index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'filtervalue' => $filtervalue, 'filterby' => $filterby));
echo $OUTPUT->paging_bar($changescount, $page, $perpage, $baseurl);

$table = new html_table();
$table->head  = array($hcolumns['tdrid'], $hcolumns['timestamp'], $hcolumns['userid'], $hcolumns['username'], $hcolumns['assetid'], $hcolumns['data'], $hcolumns['processed']);
$table->width = '95%';
$table->data  = array();

if ($sort == 'userid') {
    $orderby = "u.id $dir";
} else {
    $orderby = "tdr.$sort $dir";
}

if ($filtervalue == '') {
   $where = ''; 
} else {
	switch ($filterby) {
    case "userid":
        $where = 'WHERE tdr.userid = '.$filtervalue.' '; 
        break;
    case "tdrid":
        $where = 'WHERE tdr.tdrid = '.$filtervalue.' '; 
        break;
    case "assetid":
        $where = 'WHERE tdr.assetid = "'.$filtervalue.'" '; 
        break;
	}
}

$ufields = user_picture::fields('u');
$sql = "SELECT $ufields,
               tdr.*
          FROM {skillsoft_tdr} tdr
          LEFT JOIN {user} u ON u.id = tdr.userid
          $where
      ORDER BY $orderby";


//$orderby = "$sort $dir";
//$rs = $DB->get_recordset('skillsoft_tdr',null,$orderby,'*', $page * $perpage, $perpage);

$rs = $DB->get_recordset_sql($sql, array(), $page*$perpage, $perpage);

foreach ($rs as $tdr) {
	$minheight = strlen($tdr->data) / 200;
	$textareaattributes = array('name'=>'tdrdata['.$tdr->tdrid.']', 'cols'=>40, 'rows'=>5, 'readonly'=>'true');
	if ($minheight>1) {
    	$textareaattributes['style'] = 'min-height:' . (int) 4*$minheight . 'em;';
	}
    $textarea = html_writer::tag('textarea', $tdr->data, $textareaattributes);
	
    $row = array();
    $row[] = $tdr->tdrid;
    $row[] = userdate($tdr->timestamp);
    $row[] = html_writer::link(new moodle_url("/user/view.php?id={$tdr->userid}"), fullname($tdr));
    $row[] = $tdr->username;
    $row[] = $tdr->assetid;
    $row[] = $textarea;
    $row[] = $tdr->processed == 1? 'Yes': 'No';
    $table->data[] = $row;
}
$rs->close();

echo html_writer::table($table);

echo $OUTPUT->footer();
