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
    'reference' => array(
        'create' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_reference', 'name' => 'resources/reference'),
            'help' => 'Create a Reference',
            'callback' => 'gd_reference_resource_create',
            'access callback' => 'gd_reference_resource_access',
            'access arguments' => array('create'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'reference',
                    'description' => 'The reference to create',
                    'source' => array('data' => 'reference'),
                    'optional' => false,
                ),
            ),
        ),
        'retrieve' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_reference', 'name' => 'resources/reference'),
            'help' => 'Create a Reference',
            'callback' => 'gd_reference_resource_retrieve',
            'access callback' => 'gd_reference_resource_access',
            'access arguments' => array('retrieve'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'string',
                    'description' => 'The id of the reference to get',
                    'source' => array('path' => '0'),
                    'optional' => false
                )
            )
        ),
        'update' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_reference', 'name' => 'resources/reference'),
            'help' => 'Update a Reference',
            'callback' => 'gd_reference_resource_update',
            'access callback' => 'gd_reference_resource_access',
            'access arguments' => array('update'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the reference to update',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
                array(
                    'name' => 'reference',
                    'description' => 'The reference to update',
                    'source' => array('data' => 'reference'),
                    'optional' => false,
                ),
            ),
        ),
        'delete' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_reference', 'name' => 'resources/reference'),
            'help' => 'Delete a Reference',
            'callback' => 'gd_reference_resource_delete',
            'access callback' => 'gd_reference_resource_access',
            'access arguments' => array('delete'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the reference to delete',
                    'source' => array('path' => '0'),
                    'optional' => false,
                )
            ),
        ),
        'index' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_reference', 'name' => 'resources/reference'),
            'help' => 'Lists reference points',
            'callback' => 'gd_reference_resource_index',
            'access callback' => 'gd_reference_resource_access',
            'access arguments' => array('index'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'filter',
                    'optional' => true,
                    'type' => 'array',
                    'description' => 'Filter parameters array',
                    'default value' => null,
                    'source' => array('param' => 'filter'),
                ),
            ),
        )
    )
);