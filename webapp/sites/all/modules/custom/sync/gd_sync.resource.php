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


function gd_sync_resource_definition() {
    return array(
        'datasource' => array(
            'actions' => array(
                'import' => array(
                    'file' => array('type' => 'php', 'module' => 'gd_sync', 'name' => 'gd_sync.resource'),
                    'help' => 'Creates a new datasource from an export',
                    'callback' => 'gd_sync_resource_import',
                    'access callback' => 'gd_sync_resource_access',
                    'access arguments' => array('import'),
                    'access arguments append' => true,
                    'args' => array(
                        array(
                            'name' => 'datamartName',
                            'description' => 'The name of the datamart',
                            'source' => array('data'=>'datamart'),
                            'optional' => false
                        ),
                        array(
                            'name' => 'export',
                            'description' => 'The export to import',
                            'source' => array('data'=>'export'),
                            'optional' => false
                        )
                    )
                )
            ),
            'targeted_actions' => array(
                'sync' => array(
                    'file' => array('type' => 'php', 'module' => 'gd_sync', 'name' => 'gd_sync.resource'),
                    'help' => 'Sync datasources',
                    'callback' => 'gd_sync_resource_sync',
                    'access callback' => 'gd_sync_resource_access',
                    'access arguments' => array('sync'),
                    'access arguments append' => true,
                    'args' => array(
                        array(
                            'name' => 'datasourceName',
                            'description' => 'The name of the datasource to sync with',
                            'source' => array('path' => '0'),
                            'optional' => false
                        ),
                        array(
                            'name' => 'export',
                            'description' => 'The export to sync',
                            'source' => array('data'=>'export'),
                            'optional' => false
                        )
                    )
                )
            ),
            'relationships' => array(
                'export' => array(
                    'file' => array('type' => 'php', 'module' => 'gd_sync', 'name' => 'gd_sync.resource'),
                    'help' => 'Returns datasource export',
                    'callback' => 'gd_sync_resource_export',
                    'access callback' => 'gd_sync_resource_access',
                    'access arguments' => array('export'),
                    'access arguments append' => true,
                    'args' => array(
                        array(
                            'name' => 'datasourceName',
                            'description' => 'The name of the datasource to export',
                            'source' => array('path' => '0'),
                            'optional' => false
                        )
                    )
                )
            )
        )
    );
}

/**
 * @param $operation
 * @return bool
 */
function gd_sync_resource_access ( $operation ) {
    return gd_account_user_is_admin();
}

/**
 * @param $datasourceName
 * @return array|services_error
 */
function gd_sync_resource_export ( $datasourceName ) {
    try {
        $exportContext = new GD\Sync\Export\ExportContext(array('datasourceName'=>$datasourceName));
        $exportStream = new GD\Sync\Export\ExportStream();

        $exportController = new \GD\Sync\Export\ExportController();
        $exportController->export($exportStream,$exportContext);

        return $exportStream->flush();
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datamartName
 * @param $export
 * @return services_error|stdClass
 * @throws Exception
 */
function gd_sync_resource_import ( $datamartName, $export ) {
    try {
        $content = json_decode($export);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON');
        }

        $datasourceName = gd_datasource_create(array(
            'publicName' => $datamartName,
            'description' => 'Imported Topic'
        ));

        gd_datasource_set_active($datasourceName);
        $importContext = new GD\Sync\Import\ImportContext(array('datasourceName'=>$datasourceName,'operation'=>'create'));
        $importStream = new GD\Sync\Import\ImportStream();
        $importStream->set(null,$content);

        $importController = new \GD\Sync\Import\ImportController();
        $importController->import($importStream,$importContext);

        $apiObject = new stdClass();
        $apiObject->name = $datasourceName;
        $apiObject->messages = gd_get_session_messages();
        return $apiObject;
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datamartId
 * @param $export
 * @return services_error|stdClass
 * @throws Exception
 */
function gd_sync_resource_sync ( $datasourceName, $export ) {
    try {
        if (is_string($export)) {
            $content = json_decode($export);
        } else {
            $content = $export;
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON');
        }

        $importContext = new GD\Sync\Import\ImportContext(array('datasourceName'=>$datasourceName,'operation'=>'update'));
        $importStream = new GD\Sync\Import\ImportStream();
        $importStream->set(null,$content);

        $importController = new \GD\Sync\Import\ImportController();
        $importController->import($importStream,$importContext);

        $apiObject = new stdClass();
        $apiObject->name = $datasourceName;
        $apiObject->messages = gd_get_session_messages();
        return $apiObject;
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}