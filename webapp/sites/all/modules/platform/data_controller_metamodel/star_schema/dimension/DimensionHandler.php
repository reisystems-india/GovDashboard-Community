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


interface DimensionHandler {

    /**
     * Checking if this dimension supports the provided data type
     *
     * @param $datatype
     * @return boolean
     */
    function isDataTypeSupported($datatype);

    /**
     * Validating if changes to dataset storage structure are permitted
     *
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalLogicalDataset
     * @param DatasetMetaData $modifiedLogicalDataset
     */
    function permitDatasetStorageChanges(DataControllerCallContext $callcontext, DatasetMetaData $originalLogicalDataset, DatasetMetaData $modifiedLogicalDataset);

    /**
     * Validating if the dataset can be allowed to be truncated
     *
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $logicalDataset
     */
    function permitDatasetStorageTruncation(DataControllerCallContext $callcontext, DatasetMetaData $logicalDataset);

    /**
     * Creates dimension internal storage
     *
     * @param DataControllerCallContext $callcontext
     * @param DataSourceStructureHandler $datasourceStructureHandler
     * @param DatasetMetaData $logicalDataset
     * @param $columnName
     */
    function createDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName);

    /**
     * Truncates dimension internal storage
     *
     * @param DataControllerCallContext $callcontext
     * @param DataSourceStructureHandler $datasourceStructureHandler
     * @param DatasetMetaData $logicalDataset
     * @param $columnName
     */
    function truncateDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName);

    /**
     * Drops dimension internal storage
     *
     * @param DataControllerCallContext $callcontext
     * @param DataSourceStructureHandler $datasourceStructureHandler
     * @param DatasetMetaData $logicalDataset
     * @param $columnName
     */
    function dropDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName);
}
