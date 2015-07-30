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


abstract class AbstractElementUIMetaData extends AbstractObject {

    public $name = NULL;
    public $publicName = NULL;
    public $description = NULL;

    public $isSelectable = TRUE;
    public $isVisible = TRUE;

    // Note: do not store a reference to an object. If we do ... json_encode will not detect recursion
    public $parentName = NULL;

    abstract public function findElement($elementName);
}

class DatasetUIMetaData extends AbstractElementUIMetaData {

    // caching to improve performance for element lookup for huge configurations (example: 20k+ elements)
    protected static $sequence = 0;
    protected static $elementLookupCache = NULL;
    protected $identifier = 0;

    public $attributes = NULL;
    public $measures = NULL;

    /**
     * @var DatasetUIMetaData[]
     */
    public $linkedDatasets = NULL;

    public function __construct() {
        parent::__construct();

        self::$sequence++;
        $this->identifier = self::$sequence;
    }

    public function __destruct() {
        unset(self::$elementLookupCache[$this->identifier]);

        parent::__destruct();
    }

    public function __clone() {
        parent::__clone();

        $this->attributes = ArrayHelper::copy($this->attributes);
        $this->measures = ArrayHelper::copy($this->measures);
        $this->linkedDatasets = ArrayHelper::copy($this->linkedDatasets);
    }

    /**
     * @param $listPropertyName
     * @param $elementName
     * @return AbstractElementUIMetaData | null
     */
    protected function findElementInList($listPropertyName, $elementName) {
        if (isset(self::$elementLookupCache[$this->identifier][$listPropertyName][$elementName])) {
            return self::$elementLookupCache[$this->identifier][$listPropertyName][$elementName];
        }

        $matchedElement = NULL;
        
        if (isset($this->$listPropertyName)) {
            foreach ($this->$listPropertyName as $element) {
                if ($element->name === $elementName) {
                    $matchedElement = $element;
                    break;
                }

                $subElement = $element->findElement($elementName);
                if (isset($subElement)) {
                    $matchedElement = $subElement;
                }
            }
        }
        
        if (isset($matchedElement)) {
            self::$elementLookupCache[$this->identifier][$listPropertyName][$elementName] = $matchedElement;
        }

        return $matchedElement;
    }

    public function findAttribute($attributeName) {
        $attribute = $this->findElementInList('attributes', $attributeName);
        if (isset($attribute)) {
            return $attribute;
        }

        if (isset($this->linkedDatasets)) {
            foreach ($this->linkedDatasets as $dataset) {
                $attribute = $dataset->findAttribute($attributeName);
                if (isset($attribute)) {
                    return $attribute;
                }
            }
        }

        return NULL;
    }

    public function registerAttribute(AbstractAttributeUIMetaData $attribute) {
        $this->attributes[] = $attribute;
    }

    public function findMeasure($measureName) {
        $measure = $this->findElementInList('measures', $measureName);
        if (isset($measure)) {
            return $measure;
        }

        if (isset($this->linkedDatasets)) {
            foreach ($this->linkedDatasets as $dataset) {
                $measure = $dataset->findMeasure($measureName);
                if (isset($measure)) {
                    return $measure;
                }
            }
        }

        return NULL;
    }

    public function registerMeasure(AbstractRootElementUIMetaData $measure) {
        $this->measures[] = $measure;
    }

    public function findDataset($datasetName) {
        if ($this->name === $datasetName) {
            return $this;
        }

        if (isset($this->linkedDatasets)) {
            foreach ($this->linkedDatasets as $dataset) {
                $selectedDataset = $dataset->findDataset($datasetName);
                if (isset($selectedDataset)) {
                    return $selectedDataset;
                }
            }
        }

        return NULL;
    }

    public function findElement($elementName) {
        $element = $this->findDataset($elementName);

        if (!isset($element)) {
            $element = $this->findAttribute($elementName);
        }
        if (!isset($element)) {
            $element = $this->findMeasure($elementName);
        }

        return $element;
    }

    public function registerConnectedDataset(DatasetUIMetaData $datasetUIMetaData) {
        $datasetUIMetaData->parentName = $this->name;

        $this->linkedDatasets[] = $datasetUIMetaData;
    }

    /**
     * @param $listPropertyName
     * @param array|null $elementNames
     * @return void
     */
    protected function prepareElementNamesFromList($listPropertyName, array &$elementNames = NULL) {
        if (isset($this->$listPropertyName)) {
            foreach ($this->$listPropertyName as $element) {
                if ($element instanceof RootElementUIMetaData) {
                    continue;
                }

                $elementNames[] = $element->name;

                $element->prepareElementNames($elementNames);
            }
        }
    }

    public function prepareElementNames(array &$elementNames = NULL, $includeConnectedDatasets = TRUE) {
        if (isset($this->attributes)) {
            $this->prepareElementNamesFromList('attributes', $elementNames);
        }
        if (isset($this->measures)) {
            $this->prepareElementNamesFromList('measures', $elementNames);
        }

        if ($includeConnectedDatasets && isset($this->linkedDatasets)) {
            foreach ($this->linkedDatasets as $dataset) {
                $dataset->prepareElementNames($elementNames);
            }
        }
    }
}

abstract class AbstractRootElementUIMetaData extends AbstractElementUIMetaData {

    /**
     * @var AbstractRootElementUIMetaData[]
     */
    public $elements = array();

    public function __clone() {
        parent::__clone();

        $this->elements = ArrayHelper::copy($this->elements);
    }

    public function findElement($elementName) {
        if (isset($this->elements)) {
            foreach ($this->elements as $element) {
                if ($element->name === $elementName) {
                    return $element;
                }

                $subElement = $element->findElement($elementName);
                if (isset($subElement)) {
                    return $subElement;
                }
            }
        }

        return NULL;
    }

    public function registerElement(AbstractRootElementUIMetaData $element) {
        $element->parentName = $this->name;

        $this->elements[] = $element;
    }

    public function prepareElementNames(array &$elementNames = NULL) {
        if (isset($this->elements)) {
            foreach ($this->elements as $element) {
                $elementNames[] = $element->name;

                $element->prepareElementNames($elementNames);
            }
        }
    }
}

class RootElementUIMetaData extends AbstractRootElementUIMetaData {}

abstract class AbstractDataElementUIMetaData extends AbstractRootElementUIMetaData {

    public $type = NULL;

    public function __clone() {
        parent::__clone();

        if (isset($this->type)) {
            $this->type = clone $this->type;
        }
    }
}

// *****************************************************************************
// * Cube Attributes
// *****************************************************************************
abstract class AbstractAttributeUIMetaData extends AbstractDataElementUIMetaData {

    const NAME_SPACE = 'attr';

    public $columnIndex = NULL;
    public $datasetName = NULL;
}

class AttributeUIMetaData extends AbstractAttributeUIMetaData {}

class AttributeColumnUIMetaData extends AbstractAttributeUIMetaData {}


// *****************************************************************************
// * Measures
// *****************************************************************************
abstract class AbstractMeasureUIMetaData extends AbstractDataElementUIMetaData {

    const NAME_SPACE = 'measure';
}

class CubeMeasureUIMetaData extends AbstractMeasureUIMetaData {}

class AttributeMeasureUIMetaData extends AbstractMeasureUIMetaData {}

class FactMeasureUIMetaData extends AbstractMeasureUIMetaData {}

// *****************************************************************************
// * Formulas
// *****************************************************************************
class FormulaUIMetaData extends AbstractDataElementUIMetaData {

    const NAME_SPACE = 'formula';
}
