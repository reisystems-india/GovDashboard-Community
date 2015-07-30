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


function gd_dataset_uploader_resource_definitions() {
    return array(
        'data' => array(
            'update' => array(
                'file' => array(
                    'module' => 'gd_dataset_uploader',
                    'name' => 'provider/rest/resource/data_resource',
                    'type' => 'inc'
                ),
                'help' => 'Updates dataset data',
                'callback' => 'gd_dataset_uploader_update_data',
                'access callback' => 'gd_data_controller_access_data',
                'args' => array(
                    array(
                        'name' => 'id',
                        'type' => 'string',
                        'description' => 'Dataset system name',
                        'source' => array('path' => '0'),
                        'optional' => FALSE,
                    ),
                    array(
                        'name' => PARAMETER_NAME_CONTENT,
                        'description' => 'Body content',
                        'source' => array('data' => PARAMETER_NAME_CONTENT),
                        'optional' => FALSE
                    )
                )
            ),
            'delete' => array(
                'file' => array(
                    'module' => 'gd_dataset_uploader',
                    'name' => 'provider/rest/resource/data_resource',
                    'type' => 'inc'
                ),
                'help' => 'Truncates dataset storage',
                'callback' => 'gd_dataset_uploader_truncate_data',
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
    );
}

/**
 * @param $datasetName
 * @param $file_object <p>gd_dataset_uploader_data_resource_text_parser() creates the object from the PUT request body via text parser callback</p>
 * @return null|string
 */
function gd_dataset_uploader_update_data($datasetName, $file_object) {
    $response = NULL;
    try {
        $environmentMetaModel = data_controller_get_environment_metamodel();
        $datasource = $environmentMetaModel->getDataSource(gd_dataset_find_datasource_name($datasetName));
        if (!$datasource) {
            throw new ServicesException('Datasource not found', 404);
        }

        if ($datasource->isReadOnly()) {
            throw new ServicesException(t('Read only'), 406);
        }

        gd_datasource_set_active($datasource->name);

        if (!gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL, gd_datasource_get_active())) {
            throw new ServicesException('Forbidden', 403);
        }

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if (!$dataset) {
            throw new ServicesException(t('Not found'), 404);
        }

        // creating datafile node
        $datafileNode = gd_datafile_api_upload_get_datafile($file_object, $dataset->name);

        // associating the datafile with the dataset and processing it
        list($lineCount, $insertedRecordCount, $updatedRecordCount, $deletedRecordCount) = gd_dataset_update_data($dataset, $datafileNode);

        // preparing response
        $response = new stdClass();
        $response->status = new stdClass();
        $response->status->processed = $lineCount;
        $response->status->inserted = $insertedRecordCount;
        $response->status->updated = $updatedRecordCount;
        $response->status->deleted = $deletedRecordCount;
    }
    catch (Exception $e) {
        gd_admin_ui_service_exception_handler($e);
    }

    return $response;
}

/**
 * @param $datasetName
 * @return null
 */
function gd_dataset_uploader_truncate_data($datasetName) {
    $response = NULL;
    try {
        $environmentMetaModel = data_controller_get_environment_metamodel();
        $datasource = $environmentMetaModel->getDataSource(gd_dataset_find_datasource_name($datasetName));
        if (!$datasource) {
            throw new ServicesException('Datasource not found', 404);
        }

        if ($datasource->isReadOnly()) {
            throw new ServicesException(t('Read only'), 406);
        }

        gd_datasource_set_active($datasource->name);

        if (!gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL, gd_datasource_get_active())) {
            throw new ServicesException('Forbidden', 403);
        }

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if (!$dataset) {
            throw new ServicesException(t('Not found'), 404);
        }

        gd_data_controller_ddl_truncate_dataset($dataset->name);

        $response = new stdClass();
        $response->status = NULL;
    }
    catch (Exception $e) {
        gd_admin_ui_service_exception_handler($e);
    }

    return $response;
}
