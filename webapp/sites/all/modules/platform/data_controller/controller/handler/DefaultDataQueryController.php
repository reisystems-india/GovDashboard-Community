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


/*
 * Data Controller has the following responsibilities:
 *   - prepares context for the call
 *   - cleans/adjusts (trims, converts) input parameters
 *   - wraps input parameters into request object(s)
 */
class DefaultDataQueryController extends AbstractDataQueryController {

    public function getDatasetMetaData($datasetName) {
        $datasetName = StringHelper::trim($datasetName);

        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);
        if (!$dataset->isComplete()) {
            $callcontext = $this->prepareCallContext();

            MetaModelFactory::getInstance()->startGlobalModification();
            try {
                RequestChainFactory::getInstance()->initializeChain()->loadDatasetMetaData(
                    $this->getDataSourceQueryHandlerByDataset($dataset), $callcontext, $dataset);

                $dataset->markAsComplete();
            }
            catch (Exception $e) {
                MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
                throw $e;
            }
            MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
        }

        return $dataset;
    }

    public function getCubeMetaData($cubeName) {
        $cubeName = StringHelper::trim($cubeName);

        $metamodel = data_controller_get_metamodel();

        $cube = $metamodel->getCube($cubeName);
        if (!$cube->isComplete()) {
            $callcontext = $this->prepareCallContext();

            // preparing meta data for facts dataset
            if (!isset($cube->factsDataset)) {
                $cube->factsDataset = $this->getDatasetMetaData($cube->factsDatasetName);
            }

            // preparing meta data for dimension datasets
            foreach ($cube->getDimensions() as $dimension) {
                if (isset($dimension->datasetName) && !isset($dimension->dataset)) {
                    $dimension->dataset = $this->getDatasetMetaData($dimension->datasetName);
                }
            }

            // preparing metadata for the rest of the cube
            RequestChainFactory::getInstance()->initializeChain()->prepareCubeMetaData(
                $this->getDataSourceQueryHandlerByDatasetName($cube->factsDatasetName), $callcontext, $cube);
        }

        return $cube;
    }

    public function getNextSequenceValues($datasourceName, $sequenceName, $quantity) {
        $datasourceName = StringHelper::trim($datasourceName);
        $sequenceName = StringHelper::trim($sequenceName);

        $callcontext = $this->prepareCallContext();

        $request = new SequenceRequest($datasourceName, $sequenceName, $quantity);

        LogHelper::log_debug($request);

        return RequestChainFactory::getInstance()->initializeChain()->getNextSequenceValues(
            $this->getDataSourceQueryHandler($datasourceName), $callcontext, $request);
    }

    public function query($request) {
        $requestCleaner = new DataQueryControllerRequestCleaner();
        $adjustedRequest = $requestCleaner->adjustRequest($request);

        $result = NULL;
        if ($adjustedRequest instanceof DataQueryControllerDatasetRequest) {
            $result = $this->executeDatasetQueryRequest($adjustedRequest);
        }
        elseif ($adjustedRequest instanceof DataQueryControllerCubeRequest) {
            $result = $this->executeCubeQueryRequest($adjustedRequest);
        }
        elseif (isset($adjustedRequest)) {
            throw new UnsupportedOperationException();
        }

        return $result;
    }

    public function countRecords($request) {
        $requestCleaner = new DataQueryControllerRequestCleaner();
        $adjustedRequest = $requestCleaner->adjustRequest($request);

        $result = NULL;
        if ($adjustedRequest instanceof DataQueryControllerDatasetRequest) {
            $result = $this->executeDatasetCountRequest($adjustedRequest);
        }
        elseif ($adjustedRequest instanceof DataQueryControllerCubeRequest) {
            $result = $this->executeCubeCountRequest($adjustedRequest);
        }
        elseif (isset($adjustedRequest)) {
            throw new UnsupportedOperationException();
        }

        return $result;
    }

    protected function executeDatasetQueryRequest(DataQueryControllerDatasetRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $callcontext = $this->prepareCallContext();

        $requestPreparer = new DataSourceDatasetQueryRequestPreparer();
        $datasetQueryRequest = $requestPreparer->prepareQueryRequest($request);

        $this->prepareDatasetRequestMetaData($datasetQueryRequest);

        if (isset($request->resultFormatter)) {
            $request->resultFormatter->adjustDatasetQueryRequest($callcontext, $datasetQueryRequest);
        }

        LogHelper::log_debug($datasetQueryRequest);

        $datasetName = $datasetQueryRequest->getDatasetName();

        if (isset($request->resultFormatter)) {
            LogHelper::log_debug(t(
                "Using '!formattingPath' to format result of the dataset: @datasetName",
                array('!formattingPath' => $request->resultFormatter->printFormattingPath(), '@datasetName' => $datasetName)));
        }

        $dataset = $metamodel->getDataset($datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $isCacheSupported = $this->isCacheSupported($datasource);
        $cache = $isCacheSupported ? new DataQueryControllerCacheProxy($datasetQueryRequest) : NULL;

        list($data, $cacheHit) = isset($cache) ? $cache->getCachedResult() : array(NULL, FALSE);
        if (!$cacheHit) {
            $data = RequestChainFactory::getInstance()->initializeChain()->queryDataset(
                $this->lookupDataSourceHandler($datasource->type), $callcontext, $datasetQueryRequest);
            if ($isCacheSupported) {
                $cache->cacheResult($data);
            }
        }

        return isset($request->resultFormatter) ? $request->resultFormatter->formatRecords($data) : $data;
    }

    protected function executeDatasetCountRequest(DataQueryControllerDatasetRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $callcontext = $this->prepareCallContext();

        $requestPreparer = new DataSourceDatasetQueryRequestPreparer();
        $datasetCountRequest = $requestPreparer->prepareCountRequest($request);

        $this->prepareDatasetRequestMetaData($datasetCountRequest);

        if (isset($request->resultFormatter)) {
            $request->resultFormatter->adjustDatasetCountRequest($callcontext, $datasetCountRequest);
        }

        LogHelper::log_debug($datasetCountRequest);

        $datasetName = $datasetCountRequest->getDatasetName();

        $dataset = $metamodel->getDataset($datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $isCacheSupported = $this->isCacheSupported($datasource);
        $cache = $isCacheSupported ? new DataQueryControllerCacheProxy($datasetCountRequest) : NULL;

        list($data, $cacheHit) = isset($cache) ? $cache->getCachedResult() : array(NULL, FALSE);
        if (!$cacheHit) {
            $data = RequestChainFactory::getInstance()->initializeChain()->countDatasetRecords(
                $this->lookupDataSourceHandler($datasource->type), $callcontext, $datasetCountRequest);
            if ($isCacheSupported) {
                $cache->cacheResult($data);
            }
        }

        return $data;
    }

    protected function prepareDatasetRequestMetaData(AbstractDatasetQueryRequest $request) {
        $this->getDatasetMetaData($request->getDatasetName());
    }

    protected function executeCubeQueryRequest(DataQueryControllerCubeRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $callcontext = $this->prepareCallContext();

        $requestPreparer = new DataSourceCubeQueryRequestPreparer();
        $cubeQueryRequest = $requestPreparer->prepareQueryRequest($request);

        $this->prepareCubeRequestMetaData($cubeQueryRequest);

        if (isset($request->resultFormatter)) {
            $request->resultFormatter->adjustCubeQueryRequest($callcontext, $cubeQueryRequest);
        }

        LogHelper::log_debug($cubeQueryRequest);

        $cubeName = $cubeQueryRequest->getCubeName();

        if (isset($request->resultFormatter)) {
            LogHelper::log_debug(t(
                "Using '!formattingPath' to format result of the cube: @cubeName",
                array('!formattingPath' => $request->resultFormatter->printFormattingPath(), '@cubeName' =>  $cubeName)));

        }

        $dataset = $metamodel->getDataset($request->datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $isCacheSupported = $this->isCacheSupported($datasource);
        $cache = $isCacheSupported ? new DataQueryControllerCacheProxy($cubeQueryRequest) : NULL;

        list($data, $cacheHit) = isset($cache) ? $cache->getCachedResult() : array(NULL, FALSE);
        if (!$cacheHit) {
            $data = RequestChainFactory::getInstance()->initializeChain()->queryCube(
                $this->lookupDataSourceHandler($datasource->type), $callcontext, $cubeQueryRequest);
            if ($isCacheSupported) {
                $cache->cacheResult($data);
            }
        }

        return isset($request->resultFormatter) ? $request->resultFormatter->formatRecords($data) : $data;
    }

    protected function executeCubeCountRequest(DataQueryControllerCubeRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $callcontext = $this->prepareCallContext();

        $requestPreparer = new DataSourceCubeQueryRequestPreparer();
        $cubeCountRequest = $requestPreparer->prepareCountRequest($request);

        $this->prepareCubeRequestMetaData($cubeCountRequest);

        if (isset($request->resultFormatter)) {
            $request->resultFormatter->adjustCubeCountRequest($callcontext, $cubeCountRequest);
        }

        LogHelper::log_debug($cubeCountRequest);

        $dataset = $metamodel->getDataset($request->datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $isCacheSupported = $this->isCacheSupported($datasource);
        $cache = $isCacheSupported ? new DataQueryControllerCacheProxy($cubeCountRequest) : NULL;

        list($data, $cacheHit) = isset($cache) ? $cache->getCachedResult() : array(NULL, FALSE);
        if (!$cacheHit) {
            $data = RequestChainFactory::getInstance()->initializeChain()->countCubeRecords(
                $this->lookupDataSourceHandler($datasource->type), $callcontext, $cubeCountRequest);
            if ($isCacheSupported) {
                $cache->cacheResult($data);
            }
        }

        return $data;
    }

    protected function prepareCubeRequestMetaData(AbstractCubeQueryRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $cube = $metamodel->getCube($request->getCubeName());
        $this->getDatasetMetaData($cube->factsDatasetName);

        if (isset($request->referencedRequests)) {
            foreach ($request->referencedRequests as $referencedRequest) {
                $referencedCube = $metamodel->getCube($referencedRequest->getCubeName());
                $this->getDatasetMetaData($referencedCube->factsDatasetName);
            }
        }
    }

    // *************************************************************************
    // *  data cache-related functions
    // *************************************************************************
    protected function isCacheSupported(DataSourceMetaData $datasource) {
        return $datasource->isReadOnly() && (isset($datasource->cachable) ? $datasource->cachable : FALSE);
    }
}
