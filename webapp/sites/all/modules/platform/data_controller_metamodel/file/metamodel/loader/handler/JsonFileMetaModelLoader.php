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


class JsonFileMetaModelLoader extends AbstractFileMetaModelLoader {

    protected $converterJson2Object = NULL;

    public function __construct() {
        parent::__construct();
        $this->converterJson2Object = new Json2PHPObject();
    }

    public function load(AbstractMetaModel $metamodel, array $filters = NULL) {
        LogHelper::log_notice(t('Loading Meta Model from configuration files ...'));

        return parent::load($metamodel, $filters);
    }

    protected function getMetaModelFolderName() {
        return 'metamodel';
    }

    protected function processFileContent($filename, $fileContent) {
        $processedContent = $this->converterJson2Object->convert($fileContent);
        if (!isset($processedContent)) {
            throw new IllegalStateException(t('Error in JSON structure in %filename file', array('%filename' => $filename)));
        }

        return $processedContent;
    }

    protected function merge(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, __AbstractFileMetaModelLoader_Source $source) {
        $this->mergeWithDatasets($metamodel, $filters, $namespace, $source);
        $this->mergeWithReferences($metamodel, $filters, $namespace, $source);
        $this->mergeWithCubes($metamodel, $filters, $namespace, $source);
    }

    protected function mergeWithDatasets(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, __AbstractFileMetaModelLoader_Source $source) {
        if (!isset($source->content->datasets)) {
            return;
        }

        $loaderName = $this->getName();

        foreach ($source->content->datasets as $sourceDatasetName => $sourceDataset) {
            $dataset = $this->mergeWithDataset($metamodel, $filters, $namespace, $sourceDatasetName, $sourceDataset);

            // adding system properties
            if (isset($dataset)) {
                $dataset->loaderName = $loaderName;
                $dataset->loadedFromFile = $source->filename;
                $dataset->version = $source->datetime;
            }
        }
    }

    protected function mergeWithDataset(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, $sourceDatasetName, $sourceDataset) {
        $datasetName = NameSpaceHelper::resolveNameSpace($namespace, $sourceDatasetName);

        // dataset/datasource/name
        if (isset($sourceDataset->datasourceName)) {
            $sourceDataset->datasourceName = NameSpaceHelper::resolveNameSpace($namespace, $sourceDataset->datasourceName);
        }
        elseif (isset($namespace)) {
            $sourceDataset->datasourceName = $namespace;
        }
        else {
            throw new IllegalStateException(t(
                '%datasetName dataset definition does not contain a reference to datasource',
                array('%datasetName' => (isset($sourceDataset->publicName) ? $sourceDataset->publicName : $datasetName))));
        }

        // datasets defined using .json refer to 'persistent' column
        if (isset($sourceDataset->columns)) {
            foreach ($sourceDataset->columns as $sourceColumn) {
                if (!isset($sourceColumn->persistence)) {
                    $sourceColumn->persistence = ColumnMetaData::PERSISTENCE__STORAGE_CREATED;
                }
            }
        }

        $dataset = new DatasetMetaData();
        $dataset->name = $datasetName;
        $dataset->initializeFrom($sourceDataset);

        $isDatasetAcceptable = $this->isMetaDataAcceptable($dataset, $filters);

        if ($isDatasetAcceptable) {
            $metamodel->registerDataset($dataset);
        }

        return $isDatasetAcceptable ? $dataset : NULL;
    }

    protected function mergeWithReferences(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, __AbstractFileMetaModelLoader_Source $source) {
        if (!isset($source->content->references)) {
            return;
        }

        $loaderName = $this->getName();

        foreach ($source->content->references as $sourceReferenceName => $sourceReference) {
            $reference = $this->mergeWithReference($metamodel, $filters, $namespace, $sourceReferenceName, $sourceReference);

            // adding system properties
            $reference->loaderName = $loaderName;
            $reference->loadedFromFile = $source->filename;
        }
    }

    protected function mergeWithReference(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, $sourceReferenceName, $sourceReference) {
        $referenceName = NameSpaceHelper::resolveNameSpace($namespace, $sourceReferenceName);

        $reference = $metamodel->findReference($referenceName);
        if (isset($reference)) {
            $metamodel->unregisterReference($referenceName);
        }
        else {
            $reference = new DatasetReference();
            $reference->name = $referenceName;
        }

        // reference[]/dataset/name
        foreach ($sourceReference as $pointIndex => $sourcePoint) {
            if (!isset($sourcePoint->datasetName)) {
                throw new IllegalStateException(t(
                    '%referenceName reference point definition (index: %pointIndex) does not contain a reference to dataset',
                    array('%referenceName' => $referenceName, '%pointIndex' => $pointIndex)));
            }

            $datasetName = NameSpaceHelper::resolveNameSpace($namespace, $sourcePoint->datasetName);
            // TODO eliminate the following check because we should allow reference definition in separate file which is processed before corresponding datasets are processed
            // at the same time we do not want to have references which point to 'missing' datasets
            // we can eliminate such references during final meta model validation
            if ($metamodel->findDataset($datasetName) == NULL) {
                continue;
            }

            $referencePoint = $reference->initiatePoint();
            if (isset($sourcePoint->columnNames)) {
                foreach ($sourcePoint->columnNames as $columnName) {
                    $referencePointColumn = $referencePoint->initiateColumn();
                    $referencePointColumn->datasetName = $datasetName;
                    $referencePointColumn->columnName = $columnName;
                    $referencePoint->registerColumnInstance($referencePointColumn);
                }
            }
            else {
                $referencePointColumn = $referencePoint->initiateColumn();
                $referencePointColumn->datasetName = $datasetName;
                $referencePoint->registerColumnInstance($referencePointColumn);
            }

            $reference->registerPointInstance($referencePoint);
        }

        $metamodel->registerReference($reference);

        return $reference;
    }

    protected function mergeWithCubes(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, __AbstractFileMetaModelLoader_Source $source) {
        if (!isset($source->content->cubes)) {
            return;
        }

        $loaderName = $this->getName();

        foreach ($source->content->cubes as $sourceCubeName => $sourceCube) {
            $cube = $this->mergeWithCube($metamodel, $filters, $namespace, $sourceCubeName, $sourceCube);

            // adding system properties
            $cube->loaderName = $loaderName;
            $cube->loadedFromFile = $source->filename;
        }
    }

    protected function mergeWithCube(AbstractMetaModel $metamodel, array $filters = NULL, $namespace, $sourceCubeName, $sourceCube) {
        $cubeName = NameSpaceHelper::resolveNameSpace($namespace, $sourceCubeName);

        // cube/sourceDataset/Name
        if (!isset($sourceCube->factsDatasetName)) {
            throw new IllegalStateException(t(
                '%cubeName cube definition does not contain a reference to facts dataset',
                array('%cubeName' => (isset($sourceCube->publicName) ? $sourceCube->publicName : $cubeName))));
        }
        $sourceCube->factsDatasetName = NameSpaceHelper::resolveNameSpace($namespace, $sourceCube->factsDatasetName);

        // fix dimensions
        if (isset($sourceCube->dimensions)) {
            foreach ($sourceCube->dimensions as $dimension) {
                // cube/dimension/dataset/name
                if (!isset($dimension->datasetName)) {
                    continue;
                }
                $dimension->datasetName = NameSpaceHelper::resolveNameSpace($namespace, $dimension->datasetName);
            }
        }

        $cube = new CubeMetaData();
        $cube->name = $cubeName;
        $cube->initializeFrom($sourceCube);

        $isCubeAcceptable = $this->isMetaDataAcceptable($cube, $filters);

        // TODO eliminate this check in the future. Use different approach
        if ($isCubeAcceptable) {
            $isCubeAcceptable = $metamodel->findDataset($cube->factsDatasetName) !== NULL;
        }

        if ($isCubeAcceptable) {
            $metamodel->registerCube($cube);
        }

        return $cube;
    }
}
