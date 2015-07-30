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


function gd_data_controller_resource_definitions() {
    return array(
        'data' => array(
            'relationships' => array(
                'metadata' => array(
                    'file' => array('module' => 'gd_data_controller', 'name' => 'resource/data_resource', 'type' => 'inc'),
                    'help' => 'Returns dataset meta data',
                    'callback' => 'gd_data_controller_dataset_metadata',
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
                ),
                'dataset' => array(
                    'file' => array('module' => 'gd_data_controller', 'name' => 'resource/data_resource', 'type' => 'inc'),
                    'help' => 'Queries dataset data',
                    'callback' => 'gd_data_controller_dataset_query',
                    'access callback' => 'gd_data_controller_access_data',
                    'args' => array(
                        array(
                            'name' => 'id',
                            'type' => 'string',
                            'description' => 'Dataset system name',
                            'source' => array('path' => '0'),
                            'optional' => FALSE
                        ),
                        array(
                            'name' => 'count',
                            'type' => 'string',
                            'description' => 'Flag to count records instead of returning them',
                            'source' => array('path' => '2'),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'columns',
                            'type' => 'array',
                            'description' => 'Selected columns',
                            'source' => array('param' => DataQueryControllerUIParameterNames::COLUMNS),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'parameters',
                            'type' => 'array',
                            'description' => 'Parameters to filter data',
                            'source' => array('param' => DataQueryControllerUIParameterNames::PARAMETERS),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'sort',
                            'type' => 'array',
                            'description' => 'Columns to sort result',
                            'source' => array('param' => DataQueryControllerUIParameterNames::SORT),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'offset',
                            'type' => 'int',
                            'description' => 'Returns result starting from \'offset\' record',
                            'source' => array('param' => DataQueryControllerUIParameterNames::OFFSET),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'limit',
                            'type' => 'int',
                            'description' => 'Limit number of returned records',
                            'source' => array('param' => DataQueryControllerUIParameterNames::LIMIT),
                            'optional' => TRUE
                        )
                    )
                ),
                'cube' => array(
                    'file' => array('module' => 'gd_data_controller', 'name' => 'resource/data_resource', 'type' => 'inc'),
                    'help' => 'Queries aggregated data',
                    'callback' => 'gd_data_controller_cube_query',
                    'access callback' => 'gd_data_controller_access_data',
                    'args' => array(
                        array(
                            'name' => 'id',
                            'type' => 'string',
                            'description' => 'Dataset system name',
                            'source' => array('path' => '0'),
                            'optional' => FALSE
                        ),
                        array(
                            'name' => 'count',
                            'type' => 'string',
                            'description' => 'Flag to count records instead',
                            'source' => array('path' => '2'),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'columns',
                            'type' => 'array',
                            'description' => 'Selected columns',
                            'source' => array('param' => DataQueryControllerUIParameterNames::COLUMNS),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'parameters',
                            'type' => 'array',
                            'description' => 'Parameters to filter data',
                            'source' => array('param' => DataQueryControllerUIParameterNames::PARAMETERS),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'sort',
                            'type' => 'array',
                            'description' => 'Columns to sort result',
                            'source' => array('param' => DataQueryControllerUIParameterNames::SORT),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'offset',
                            'type' => 'int',
                            'description' => 'Returns result starting from \'offset\' record',
                            'source' => array('param' => DataQueryControllerUIParameterNames::OFFSET),
                            'optional' => TRUE
                        ),
                        array(
                            'name' => 'limit',
                            'type' => 'int',
                            'description' => 'Limit number of returned records',
                            'source' => array('param' => DataQueryControllerUIParameterNames::LIMIT),
                            'optional' => TRUE
                        )
                    )
                )
            )
        )
    );
}

function gd_data_controller_dataset_metadata($datasetName) {
    $response = NULL;

    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        gd_datasource_set_active($datasourceName);

        $dataQueryController = data_controller_get_instance();

        $dataset = $dataQueryController->getDatasetMetaData($datasetName);

        $response = $dataset;
    }
    catch (Exception $e) {
        gd_admin_ui_service_exception_handler($e);
    }

    return $response;
}

function gd_data_controller_dataset_query($datasetName, $recordCountFlag, $columns, $parameters, $sortColumns, $offset, $limit) {
    $response = NULL;

    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        gd_datasource_set_active($datasourceName);

        $adjustedColumns = DataQueryControllerUIRequestParser::parseColumns($columns);
        $adjustedParameters = DataQueryControllerUIRequestParser::parseParameters($parameters);
        $adjustedSortColumns = DataQueryControllerUIRequestParser::parseSortColumns($sortColumns);
        $adjustedOffset = DataQueryControllerUIRequestParser::parseOffset($offset);
        $adjustedLimit = DataQueryControllerUIRequestParser::parseLimit($limit);

        $dataQueryController = data_controller_get_instance();

        if (isset($recordCountFlag)) {
            if ($recordCountFlag != 'count') {
                throw new UnsupportedOperationException();
            }

            $response = $dataQueryController->countDatasetRecords($datasetName, $adjustedParameters);
        }
        else {
            $response = $dataQueryController->queryDataset(
                $datasetName, $adjustedColumns,
                $adjustedParameters,
                $adjustedSortColumns,
                $adjustedOffset, $adjustedLimit);
        }
    }
    catch (Exception $e) {
        gd_admin_ui_service_exception_handler($e);
    }

    return $response;
}

function gd_data_controller_cube_query($datasetName, $recordCountFlag, $columns, $parameters, $sortColumns, $offset, $limit) {
    $response = NULL;

    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        gd_datasource_set_active($datasourceName);

        $adjustedColumns = DataQueryControllerUIRequestParser::parseColumns($columns);
        $adjustedParameters = DataQueryControllerUIRequestParser::parseParameters($parameters);
        $adjustedSortColumns = DataQueryControllerUIRequestParser::parseSortColumns($sortColumns);
        $adjustedOffset = DataQueryControllerUIRequestParser::parseOffset($offset);
        $adjustedLimit = DataQueryControllerUIRequestParser::parseLimit($limit);

        $dataQueryController = data_controller_get_instance();

        if (isset($recordCountFlag)) {
            if ($recordCountFlag != 'count') {
                throw new UnsupportedOperationException();
            }

            $response = $dataQueryController->countCubeRecords($datasetName, $adjustedParameters);
        }
        else {
            $response = $dataQueryController->queryCube(
                $datasetName, $adjustedColumns,
                $adjustedParameters,
                $adjustedSortColumns,
                $adjustedOffset, $adjustedLimit);
        }
    }
    catch (Exception $e) {
        gd_admin_ui_service_exception_handler($e);
    }

    return $response;
}