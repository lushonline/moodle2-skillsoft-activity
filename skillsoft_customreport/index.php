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
 * Main file for skillsoft_customreport report
 *
 * Simple diagnostics report to show the custom report records stored in the database.
 *
 * @package    report_skillsoft_customreport
 * @copyright  2013 onwards Martin Holden}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// page parameters
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 30, PARAM_INT);    // how many per page
$sort    = optional_param('sort', 'userid', PARAM_ALPHA);
$dir     = optional_param('dir', 'ASC', PARAM_ALPHA);
$filtervalue = optional_param('filtervalue', '', PARAM_RAW);
$filterby = optional_param('filterby', 'userid', PARAM_ALPHA);

$PAGE->set_url('/report/skillsoft_customreport/index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage,'filtervalue' => $filtervalue, 'filterby' => $filterby));
$PAGE->set_pagelayout('report');

require_login();
require_capability('report/skillsoft_customreport:view', context_system::instance());

if ($filtervalue == '') {
	$where = '';
} else {
	switch ($filterby) {
		case "userid":
			if (is_numeric($filtervalue)) {
				$where = 'userid = '.$filtervalue.' ';
			} else {
				$filtervalue = '';
			}
			break;
		case "loginname":
			$where = 'loginname = "'.$filtervalue.'" ';
			break;
		case "assetid":
			$where = 'assetid = "'.$filtervalue.'" ';
			break;

	}
}

$changescount = $DB->count_records_select('skillsoft_report_results',$where);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_skillsoft_customreport'));

echo $OUTPUT->box_start();     // The forms section at the top





?>
<div class="mdl-align">
<form method="get" action="index.php">
<div><label for="filtervalue_el"><?php print_string('skillsoft_customreport_filterlabel', 'report_skillsoft_customreport') ?></label>
<input type="text" name="filtervalue" id="filtervalue_el"
	value="<?php echo $filtervalue ?>" /> <?php $options = array(
                     	'userid' => get_string('skillsoft_customreport_userid', 'report_skillsoft_customreport'),
    					'loginname' => get_string('skillsoft_customreport_loginname', 'report_skillsoft_customreport'),
                     	'assetid' => get_string('skillsoft_customreport_assetid', 'report_skillsoft_customreport')
	);
	echo html_writer::select($options, 'filterby', $filterby, false);
	?> <input type="submit"
	value="<?php print_string('skillsoft_customreport_applyfilter', 'report_skillsoft_customreport')?>" />
</div>
</form>
</div>
<p><?php echo get_string('skillsoft_customreport_recordcount', 'report_skillsoft_customreport').' '.$changescount;?></p>
	<?php
	echo $OUTPUT->box_end();

	if ($changescount >0) {

		$columns = array('userid' 		=> get_string('skillsoft_customreport_userid', 'report_skillsoft_customreport'),
                 'loginname'     => get_string('skillsoft_customreport_loginname', 'report_skillsoft_customreport'),
                 'assetid'      => get_string('skillsoft_customreport_assetid', 'report_skillsoft_customreport'),
			     'firstaccessdate' => get_string('skillsoft_customreport_firstaccessdate', 'report_skillsoft_customreport'),
				 'lastaccessdate'=> get_string('skillsoft_customreport_lastaccessdate', 'report_skillsoft_customreport'),
				 'completeddate'=> get_string('skillsoft_customreport_completeddate', 'report_skillsoft_customreport'),
				 'firstscore'=> get_string('skillsoft_customreport_firstscore', 'report_skillsoft_customreport'),
				 'bestscore'=> get_string('skillsoft_customreport_bestscore', 'report_skillsoft_customreport'),
				 'currentscore'=> get_string('skillsoft_customreport_currentscore', 'report_skillsoft_customreport'),
				 'lessonstatus'=> get_string('skillsoft_customreport_lessonstatus', 'report_skillsoft_customreport'),
				 'duration'=> get_string('skillsoft_customreport_duration', 'report_skillsoft_customreport'),
				 'accesscount'=> get_string('skillsoft_customreport_accesscount', 'report_skillsoft_customreport'),
				 'attempt'=> get_string('skillsoft_customreport_attempt', 'report_skillsoft_customreport'),
                 'processed'    => get_string('skillsoft_customreport_processed', 'report_skillsoft_customreport'),
		);
		$hcolumns = array();


		if (!isset($columns[$sort])) {
			$sort = 'userid';
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
				//Always revert to page 0 if we sort
				$hcolumns[$column] = "<a href=\"index.php?sort=$column&amp;dir=$columndir&amp;perpage=$perpage&amp;filtervalue=$filtervalue&amp;filterby=$filterby\">".$strcolumn."</a>$columnicon";
			} else {
				$hcolumns[$column] = $strcolumn;
			}
		}

		$baseurl = new moodle_url('index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'filtervalue' => $filtervalue, 'filterby' => $filterby));
		echo $OUTPUT->paging_bar($changescount, $page, $perpage, $baseurl);

		$table = new html_table();
		$table->head  = array(	$hcolumns['userid'], $hcolumns['loginname'], $hcolumns['assetid'],
		$hcolumns['firstaccessdate'],$hcolumns['lastaccessdate'],$hcolumns['completeddate'],
		$hcolumns['firstscore'],$hcolumns['bestscore'],$hcolumns['currentscore'],
		$hcolumns['lessonstatus'],$hcolumns['duration'],$hcolumns['accesscount'],
		$hcolumns['attempt'],$hcolumns['processed']
		);
		$table->width = '95%';
		$table->data  = array();

		$orderby = "rr.$sort $dir";

		if ($filtervalue == '') {
			$where = '';
		} else {
			switch ($filterby) {
				case "userid":
					if (is_numeric($filtervalue)) {
						$where = 'WHERE rr.userid = '.$filtervalue.' ';
					} else {
						$filtervalue = '';
					}
					break;
				case "loginname":
					$where = 'WHERE rr.loginname = "'.$filtervalue.'" ';
					break;
				case "assetid":
					$where = 'WHERE rr.assetid = "'.$filtervalue.'" ';
					break;
			}
		}

		$ufields = user_picture::fields('u');
		$sql = "SELECT $ufields,
               rr.id,
				rr.loginname,
				rr.assetid,
				rr.firstaccessdate,
				rr.lastaccessdate,
				rr.completeddate,
				rr.firstscore,
				rr.currentscore,
				rr.bestscore,
				rr.lessonstatus,
				rr.duration,
				rr.accesscount,
				rr.userid,
				rr.processed,
				rr.attempt
          FROM {skillsoft_report_results} rr
          LEFT JOIN {user} u ON u.id = rr.userid
          $where
      ORDER BY $orderby";


          //$orderby = "$sort $dir";
          //$rs = $DB->get_recordset('skillsoft_tdr',null,$orderby,'*', $page * $perpage, $perpage);

          $rs = $DB->get_recordset_sql($sql, array(), $page*$perpage, $perpage);

          foreach ($rs as $rr) {

          	$row = array();
          	$row[] = html_writer::link(new moodle_url("/user/view.php?id={$rr->userid}"), fullname($rr));
          	$row[] = $rr->loginname;
          	$row[] = $rr->assetid;
          	$row[] = $rr->firstaccessdate != 0? userdate($rr->firstaccessdate):'';
          	$row[] = $rr->lastaccessdate != 0? userdate($rr->lastaccessdate):'';
          	$row[] = $rr->completeddate != 0? userdate($rr->completeddate):'';
          	$row[] = $rr->firstscore;
          	$row[] = $rr->bestscore;
          	$row[] = $rr->currentscore;
          	$row[] = $rr->lessonstatus;
          	$row[] = $rr->duration;
          	$row[] = $rr->accesscount;
          	$row[] = $rr->attempt;
          	$row[] = $tdr->processed == 1? 'Yes': 'No';
          	$table->data[] = $row;
          }
          $rs->close();

          echo html_writer::table($table);
	}
	echo $OUTPUT->footer();
