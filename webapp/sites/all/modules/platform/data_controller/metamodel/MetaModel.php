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


class MetaModel extends AbstractMetaModel {

    /**
     * @var DatasetMetaData[]
     */
    public $datasets = array();
    /**
     * @var DatasetReference[]
     */
    public $references = array();
    /**
     * @var CubeMetaData[]
     */
    public $cubes = array();

    public function __clone() {
        parent::__clone();

        $this->datasets = ArrayHelper::copy($this->datasets);
        $this->references = ArrayHelper::copy($this->references);
        $this->cubes = ArrayHelper::copy($this->cubes);
    }

    protected function finalize() {
        parent::finalize();

        $this->finalizeDatasets($this->datasets);
        $this->finalizeReferences($this->references);
        $this->finalizeCubes($this->cubes);
    }

    protected function validate() {
        parent::validate();

        $this->validateDatasets($this->datasets);
        $this->validateReferences($this->references);
        $this->validateCubes($this->cubes);
    }

    // *****************************************************************************************************************************
    //   Dataset
    // *****************************************************************************************************************************
    public function findDataset($datasetName, $localOnly = FALSE) {
        if (isset($this->datasets)) {
            if (isset($this->datasets[$datasetName])) {
                return $this->datasets[$datasetName];
            }

            // using alternative way to find the dataset -> by alias
            foreach ($this->datasets as $dataset) {
                if ($dataset->isAliasMatched($datasetName)) {
                    return $dataset;
                }
            }
        }

        return NULL;
    }

    /**
     * @param $datasetName
     * @return DatasetMetaData
     */
    public function getDataset($datasetName, $localOnly = FALSE) {
        $dataset = $this->findDataset($datasetName, $localOnly);
        if (!isset($dataset)) {
            LogHelper::log_debug(array_keys($this->datasets));
            throw new IllegalArgumentException(t('Could not find %datasetName dataset definition', array('%datasetName' => $datasetName)));
        }

        return $dataset;
    }

    protected function finalizeDatasets(array &$datasets) {
        foreach ($datasets as $dataset) {
            $dataset->finalize();
        }
    }

    protected function validateDataset(DatasetMetaData $dataset) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        // dataset/datasourceName
        $datasourceName = isset($dataset->datasourceName) ? $dataset->datasourceName : NULL;
        if (!isset($datasourceName) || ($environment_metamodel->findDataSource($datasourceName) == NULL)) {
            LogHelper::log_debug(array_keys($environment_metamodel->datasources));
            LogHelper::log_debug($dataset);
            throw new IllegalStateException(t(
                '%datasourceName data source for %datasetName dataset cannot be resolved',
                array('%datasourceName' => $datasourceName, '%datasetName' => $dataset->publicName)));
        }
    }

    protected function validateDatasets(array &$datasets) {
        foreach ($datasets as $dataset) {
            $this->validateDataset($dataset);
        }
    }

    public function registerDataset(DatasetMetaData $dataset) {
        $this->checkAssemblingStarted();

        if (!isset($dataset->name)) {
            LogHelper::log_debug($dataset);
            throw new IllegalArgumentException(t('Dataset name has not been defined'));
        }
        $datasetName = $dataset->name;
        NameSpaceHelper::checkAlias($datasetName);

        $existingDataset = $this->findDataset($datasetName, TRUE);
        if (isset($existingDataset)) {
            if ($dataset->isTemporary()) {
                $this->unregisterDataset($datasetName);
            }
            else {
                LogHelper::log_debug($existingDataset);
                LogHelper::log_debug($dataset);
                throw new IllegalArgumentException(t(
                    'Dataset with name %datasetName has already been defined',
                    array('%datasetName' => $dataset->publicName)));
            }
        }

        // registering references to lookups based on column type
        foreach ($dataset->getColumns() as $column) {
            $lookupDatasetName = $column->type->getReferencedDatasetName();
            if (isset($lookupDatasetName)) {
                $referenceName = $lookupDatasetName;
                // FIXME it would be better if we provide the primary key (check findReferencesByDatasetName where we fix this issue)
                $this->registerSimpleReferencePoint($referenceName, $lookupDatasetName, /* Primary Key */ NULL);
                $this->registerSimpleReferencePoint($referenceName, $dataset->name, $column->name);
            }

            if (isset($column->type->logicalApplicationType)) {
                list($refDatasetName, $refColumnName) = ReferencePathHelper::splitReference($column->type->logicalApplicationType);
                if (isset($refDatasetName)) {
                    $referenceName = $refDatasetName;
                    // FIXME it would be better if we provide the primary key (check findReferencesByDatasetName where we fix this issue)
                    $this->registerSimpleReferencePoint($referenceName, $refDatasetName, /* Primary Key */ NULL);
                    $this->registerSimpleReferencePoint($referenceName, $dataset->name, $column->name);
                }
            }
        }

        $this->datasets[$datasetName] = $dataset;
    }

    public function unregisterDataset($datasetName) {
        $this->checkAssemblingStarted();

        $dataset = $this->getDataset($datasetName, TRUE);

        // removing references to this dataset
        foreach ($this->references as $reference) {
            foreach ($reference->points as $referencePointIndex => $referencePoint) {
                $isThisDatasetFound = FALSE;
                $isOtherDatasetFound = FALSE;
                foreach ($referencePoint->columns as $referencePointColumn) {
                    if ($referencePointColumn->datasetName == $datasetName) {
                        $isThisDatasetFound = TRUE;
                    }
                    else {
                        $isOtherDatasetFound = TRUE;
                    }
                }
                if ($isThisDatasetFound) {
                    if ($isOtherDatasetFound) {
                        LogHelper::log_debug($dataset);
                        LogHelper::log_debug($reference);
                        throw new IllegalStateException(t(
                            "%datasetName dataset cannot be unregistered. Meta model contains unremovable reference: %referenceName",
                            array('%datasetName' => $dataset->publicName, '%referenceName' => $reference->publicName)));
                    }
                    else {
                        unset($reference->points[$referencePointIndex]);
                    }
                }
            }
        }

        unset($this->datasets[$datasetName]);

        return $dataset;
    }

    // *****************************************************************************************************************************
    //   Dataset Reference
    // *****************************************************************************************************************************
    /**
     * @param $referenceName
     * @return DatasetReference|null
     */
    public function findReference($referenceName, $localOnly = FALSE) {
        return isset($this->references[$referenceName])
            ? $this->references[$referenceName]
            : NULL;
    }

    /**
     * @param $referenceName
     * @return DatasetReference
     */
    public function getReference($referenceName, $localOnly = FALSE) {
        $reference = $this->findReference($referenceName, $localOnly);
        if (!isset($reference)) {
            LogHelper::log_debug(array_keys($this->references));
            throw new IllegalArgumentException(t('Could not find %referenceName reference definition', array('%referenceName' => $referenceName)));
        }

        return $reference;
    }

    /**
     * @param $datasetName
     * @return DatasetReference[]|null
     */
    public function findReferencesByDatasetName($datasetName) {
        $references = NULL;
        foreach ($this->references as $reference) {
            foreach ($reference->points as $referencePoint) {
                foreach ($referencePoint->columns as $referencePointColumn) {
                    if ($referencePointColumn->datasetName == $datasetName) {
                        $references[] = $reference;
                        continue 3;
                    }
                }
            }
        }
        if (!isset($references)) {
            return NULL;
        }

        $environment_metamodel = NULL;

        $dataset = $this->getDataset($datasetName);
        $datasourceName = $dataset->datasourceName;

        // eliminating references from different data sources
        $selectedReferences = NULL;
        foreach ($references as $reference) {
            $selectedReference = new DatasetReference();
            foreach ($reference->points as $referencePoint) {
                $isPointSelected = TRUE;
                foreach ($referencePoint->columns as $referencePointColumn) {
                    // FIXME fixing references (to resolve the issue we need to post process configuration)
                    if (!isset($referencePointColumn->columnName)) {
                        $referencePointColumn->columnName = $this->getDataset($referencePointColumn->datasetName)->getKeyColumn()->name;
                    }

                    if ($referencePointColumn->datasetName == $dataset->name) {
                        continue;
                    }

                    $columnDataset = $this->getDataset($referencePointColumn->datasetName);
                    // this dataset is shared across different data sources
                    if ($columnDataset->isShared()) {
                        continue;
                    }

                    $columnDataSourceName = $columnDataset->datasourceName;
                    // this dataset is from the same data source
                    if ($columnDataSourceName == $datasourceName) {
                        continue;
                    }

                    if (!isset($environment_metamodel)) {
                        $environment_metamodel = data_controller_get_environment_metamodel();
                    }
                    $datasource = $environment_metamodel->getDataSource($columnDataSourceName);
                    if (!$datasource->isShared()) {
                        $isPointSelected = FALSE;
                        break;
                    }
                }
                if ($isPointSelected) {
                    $selectedReference->points[] = $referencePoint;
                }
            }

            if ($selectedReference->getPointCount() > 1) {
                // preparing properties of selected reference
                $selectedReference->initializeInstanceFrom($reference);
                $selectedReferences[] = $selectedReference;
            }
        }

        return $selectedReferences;
    }

    protected function finalizeReferences(array &$references) {
        foreach ($references as $reference) {
            $reference->finalize();
        }
    }

    protected function validateReference(DatasetReference $reference) {
        // checking if dataset name is valid for all reference points
        $pointCount = 0;
        foreach ($reference->points as $referencePoint) {
            // FIXME do not check for dataset name. Simplify code which needed to be changed to accommodate such check (Example: post processing of loaded configuration)
            /*
            foreach ($referencePoint->columns as $referencePointColumn) {
                $dataset = $this->findDataset($referencePointColumn->datasetName);
                if (!isset($dataset)) {
                    LogHelper::log_debug($this->datasets do not store while model in log!!!);
                    throw new IllegalStateException(t(
                        '%datasetName dataset for %referenceName reference cannot be resolved',
                        array('%datasetName' => $referencePointColumn->datasetName, '%referenceName' => $reference->publicName)));
                }
            }*/

            $pointCount++;
        }
    }

    protected function validateReferences(array &$references) {
        foreach ($references as $reference) {
            $this->validateReference($reference);
        }
    }

    public function registerReference(DatasetReference $reference) {
        $this->checkAssemblingStarted();

        if (!isset($reference->name)) {
            LogHelper::log_debug($reference);
            throw new IllegalArgumentException(t('Reference name has not been defined'));
        }

        $referenceName = $reference->name;
        NameSpaceHelper::checkAlias($referenceName);

        $existingReference = $this->findReference($referenceName, TRUE);
        if (isset($existingReference)) {
            LogHelper::log_debug($existingReference);
            LogHelper::log_debug($reference);
            throw new IllegalArgumentException(t(
                'Reference with name %referenceName has already been defined',
                array('%referenceName' => $reference->publicName)));
        }

        $this->references[$referenceName] = $reference;
    }

    public function unregisterReference($referenceName) {
        $this->checkAssemblingStarted();

        $reference = $this->getReference($referenceName, TRUE);

        unset($this->references[$referenceName]);

        return $reference;
    }

    public function registerSimpleReferencePoint($referenceName, $datasetName, $columnName) {
        $reference = $this->findReference($referenceName);
        if (isset($reference)) {
            $this->unregisterReference($referenceName);
        }
        else {
            $reference = new DatasetReference();
            $reference->name = $referenceName;
        }

        $referencePoint = $reference->initiatePoint();
        $referencePointColumn = $referencePoint->initiateColumn();
        $referencePointColumn->datasetName = $datasetName;
        $referencePointColumn->columnName = $columnName;
        $referencePoint->registerColumnInstance($referencePointColumn);
        $reference->registerPointInstance($referencePoint);

        $this->registerReference($reference);
    }

    // *****************************************************************************************************************************
    //   Cube
    // *****************************************************************************************************************************
    protected function fixCube(CubeMetaData $cube) {
        // ... facts dataset
        if (!isset($cube->factsDataset)) {
            $cube->factsDataset = $this->findDataset($cube->factsDatasetName);
        }

        if (isset($cube->dimensions)) {
            foreach ($cube->dimensions as $dimension) {
                // ... datasets
                if (isset($dimension->datasetName) && !isset($dimension->dataset)) {
                    $dimension->dataset = $this->findDataset($dimension->datasetName);
                }
            }
        }
    }

    public function findCube($cubeName, $localOnly = FALSE) {
        $cube = NULL;

        if (isset($this->cubes[$cubeName])) {
            $cube = $this->cubes[$cubeName];
            $this->fixCube($cube);
        }

        return $cube;
    }

    /**
     * @param $cubeName
     * @return CubeMetaData
     */
    public function getCube($cubeName, $localOnly = FALSE) {
        $cube = $this->findCube($cubeName, $localOnly);
        if (!isset($cube)) {
            LogHelper::log_debug(array_keys($this->cubes));
            throw new IllegalArgumentException(t('Could not find %cubeName cube definition', array('%cubeName' => $cubeName)));
        }

        return $cube;
    }

    /**
     * @param $datasetName
     * @return CubeMetaData
     */
    public function findCubeByDatasetName($datasetName) {
        $dataset = $this->findDataset($datasetName);

        if (isset($dataset)) {
            foreach ($this->cubes as $cube) {
                if ($cube->factsDatasetName == $dataset->name) {
                    $this->fixCube($cube);
                    return $cube;
                }
            }
        }

        return NULL;
    }

    /**
     * @throws IllegalArgumentException
     * @param $datasetName
     * @return CubeMetaData
     */
    public function getCubeByDatasetName($datasetName) {
        $cube = $this->findCubeByDatasetName($datasetName);
        if (!isset($cube)) {
            $dataset = $this->getDataset($datasetName);
            throw new IllegalArgumentException(t(
                'Could not find a cube for the dataset: %datasetName',
                array('%datasetName' => $dataset->publicName)));
        }

        return $cube;
    }

    protected function finalizeCubes(array &$cubes) {
        foreach ($cubes as $cube) {
            $cube->finalize();
        }
    }

    protected function validateCube(CubeMetaData $cube) {
        if ($this->findDataset($cube->factsDatasetName) == NULL) {
            LogHelper::log_debug(array_keys($this->datasets));
            throw new IllegalStateException(t(
            	'%datasetName facts dataset for %cubeName cube cannot be resolved',
                array('%datasetName' => $cube->factsDatasetName, '%cubeName' => $cube->publicName)));
        }

        if (isset($cube->dimensions)) {
            foreach ($cube->dimensions as $dimension) {
                // the dimension should have a reference to facts dataset
                if (!isset($dimension->attributeColumnName)) {
                    LogHelper::log_debug($dimension);
                    throw new IllegalStateException(t(
                        "%dimensionName dimension in %cubeName cube should have a reference to facts dataset ('attributeColumnName' property)",
                        array('%cubeName' => $cube->publicName, '%dimensionName' => $dimension->publicName)));

                }

                if (!isset($dimension->datasetName)) {
                    continue;
                }

                $datasetName = $dimension->datasetName;
                $dataset = $this->findDataset($datasetName);
                if (!isset($dataset)) {
                    LogHelper::log_debug(array_keys($this->datasets));
                    throw new IllegalStateException(t(
                        "%datasetName dataset for %dimensionName dimension in %cubeName cube cannot be resolved",
                        array('%datasetName' => $datasetName, '%cubeName' => $cube->publicName, '%dimensionName' => $dimension->publicName)));
                }

                // FIXME remove the following functionality
                // setting key field for each dimension
                if (!isset($dimension->key)) {
                    $keyColumn = $dataset->findKeyColumn();
                    if (isset($keyColumn)) {
                        $dimension->key = $keyColumn->name;
                    }
                    else {
                        LogHelper::log_debug($dataset);
                        LogHelper::log_debug($dimension);
                        throw new IllegalStateException(t(
                            "Could not identify 'key' attribute to access %datasetName dataset records for %dimensionName dimension of %cubeName cube",
                            array('%datasetName' => $dataset->publicName, '%cubeName' => $cube->publicName, '%dimensionName' => $dimension->publicName)));
                    }
                }
            }
        }
    }

    protected function validateCubes(array &$cubes) {
        foreach ($cubes as $cube) {
            $this->validateCube($cube);
        }
    }

    public function registerCube(CubeMetaData $cube) {
        $this->checkAssemblingStarted();

        if (!isset($cube->name)) {
            LogHelper::log_debug($cube);
            throw new IllegalArgumentException(t('Cube name has not been defined'));
        }

        $cubeName = $cube->name;
        NameSpaceHelper::checkAlias($cubeName);

        $existingCube = $this->findCube($cubeName, TRUE);
        if (isset($existingCube)) {
            if ($cube->isTemporary()) {
                $this->unregisterCube($cubeName);
            }
            else {
                LogHelper::log_debug($existingCube);
                LogHelper::log_debug($cube);
                throw new IllegalArgumentException(t(
                    'Cube with name %cubeName has already been defined',
                    array('%cubeName' => $cube->publicName)));
            }
        }

        if (!$cube->isTemporary()) {
            // we support only one cube per dataset
            $cube2 = $this->findCubeByDatasetName($cube->factsDatasetName);
            if (isset($cube2)) {
                LogHelper::log_debug($cube2);
                LogHelper::log_debug($cube);
                throw new IllegalArgumentException(t(
                    'Found several cubes for %datasetName dataset: [%cubeName1, %cubeName2]',
                    array('%datasetName' => $cube->factsDatasetName, '%cubeName1' => $cube->publicName, '%cubeName2' => $cube2->publicName)));
            }
        }

        // fixing cube properties
        if (isset($cube->dimensions)) {
            foreach ($cube->dimensions as $dimension) {
                if (!isset($dimension->attributeColumnName)) {
                    $dimension->attributeColumnName = $dimension->name;
                }
            }
        }

        $this->cubes[$cubeName] = $cube;
    }

    public function unregisterCube($cubeName) {
        $this->checkAssemblingStarted();

        $cube = $this->getCube($cubeName, TRUE);

        unset($this->cubes[$cubeName]);

        return $cube;
    }
}
