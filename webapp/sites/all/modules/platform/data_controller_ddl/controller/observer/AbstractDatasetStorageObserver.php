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


abstract class AbstractDatasetStorageObserver extends AbstractObject implements DatasetStorageObserver {

    public function initialize(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}
    public function validate(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}
    public function finalize(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}

    public function registerDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {}
    public function updateDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}
    public function truncateDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}
    public function unregisterDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {}

    public function enableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}
    public function disableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {}

    public function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {}
    public function updateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {}
    public function relocateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {}
    public function disableColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {}
    public function unregisterColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {}

    public function updateColumnParticipationInDatasetKey(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {}

    public function createColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {}
    public function truncateColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName) {}
    public function dropColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {}
}
