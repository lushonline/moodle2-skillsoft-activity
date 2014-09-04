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
 * @package mod
 * @subpackage skillsoft
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_survey_activity_task
 */

/**
 * Structure step to restore one survey activity
 */
class restore_skillsoft_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = false;

        $paths[] = new restore_path_element('skillsoft', '/activity/skillsoft');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_skillsoft($data) {
        global $DB, $CFG;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        
        
        
        // insert the skillsoft record
        $newitemid = $DB->insert_record('skillsoft', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
        
        //Enable (re-)grading:
        require_once($CFG->dirroot . '/mod/skillsoft/locallib.php');
        skillsoft_reset_processed($data->assetid);
    }

    protected function after_execute() {
        // Add survey related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_skillsoft', 'intro', null);
    }
}