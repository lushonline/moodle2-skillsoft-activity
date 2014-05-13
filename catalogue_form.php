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

require_once($CFG->libdir . '/formslib.php');

class mod_skillsoft_catalogue_form extends moodleform {

    function definition() {
        global $PAGE;

        $mform = $this->_form;

        $renderer = $PAGE->get_renderer('mod_skillsoft');
        $tooltopicsrenderer = $PAGE->get_renderer('tool_topics');
        $PAGE->requires->yui_module(
            'moodle-mod_skillsoft-catalogue',
            'M.mod_skillsoft.init_catalogue',
            array(array(
                'topics' => $tooltopicsrenderer->tree_node_classify_tree(tool_topics_get_topics())
            )));
        $config = array(
            'tree_table' => 'table.topics-tree',
            'classify_tree_table' => 'table.topics-tree-classify',
            'node_prefix' => 'topic-',
        );
        $PAGE->requires->yui_module('moodle-local_agora-tree', 'M.agora_tree.init_tree', array($config));

        $mform->addElement('html', '<p>'.get_string('bulk_instructions', 'mod_skillsoft').'</p>');
        $mform->addElement('html', '<table id="skillsoft-catalogue" width="100%">');
        $mform->addElement('html', '<tr><th class="catalogue">'.get_string('catalogue', 'mod_skillsoft').'<a class="download" href="catalogue_download.php">'.get_string('download').'</a></th>');
        $mform->addElement('html', '<th class="categories">Totara<a class="expand-all">'.get_string('expand_all', 'mod_skillsoft').'</a></th></tr>');
        $mform->addElement('html', '<tr><td width="50%" class="catalogue">');
        $mform->addElement('html', '<div><ul>');
        $mform->addElement('html', $renderer->render_catalogue_items(''));
        $mform->addElement('html', '</ul></div>');
        $mform->addElement('html', '</td><td width="50%" class="categories"><div>');
        $mform->addElement('html', '<ul id="skillsoft-selected">');
        $mform->addElement('html', $renderer->render_category(0));
        $mform->addElement('html', '</ul></div></td></tr>');
        $mform->addElement('html', '</table>');

        $this->add_action_buttons(true, get_string('startbulkimport', 'mod_skillsoft'));
    }
}
