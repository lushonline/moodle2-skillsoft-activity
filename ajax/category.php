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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once(dirname(dirname(__FILE__)) . '/locallib.php');

require_login();

$context = context_system::instance();
require_capability('moodle/course:create', $context);
$PAGE->set_context($context);

$category = required_param('category', PARAM_INT);
$recurse = optional_param('recurse', 0, PARAM_BOOL);
$renderer = $PAGE->get_renderer('mod_skillsoft');

echo $renderer->render_category($category, $recurse);
