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
 *
 * @package   mod-skillsoft
 * @author    Phil Lello <philipl@catalyst-eu.net>
 * @copyright 2014 Catalyst IT Europe Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->libdir . '/formslib.php');
require_once(dirname(__FILE__).'/cataloguesync_form.php');

$context = context_system::instance();
require_capability('moodle/course:create', $context);
$PAGE->set_context($context);

require_login();

$PAGE->set_url('/mod/skillsoft/cataloguesync.php');

//Retrieve the localisation strings
$strskillsoft = get_string('modulename', 'skillsoft');
$strskillsofts = get_string('modulenameplural', 'skillsoft');
$strbulkimport = get_string('cataloguesync', 'skillsoft');

$PAGE->set_title($strskillsofts);
$PAGE->set_heading($strbulkimport);
$PAGE->navbar->add($strskillsofts);

$PAGE->set_periodic_refresh_delay(60);

echo $OUTPUT->header();

$mform = new mod_skillsoft_cataloguesync_form();
if ($data = $mform->get_data()) {
    skillsoft_queue_full_course_listing_download();
} else {
echo $mform->render();
}

echo $OUTPUT->footer();

