<?php


function xmldb_plagiarism_tomagrade_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020220214) {

        // Define field idmatchontg to be added to plagiarism_tomagrade_config.
        $table = new xmldb_table('plagiarism_tomagrade_config');
        $field = new xmldb_field('idmatchontg', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'show_report_to_students');

        // Conditionally launch add field idmatchontg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tomagrade savepoint reached.
        upgrade_plugin_savepoint(true, 2020220214, 'plagiarism', 'tomagrade');
    }

    if ($oldversion < 2020220268) {
        // Define field idmatchontg to be added to plagiarism_tomagrade_config.
        $table = new xmldb_table('plagiarism_tomagrade_config');
        $field = new xmldb_field('share_teachers', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'idmatchontg');

        // Conditionally launch add field idmatchontg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Tomagrade savepoint reached.
        upgrade_plugin_savepoint(true, 2020220268, 'plagiarism', 'tomagrade');
    }

    if ($oldversion < 2020220272) {
        // Define field idmatchontg to be added to plagiarism_tomagrade_config.
        $table = new xmldb_table('plagiarism_tomagrade_config');
        $field = new xmldb_field('examid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'share_teachers');

        // Conditionally launch add field idmatchontg.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

       $records = $DB->get_records_sql(" select * from {plagiarism_tomagrade_config}  ");
      
       foreach($records as $record) {
            if ($record->idmatchontg == "0") {
                 $update = " update {plagiarism_tomagrade_config} set examid = 'Assign$record->cm' where id = $record->id";
            } else {
                $update = " update {plagiarism_tomagrade_config} set examid = '$record->idmatchontg' where id = $record->id";     
            }
            $DB->execute($update);
       }

     
       

        // Tomagrade savepoint reached.
        upgrade_plugin_savepoint(true, 2020220272, 'plagiarism', 'tomagrade');
    }

    if ($oldversion < 2020220279) {

        $records = $DB->get_records_sql(" select * from {plagiarism_tomagrade_config}  ");
      
        foreach($records as $record) {
            if (strpos($record->username, '@') !== false) {
                $update = " update {plagiarism_tomagrade_config} set username = ( select id from {user} where email = '$record->username' limit 1 ) where id = $record->id";
                $DB->execute($update);
            }
        }
 
      
        
 
         // Tomagrade savepoint reached.
         upgrade_plugin_savepoint(true, 2020220279, 'plagiarism', 'tomagrade');

    }



    return true;
}
