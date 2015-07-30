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
    'datatype' => array(
        'index' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_datatype', 'name' => 'resources/datatype'),
            'help' => 'Returns datatypes',
            'callback' => 'gd_datatype_resource_index',
            'access callback' => 'gd_datatype_resource_access',
            'access arguments' => array(),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'datasourceName',
                    'type' => 'string',
                    'description' => 'Datasource when no dataset is available',
                    'source' => array('param' => 'ds'),
                    'optional' => true
                )
            )
        )
    )
);