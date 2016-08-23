<?php
namespace mod_skillsoft\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Event for when a page activity is viewed.
 *
 * @package    mod_page
 * @since      Moodle 2.6
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_viewed extends \core\event\course_module_viewed {
    protected function init() {
        parent::init();
    	$this->data['crud'] = 'r';
        $this->data['level'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'skillsoft';
    }
}


