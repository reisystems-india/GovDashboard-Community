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


class CreateFunctionDateDimensionObserver extends AbstractDatasetStorageObserver {

    protected $newLogicalDataset = NULL;

    public function __construct(DatasetMetaData $newLogicalDataset) {
        parent::__construct();

        $this->newLogicalDataset = $newLogicalDataset;
    }

    public function createColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        parent::createColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);

        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $environment_metamodel = data_controller_get_environment_metamodel();

            $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
            $newColumn = $dataset->getColumn($columnName);

            $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);
            $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

            $generator = new FunctionDateDimensionMetaDataGenerator($datasourceQueryHandler);
            $generator->generate($newLogicalColumn);
            $generator->generate($newColumn);
        }
    }
}
