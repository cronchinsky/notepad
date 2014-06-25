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
 * This file keeps track of upgrades to the notepad module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod
 * @subpackage notepad
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute notepad upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_notepad_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

   if ($oldversion < 2012100200) {

        // Define field directions to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('directions', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'prompts');

        // Conditionally launch add field directions
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notepad savepoint reached
        upgrade_mod_savepoint(true, 2012100200, 'notepad');
    }
    
    if ($oldversion < 2012100206) {

        // Define field directions to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('weight', XMLDB_TYPE_INTEGER, '5', null, null, null, 0, 'directions');

        // Conditionally launch add field directions
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notepad savepoint reached
        upgrade_mod_savepoint(true, 2012100206, 'notepad');
    }
    
    if ($oldversion < 2014060101) {

        // Define field textfield to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('textfield', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'weight');

        // Conditionally launch add field textfield
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notes savepoint reached
        upgrade_mod_savepoint(true, 2014060101, 'notepad');
    }

    if ($oldversion < 2014060200) {

        // Define field textfield to be added to notepad
        $table = new xmldb_table('notepad');
        $field = new xmldb_field('textfield', XMLDB_TYPE_TEXT, 'big', null, null, null, null, 'grade');

        // Conditionally launch add field textfield
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notes savepoint reached
        upgrade_mod_savepoint(true, 2014060200, 'notepad');
    }
    
        if ($oldversion < 2014060201) {

        // Changing precision of field textfield on table notepad_sessions to (medium)
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('textfield', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'weight');

        // Launch change of precision for field textfield
        $dbman->change_field_precision($table, $field);
        
              // Define field textfieldformat to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('textfieldformat', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, '0', 'textfield');

        // Conditionally launch add field textfieldformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define field textfieldtrust to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('textfieldtrust', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, '0', 'textfieldformat');

        // Conditionally launch add field textfieldtrust
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notes savepoint reached
        upgrade_mod_savepoint(true, 2014060201, 'notepad');
    }
    
       if ($oldversion < 2014060300) {

        // Define field wysiwyg to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('wysiwyg', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, null, 'textfieldtrust');

        // Conditionally launch add field wysiwyg
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define field wysiwyg_prompt to be added to notepad_sessions
        $table = new xmldb_table('notepad_sessions');
        $field = new xmldb_field('wysiwyg_prompt', XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'wysiwyg');

        // Conditionally launch add field wysiwyg_prompt
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notes savepoint reached
        upgrade_mod_savepoint(true, 2014060300, 'notepad');
    }
        if ($oldversion < 2014062400) {
   
              
        $table = new xmldb_table('notepad_wysiwyg');
 
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('textfield', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'id');
		$table->add_field('textfieldformat', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, null, 'textfield');
		$table->add_field('textfieldtrust', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, null, null, null, 'textfieldformat');
 
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);
 
		$status = $dbman->create_table($table);

        // notes savepoint reached
        upgrade_mod_savepoint(true, 2014062400, 'notepad');
    }
    
        if ($oldversion < 2014062401) {

        // Define field sid to be added to notepad_wysiwyg
        $table = new xmldb_table('notepad_wysiwyg');
        $field = new xmldb_field('sid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'id');

        // Conditionally launch add field sid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
                // Define field uid to be added to notepad_wysiwyg
        $table = new xmldb_table('notepad_wysiwyg');
        $field = new xmldb_field('uid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'sid');

        // Conditionally launch add field uid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // notes savepoint reached
        upgrade_mod_savepoint(true, 2014062401, 'notepad');
    }
    return true;
}
