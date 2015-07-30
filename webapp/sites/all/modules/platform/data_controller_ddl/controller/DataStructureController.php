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


interface DataStructureController extends DataController {

    function createDatabase($datasourceName, array $options = NULL);
    function dropDatabase($datasourceName);

    function createDatasetStorage(DatasetMetaData $newDataset, array $observers = NULL);
    function modifyDatasetStorage(DatasetMetaData $modifiedDataset, array $observers = NULL);
    function truncateDatasetStorage($datasetName, array $observers = NULL);
    function dropDatasetStorage($datasetName, array $observers = NULL);

    function enableDataset($datasetName, array $observers = NULL);
    function disableDataset($datasetName, array $observers = NULL);
}


class DatasetStorageChangeAction extends AbstractObject {

    public $newIncludedColumns = NULL;
    public $newExcludedColumns = NULL;
    public $restoredColumns = NULL;
    public $excludedColumns = NULL;
    public $updatedIndexColumns = NULL;
    public $updatedDataTypeIncludedColumns = NULL;
    public $updatedDataTypeExcludedColumns = NULL;
    public $updatedDataTypeCompatibleColumns = NULL;
    public $updatedColumns = NULL;
    public $deletedColumns = NULL;

    public $isKeyUpdated = FALSE;
    public $isDatasetUpdated = FALSE;

    public function isUpdated() {
        return isset($this->newIncludedColumns)
            || isset($this->newExcludedColumns)
            || isset($this->restoredColumns)
            || isset($this->excludedColumns)
            || isset($this->updatedIndexColumns)
            || isset($this->updatedDataTypeIncludedColumns)
            || isset($this->updatedDataTypeExcludedColumns)
            || isset($this->updatedDataTypeCompatibleColumns)
            || isset($this->updatedColumns)
            || isset($this->deletedColumns)
            || $this->isKeyUpdated
            || $this->isDatasetUpdated;
    }
}


interface DatasetStorageObserver {

    const STAGE__BEFORE = 'before';
    const STAGE__AFTER = 'after';

    function initialize(DataControllerCallContext $callcontext, DatasetMetaData $dataset);
    function validate(DataControllerCallContext $callcontext, DatasetMetaData $dataset);
    function finalize(DataControllerCallContext $callcontext, DatasetMetaData $dataset);

    function registerDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage);
    function updateDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset);
    function truncateDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset);
    function unregisterDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage);

    function enableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset);
    function disableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset);

    function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage);
    function updateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName);
    function relocateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName);
    function disableColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName);
    function unregisterColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage);

    function updateColumnParticipationInDatasetKey(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName);

    function createColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage);
    function truncateColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName);
    function dropColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage);
}
