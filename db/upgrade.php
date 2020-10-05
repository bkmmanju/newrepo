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
 * Upgrade logic.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)).'/locallib.php');

/**
 * Performs data migrations and updates on upgrade.
 *
 * @param   integer   $oldversion
 * @return  boolean
 */
function xmldb_bigbluebuttonbn_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2015080605) {
        // Drop field description.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'description');
        // Change welcome, allow null.
        $fielddefinition = array('type' => XMLDB_TYPE_TEXT, 'precision' => null, 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => null, 'previous' => 'type');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'welcome',
            $fielddefinition);
        // Change userid definition in bigbluebuttonbn_log.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '10', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => null,
            'previous' => 'bigbluebuttonbnid');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn_log', 'userid',
            $fielddefinition);
        // No settings to migrate.
        // Update db version tag.
        upgrade_mod_savepoint(true, 2015080605, 'bigbluebuttonbn');
    }
    if ($oldversion < 2016011305) {
        // Define field type to be droped from bigbluebuttonbn.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'type');
        // Rename table bigbluebuttonbn_log to bigbluebuttonbn_logs.
        xmldb_bigbluebuttonbn_rename_table($dbman, 'bigbluebuttonbn_log', 'bigbluebuttonbn_logs');
        // Rename field event to log in table bigbluebuttonbn_logs.
        xmldb_bigbluebuttonbn_rename_field($dbman, 'bigbluebuttonbn_logs', 'event', 'log');
        // No settings to migrate.
        // Update db version tag.
        upgrade_mod_savepoint(true, 2016011305, 'bigbluebuttonbn');
    }
    if ($oldversion < 2017101000) {
        // Drop field newwindow.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'newwindow');
        // Add field type.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '2', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => 'id');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'type',
            $fielddefinition);
        // Add field recordings_html.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_html',
            $fielddefinition);
        // Add field recordings_deleted.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 1, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_deleted',
            $fielddefinition);
        // Add field recordings_imported.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_imported',
            $fielddefinition);
        // Drop field newwindow.
        xmldb_bigbluebuttonbn_drop_field($dbman, 'bigbluebuttonbn', 'tagging');
        // Migrate settings.
        unset_config('bigbluebuttonbn_recordingtagging_default', '');
        unset_config('bigbluebuttonbn_recordingtagging_editable', '');
        $cfgvalue = get_config('', 'bigbluebuttonbn_importrecordings_from_deleted_activities_enabled');
        set_config('bigbluebuttonbn_importrecordings_from_deleted_enabled', $cfgvalue, '');
        unset_config('bigbluebuttonbn_importrecordings_from_deleted_activities_enabled', '');
        $cfgvalue = get_config('', 'bigbluebuttonbn_moderator_default');
        set_config('bigbluebuttonbn_participant_moderator_default', $cfgvalue, '');
        unset_config('bigbluebuttonbn_moderator_default', '');
        // Update db version tag.
        upgrade_mod_savepoint(true, 2017101000, 'bigbluebuttonbn');
    }
    if ($oldversion < 2017101009) {
        // Add field recordings_preview.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_preview',
            $fielddefinition);
        // Update db version tag.
        upgrade_mod_savepoint(true, 2017101009, 'bigbluebuttonbn');
    }
    if ($oldversion < 2017101010) {
        // Fix for CONTRIB-7221.
        if ($oldversion == 2017101003) {
            // A bug intorduced in 2017101003 causes new instances to be created without BBB passwords.
            // A workaround was put in place in version 2017101004 that was relabeled to 2017101005.
            // However, as the code was relocated to upgrade.php in version 2017101010, a new issue came up.
            // There is now a timeout error when the plugin is upgraded in large Moodle sites.
            // The script should only be considered when migrating from this version.
            $sql  = "SELECT * FROM {bigbluebuttonbn} ";
            $sql .= "WHERE moderatorpass = ? OR viewerpass = ?";
            $instances = $DB->get_records_sql($sql, array('', ''));
            foreach ($instances as $instance) {
                $instance->moderatorpass = bigbluebuttonbn_random_password(12);
                $instance->viewerpass = bigbluebuttonbn_random_password(12, $instance->moderatorpass);
                // Store passwords in the database.
                $DB->update_record('bigbluebuttonbn', $instance);
            }
        }
        // Update db version tag.
        upgrade_mod_savepoint(true, 2017101010, 'bigbluebuttonbn');
    }
    if ($oldversion < 2017101012) {
        // Update field type (Fix for CONTRIB-7302).
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '2', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => 'id');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'type',
            $fielddefinition);
        // Update field meetingid (Fix for CONTRIB-7302).
        $fielddefinition = array('type' => XMLDB_TYPE_CHAR, 'precision' => '255', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => null, 'previous' => 'introformat');
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'meetingid',
            $fielddefinition);
        // Update field recordings_imported (Fix for CONTRIB-7302).
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_imported',
            $fielddefinition);
        // Add field recordings_preview.(Fix for CONTRIB-7302).
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordings_preview',
            $fielddefinition);
        // Update db version tag.
        upgrade_mod_savepoint(true, 2017101012, 'bigbluebuttonbn');
    }
    if ($oldversion < 2017101015) {
        // Add field for client technology choice.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'clienttype',
            $fielddefinition);
        // Update db version tag.
        upgrade_mod_savepoint(true, 2017101015, 'bigbluebuttonbn');
    }
    if ($oldversion < 2019042000) {
        // Add field for Mute on start feature.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'muteonstart',
            $fielddefinition);
        // Add field for record all from start.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordallfromstart',
            $fielddefinition);
        // Add field for record hide button.
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordhidebutton',
            $fielddefinition);
        // Update db version tag.
        upgrade_mod_savepoint(true, 2019042000, 'bigbluebuttonbn');
    }
    if ($oldversion < 2019042009) {
        // Add index to bigbluebuttonbn_logs (Fix for CONTRIB-8157).
        xmldb_bigbluebuttonbn_index_table($dbman, 'bigbluebuttonbn_logs', 'courseid',
            ['courseid']);
        xmldb_bigbluebuttonbn_index_table($dbman, 'bigbluebuttonbn_logs', 'log',
            ['log']);
        xmldb_bigbluebuttonbn_index_table($dbman, 'bigbluebuttonbn_logs', 'logrow',
            ['courseid', 'bigbluebuttonbnid', 'userid', 'log']);
        // Update db version tag.
        upgrade_mod_savepoint(true, 2019042009, 'bigbluebuttonbn');
    }
    //Manju: Added for field recording style. 31/01/2020.
    if ($oldversion < 2019042013) {
        $fielddefinition = array('type' => XMLDB_TYPE_INTEGER, 'precision' => '1', 'unsigned' => null,
            'notnull' => XMLDB_NOTNULL, 'sequence' => null, 'default' => 0, 'previous' => null);
        xmldb_bigbluebuttonbn_add_change_field($dbman, 'bigbluebuttonbn', 'recordingstyle',
            $fielddefinition);
        // Update db version tag.
        upgrade_mod_savepoint(true, 2019042013, 'bigbluebuttonbn');
    }
    //Manju: 14/09/2020.
    if ($oldversion < 2019042014) {
        $table = new xmldb_table('bigbluebutton_publish');
        //organization Address
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, 
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null); 
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10',
            null, null,null, null, null);
        $table->add_field('meetingid', XMLDB_TYPE_TEXT, '250',
            null, null,null, null, null);
        $table->add_field('publishflag', XMLDB_TYPE_INTEGER, '10',
            null, null,null, null, null);
        $table->add_field('plublishdate', XMLDB_TYPE_TEXT, '250',
            null, null,null, null, null);
        $table->add_field('filesize', XMLDB_TYPE_TEXT, '250',
            null, null,null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch add field organization address.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Update db version tag.
        upgrade_mod_savepoint(true, 2019042014, 'bigbluebuttonbn');
    }

    //Manju: 21/09/2020.
    if ($oldversion < 2019042017) {
        $table = new xmldb_table('bigbluebutton_attendance');
        //organization Address
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, 
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null); 
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10',
            null, null,null, null, null);
        $table->add_field('bigbluebuttonbnid', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10',
            null, null,null, null, null);
        $table->add_field('jointime', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('lefttime', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('recordingid', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('meetingid', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('role', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('presenter', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('listeningonly', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('clientype', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_field('extra', XMLDB_TYPE_TEXT, '256',
            null, null,null, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch add field organization address.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Update db version tag.
        upgrade_mod_savepoint(true, 2019042015, 'bigbluebuttonbn');
    }
    return true;
}

/**
 * Generic helper function for adding or changing a field in a table.
 *
 * @param   object    $dbman
 * @param   string    $tablename
 * @param   string    $fieldname
 * @param   array     $fielddefinition
 */
function xmldb_bigbluebuttonbn_add_change_field($dbman, $tablename, $fieldname, $fielddefinition) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldname);
    $field->set_attributes($fielddefinition['type'], $fielddefinition['precision'], $fielddefinition['unsigned'],
        $fielddefinition['notnull'], $fielddefinition['sequence'], $fielddefinition['default'],
        $fielddefinition['previous']);
    if ($dbman->field_exists($table, $field)) {
        $dbman->change_field_type($table, $field, true, true);
        $dbman->change_field_precision($table, $field, true, true);
        $dbman->change_field_notnull($table, $field, true, true);
        $dbman->change_field_default($table, $field, true, true);
        return;
    }
    $dbman->add_field($table, $field, true, true);
}

/**
 * Generic helper function for dropping a field from a table.
 *
 * @param   object    $dbman
 * @param   string    $tablename
 * @param   string    $fieldname
 */
function xmldb_bigbluebuttonbn_drop_field($dbman, $tablename, $fieldname) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldname);
    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field, true, true);
    }
}

/**
 * Generic helper function for renaming a field in a table.
 *
 * @param   object    $dbman
 * @param   string    $tablename
 * @param   string    $fieldnameold
 * @param   string    $fieldnamenew
 */
function xmldb_bigbluebuttonbn_rename_field($dbman, $tablename, $fieldnameold, $fieldnamenew) {
    $table = new xmldb_table($tablename);
    $field = new xmldb_field($fieldnameold);
    if ($dbman->field_exists($table, $field)) {
        $dbman->rename_field($table, $field, $fieldnamenew, true, true);
    }
}

/**
 * Generic helper function for renaming a table.
 *
 * @param   object    $dbman
 * @param   string    $tablenameold
 * @param   string    $tablenamenew
 */
function xmldb_bigbluebuttonbn_rename_table($dbman, $tablenameold, $tablenamenew) {
    $table = new xmldb_table($tablenameold);
    if ($dbman->table_exists($table)) {
        $dbman->rename_table($table, $tablenamenew, true, true);
    }
}

/**
 * Generic helper function for adding index to a table.
 *
 * @param   object    $dbman
 * @param   string    $tablename
 * @param   string    $indexname
 * @param   array     $indexfields
 * @param   string    $indextype
 */
function xmldb_bigbluebuttonbn_index_table($dbman, $tablename, $indexname, $indexfields,
    $indextype = XMLDB_INDEX_NOTUNIQUE) {
    $table = new xmldb_table($tablename);
    if (!$dbman->table_exists($table)) {
        return;
    }
    $index = new xmldb_index($indexname, $indextype, $indexfields);
    if ($dbman->index_exists($table, $index)) {
        $dbman->drop_index($table, $index);
    }
    $dbman->add_index($table, $index, true, true);
}


