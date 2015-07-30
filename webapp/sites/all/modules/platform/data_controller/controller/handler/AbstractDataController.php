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


abstract class AbstractDataController extends AbstractObject implements DataController {

    /**
     * @return DataControllerCallContext
     */
    protected function prepareCallContext() {
        return new DataControllerCallContext();
    }

    /**
     * @param string $datasetName
     * @return DataSourceHandler
     */
    protected function getDataSourceHandlerByDatasetName($datasetName) {
        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        return $this->getDataSourceHandlerByDataset($dataset);
    }

    /**
     * @param DatasetMetaData $dataset
     * @return DataSourceHandler
     */
    protected function getDataSourceHandlerByDataset(DatasetMetaData $dataset) {
        return $this->getDataSourceHandler($dataset->datasourceName);
    }

    /**
     * @param string $datasourceName
     * @return DataSourceHandler
     */
    protected function getDataSourceHandler($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        return $this->lookupDataSourceHandler($datasource->type);
    }

    /**
     * @param string $type
     * @return DataSourceHandler
     */
    abstract protected function lookupDataSourceHandler($type);
}
