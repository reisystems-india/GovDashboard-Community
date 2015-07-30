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


class DefaultDataStructureController extends AbstractDataStructureController {

    public function createDatabase($datasourceName, array $options = NULL) {
        $datasourceName = StringHelper::trim($datasourceName);

        $callcontext = $this->prepareCallContext();

        $request = new CreateDatabaseRequest($datasourceName, $options);
        LogHelper::log_debug($request);

        $datasourceStructureHandler = $this->getDataSourceStructureHandler($datasourceName);
        $datasourceStructureHandler->createDatabase($callcontext, $request);
    }

    public function dropDatabase($datasourceName) {
        $datasourceName = StringHelper::trim($datasourceName);

        $this->checkDataSourceStructurePermission($datasourceName);

        $callcontext = $this->prepareCallContext();

        $request = new DropDatabaseRequest($datasourceName);
        LogHelper::log_debug($request);

        $datasourceStructureHandler = $this->getDataSourceStructureHandler($datasourceName);
        $datasourceStructureHandler->dropDatabase($callcontext, $request);
    }

    public function createDatasetStorage(DatasetMetaData $newDataset, array $observers = NULL) {
        $this->checkDatasetStructurePermission($newDataset->name);

        $callcontext = $this->prepareCallContext();

        $datasourceStructureHandler = $this->getDataSourceStructureHandlerByDataset($newDataset);

        $impl = new CreateDatasetStorageImpl($datasourceStructureHandler);
        $impl->execute($callcontext, $newDataset, $observers);
    }

    public function modifyDatasetStorage(DatasetMetaData $modifiedDataset, array $observers = NULL) {
        $this->checkDatasetStructurePermission($modifiedDataset->name);

        $callcontext = $this->prepareCallContext();

        $datasourceStructureHandler = $this->getDataSourceStructureHandlerByDataset($modifiedDataset);

        $impl = new ModifyDatasetStorageImpl($datasourceStructureHandler);
        $impl->execute($callcontext, $modifiedDataset, $observers);
    }

    public function truncateDatasetStorage($datasetName, array $observers = NULL) {
        $datasetName = StringHelper::trim($datasetName);

        $this->checkDatasetStructurePermission($datasetName);

        $callcontext = $this->prepareCallContext();

        $datasourceStructureHandler = $this->getDataSourceStructureHandlerByDatasetName($datasetName);

        $impl = new TruncateDatasetStorageImpl($datasourceStructureHandler);
        $impl->execute($callcontext, $datasetName, $observers);
    }

    public function dropDatasetStorage($datasetName, array $observers = NULL) {
        $datasetName = StringHelper::trim($datasetName);

        $this->checkDatasetStructurePermission($datasetName);

        $callcontext = $this->prepareCallContext();

        $datasourceStructureHandler = $this->getDataSourceStructureHandlerByDatasetName($datasetName);

        $impl = new DropDatasetStorageImpl($datasourceStructureHandler);
        $impl->execute($callcontext, $datasetName, $observers);
    }

    public function enableDataset($datasetName, array $observers = NULL) {
        $datasetName = StringHelper::trim($datasetName);

        $this->checkDatasetStructurePermission($datasetName);

        $callcontext = $this->prepareCallContext();

        $datasourceStructureHandler = $this->getDataSourceStructureHandlerByDatasetName($datasetName);

        $impl = new EnableDatasetImpl($datasourceStructureHandler);
        $impl->execute($callcontext, $datasetName, $observers);
    }

    public function disableDataset($datasetName, array $observers = NULL) {
        $datasetName = StringHelper::trim($datasetName);

        $this->checkDatasetStructurePermission($datasetName);

        $callcontext = $this->prepareCallContext();

        $datasourceStructureHandler = $this->getDataSourceStructureHandlerByDatasetName($datasetName);

        $impl = new DisableDatasetImpl($datasourceStructureHandler);
        $impl->execute($callcontext, $datasetName, $observers);
    }
}
