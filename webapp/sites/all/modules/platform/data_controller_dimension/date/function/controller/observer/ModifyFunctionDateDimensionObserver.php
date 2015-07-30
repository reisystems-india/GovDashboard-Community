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


class ModifyFunctionDateDimensionObserver extends CreateFunctionDateDimensionObserver {

    public function __construct(DatasetMetaData $newLogicalDataset) {
        parent::__construct($newLogicalDataset);

        $metamodel = data_controller_get_metamodel();

        $this->originalLogicalDataset = $metamodel->getDataset($newLogicalDataset->name);
    }

    public function dropColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $environment_metamodel = data_controller_get_environment_metamodel();

            $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);
            $originalColumn = $dataset->getColumn($columnName);

            $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);
            $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

            $generator = new FunctionDateDimensionMetaDataGenerator($datasourceQueryHandler);
            $generator->clean($originalColumn);
            $generator->clean($originalLogicalColumn);
        }

        parent::dropColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);
    }
}
