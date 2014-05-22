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
require_once($CFG->dirroot . '/admin/tool/topics/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/catalogue_form.php');

require_login();

$context = context_system::instance();
require_capability('moodle/course:create', $context);
$PAGE->set_context($context);

$renderer = $PAGE->get_renderer('mod_skillsoft');

$PAGE->set_url('/mod/skillsoft/catalogue.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('catalogue', 'mod_skillsoft'));
$PAGE->set_heading(get_string('catalogue', 'mod_skillsoft'));

$mform = new mod_skillsoft_catalogue_form();

echo $OUTPUT->header();
if ($data = $mform->get_data()) {
    // Keep processing even if we detect the user has navigated away
    ignore_user_abort(true);

    $asset_categories = optional_param_array('asset_category', array(), PARAM_INT);
    $asset_classification = optional_param_array('asset_classification', array(), PARAM_TAGLIST);
    foreach ($asset_categories as $asset => $category) {
        set_time_limit(30);
        echo html_writer::tag('div', 'Importing asset ' . $asset);
        $classification = $asset_classification[$asset];
        if ($classification) {
            $classification = explode(',', $classification);
        } else {
            $classification = array();
        }
        skillsoft_import_asset($asset, $category, $classification);
    }
    echo $OUTPUT->single_button('/mod/skillsoft/catalogue.php', get_string('ok'));
} else {
    $mform->display();
    $classifyform = new mod_skillsoft_catalogue_classify_form();
    echo html_writer::tag('div', $classifyform->render(), array('id' => 'classify-form'));
}
echo $OUTPUT->footer();
