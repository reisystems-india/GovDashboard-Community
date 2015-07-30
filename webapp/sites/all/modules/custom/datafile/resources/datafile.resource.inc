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
    'datafile' => array(

        'retrieve' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_datafile', 'name' => 'resources/datafile'),
            'help' => 'Returns datafile properties',
            'callback' => 'gd_datafile_resource_retrieve',
            'access callback' => 'gd_datafile_resource_access',
            'access arguments' => array('view'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'string',
                    'description' => 'The id of the data file to get',
                    'source' => array('path' => '0'),
                    'optional' => false
                )
            )
        ),

        'update' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_datafile', 'name' => 'resources/datafile'),
            'help' => 'Updates datafile properties',
            'callback' => 'gd_datafile_resource_update',
            'access callback' => 'gd_datafile_resource_access',
            'access arguments' => array('update'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'string',
                    'description' => 'The id of the data file to get',
                    'source' => array('path' => '0'),
                    'optional' => false
                ),
                array(
                    'name' => 'datafile',
                    'description' => 'Updated datafile properties',
                    'source' => array('data' => 'datafile'),
                    'optional' => false
                )
            )
        ),

        'relationships' => array(

            'structure' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_datafile', 'name' => 'resources/datafile'),
                'help' => 'Returns datafile structure',
                'callback' => 'gd_datafile_resource_structure',
                'access callback' => 'gd_datafile_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the data file to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'datasource',
                        'type' => 'string',
                        'description' => 'Datasource when no dataset is available',
                        'source' => array('param' => 'ds'),
                        'optional' => true
                    )
                )
            )
        )
    )
);
