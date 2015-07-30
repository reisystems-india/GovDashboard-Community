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


class DatasetUIMetaDataAssembler extends AbstractObject {

    // NULL    - not provided (no names should be referenced)
    // ''      - self-referenced (all names have to be referenced)
    // <other> - custom reference (all names have to be referenced)
    protected static $REFERENCE_PATH__DEFAULT = '';

    public $cache = NULL;

    public function __construct() {
        parent::__construct();

        // Note: do not store in local cache. Cloning of some objects could be very slow
        $this->cache = new SharedCacheFactoryProxy($this, 'metadata', FALSE);
    }

    protected function isSelfReferenced($referencePath) {
        return isset($referencePath)
            ? (($referencePath == self::$REFERENCE_PATH__DEFAULT) ? TRUE : NULL)
            : FALSE;
    }

    public function assemble($datasetName, array $connectedDatasetNames = NULL) {
        $cacheEntryNames = NULL;
        // processing primary dataset name
        $primaryDatasetCacheEntryName = $this->prepareCacheEntryName($datasetName);
        $cacheEntryNames[$datasetName] = $primaryDatasetCacheEntryName;
        // processing connected dataset names
        if (isset($connectedDatasetNames)) {
            foreach ($connectedDatasetNames as $connectedDatasetName) {
                $connectedDatasetCacheEntryName = $this->prepareCacheEntryName($connectedDatasetName, TRUE);
                $cacheEntryNames[$connectedDatasetName] = $connectedDatasetCacheEntryName;
            }
        }
        // loading UI meta datas from cache
        $cachedDatasetUIMetaData = $this->cache->getCachedEntries($cacheEntryNames);

        $cachableDatasetUIMetaDatas = NULL;
        // preparing UI meta data for primary dataset
        $primaryDatasetUIMetaData = isset($cachedDatasetUIMetaData[$primaryDatasetCacheEntryName]) ? $cachedDatasetUIMetaData[$primaryDatasetCacheEntryName] : NULL;
        if (!isset($primaryDatasetUIMetaData)) {
            $primaryDatasetUIMetaData = $this->generateDatasetUIMetaData($datasetName);

            // creating a clone of the UI meta data because it could be updated below with linked datasets
            // we should not store the UI meta data with linked datasets in cache
            $cachableDatasetUIMetaDatas[$primaryDatasetCacheEntryName] = clone $primaryDatasetUIMetaData;
        }
        // preparing UI meta data for connected datasets
        if (isset($connectedDatasetNames)) {
            foreach ($connectedDatasetNames as $connectedDatasetName) {
                $connectedDatasetCacheEntryName = $cacheEntryNames[$connectedDatasetName];

                $connectedDatasetUIMetaData = isset($cachedDatasetUIMetaData[$connectedDatasetCacheEntryName]) ? $cachedDatasetUIMetaData[$connectedDatasetCacheEntryName] : NULL;
                if (!isset($connectedDatasetUIMetaData)) {
                    $connectedDatasetUIMetaData = $this->generateDatasetUIMetaData($connectedDatasetName, self::$REFERENCE_PATH__DEFAULT);

                    $cachableDatasetUIMetaDatas[$connectedDatasetCacheEntryName] = $connectedDatasetUIMetaData;
                }

                $primaryDatasetUIMetaData->registerConnectedDataset($connectedDatasetUIMetaData);
            }
        }
        if (isset($cachableDatasetUIMetaDatas)) {
            $this->cache->cacheEntries($cachableDatasetUIMetaDatas);
        }

        return $primaryDatasetUIMetaData;
    }

    public function connectWith(DatasetUIMetaData $primaryDatasetUIMetaData, $datasetName, $referencePath = NULL) {
        // do not store generated UI meta data if reference path is provided for the following reasons:
        //   * there is unlimited number of possible reference paths;
        //   * it is almost impossible to delete cached items if dataset structure changes
        $isSelfReferenced = $this->isSelfReferenced($referencePath);
        $connectedDatasetCacheEntryName = isset($isSelfReferenced) ? $this->prepareCacheEntryName($datasetName, TRUE) : NULL;

        $connectedDatasetUIMetaData = isset($connectedDatasetCacheEntryName)
            ? $this->cache->getCachedEntry($connectedDatasetCacheEntryName)
            : NULL;
        if (!isset($connectedDatasetUIMetaData)) {
            $connectReferencePath = isset($referencePath) ? $referencePath : self::$REFERENCE_PATH__DEFAULT;
            $connectedDatasetUIMetaData = $this->generateDatasetUIMetaData($datasetName, $connectReferencePath);

            if (isset($connectedDatasetCacheEntryName)) {
                $this->cache->cacheEntry($connectedDatasetCacheEntryName, $connectedDatasetUIMetaData);
            }
        }

        $primaryDatasetUIMetaData->registerConnectedDataset($connectedDatasetUIMetaData);
    }

    protected function generateDatasetUIMetaData($datasetName, $referencePath = NULL) {
        $dataQueryController = data_controller_get_instance();
        $metamodel = data_controller_get_metamodel();

        $uiMetaData = new DatasetUIMetaData();

        $cube = $metamodel->findCubeByDatasetName($datasetName);
        if (isset($cube)) {
            // loading full meta data for the cube
            $cube = $dataQueryController->getCubeMetaData($cube->name);

            $generator = new CubeUIMetaDataGenerator();
            $generator->generate($uiMetaData, $cube, $referencePath);
        }
        else {
            $dataset = $metamodel->getDataset($datasetName);
            // loading full meta data for the dataset
            $dataset = $dataQueryController->getDatasetMetaData($dataset->name);

            $generator = new DatasetUIMetaDataGenerator();
            $generator->generate($uiMetaData, $dataset, $referencePath);
        }

        return $uiMetaData;
    }

    // *************************************************************************
    // *  Cache functions
    // *************************************************************************
    public function prepareCacheEntryName($datasetName, $isSelfReferenced = FALSE) {
        $key = $datasetName;
        if ($isSelfReferenced) {
            $key .= '[self-referenced]';
        }

        return $key;
    }
}
