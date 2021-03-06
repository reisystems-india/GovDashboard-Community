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


abstract class AbstractDataManipulationController extends AbstractDataController implements DataManipulationController {

    /**
     * @param string $datasetName
     * @return DataSourceManipulationHandler
     */
    protected function getDataSourceManipulationHandlerByDatasetName($datasetName) {
        return $this->getDataSourceHandlerByDatasetName($datasetName);
    }

    /**
     * @param DatasetMetaData $dataset
     * @return DataSourceManipulationHandler
     */
    protected function getDataSourceManipulationHandlerByDataset(DatasetMetaData $dataset) {
        return $this->getDataSourceHandlerByDataset($dataset);
    }

    /**
     * @param string $datasourceName
     * @return DataSourceManipulationHandler
     */
    protected function getDataSourceManipulationHandler($datasourceName) {
        return $this->getDataSourceHandler($datasourceName);
    }

    protected function lookupDataSourceHandler($type) {
        return DataSourceManipulationFactory::getInstance()->getHandler($type);
    }
}
