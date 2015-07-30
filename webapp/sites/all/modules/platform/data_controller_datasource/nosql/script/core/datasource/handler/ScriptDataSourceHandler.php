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


class ScriptDataSourceHandler extends AbstractNoSQLDataSourceQueryHandler {

    const PARAMETER_NAME__OPERATION = 'exec';
    const PARAMETER_NAME__CALLBACK_SERVER_NAME = 'callback';
    const PARAMETER_NAME__DATASET_VERSION = 'ver';

    const CALLBACK_SERVER_NAME__DEFAULT = 'default';

    protected $callbackServerName = NULL;

    public function __construct($datasourceType, $extensionConfigurations) {
        parent::__construct($datasourceType, $extensionConfigurations);

        $serverConfig = Environment::getInstance()->getConfigurationSection('Server');
        $this->callbackServerName = isset($serverConfig['Name']) ? $serverConfig['Name'] : self::CALLBACK_SERVER_NAME__DEFAULT;
    }

    protected function initiateCURLProxy($uri) {
        return new CURLProxy($uri, new CURLHandlerOutputFormatter());
    }

    protected function executeScriptFunction(DatasetMetaData $dataset, $functionName, array $parameters = NULL) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        // preparing URI
        $uri = $datasource->protocol . '://' . $datasource->host . (isset($datasource->port) ? (':' . $datasource->port) : '') . $datasource->path;

        // preparing query parameters
        $queryParameters = NULL;
        $queryParameters[self::PARAMETER_NAME__OPERATION] = $functionName;
        $queryParameters[DataQueryControllerUIParameterNames::DATASET] = $dataset->name;
        // preparing server callback name
        $queryParameters[self::PARAMETER_NAME__CALLBACK_SERVER_NAME] = $this->callbackServerName;
        // preparing version
        $scriptFileName = data_controller_script_get_script_file_name($dataset);
        $selectedVersion = data_controller_script_prepare_version($dataset, $scriptFileName);
        if (isset($selectedVersion)) {
            $queryParameters[DataQueryControllerUIParameterNames::PARAMETER_NAME__DATASET_VERSION] = $selectedVersion;
        }
        ArrayHelper::merge($queryParameters, $parameters);

        // preparing CURL request
        $curlProxy = $this->initiateCURLProxy($uri);
        $handler = $curlProxy->initializeHandler('GET', '/dp_datasource_integration.py', $queryParameters);
        // executing the request in single thread environment
        $executor = new SingleCURLHandlerExecutor($handler);
        $output = $executor->execute();

        $records = NULL;
        if (isset($output)) {
            try {
                $records = json_decode($output, TRUE);
                if (isset($records)) {
                    if (count($records) == 0) {
                        $records = NULL;
                    }
                }
                else {
                    throw new IllegalStateException(t(
                        'Error occurred during execution of a script for the dataset: %datasetName',
                        array('%datasetName' => $dataset->publicName)));
                }
            }
            catch (Exception $e) {
                LogHelper::log_debug(new PreservedTextMessage($output));
                throw $e;
            }
        }

        return $records;
    }

    public function loadDatasetMetaData(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::loadDatasetMetaData($callcontext, $dataset);

        $loadedColumns = $this->executeScriptFunction($dataset, 'defineDatasetColumns');
        LogHelper::log_info(t('Received @count column(s)', array('@count' => count($loadedColumns))));
        LogHelper::log_debug($loadedColumns);

        $dataset->initializeColumnsFrom($loadedColumns);
    }

    public function queryDataset(DataControllerCallContext $callcontext, DatasetQueryRequest $request) {
        $datasetName = $request->getDatasetName();
        LogHelper::log_info(t('Querying script-based dataset: @datasetName', array('@datasetName' => $datasetName)));

        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        $serializer = new DatasetQueryUIRequestSerializer();
        $parameters = $serializer->serialize($request);

        $records = $this->executeScriptFunction($dataset, 'queryDataset', $parameters);
        LogHelper::log_info(t('Received @count records(s)', array('@count' => count($records))));
        // converting type of returned values
        if (isset($records)) {
            $columnTypeHandlers = NULL;
            foreach ($records as &$record) {
                foreach ($record as $columnName => $columnValue) {
                    if (!isset($columnTypeHandlers[$columnName])) {
                        $type = $dataset->getColumn($columnName)->type->applicationType;
                        $columnTypeHandlers[$columnName] = DataTypeFactory::getInstance()->getHandler($type);
                    }
                    $record[$columnName] = $columnTypeHandlers[$columnName]->castValue($columnValue);
                }
            }
            unset($record);
        }
        LogHelper::log_debug($records);

        return $records;
    }

    public function countDatasetRecords(DataControllerCallContext $callcontext, DatasetCountRequest $request) {
        $datasetName = $request->getDatasetName();
        LogHelper::log_notice(t('Counting script-based dataset records: @datasetName', array('@datasetName' => $datasetName)));

        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        $serializer = new DatasetCountUIRequestSerializer();
        $parameters = $serializer->serialize($request);

        $count = $this->executeScriptFunction($dataset, 'countDatasetRecords', $parameters);
        LogHelper::log_info(t('Counted @count record(s)', array('@count' => $count)));

        return $count;
    }

    public function queryCube(DataControllerCallContext $callcontext, CubeQueryRequest $request) {
        throw new UnsupportedOperationException();
    }

    public function countCubeRecords(DataControllerCallContext $callcontext, CubeCountRequest $request) {
        throw new UnsupportedOperationException();
    }
}
