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


function gd_data_controller_datasource_resource_definitions() {
    return array(
        'dataset' => array(
            'relationships' => array(
                'script' => array(
                    'file' => array('module' => 'gd_data_controller_datasource', 'name' => 'resource/dataset_resource', 'type' => 'inc'),
                    'help' => 'Returns dataset script',
                    'callback' => 'data_controller_script_load_dataset_script',
                    'access callback' => 'gd_data_controller_access_data',
                    'args' => array(
                        array(
                            'name' => 'id',
                            'type' => 'string',
                            'description' => 'Dataset system name',
                            'source' => array('path' => '0'),
                            'optional' => FALSE
                        )
                    )
                )
            )
        )
    );
}
