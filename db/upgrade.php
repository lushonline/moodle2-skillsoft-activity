<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * xmldb_skillsoft_upgrade
 *
 * @param int $oldversion
 * @return bool
 */

function xmldb_skillsoft_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $result = true;


//	// Adding missing 'intro' and 'introformat' field to table skillsoft
    if ($result && $oldversion < 2011080100) {
        $table = new xmldb_table('skillsoft');
        $summaryfield = new xmldb_field('summary', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'name');

	    /// Launch rename field summary
        $dbman->rename_field($table, $summaryfield, 'intro');


        //Add intr0format
        $introformatfield = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, '1', 'intro');
		if (!$dbman->field_exists($table,$introformatfield)) {
        	$dbman->add_field($table, $introformatfield);
		}

		upgrade_mod_savepoint(true, 2011080100, 'skillsoft');
        $result = true;
    }

    if ($result && $oldversion < 2011080500) {
    	upgrade_mod_savepoint(true, 2011080500, 'skillsoft');
    	$result = true;
    }

    if ($result && $oldversion < 2011080600) {
	    	upgrade_mod_savepoint(true, 2011080600, 'skillsoft');
	    	$result = true;
    }

    if ($result && $oldversion < 2011080601) {
	    	upgrade_mod_savepoint(true, 2011080601, 'skillsoft');
	    	$result = true;
    }


    if ($result && $oldversion < 2011080602) {
	    	upgrade_mod_savepoint(true, 2011080602, 'skillsoft');
	    	$result = true;
    }

    if ($result && $oldversion < 2011090100) {
	    	upgrade_mod_savepoint(true, 2011090100, 'skillsoft');
	    	$result = true;
    }

    if ($result && $oldversion < 2011092000) {
	    	upgrade_mod_savepoint(true, 2011092000, 'skillsoft');
	    	$result = true;
    }

	if ($result && $oldversion < 2012022900) {
			// Drop the index on the skillsoft_report_results table as it may
			// prvent upgrades to Moodle 2.2 which introduces restrictive checks
			// on indexes see http://tracker.moodle.org/browse/MDL-29314
			 
			// Define index loginname-assetid-firstaccessdate (unique) to be dropped form skillsoft_report_results
	        $table = new xmldb_table('skillsoft_report_results');
	        $index = new xmldb_index('loginname-assetid-firstaccessdate', XMLDB_INDEX_UNIQUE, array('loginname', 'assetid', 'firstaccessdate'));
	
	        // Conditionally launch drop index loginname-assetid-firstaccessdate
	        if ($dbman->index_exists($table, $index)) {
	            $dbman->drop_index($table, $index);
	        }
		
	    	upgrade_mod_savepoint(true, 2012022900, 'skillsoft');
	    	$result = true;
    }

	if ($result && $oldversion < 2012081000) {
	    	upgrade_mod_savepoint(true, 2012081000, 'skillsoft');
	    	$result = true;
    }
    
    if ($result && $oldversion < 2012090700) {
    	upgrade_mod_savepoint(true, 2012090700, 'skillsoft');
    	$result = true;
    }

    if ($result && $oldversion < 2013010200) {
    	upgrade_mod_savepoint(true, 2013010200, 'skillsoft');
    	$result = true;
    }
    
    if ($result && $oldversion < 2013051400) {
    	upgrade_mod_savepoint(true, 2013051400, 'skillsoft');
    	$result = true;
    }
    
    if ($result && $oldversion < 2013091300) {
    	upgrade_mod_savepoint(true, 2013091300, 'skillsoft');
    	$result = true;
    }
    
	if ($result && $oldversion < 2014051300) {
    	upgrade_mod_savepoint(true, 2014051300, 'skillsoft');
    	$result = true;
    }
    
    if ($result && $oldversion < 2014051301) {
     	// Define field aiccwindowsettings to be added to skillsoft.
        $table = new xmldb_table('skillsoft');
        $field = new xmldb_field('aiccwindowsettings', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'timecreated');

        // Conditionally launch add field aiccwindowsettings.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    	$skillsofts = $DB->get_recordset('skillsoft');
        foreach ($skillsofts as $skillsoft) {
                $skillsoft->aiccwindowsettings = $CFG->skillsoft_aiccwindowsettings;
                $skillsoft->timemodified = time();
            $DB->update_record('skillsoft', $skillsoft);
        }
        
        
        // Skillsoft savepoint reached.
        upgrade_mod_savepoint(true, 2014051301, 'skillsoft');
        $result = true;
    }
    
	if ($result && $oldversion < 2014090400) {
    	upgrade_mod_savepoint(true, 2014090400, 'skillsoft');
    	$result = true;
    }

	if ($result && $oldversion < 2014090401) {
    	upgrade_mod_savepoint(true, 2014090401, 'skillsoft');
    	$result = true;
    }

	if ($result && $oldversion < 2014100601) {
    	upgrade_mod_savepoint(true, 2014100601, 'skillsoft');
    	$result = true;
    }
    
    if ($result && $oldversion < 2014120201) {
    	upgrade_mod_savepoint(true, 2014120201, 'skillsoft');
    	$result = true;
    }

    if ($result && $oldversion < 2015031900) {
    	upgrade_mod_savepoint(true, 2015031900, 'skillsoft');
    	$result = true;
    }

    if ($result && $oldversion < 2015031901) {
    	upgrade_mod_savepoint(true, 2015031901, 'skillsoft');
    	$result = true;
    }    
        
    if ($result && $oldversion < 2015080400) {
        $table = new xmldb_table('skillsoft');
        $field = new xmldb_field('completionsync', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table,$field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015080400, 'skillsoft');
        $result = true;
    }

    if ($result && $oldversion < 2015082000) {
        // Check for completed skillsoft activities that do not have a corresponding activity completion record.
        require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');
        require_once($CFG->libdir.'/completionlib.php');

        // Get skillsoft activities.
        $modtype = $DB->get_record('modules', array('name' => 'skillsoft'), 'id', MUST_EXIST);
        $moduleinstances = $DB->get_recordset('course_modules', array('module' => $modtype->id), 'id', 'id,course,instance');

        foreach ($moduleinstances as $instance) {
            $cm = get_coursemodule_from_instance('skillsoft', $instance->instance, $instance->course, false, MUST_EXIST);
            // Get instance track data.
            $tracks = $DB->get_recordset('skillsoft_au_track', array('skillsoftid' => $cm->instance, 'element' => '[CORE]lesson_status'), 'id');
            // for each track, update the activity completion.
            foreach ($tracks as $track) {
                // Get the correct completed time.
                $track->timecompleted = $DB->get_field('skillsoft_au_track', 'value',
                    array('skillsoftid' => $track->skillsoftid, 'element' => '[SUMMARY]completed', 'userid' => $track->userid, 'attempt' => $track->attempt));
                skillsoft_setActivityCompletionState($track->userid, $track->skillsoftid, $track->value, $track->timecompleted);
            }
            $tracks->close();
        }
        $moduleinstances->close();

        upgrade_mod_savepoint(true, 2015082000, 'skillsoft');
        $result = true;
    }

	return $result;
}
