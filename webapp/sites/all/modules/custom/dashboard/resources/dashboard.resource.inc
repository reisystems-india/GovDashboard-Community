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
    'dashboard' => array(
        'create' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name'=>'resources/dashboard'),
            'help' => 'Create a dashboard',
            'callback' => 'gd_dashboard_resource_create',
            'access callback' => 'gd_dashboard_resource_access',
            'access arguments' => array('create'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'dashboard',
                    'description' => 'The dashboard to create',
                    'source' => array('data' => 'dashboard'),
                    'optional' => false,
                ),
            ),
        ),
        'retrieve' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name'=>'resources/dashboard'),
            'help' => 'Retrieves a dashboard',
            'callback' => 'gd_dashboard_resource_retrieve',
            'access callback' => 'gd_dashboard_resource_access',
            'access arguments' => array('retrieve'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the dashboard to get',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
                array(
                    'name' => 'fields',
                    'optional' => true,
                    'type' => 'string',
                    'description' => 'The fields to get.',
                    'default value' => '*',
                    'source' => array('param' => 'fields'),
                )
            )
        ),
        'update' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name'=>'resources/dashboard'),
            'help' => 'Update a dashboard',
            'callback' => 'gd_dashboard_resource_update',
            'access callback' => 'gd_dashboard_resource_access',
            'access arguments' => array('update'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the dashboard to update',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
                array(
                    'name' => 'dashboard',
                    'description' => 'The dashboard to create',
                    'source' => array('data' => 'dashboard'),
                    'optional' => false,
                ),
            ),
        ),
        'delete' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name'=>'resources/dashboard'),
            'help' => 'Deletes a dashboard',
            'callback' => 'gd_dashboard_resource_delete',
            'access callback' => 'gd_dashboard_resource_access',
            'access arguments' => array('delete'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the dashboard to delete',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
            ),
        ),
        'index' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name'=>'resources/dashboard'),
            'help' => 'Lists dashboards a user has access to',
            'callback' => 'gd_dashboard_resource_index',
            'access callback' => 'gd_dashboard_resource_access',
            'access arguments' => array('index'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'fields',
                    'optional' => true,
                    'type' => 'string',
                    'description' => 'The fields to get.',
                    'default value' => null,
                    'source' => array('param' => 'fields'),
                ),
                array(
                    'name' => 'page',
                    'optional' => true,
                    'type' => 'int',
                    'description' => 'The zero-based index of the page to get, defaults to 0.',
                    'default value' => null,
                    'source' => array('param' => 'page'),
                ),
                array(
                    'name' => 'limit',
                    'optional' => true,
                    'type' => 'int',
                    'description' => 'Filter by parent dataset',
                    'default value' => null,
                    'source' => array('param' => 'limit'),
                ),
                array(
                    'name' => 'sort',
                    'optional' => true,
                    'type' => 'array',
                    'description' => 'Sort parameters array',
                    'default value' => null,
                    'source' => array('param' => 'sort'),
                ),
                array(
                    'name' => 'filter',
                    'optional' => true,
                    'type' => 'array',
                    'description' => 'Filter parameters array',
                    'default value' => null,
                    'source' => array('param' => 'filter'),
                )
            )
        ),
        'actions' => array(
            'lookup' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name' => 'resources/dashboard'),
                'help' => 'Lookup filter data',
                'callback' => 'gd_dashboard_resource_lookup',
                'access callback' => 'gd_dashboard_resource_access',
                'access arguments' => array('lookup'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'dashboard',
                        'description' => 'The dashboard config',
                        'source' => array('data'=>'dashboard'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'filter',
                        'type' => 'string',
                        'description' => 'The filter to lookup',
                        'source' => array('data'=>'filter'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'query',
                        'type' => 'string',
                        'description' => 'Search string',
                        'source' => array('data'=>'query'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'offset',
                        'type' => 'int',
                        'description' => 'Starting offset',
                        'source' => array('data'=>'offset'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'limit',
                        'type' => 'int',
                        'description' => 'Number of results to return',
                        'source' => array('data'=>'limit'),
                        'optional' => true
                    )
                )
            ),
            'data' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name' => 'resources/dashboard'),
                'help' => 'Retrieve dashboard report data',
                'callback' => 'gd_dashboard_resource_report_data_builder',
                'access callback' => 'gd_dashboard_resource_access',
                'access arguments' => array('data'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'dashboard',
                        'type' => 'string',
                        'description' => 'The dashboard config',
                        'source' => array('data'=>'dashboard'),
                        'optional' => false,
                    ),
                    array(
                        'name' => 'reportId',
                        'type' => 'int',
                        'description' => 'The report id to get data for',
                        'source' => array('data'=>'reportId'),
                        'optional' => false,
                    ),
                    array(
                        'name' => 'filter',
                        'type' => 'array',
                        'description' => 'Filters to apply',
                        'source' => array('data'=>'filter'),
                        'optional' => true,
                    ),
                    array(
                        'name' => 'orderBy',
                        'type' => 'string',
                        'description' => 'Column to order by',
                        'source' => array('data'=>'orderBy'),
                        'optional' => true,
                    ),
                    array(
                        'name' => 'offset',
                        'type' => 'int',
                        'description' => 'Starting offset',
                        'source' => array('data'=>'offset'),
                        'optional' => true,
                    ),
                    array(
                        'name' => 'limit',
                        'type' => 'int',
                        'description' => 'Number of results to return',
                        'source' => array('data'=>'limit'),
                        'optional' => true,
                    )
                )
            )
        ),
        'relationships' => array(
            'data' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dashboard', 'name' => 'resources/dashboard'),
                'help' => 'Retrieve dashboard report data',
                'callback' => 'gd_dashboard_resource_report_data_viewer',
                'access callback' => 'gd_dashboard_resource_access',
                'access arguments' => array('data'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'dashboard',
                        'type' => 'int',
                        'description' => 'The id of the dashboard to query',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'reportId',
                        'type' => 'int',
                        'description' => 'The id of the report to query',
                        'default value' => null,
                        'source' => array('param' => 'reportId'),
                        'optional' => false,
                    ),
                    array(
                        'name' => 'filter',
                        'optional' => true,
                        'type' => 'array',
                        'description' => 'The filters to apply',
                        'default value' => null,
                        'source' => array('param' => 'filter'),
                    ),
                    array(
                        'name' => 'orderBy',
                        'type' => 'string',
                        'description' => 'The column to order by',
                        'source' => array('param' => 'orderBy'),
                        'optional' => true,
                    ),
                    array(
                        'name' => 'offset',
                        'optional' => true,
                        'type' => 'int',
                        'description' => 'Starting offset',
                        'default value' => null,
                        'source' => array('param' => 'offset'),
                    ),
                    array(
                        'name' => 'limit',
                        'optional' => true,
                        'type' => 'int',
                        'description' => 'Number of results to return',
                        'default value' => null,
                        'source' => array('param' => 'limit'),
                    )
                )
            )
        )
    )

);