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
    'datasource' => array(
        'index' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datasource', 'name' => 'resources/datasource'),
            'help' => 'Lists datasources a user has access to',
            'callback' => 'gd_datasource_resource_index',
            'access callback' => 'gd_datasource_resource_access',
            'access arguments' => array('index'),
            'access arguments append' => true,
        ),
        'retrieve' => array(
            'file' => array('file' => 'inc', 'module' => 'gd_datasource', 'name' => 'resources/datasource'),
            'help' => 'Retrieves a datasource',
            'callback' => 'gd_datasource_resource_retrieve',
            'access callback' => 'gd_datasource_resource_access',
            'access arguments' => array('view'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'name',
                    'type' => 'string',
                    'description' => 'The id of the datasource to get',
                    'source' => array('path' => '0'),
                    'optional' => false,
                )
            )
        ),
        'targeted_actions' => array(
            'setactive' => array(
                'file' => array('file' => 'inc', 'module' => 'gd_datasource', 'name' => 'resources/datasource'),
                'help' => 'Set the active datasource for the current user',
                'callback' => 'gd_datasource_resource_setactive',
                'access callback' => 'gd_datasource_resource_access',
                'access arguments' => array('setactive'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'name',
                        'type' => 'string',
                        'description' => 'The name of the datasource to set active',
                        'source' => array('path' => '0'),
                        'optional' => false,
                    )
                )
            )
        )
    )
);


