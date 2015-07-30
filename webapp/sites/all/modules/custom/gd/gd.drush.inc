<?php
/*
 * Copyright 2014 REI Systems, Inc.
 * 
 * This file is part of GovDashboard.
 * 
 * GovDashboard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GovDashboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GovDashboard.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Implements hook_drush_help().
 */
function gd_drush_help($command) {
    switch ($command) {
        case 'drush:gd-sync-modules':
            return dt('Sync GovDashboard module dependencies.');
    }
    return null;
}

/**
 * Implements hook_drush_command().
 */
function gd_drush_command() {
    $items = array();

    $items['gd-sync-modules'] = array(
        'description' => dt('Sync GovDashboard module dependencies.'),
        'arguments'   => array(),
        'examples' => array(
            'Standard example' => 'drush gd-sync-modules'
        ),
        'aliases' => array('govdash-sync-modules')
    );

    return $items;
}

// Callback function for drush gd-post-deploy.
function drush_gd_sync_modules () {

    drush_log('Syncing modules with profile dependencies.','ok');

    $profile = parse_ini_file(DRUPAL_ROOT.'/profiles/govdash/govdash.info');

    $modules = array();
    $result = drush_db_select('system', array('name','type','status'), 'type=:type', array(':type' => 'module'));
    while ( $module = drush_db_fetch_object($result)) {
      $modules[$module->name] = $module;
    }

    // make sure require modules are not overlooked
    $required_modules = array_merge($profile['dependencies'],drupal_required_modules());

    foreach ( $modules as $m ) {
        $found = false;
        foreach ( $required_modules as $dep ) {
            if ( $m->name == $dep ) {
                $found = true;
                if ( $m->status != 1 ) {
                    $enabled = module_enable(array($dep));
                    if ( $enabled ) {
                        drush_log('Enabling '.$dep,'ok');
                    } else {
                        drush_log('Enabling '.$dep,'error');
                    }
                }
            }
        }
        if ( !$found ) {
            if ( $m->status == 1 ) {
                module_disable(array($m->name));
                drush_log('Disabling '.$m->name,'ok');
            }
        }
    }

    drush_log('Module sync complete.','completed');
}