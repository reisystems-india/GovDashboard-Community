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


abstract class AbstractDataQueryController extends AbstractDataController implements DataQueryController {

    /**
     * @param string $cubeName
     * @return DataSourceQueryHandler
     */
    protected function getDataSourceQueryHandlerByCubeName($cubeName) {
        $metamodel = data_controller_get_metamodel();

        $cube = $metamodel->getCube($cubeName);

        return $this->getDataSourceQueryHandlerByCube($cube);
    }

    /**
     * @param CubeMetaData $cube
     * @return DataSourceQueryHandler
     */
    protected function getDataSourceQueryHandlerByCube(CubeMetaData $cube) {
        return $this->getDataSourceQueryHandlerByDatasetName($cube->factsDatasetName);
    }

    /**
     * @param string $datasetName
     * @return DataSourceQueryHandler
     */
    protected function getDataSourceQueryHandlerByDatasetName($datasetName) {
        return $this->getDataSourceHandlerByDatasetName($datasetName);
    }

    /**
     * @param DatasetMetaData $dataset
     * @return DataSourceQueryHandler
     */
    protected function getDataSourceQueryHandlerByDataset(DatasetMetaData $dataset) {
        return $this->getDataSourceHandlerByDataset($dataset);
    }

    /**
     * @param string $datasourceName
     * @return DataSourceQueryHandler
     */
    protected function getDataSourceQueryHandler($datasourceName) {
        return $this->getDataSourceHandler($datasourceName);
    }

    protected function lookupDataSourceHandler($type) {
        return DataSourceQueryFactory::getInstance()->getHandler($type);
    }

    public function queryDataset($datasetName, $columns = NULL, $parameters = NULL, $orderBy = NULL, $startWith = 0, $limit = NULL, array $options = NULL, ResultFormatter $resultFormatter = NULL) {
        $request = new DataQueryControllerDatasetRequest();
        $request->initializeFrom($datasetName, $columns, $parameters, $orderBy, $startWith, $limit, $options, $resultFormatter);

        return $this->query($request);
    }

    public function queryCube($datasetName, $columns, $parameters = NULL, $orderBy = NULL, $startWith = 0, $limit = NULL, array $options = NULL, ResultFormatter $resultFormatter = NULL) {
        $request = new DataQueryControllerCubeRequest();
        $request->initializeFrom($datasetName, $columns, $parameters, $orderBy, $startWith, $limit, $options, $resultFormatter);

        return $this->query($request);
    }

    public function countDatasetRecords($datasetName, $parameters = NULL, array $options = NULL, ResultFormatter $resultFormatter = NULL) {
        $request = new DataQueryControllerDatasetRequest();
        $request->initializeFrom($datasetName, NULL, $parameters, NULL, 0, NULL, $options, $resultFormatter);

        return $this->countRecords($request);
    }

    public function countCubeRecords($datasetName, $columns, $parameters = NULL, array $options = NULL, ResultFormatter $resultFormatter = NULL) {
        $request = new DataQueryControllerCubeRequest();
        // Note: even if we want we cannot remove $columns parameter for cube record number calculation.
        // That is because $columns are used to identify columns for aggregation
        $request->initializeFrom($datasetName, $columns, $parameters, NULL, 0, NULL, $options, $resultFormatter);

        return $this->countRecords($request);
    }
}
