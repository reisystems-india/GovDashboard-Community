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
class CreateStarSchemaStorageObserver extends AbstractDatasetStorageObserver {

    protected $newLogicalDataset = NULL;
    protected $newCube = NULL;

    public function __construct(DatasetMetaData $newLogicalDataset) {
        parent::__construct();

        $metamodel = data_controller_get_metamodel();

        $this->newLogicalDataset = $newLogicalDataset;

        $cubeName = $newLogicalDataset->name;
        $this->newCube = $metamodel->getCube($cubeName);
    }

    public function registerDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {
        parent::registerDataset($callcontext, $dataset, $stage);

        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $this->newCube->loaderName = $dataset->loaderName;
        }
    }

    public function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        parent::registerColumn($callcontext, $dataset, $columnName, $stage);

        if ($stage == DatasetStorageObserver::STAGE__BEFORE) {
            $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
            $newColumn = $dataset->getColumn($columnName);

            // updating properties which were potentially changed in revertIneligibleColumnPropertyValues()
            $newLogicalColumn->key = $newColumn->key;
        }
    }

    public function createColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        parent::createColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);

        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
            $newColumn = $dataset->getColumn($columnName);

            $newLogicalColumn->persistence = $newColumn->persistence;

            $handler = DimensionFactory::getInstance()->getHandler($newLogicalColumn->type->getLogicalApplicationType());
            $handler->createDimensionStorage($callcontext, $datasourceStructureHandler, $this->newLogicalDataset, $columnName);
        }
    }
}
