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


abstract class AbstractDimensionHandler extends AbstractObject implements DimensionHandler {

    public function isDataTypeSupported($datatype) {
        throw new UnsupportedOperationException(t('The data type is not supported yet: %datatype', array('%datatype' => $datatype)));
    }

    public function permitDatasetStorageChanges(DataControllerCallContext $callcontext, DatasetMetaData $originalLogicalDataset, DatasetMetaData $modifiedLogicalDataset) {}
    public function permitDatasetStorageTruncation(DataControllerCallContext $callcontext, DatasetMetaData $logicalDataset) {}

    public function createDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {}
    public function truncateDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {}
    public function dropDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {}
}
