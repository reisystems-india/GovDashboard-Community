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


// FIXME move the file to controller/observer
class TruncateStarSchemaStorageObserver extends AbstractDatasetStorageObserver {

    protected $logicalDataset = NULL;

    public function __construct(DatasetMetaData $logicalDataset) {
        parent::__construct();

        $this->logicalDataset = $logicalDataset;
    }

    public function truncateDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::truncateDatasetStorage($callcontext, $dataset);

        // checking if all dimensions approve dataset truncation
        foreach (DimensionFactory::getInstance()->getHandlers() as $handler) {
            $handler->permitDatasetStorageTruncation($callcontext, $this->logicalDataset);
        }
    }

    public function truncateColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName) {
        $logicalColumn = $this->logicalDataset->getColumn($columnName);

        $handler = DimensionFactory::getInstance()->getHandler($logicalColumn->type->getLogicalApplicationType());
        $handler->truncateDimensionStorage($callcontext, $datasourceStructureHandler, $this->logicalDataset, $columnName);

        parent::truncateColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName);
    }
}
