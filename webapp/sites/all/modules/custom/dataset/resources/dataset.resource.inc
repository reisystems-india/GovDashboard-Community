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
    'dataset' => array(

        'index' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name'=>'resources/dataset'),
            'help' => 'Lists datasets a user has access to',
            'callback' => 'gd_dataset_resource_index',
            'access callback' => 'gd_dataset_resource_access',
            'access arguments' => array('index'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'filter',
                    'optional' => true,
                    'type' => 'array',
                    'description' => 'Filter parameters array',
                    'default value' => null,
                    'source' => array('param' => 'filter')
                )
            )
        ),

        'create' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name'=>'resources/dataset'),
            'help' => 'Create a dataset',
            'callback' => 'gd_dataset_resource_create',
            'access callback' => 'gd_dataset_resource_access',
            'access arguments' => array('create'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'dataset',
                    'description' => 'The dataset data',
                    'source' => array('data' => 'dataset'),
                    'optional' => false
                )
            )
        ),

        'retrieve' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name'=>'resources/dataset'),
            'help' => 'Retrieves a dataset',
            'callback' => 'gd_dataset_resource_retrieve',
            'access callback' => 'gd_dataset_resource_access',
            'access arguments' => array('retrieve'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'string',
                    'description' => 'The id of the dataset to get',
                    'source' => array('path' => '0'),
                    'optional' => false
                )
            )
        ),

        'update' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name'=>'resources/dataset'),
            'help' => 'Update a dataset',
            'callback' => 'gd_dataset_resource_update',
            'access callback' => 'gd_dataset_resource_access',
            'access arguments' => array('update'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the dataset to update',
                    'source' => array('path' => '0'),
                    'optional' => false
                ),
                array(
                    'name' => 'dataset',
                    'description' => 'The dataset data',
                    'source' => array('data' => 'dataset'),
                    'optional' => false
                )
            )
        ),

        'delete' => array(
            'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name'=>'resources/dataset'),
            'help' => 'Deletes a dataset',
            'callback' => 'gd_dataset_resource_delete',
            'access callback' => 'gd_dataset_resource_access',
            'access arguments' => array('delete'),
            'access arguments append' => true,
            'args' => array(
                array(
                    'name' => 'id',
                    'type' => 'int',
                    'description' => 'The id of the dataset to delete',
                    'source' => array('path' => '0'),
                    'optional' => false,
                ),
            ),
        ),

        'targeted_actions' => array(
            'truncate' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Truncates dataset data',
                'callback' => 'gd_dataset_resource_data_truncate',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('delete'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to truncate',
                        'source' => array('path' => '0'),
                        'optional' => false
                    )
                )
            )
        ),

        'relationships' => array(
            'statistic' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset statistics',
                'callback' => 'gd_dataset_resource_statistic',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    )
                )
            ),

            'data' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset data or changelog',
                'callback' => 'gd_dataset_resource_data',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'view',
                        'type' => 'string',
                        'description' => 'The type of specific info about the data',
                        'source' => array('path' => '2'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'columns',
                        'type' => 'array',
                        'description' => 'the columns you want from the data',
                        'source' => array('param' => 'columns'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'order',
                        'type' => 'array',
                        'description' => 'the column that the data will be sorted by',
                        'source' => array('param' => 'order'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'filters',
                        'type' => 'array',
                        'description' => 'The operations on which you are filtering the query by',
                        'source' => array('param' => 'filters'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'startRow',
                        'type' => 'int',
                        'description' => 'The row where you want to start capturing data',
                        'source' => array('param' => 'srow'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'endRow',
                        'type' => 'int',
                        'description' => 'The row(inclusive) you want to end capturing data',
                        'source' => array('param' => 'erow'),
                        'optional' => true
                    )
                )
            ),

            'lookup' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset lookup or data',
                'callback' => 'gd_dataset_resource_lookup',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'column',
                        'type' => 'string',
                        'description' => 'the column you want from the data',
                        'source' => array('param' => 'c'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'query',
                        'type' => 'string',
                        'description' => 'the query you want to run',
                        'source' => array('param' => 'query'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'generic',
                        'type' => 'string',
                        'description' => 'whether or not the lookup is generic',
                        'source' => array('param' => 'g'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'sort',
                        'type' => 'string',
                        'description' => 'the column to sort by',
                        'source' => array('param' => 's'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'offset',
                        'type' => 'string',
                        'description' => 'the number of results to offset by',
                        'source' => array('param' => 'o'),
                        'optional' => true
                    ),
                    array(
                        'name' => 'limit',
                        'type' => 'string',
                        'description' => 'the number of results to return',
                        'source' => array('param' => 'l'),
                        'optional' => true
                    )
                )
            ),

            'ui' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset ui metadata',
                'callback' => 'gd_dataset_resource_ui',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'linked',
                        'type' => 'array',
                        'description' => 'The datasets to link against.',
                        'source' => array('param' => 'linked'),
                        'optional' => true
                    )
                )
            ),

            'references' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset ui metadata',
                'callback' => 'gd_dataset_resource_references',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'type',
                        'type' => 'string',
                        'description' => 'The dataset reference type.',
                        'source' => array('param' => 'type'),
                        'optional' => true
                    )
                )
            ),

            'referenced' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset ui metadata',
                'callback' => 'gd_dataset_resource_referenced',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
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
            ),

            'connections' => array(
                'file' => array('type' => 'inc', 'module' => 'gd_dataset', 'name' => 'resources/dataset'),
                'help' => 'Returns dataset connection metadata',
                'callback' => 'gd_dataset_resource_connections',
                'access callback' => 'gd_dataset_resource_access',
                'access arguments' => array('view'),
                'access arguments append' => true,
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'The id of the dataset to get',
                        'source' => array('path' => '0'),
                        'optional' => false
                    ),
                    array(
                        'name' => 'depth',
                        'type' => 'integer',
                        'description' => 'The depth of connection data',
                        'source' => array('path' => '2'),
                        'optional' => true
                    )
                )
            )

        )
    )
);
