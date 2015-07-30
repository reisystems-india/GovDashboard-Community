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
function gd_account_drush_help ( $command ) {
    switch ( $command ) {
        case 'drush:govdash-init':
            return dt('Initializes an Account');
    }

    return null;
}

/**
 * Implements hook_drush_command().
 */
function gd_account_drush_command () {
    $items = array();

    $items['govdash-init'] = array(
        'description' => dt('Initializes an Account.'),
        'arguments' => array(),
        'examples' => array(
            'Standard example' => 'drush govdash-init'
        ),
        'aliases' => array('gd-init'),
    );

    return $items;
}

/**
 * Callback function for drush govdash-init.
 */
function drush_gd_account_govdash_init () {

    $datasource = array();
    $datasource['publicName'] = 'Default';
    $datasource['description'] = 'Default Topic';

    gd_datasource_create($datasource);

    // log to the command line with an OK status
    drush_log('Completed Govdash Initialization', 'ok');
}