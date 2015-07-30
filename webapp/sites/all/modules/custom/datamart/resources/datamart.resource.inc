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

$resource = array(
    'datamart' => array(
        'create' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datamart', 'name' => 'resources/datamart'),
            'help' => 'Create a datamart',
            'callback' => 'gd_datamart_resource_create',
            'access callback' => 'gd_datamart_resource_access',
            'access arguments' => array('create'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'datamart',
                    'description' => 'The datamart to create',
                    'source' => array('data' => 'datamart'),
                    'optional' => false,
                ),
            ),
        ),
        'retrieve' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datamart', 'name' => 'resources/datamart'),
            'help' => 'Retrieves a datamart',
            'callback' => 'gd_datamart_resource_retrieve',
            'access callback' => 'gd_datamart_resource_access',
            'access arguments' => array('view'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the datamart to get',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
            ),
        ),
        'update' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datamart', 'name' => 'resources/datamart'),
            'help' => 'Update a datamart',
            'callback' => 'gd_datamart_resource_update',
            'access callback' => 'gd_datamart_resource_access',
            'access arguments' => array('update'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the datamart to update',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
                array(
                    'name' => 'datamart',
                    'description' => 'The details to update',
                    'source' => array('data' => 'datamart'),
                    'optional' => false,
                ),
            ),
        ),
        'delete' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datamart', 'name' => 'resources/datamart'),
            'help' => 'Deletes a datamart',
            'callback' => 'gd_datamart_resource_delete',
            'access callback' => 'gd_datamart_resource_access',
            'access arguments' => array('delete'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the datamart to delete',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
            ),
        ),
        'index' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datamart', 'name' => 'resources/datamart'),
            'help' => 'Lists datamarts a user has access to',
            'callback' => 'gd_datamart_resource_index',
            'access callback' => 'gd_datamart_resource_access',
            'access arguments' => array('index'),
            'access arguments append' => true,
        ),
        'targeted_actions' => array(
            'setactive' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_datamart', 'name' => 'resources/datamart'),
                'help' => 'Set the active datamart for the current user',
                'callback' => 'gd_datamart_resource_setactive',
                'access callback' => 'gd_datamart_resource_access',
                'access arguments' => array('setactive'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the datamart to set active',
                        'source' => array('path' => '0'),
                        'optional' => false,
                    )
                )
            )
        )
    )
);


