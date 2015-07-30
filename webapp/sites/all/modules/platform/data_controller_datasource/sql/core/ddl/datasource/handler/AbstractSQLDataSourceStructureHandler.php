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


abstract class AbstractSQLDataSourceStructureHandler extends AbstractSQLDataSourceHandler implements DataSourceStructureHandler {

    protected function checkDatabaseName(DataSourceMetaData $datasource) {
        if (!isset($datasource->database)) {
            throw new IllegalArgumentException(t(
                'Database name is not set for %datasourceName data source',
                array('%datasourceName' => $datasource->publicName)));
        }
    }

    public function createDatabase(DataControllerCallContext $callcontext, CreateDatabaseRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($request->datasourceName);
        $this->checkDatabaseName($datasource);

        // cloning data source to exclude database name which we actually want to create
        // otherwise we do not want to have Catch 22: trying to connect to a database which we actually try to create
        $connectionDataSource = clone $datasource;
        $connectionDataSource->database = NULL;
        // because the datasource is temporary we will not cache corresponding connection
        $connectionDataSource->temporary = TRUE;

        $sql = $this->getExtension('createDatabase')->generate($this, $datasource, $request->options);
        LogHelper::log_info(new StatementLogMessage('database.create', $sql));
        $this->executeStatement($connectionDataSource, $sql);
    }

    public function dropDatabase(DataControllerCallContext $callcontext, DropDatabaseRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($request->datasourceName);
        $this->checkDatabaseName($datasource);

        $sql = $this->getExtension('dropDatabase')->generate($this, $datasource);
        LogHelper::log_info(new StatementLogMessage('database.drop', $sql));
        $this->executeStatement($datasource, $sql);
    }

    public function createDatasetStorage(DataControllerCallContext $callcontext, DatasetStorageRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $dataset = DatasetSourceTypeFactory::getInstance()->getTableDataset($request->datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $sql = $this->getExtension('createTable')->prepareTableCreateStatement($this, $dataset);
        LogHelper::log_info(new StatementLogMessage('table.create', $sql));
        $this->executeStatement($datasource, $sql);
    }

    public function updateDatasetStorage(DataControllerCallContext $callcontext, UpdateDatasetStorageRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $dataset = DatasetSourceTypeFactory::getInstance()->getTableDataset($request->datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $sql = $this->getExtension('createTable')->prepareTableUpdateStatement($this, $callcontext, $dataset, $request->operations);
        LogHelper::log_info(new StatementLogMessage('table.update', $sql));
        $this->executeStatement($datasource, $sql);
    }

    public function truncateDatasetStorage(DataControllerCallContext $callcontext, DatasetStorageRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $dataset = DatasetSourceTypeFactory::getInstance()->getTableDataset($request->datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $sql = $this->getExtension('truncateTable')->generate($this, $dataset);
        LogHelper::log_info(new StatementLogMessage('table.truncate', $sql));
        $this->executeStatement($datasource, $sql);
    }

    public function dropDatasetStorage(DataControllerCallContext $callcontext, DatasetStorageRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $dataset = DatasetSourceTypeFactory::getInstance()->getTableDataset($request->datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $sql = $this->getExtension('dropTable')->generate($this, $dataset);
        LogHelper::log_info(new StatementLogMessage('table.drop', $sql));
        $this->executeStatement($datasource, $sql);
    }
}
