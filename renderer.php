<?php
/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2014 Catalyst IT Europe Ltd
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package mod-skillsoft
 * @author Phil Lello <philipl@catalyst-eu.net>
 * @copyright  Catalyst IT Ltd 2014 <http://catalyst-eu.net>
 */

class mod_skillsoft_renderer extends plugin_renderer_base {

    /**
     * Render the list contents (<li> nodes) for the skillsoft
     * catalouge at the path specified by parentgroups.
     *
     * @param string $groups Space seperated list of groups from root
     * @return string Rendered HTML
     */
    public function render_catalogue_items($groups, $recurse = 0) {

        $doc = skillsoft_full_course_listing_document();
        $xpath = new DOMXPath($doc);

        // Arguably the catalogue pruning should happen at import time
        $query = '//group[not(child::*)]';
        do {
            $nodes = $xpath->query($query);
            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                $node->parentNode->removeChild($node);
            }
        } while ($nodes->length > 0);

        return $this->_render_catalogue_items($doc, $xpath, $groups, $recurse);
    }

    private function _render_catalogue_items(DOMDocument &$doc, DOMXPath &$xpath, $groups, $recurse) {
        global $DB;

        $result = '';
        $query = '/full_listing_detail';
        if ($groups) {
            foreach (explode(' ', $groups) as $group) {
                $query .= '/group[@id="' . $group . '"]';
            }
        }
        $query .= '/*[name()="asset" or name()="group"]';

        $nodes = $xpath->query($query);
        for ($i = 0; $i < $nodes->length; $i++) {
            $node = $nodes->item($i);
            $title = $node->getAttribute('title');
            $id = $node->getAttribute('id');
            $type = $node->nodeName;
            if ($type == 'group') {
                $ids = array();
                while ($node->nodeName == 'group') {
                    array_unshift($ids, $node->getAttribute('id'));
                    $node = $node->parentNode;
                };
                $url = 'ajax/catalogue.php?groups='.implode($ids, ' ');
                $caption  = $this->pix_icon('t/collapsed', get_string('expand', 'mod_skillsoft'), 'moodle', array('class' => 'expand'));
                $caption .= $this->pix_icon('t/expanded', get_string('collapse', 'mod_skillsoft'), 'moodle', array('class' => 'collapse'));
                $caption .= $title;
                $link = html_writer::link($url, $caption);
                if ($recurse) {
                    $result .= html_writer::start_tag('li', array('class' => 'expanded skillsoft-group'));
                    $result .= $link;
                    $result .= html_writer::tag(
                        'ul',
                        $this->_render_catalogue_items($doc, $xpath, implode($ids, ' '), $recurse),
                        array('class' => 'loaded')
                    );
                    $result .= html_writer::end_tag('li');
                } else {
                    $result .= html_writer::start_tag('li', array('class' => 'collapsed skillsoft-group'));
                    $result .= $link;
                    // <ul /> would cause problems
                    $result .= html_writer::start_tag('ul') . html_writer::end_tag('ul');
                    $result .= html_writer::end_tag('li');
                }
            } else {
                $caption  = $title;
                $caption .= $this->pix_icon('i/info', get_string('info', 'mod_skillsoft'), 'moodle', array('class' => 'info'));
                $result .= html_writer::tag('li', $caption, array('class' => 'skillsoft-asset', 'data-asset' => $id));
            }
        }
        return $result;
    }

    /**
     * Render the list contents (<li> nodes) for the moodle
     * category specified by $category
     *
     * @param int $category The parent category
     * @return string Rendered HTML
     */
    function render_category($category, $recurse = 0) {
        global $CFG, $DB;

        require_once($CFG->libdir . '/coursecatlib.php');

        $result = "";
        $coursecat = coursecat::get($category);
        foreach ($coursecat->get_children() as $child) {
            $url = 'ajax/category.php?category='.$child->id;
            $caption  = $this->pix_icon('t/collapsed', get_string('expand', 'mod_skillsoft'), 'moodle', array('class' => 'expand'));
            $caption .= $this->pix_icon('t/expanded', get_string('collapse', 'mod_skillsoft'), 'moodle', array('class' => 'collapse'));
            $caption .= $child->name;
            $link = html_writer::link($url, $caption);
            if ($recurse) {
                $result .= html_writer::start_tag('li', array('class' => 'expanded category', 'data-category' => $child->id));
                $result .= $link;
                $result .= html_writer::tag('ul', $this->render_category($child->id, $recurse), array('class' => 'loaded'));
            } else {
                $result .= html_writer::start_tag('li', array('class' => 'collapsed category', 'data-category' => $child->id));
                $result .= $link;
                // <ul /> would cause problems
                $result .= html_writer::start_tag('ul') . html_writer::end_tag('ul');
            }
            $result .= html_writer::end_tag('li');
        }
        return $result;
    }

    function render_asset($asset) {

        $result = "";
        $data = skillsoft_get_asset_metadata($asset);
        $properties = array();
        for ($i = 0; $i < $data->childNodes->length; $i++) {
            $child = $data->childNodes->item($i);
            $properties[$child->nodeName] = $child->textContent;
        }
        $table = new html_table();
        foreach (array('dc:identifier', 'dc:title', 'dc:language', 'olsa:duration', 'dc:description', 'olsa:prerequisites') as $field) {
            $table->data[] = array(get_string($field, 'mod_skillsoft'), $properties[$field]);
        }
        $result .= html_writer::table($table);
        return $result;
    }
}
