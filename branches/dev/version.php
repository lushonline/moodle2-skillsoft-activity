<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$module->version  = 2014090401;  // If version == 0 then module will not be installed
$module->requires = 2010080300;  // Requires this Moodle version
$module->cron     = 60;           // Period for cron to check this module (secs)
$module->component = 'mod_skillsoft'; // Full name of the plugin (used for diagnostics)