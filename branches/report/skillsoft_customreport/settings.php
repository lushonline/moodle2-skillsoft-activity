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
 * Settings and Link file for skillsoft_customreport report
 *
 * @package    report_skillsoft_customreport
 * @copyright  2013 onwards Martin Holden}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
$ADMIN->add('reports', new admin_category('skillsoft_reports', get_string('skillsoft_report_menu', 'report_skillsoft_customreport')));
$ADMIN->add('skillsoft_reports', new admin_externalpage('report_skillsoft_customreport', get_string('pluginname', 'report_skillsoft_customreport'), "$CFG->wwwroot/report/skillsoft_customreport/index.php",'report/skillsoft_customreport:view'));

// no report settings
$settings = null;
