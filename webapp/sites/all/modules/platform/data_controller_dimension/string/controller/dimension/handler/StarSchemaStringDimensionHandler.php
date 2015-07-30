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


class StarSchemaStringDimensionHandler extends AbstractDimensionHandler {

    public function createDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {
        parent::createDimensionStorage($callcontext, $datasourceStructureHandler, $logicalDataset, $columnName);

        $lookupDatasetName = StarSchemaNamingConvention::getAttributeRelatedName($logicalDataset->name, $columnName);

        $request = new DatasetStorageRequest($lookupDatasetName);
        LogHelper::log_debug($request);
        $datasourceStructureHandler->createDatasetStorage($callcontext, $request);

        $factsDatasetName = StarSchemaNamingConvention::getFactsRelatedName($logicalDataset->name);
        $request = new UpdateDatasetStorageRequest($factsDatasetName);
        $request->addOperation(new CreateColumnReferenceOperation($columnName, $lookupDatasetName));
        LogHelper::log_debug($request);
        $datasourceStructureHandler->updateDatasetStorage($callcontext, $request);
    }

    public function truncateDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {
        $lookupDatasetName = StarSchemaNamingConvention::getAttributeRelatedName($logicalDataset->name, $columnName);

        $request = new DatasetStorageRequest($lookupDatasetName);
        LogHelper::log_debug($request);
        $datasourceStructureHandler->truncateDatasetStorage($callcontext, $request);

        parent::truncateDimensionStorage($callcontext, $datasourceStructureHandler, $logicalDataset, $columnName);
    }

    public function dropDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {
        $lookupDatasetName = StarSchemaNamingConvention::getAttributeRelatedName($logicalDataset->name, $columnName);

        $request = new DatasetStorageRequest($lookupDatasetName);
        LogHelper::log_debug($request);
        $datasourceStructureHandler->dropDatasetStorage($callcontext, $request);

        parent::dropDimensionStorage($callcontext, $datasourceStructureHandler, $logicalDataset, $columnName);
    }
}
