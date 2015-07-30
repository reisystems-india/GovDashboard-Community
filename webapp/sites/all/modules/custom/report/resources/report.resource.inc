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


$resource = array (
    'report' => array(
        'create' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
            'help' => 'Create a report',
            'callback' => 'gd_report_resource_create',
            'access callback' => 'gd_report_resource_access',
            'access arguments' => array('create'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'report',
                    'description' => 'The report to create',
                    'source' => array('data' => 'report'),
                    'optional' => false
                ),
            ),
        ),
        'retrieve' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
            'help' => 'Retrieves a report',
            'callback' => 'gd_report_resource_retrieve',
            'access callback' => 'gd_report_resource_access',
            'access arguments' => array('retrieve'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the report to get',
                    'source' => array('path' => '0'),
                    'optional' => false
                ),
                array(
                    'name' => 'fields',
                    'optional' => true,
                    'type' => 'string',
                    'description' => 'The fields to get.',
                    'default value' => '*',
                    'source' => array('param' => 'fields')
                ),
            ),
        ),
        'update' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
            'help' => 'Update a report',
            'callback' => 'gd_report_resource_update',
            'access callback' => 'gd_report_resource_access',
            'access arguments' => array('update'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the report to update',
                    'source' => array('path' => '0'),
                    'optional' => false
                ),
                array(
                    'name' => 'report',
                    'description' => 'The report items',
                    'source' => array('data' => 'report'),
                    'optional' => false
                ),
            ),
        ),
        'delete' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
            'help' => 'Deletes a report',
            'callback' => 'gd_report_resource_delete',
            'access callback' => 'gd_report_resource_access',
            'access arguments' => array('delete'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the report to delete',
                    'source' => array('path' => '0'),
                    'optional' => false
                ),
            ),
        ),
        'index' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
            'help' => 'Lists reports a user has access to',
            'callback' => 'gd_report_resource_index',
            'access callback' => 'gd_report_resource_access',
            'access arguments' => array('index'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'fields',
                    'optional' => true,
                    'type' => 'string',
                    'description' => 'The fields to get.',
                    'default value' => '*',
                    'source' => array('param' => 'fields')
                ),
                array(
                    'name' => 'page',
                    'optional' => true,
                    'type' => 'int',
                    'description' => 'The zero-based index of the page to get, defaults to 0.',
                    'default value' => null,
                    'source' => array('param' => 'page')
                ),
                array(
                    'name' => 'limit',
                    'optional' => true,
                    'type' => 'int',
                    'description' => 'Filter by parent dataset',
                    'default value' => null,
                    'source' => array('param' => 'limit')
                ),
                array(
                    'name' => 'sort',
                    'optional' => true,
                    'type' => 'array',
                    'description' => 'Sort parameters array',
                    'default value' => null,
                    'source' => array('param' => 'sort')
                ),
                array(
                    'name' => 'filter',
                    'optional' => true,
                    'type' => 'array',
                    'description' => 'Filter parameters array',
                    'default value' => null,
                    'source' => array('param' => 'filter')
                ),
            ),
        ),
        'targeted_actions' => array(
            'save_as' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Save as another report',
                'callback' => 'gd_report_resource_save_as',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('save_as'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the report to save as',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'report',
                        'description' => 'The report items',
                        'source' => array('data' => 'report'),
                        'optional' => false
                    )
                )
            ),
            'copy' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Copy report',
                'callback' => 'gd_report_resource_copy',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('copy'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the report to copy',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'name',
                        'type' => 'string',
                        'description' => 'The new report name',
                        'source' => array('data' => 'name'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'datasource',
                        'type' => 'string',
                        'description' => 'The new datasource name',
                        'source' => array('data' => 'datasource'),
                        'optional' => true
                    )
                )
            ),
            'export' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Retrieve report data',
                'callback' => 'gd_report_resource_export_pdf',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('data'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the report to query',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'dashboard',
                        'optional' => true,
                        'type' => 'int',
                        'description' => 'Dashboard id if using',
                        'default value' => null,
                        'source' => array('param' => 'dashboard')
                    )
                )
            )
        ),
        'actions' => array(
            'lookup' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Lookup column data',
                'callback' => 'gd_report_resource_lookup',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('lookup'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'report',
                        'description' => 'The report preview config',
                        'source' => array('data'=>'report'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'column',
                        'type' => 'string',
                        'description' => 'The column to lookup',
                        'source' => array('data'=>'column'),
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
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Retrieve report data',
                'callback' => 'gd_report_resource_data',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('data'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'report',
                        'description' => 'The report preview config',
                        'source' => array('data'=>'report'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'filter',
                        'type' => 'array',
                        'description' => 'Filters to apply',
                        'source' => array('data'=>'filter'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'orderBy',
                        'type' => 'array',
                        'description' => 'Column to order by',
                        'source' => array('data'=>'orderBy'),
                        'optional' => true,
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
            )
        ),
        'relationships' => array(
            'data' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Retrieve report data',
                'callback' => 'gd_report_resource_get_data',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('data'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the report to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'filter',
                        'type' => 'array',
                        'description' => 'Filters to apply',
                        'source' => array('param'=>'filter'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'orderBy',
                        'type' => 'array',
                        'description' => 'Column to order by',
                        'source' => array('param'=>'orderBy'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'offset',
                        'type' => 'int',
                        'description' => 'Starting offset',
                        'source' => array('param'=>'offset'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'limit',
                        'type' => 'int',
                        'description' => 'Number of results to return',
                        'source' => array('param'=>'limit'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'columns',
                        'type' => 'array',
                        'description' => 'Columns to return',
                        'source' => array('param'=>'columns'),
                        'optional' => true
                    )
                )
            ),
            'export' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Retrieve report data',
                'callback' => 'gd_report_resource_export',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('data'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'int',
                        'description' => 'The id of the report to query',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'dashboard',
                        'optional' => true,
                        'type' => 'int',
                        'description' => 'Dashboard id if using',
                        'default value' => null,
                        'source' => array('param' => 'dashboard')
                    )
                )
            ),
            'referenced' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_report', 'name' => 'resources/report'),
                'help' => 'Returns report ui metadata',
                'callback' => 'gd_report_resource_referenced',
                'access callback' => 'gd_report_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the report to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'group',
                        'type' => 'string',
                        'description' => 'The group of referenced data',
                        'source' => array('path' => '2'),
                        'optional' => true
                    )
                )
            )
        )
    )
);