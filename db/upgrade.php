<?php
// This file keeps track of upgrades to the block_learningassist plugin
//
// Sometimes, changes between versions involve alterations to database structures
// and other major things that may break installations. The upgrade function in
// this file will attempt to perform all the necessary actions to upgrade your
// older installation to the current version.

/**
 * Upgrade code for the block_learningassist plugin.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_block_learningassist_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();


    return true;
}

