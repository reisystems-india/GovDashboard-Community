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


abstract class AbstractDataStructureController extends AbstractDataController implements DataStructureController {

    /**
     * @param string $datasetName
     * @return DataSourceStructureHandler
     */
    protected function getDataSourceStructureHandlerByDatasetName($datasetName) {
        return $this->getDataSourceHandlerByDatasetName($datasetName);
    }

    /**
     * @param DatasetMetaData $dataset
     * @return DataSourceStructureHandler
     */
    protected function getDataSourceStructureHandlerByDataset(DatasetMetaData $dataset) {
        return $this->getDataSourceHandlerByDataset($dataset);
    }

    /**
     * @param string $datasourceName
     * @return DataSourceStructureHandler
     */
    protected function getDataSourceStructureHandler($datasourceName) {
        return $this->getDataSourceHandler($datasourceName);
    }

    protected function lookupDataSourceHandler($type) {
        return DataSourceStructureFactory::getInstance()->getHandler($type);
    }

    protected function checkDatasetStructurePermission($datasetName) {
        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        $this->checkDataSourceStructurePermission($dataset->datasourceName);
    }

    protected function checkDataSourceStructurePermission($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        if ($datasource->isReadOnly()) {
            throw new IllegalStateException(t(
                'Structure manipulation is not permitted for the data source: %datasourceName',
                array('%datasourceName' => $datasource->publicName)));
        }
    }
}
