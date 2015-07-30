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


class CubeMetaData extends AbstractMetaData {

    public $factsDatasetName = NULL;
    /**
     * @var DatasetMetaData
     */
    public $factsDataset = NULL; // populated automatically when the cube is used for first time
    /**
     * @var DimensionMetaData[]
     */
    public $dimensions = NULL;
    /**
     * @var MeasureMetaData[]
     */
    public $measures = NULL;

    public function __clone() {
        parent::__clone();

        $this->dimensions = ArrayHelper::copy($this->dimensions);
        $this->measures = ArrayHelper::copy($this->measures);
    }

    protected function prepareUnserializablePropertyNames(&$names) {
        $names['factsDataset'] = TRUE;
    }

    public function finalize() {
        parent::finalize();

        if (isset($this->dimensions)) {
            foreach ($this->dimensions as $dimension) {
                $dimension->finalize();
            }
        }

        if (isset($this->measures)) {
            foreach ($this->measures as $measure) {
                $measure->finalize();
            }
        }
    }

    public function isComplete() {
        $complete = parent::isComplete() && isset($this->factsDataset) && $this->factsDataset->isComplete();

        if (isset($this->dimensions)) {
            reset($this->dimensions);
            while ($complete && (list($index, $dimension) = each($this->dimensions))) {
                $complete = $dimension->isComplete();
            }
        }

        if ($complete) {
            foreach ($this->measures as $measure) {
                if ($measure->isComplete() !== TRUE) {
                    return FALSE;
                }
            }
        }

        return $complete;
    }

    public function initializeFrom($sourceCube) {
        parent::initializeFrom($sourceCube);

        // preparing dataset property
        $sourceFactsDataset = ObjectHelper::getPropertyValue($sourceCube, 'factsDataset');
        if (isset($sourceFactsDataset)) {
            $this->initializeFactsDatasetFrom($sourceFactsDataset);
        }

        // preparing list of dimensions
        $sourceDimensions = ObjectHelper::getPropertyValue($sourceCube, 'dimensions');
        if (isset($sourceDimensions)) {
            $this->initializeDimensionsFrom($sourceDimensions);
        }

        // preparing list of measures
        $sourceMeasures = ObjectHelper::getPropertyValue($sourceCube, 'measures');
        if (isset($sourceMeasures)) {
            $this->initializeMeasuresFrom($sourceMeasures);
        }
    }

    public function initializeFactsDatasetFrom($sourceFactsDataset) {
        if (isset($sourceFactsDataset)) {
            if (!isset($this->factsDataset)) {
                $this->initiateFactsDataset();
            }
            $this->factsDataset->initializeFrom($sourceFactsDataset);
        }
    }

    public function initiateFactsDataset() {
        $this->factsDataset = new DatasetMetaData();

        return $this->factsDataset;
    }

    public function initializeDimensionsFrom($sourceDimensions) {
        if (isset($sourceDimensions)) {
            foreach ($sourceDimensions as $sourceDimension) {
                $sourceDimensionName = ObjectHelper::getPropertyValue($sourceDimension, 'name');

                $dimension = $this->findDimension($sourceDimensionName);
                if (!isset($dimension)) {
                    $dimension = $this->registerDimension($sourceDimensionName);
                }
                $dimension->initializeFrom($sourceDimension);
            }
        }
    }

    public function initiateDimension() {
        return new DimensionMetaData();
    }

    public function registerDimension($dimensionName) {
        $dimension = $this->initiateDimension();
        $dimension->name = $dimensionName;

        $this->registerDimensionInstance($dimension);

        return $dimension;
    }

    public function registerDimensionInstance(DimensionMetaData $unregisteredDimension) {
        $existingDimension = $this->findDimension($unregisteredDimension->name);
        if (isset($existingDimension)) {
            $this->errorDimensionFound($existingDimension);
        }

        $this->dimensions[] = $unregisteredDimension;
    }

    public function unregisterDimension($dimensionName) {
        if (isset($this->dimensions)) {
            foreach ($this->dimensions as $index => $dimension) {
                if ($dimension->name === $dimensionName) {
                    unset($this->dimensions[$index]);

                    return $dimension;
                }
            }
        }

        $this->errorDimensionNotFound($dimensionName);
    }

    public function findDimension($dimensionName) {
        if (isset($this->dimensions)) {
            foreach ($this->dimensions as $dimension) {
                if ($dimension->name === $dimensionName) {
                    return $dimension;
                }
            }
        }

        return NULL;
    }

    public function getDimension($dimensionName) {
        $dimension = $this->findDimension($dimensionName);
        if (!isset($dimension)) {
            $this->errorDimensionNotFound($dimensionName);
        }

        return $dimension;
    }

    public function getDimensions($usedOnly = TRUE) {
        $dimensions = array();

        if (isset($this->dimensions)) {
            foreach ($this->dimensions as $dimension) {
                if (!$usedOnly || $dimension->isUsed()) {
                    $dimensions[] = $dimension;
                }
            }
        }

        return $dimensions;
    }

    public function findDimensionByAttributeColumnName($attributeColumnName) {
        $selectedDimension = NULL;

        if (isset($this->dimensions)) {
            foreach ($this->dimensions as $dimension) {
                if ($dimension->attributeColumnName == $attributeColumnName) {
                    if (isset($selectedDimension)) {
                        $this->errorSeveralDimensionsFoundByAttributeColumnName($attributeColumnName);
                    }

                    $selectedDimension = $dimension;
                }
            }
        }

        return $selectedDimension;
    }

    public function getDimensionByAttributeColumnName($attributeColumnName) {
        $dimension = $this->findDimensionByAttributeColumnName($attributeColumnName);
        if (!isset($dimension)) {
            $this->errorDimensionNotFoundByAttributeColumnName($attributeColumnName);
        }

        return $dimension;
    }

    public function getDimensionCount() {
        return isset($this->dimensions) ? count($this->dimensions) : 0;
    }

    protected function errorDimensionFound($dimension) {
        throw new IllegalArgumentException(t(
        	'%dimensionName dimension has been already registered in %cubeName cube',
            array('%dimensionName' => $dimension->name, '%cubeName' => $this->publicName)));
    }

    protected function errorDimensionNotFound($dimensionName) {
        // logging list of available dimension names
        $availableDimensionNames = NULL;
        if (isset($this->dimensions)) {
            foreach ($this->dimensions as $dimension) {
                if ($dimension->isUsed()) {
                    $availableDimensionNames[] = $dimension->name;
                }
            }
        }
        LogHelper::log_debug(t('Available dimensions:'));
        LogHelper::log_debug($availableDimensionNames);

        throw new IllegalArgumentException(t(
        	'%dimensionName dimension is not registered in %cubeName cube',
            array('%dimensionName' => $dimensionName, '%cubeName' => $this->publicName)));
    }

    protected function errorSeveralDimensionsFoundByAttributeColumnName($attributeColumnName) {
        throw new IllegalArgumentException(t(
        	'Found several dimensions in %cubeName cube by the attribute column name: %attributeColumnName',
            array('%attributeColumnName' => $attributeColumnName, '%cubeName' => $this->publicName)));
    }

    protected function errorDimensionNotFoundByAttributeColumnName($attributeColumnName) {
        throw new IllegalArgumentException(t(
        	'Cannot find dimension in %cubeName cube by the attribute column name: %attributeColumnName',
            array('%attributeColumnName' => $attributeColumnName, '%cubeName' => $this->publicName)));
    }

    public function initializeMeasuresFrom($sourceMeasures) {
        if (isset($sourceMeasures)) {
            foreach ($sourceMeasures as $sourceMeasureName => $sourceMeasure) {
                $measure = $this->findMeasure($sourceMeasureName);
                if (!isset($measure)) {
                    $measure = $this->registerMeasure($sourceMeasureName);
                }

                $measure->initializeFrom($sourceMeasure);
            }
        }
    }

    public function initiateMeasure() {
        return new MeasureMetaData();
    }

    public function registerMeasure($measureName) {
        $measure = $this->initiateMeasure();
        $measure->name = $measureName;

        $this->registerMeasureInstance($measure);

        return $measure;
    }

    public function registerMeasureInstance(MeasureMetaData $unregisteredMeasure) {
        $existingMeasure = $this->findMeasure($unregisteredMeasure->name);
        if (isset($existingMeasure)) {
            $this->errorMeasureFound($existingMeasure);
        }

        $this->measures[$unregisteredMeasure->name] = $unregisteredMeasure;
    }

    public function unregisterMeasure($measureName) {
        if (isset($this->measures[$measureName])) {
            $measure = $this->measures[$measureName];
            unset($this->measures[$measureName]);

            return $measure;
        }

        $this->errorMeasureNotFound($measureName);
    }

    /**
     * @param $measureName
     * @return MeasureMetaData
     */
    public function findMeasure($measureName) {
        return isset($this->measures[$measureName])
            ? $this->measures[$measureName]
            : NULL;
    }

    /**
     * @param $measureName
     * @return MeasureMetaData
     */
    public function getMeasure($measureName) {
        $measure = $this->findMeasure($measureName);
        if (!isset($measure)) {
            $this->errorMeasureNotFound($measureName);
        }

        return $measure;
    }

    public function getMeasureCount() {
        return isset($this->measures) ? count($this->measures) : 0;
    }

    protected function errorMeasureFound($measure) {
        throw new IllegalArgumentException(t(
        	'%measureName measure has been already registered in %cubeName cube',
            array('%measureName' => $measure->name, '%cubeName' => $this->publicName)));
    }

    protected function errorMeasureNotFound($measureName) {
        throw new IllegalArgumentException(t(
        	'%measureName measure is not registered in %cubeName cube',
            array('%measureName' => $measureName, '%cubeName' => $this->publicName)));
    }
}
