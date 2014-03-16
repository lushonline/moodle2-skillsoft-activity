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
 * @author 	  Phil Lello <philipl@catalyst-eu.net>
 * @copyright 2014 Catalyst IT Europe Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/locallib.php');

class mod_skillsoft_cataloguesync_form extends moodleform {

	function definition() {

		$mform = $this->_form;

		$mform->addElement('header', 'import', get_string('import', 'mod_skillsoft'));

        $timestamp = skillsoft_full_course_listing_timestamp();
        $text = $timestamp ? date(DATE_RSS, $timestamp) : 'Never';
        if (skillsoft_full_course_listing_download_running()) {
            $text .= ' (' . get_string('running', 'mod_skillsoft') . ')';
        }
        $mform->addElement('static', 'cataloguetime', get_string('cataloguetime', 'mod_skillsoft'), $text);

        $timestamp = skillsoft_asset_metadata_timestamp();
        $text = $timestamp ? date(DATE_RSS, $timestamp) : 'Never';
        if (skillsoft_bulk_course_metadata_download_running()) {
            $text .= ' (' . get_string('running', 'mod_skillsoft') . ')';
        }
        $mform->addElement('static', 'metadatatime', get_string('metadatatime', 'mod_skillsoft'), $text);

        $buttonarray = array();
        if (!skillsoft_full_course_listing_download_running() && !skillsoft_bulk_course_metadata_download_running()) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('importcatalogue', 'mod_skillsoft'));
        }
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
	}
}
