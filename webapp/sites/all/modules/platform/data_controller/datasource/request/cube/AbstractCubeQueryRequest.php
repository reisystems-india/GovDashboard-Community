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


abstract class AbstractCubeQueryRequest extends AbstractQueryRequest {

    public $referenced = FALSE;

    public $dimensions = NULL;
    public $measures = NULL;

    /**
     * @var AbstractCubeQueryRequest[]
     */
    public $referencedRequests = NULL;

    public function __clone() {
        parent::__clone();
        $this->referencedRequests = ArrayHelper::copy($this->referencedRequests);
    }

    public function getCubeName() {
        return $this->sourceName;
    }

    public function setCubeName($newCubeName) {
        $this->sourceName = $newCubeName;
    }

    /**
     * @param $dimensionName
     * @return __AbstractCubeQueryRequest_Dimension
     */
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

    public function importDimensionFrom(__AbstractCubeQueryRequest_Dimension $sourceDimension) {
        if ($this->findDimension($sourceDimension->name) != NULL) {
            throw new IllegalArgumentException(t(
                '%dimensionName dimension has been registered already',
                array('%dimensionName' => $sourceDimension->name)));
        }

        $this->dimensions[] = $sourceDimension;
    }

    public function addDimension($requestColumnIndex, $dimensionName) {
        $dimension = $this->findDimension($dimensionName);

        if (!isset($dimension)) {
            $isFormula = $this->findFormula($dimensionName) != NULL;
            if (!$isFormula) {
                StringDataTypeHandler::checkValueAsWord($dimensionName);
            }

            $dimension = new __AbstractCubeQueryRequest_Dimension($dimensionName);

            $this->dimensions[] = $dimension;
        }

        if (isset($requestColumnIndex)) {
            $dimension->requestColumnIndex = $requestColumnIndex;
        }

        return $dimension;
    }

    public function addDimensionColumn($requestColumnIndex, $dimensionName, $columnName) {
        ReferencePathHelper::checkReference($columnName);

        $this->addDimension(NULL, $dimensionName)->registerColumnName($requestColumnIndex, $columnName);
    }

    /**
     * @param $measureName
     * @return __AbstractCubeQueryRequest_Measure
     */
    public function findMeasure($measureName) {
        if (isset($this->measures)) {
            foreach ($this->measures as $measure) {
                if ($measure->name === $measureName) {
                    return $measure;
                }
            }
        }

        return NULL;
    }

    public function importMeasureFrom(__AbstractCubeQueryRequest_Measure $sourceMeasure) {
        if ($this->findMeasure($sourceMeasure->name) != NULL) {
            throw new IllegalArgumentException(t(
                '%measureName measure has been registered already',
                array('%measureName' => $sourceMeasure->name)));
        }

        $this->measures[] = $sourceMeasure;
    }

    public function importMeasuresFrom(AbstractCubeQueryRequest $sourceQueryRequest) {
        if (isset($sourceQueryRequest->measures)) {
            foreach ($sourceQueryRequest->measures as $measure) {
                $this->importMeasureFrom($measure);
            }
        }
    }

    public function addMeasure($requestColumnIndex, $measureName) {
        if ($this->findMeasure($measureName) != NULL) {
            throw new IllegalArgumentException(t(
                '%measureName measure has been registered already',
                array('%measureName' => $measureName)));
        }

        $isFormula = $this->findFormula($measureName) != NULL;
        if (!$isFormula) {
            StringDataTypeHandler::checkValueAsWord($measureName);
        }

        $measure = new __AbstractCubeQueryRequest_Measure($measureName);
        $measure->requestColumnIndex = $requestColumnIndex;

        $this->measures[] = $measure;
    }

    public function findDimensionQuery($dimensionName) {
        $dimensionQueries = $this->findDimensionQueries();
        if (isset($dimensionQueries)) {
            foreach ($dimensionQueries as $query) {
                if ($query->name === $dimensionName) {
                    return $query;
                }
            }
        }

        return NULL;
    }

    /**
     * @return __AbstractCubeQueryRequest_DimensionQuery[]|null
     */
    public function findDimensionQueries() {
        $queries = NULL;

        if (isset($this->queries)) {
            foreach ($this->queries as $query) {
                if ($query instanceof __AbstractCubeQueryRequest_DimensionQuery) {
                    $queries[] = $query;
                }
            }
        }

        return $queries;
    }

    protected function addDimensionQuery($dimensionName) {
        $query = $this->findDimensionQuery($dimensionName);

        if (!isset($query)) {
            StringDataTypeHandler::checkValueAsWord($dimensionName);

            $query = new __AbstractCubeQueryRequest_DimensionQuery($dimensionName);

            $this->queries[] = $query;
        }

        return $query;
    }

    public function importDimensionQueryFrom(__AbstractCubeQueryRequest_DimensionQuery $sourceDimensionQuery) {
        if ($this->findDimensionQuery($sourceDimensionQuery->name) != NULL) {
            throw new IllegalArgumentException(t(
                'Query for %dimensionName dimension has been registered already',
                array('%dimensionName' => $sourceDimensionQuery->name)));
        }

        $this->queries[] = $sourceDimensionQuery;
    }

    public function addDimensionColumnQueryValue($dimensionName, $columnName, $value) {
        $this->addDimensionQuery($dimensionName)->addColumnValue($columnName, $value);
    }

    public function addDimensionColumnQueryValues($dimensionName, $columnName, $values) {
        $this->addDimensionQuery($dimensionName)->addColumnValues($columnName, $values);
    }

    public function findFactsDatasetColumnQuery($columnName) {
        $factsDatasetColumnQueries = $this->findFactsDatasetColumnQueries();
        if (isset($factsDatasetColumnQueries)) {
            foreach ($factsDatasetColumnQueries as $query) {
                if ($query->name === $columnName) {
                    return $query;
                }
            }
        }

        return NULL;
    }

    public function findFactsDatasetColumnQueries() {
        $queries = NULL;

        if (isset($this->queries)) {
            foreach ($this->queries as $query) {
                if ($query instanceof __AbstractCubeQueryRequest_FactsDatasetColumnQuery) {
                    $queries[] = $query;
                }
            }
        }

        return $queries;
    }

    protected function importFactsDatasetColumnQueryFrom(__AbstractCubeQueryRequest_FactsDatasetColumnQuery $sourceFactsDatasetColumnQuery) {
        if ($this->findFactsDatasetColumnQuery($sourceFactsDatasetColumnQuery->name) != NULL) {
            throw new IllegalArgumentException(t(
                'Query for %columnName column from facts dataset has been registered already',
                array('%columnName' => $sourceFactsDatasetColumnQuery->name)));
        }

        $this->queries[] = $sourceFactsDatasetColumnQuery;

    }

    public function importFactsDatasetColumnQueriesFrom(AbstractCubeQueryRequest $sourceQueryRequest) {
        $sourceFactsDatasetColumnQueries = $sourceQueryRequest->findFactsDatasetColumnQueries();
        if (isset($sourceFactsDatasetColumnQueries)) {
            foreach ($sourceFactsDatasetColumnQueries as $sourceFactsDatasetColumnQuery) {
                $this->importFactsDatasetColumnQueryFrom($sourceFactsDatasetColumnQuery);
            }
        }
    }

    protected function addFactsDatasetColumnQuery($columnName) {
        $query = $this->findFactsDatasetColumnQuery($columnName);
        if (!isset($query)) {
            $isFormula = $this->findFormula($columnName) != NULL;
            ReferencePathHelper::checkReference($columnName, TRUE, !$isFormula);

            $query = new __AbstractCubeQueryRequest_FactsDatasetColumnQuery($columnName);

            $this->queries[] = $query;
        }

        return $query;
    }

    public function addFactsDatasetColumnQueryValue($columnName, $value) {
        $this->addFactsDatasetColumnQuery($columnName)->addValue($value);
    }

    public function addFactsDatasetColumnQueryValues($columnName, $values) {
        $this->addFactsDatasetColumnQuery($columnName)->addValues($values);
    }

    /**
     * @param $measureName
     * @return __AbstractCubeQueryRequest_MeasureQuery|null
     */
    public function findMeasureQuery($measureName) {
        $measureQueries = $this->findMeasureQueries();
        if (isset($measureQueries)) {
            foreach ($measureQueries as $query) {
                if ($query->name === $measureName) {
                    return $query;
                }
            }
        }

        return NULL;
    }

    /**
     * @return __AbstractCubeQueryRequest_MeasureQuery[]|null
     */
    public function findMeasureQueries() {
        $queries = NULL;

        if (isset($this->queries)) {
            foreach ($this->queries as $query) {
                if ($query instanceof __AbstractCubeQueryRequest_MeasureQuery) {
                    $queries[] = $query;
                }
            }
        }

        return $queries;
    }

    protected function importMeasureQueryFrom(__AbstractCubeQueryRequest_MeasureQuery $sourceMeasureQuery) {
        if ($this->findMeasureQuery($sourceMeasureQuery->name) != NULL) {
            throw new IllegalArgumentException(t(
                'Query for %measureName measure has been registered already',
                array('%measureName' => $sourceMeasureQuery->name)));
        }

        $this->queries[] = $sourceMeasureQuery;

    }

    public function importMeasureQueriesFrom(AbstractCubeQueryRequest $sourceQueryRequest) {
        $sourceMeasureQueries = $sourceQueryRequest->findMeasureQueries();
        if (isset($sourceMeasureQueries)) {
            foreach ($sourceMeasureQueries as $sourceMeasureQuery) {
                $this->importMeasureQueryFrom($sourceMeasureQuery);
            }
        }
    }

    protected function addMeasureQuery($measureName) {
        $query = $this->findMeasureQuery($measureName);
        if (!isset($query)) {
            $isFormula = $this->findFormula($measureName) != NULL;
            if (!$isFormula) {
                StringDataTypeHandler::checkValueAsWord($measureName);
            }

            $query = new __AbstractCubeQueryRequest_MeasureQuery($measureName);

            $this->queries[] = $query;
        }

        return $query;
    }

    public function addMeasureQueryValue($measureName, $value) {
        $this->addMeasureQuery($measureName)->addValue($value);
    }

    public function addMeasureQueryValues($measureName, $values) {
        $this->addMeasureQuery($measureName)->addValues($values);
    }

    public function initiateSortingConfiguration($columnName, $isSortAscending = TRUE) {
        list($rootName, $leafName) = ParameterNameHelper::split($columnName);

        $isFormula = $this->findFormula($rootName) != NULL;
        if (!$isFormula) {
            StringDataTypeHandler::checkValueAsWord($rootName);
            ReferencePathHelper::checkReference($leafName);
        }

        return new __AbstractCubeQueryRequest_SortingConfiguration($rootName, $leafName, $isSortAscending);
    }

    public function registerReferencedRequest($cubeName) {
        if (isset($this->referencedRequests[$cubeName])) {
            return $this->referencedRequests[$cubeName];
        }

        $classname = get_class($this);

        $request = new $classname($cubeName);
        $request->referenced = TRUE;

        $this->referencedRequests[$cubeName] = $request;

        return $request;
    }
}

abstract class __AbstractCubeQueryRequest_AbstractElement extends AbstractObject {

    public $name = NULL;
    public $requestColumnIndex = NULL;

    public function __construct($name) {
        parent::__construct();
        $this->name = $name;
    }
}

abstract class __AbstractCubeQueryRequest_AbstractDimension extends __AbstractCubeQueryRequest_AbstractElement {

    public $columns = NULL;

    public function getColumnNames() {
        $columnNames = NULL;

        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                if (isset($column->name)) {
                    $columnNames[] = $column->name;
                }
            }
        }

        return $columnNames;
    }

    protected function findColumn($columnName) {
        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column->name === $columnName) {
                    return $column;
                }
            }
        }

        return NULL;
    }
}

class __AbstractCubeQueryRequest_Column extends __AbstractCubeQueryRequest_AbstractElement {}

class __AbstractCubeQueryRequest_Dimension extends __AbstractCubeQueryRequest_AbstractDimension {

    public function registerColumnName($requestColumnIndex, $columnName) {
        if ($this->findColumn($columnName) != NULL) {
            throw new IllegalArgumentException(t('The column has been registered already: %columnName', array('%columnName' => $columnName)));
        }

        $column = new __AbstractCubeQueryRequest_Column($columnName);
        $column->requestColumnIndex = $requestColumnIndex;

        $this->columns[] = $column;
    }
}

class __AbstractCubeQueryRequest_ColumnValue extends __AbstractCubeQueryRequest_Column {

    public $values = NULL;

    public function addValue($value) {
        $this->values[] = $value;
    }

    public function addValues($values) {
        foreach ($values as $value) {
            $this->addValue($value);
        }
    }
}

class __AbstractCubeQueryRequest_DimensionQuery extends __AbstractCubeQueryRequest_AbstractDimension {

    public function addColumnValue($columnName, $value) {
        $column = $this->findColumn($columnName);
        if (!isset($column)) {
            $column = new __AbstractCubeQueryRequest_ColumnValue($columnName);
            $this->columns[] = $column;
        }
        $column->addValue($value);
    }

    public function addColumnValues($columnName, $values) {
        foreach ($values as $value) {
            $this->addColumnValue($columnName, $value);
        }
    }
}

class __AbstractCubeQueryRequest_FactsDatasetColumnQuery extends __AbstractCubeQueryRequest_ColumnValue {}

class __AbstractCubeQueryRequest_Measure extends __AbstractCubeQueryRequest_Column {}

class __AbstractCubeQueryRequest_MeasureQuery extends __AbstractCubeQueryRequest_ColumnValue {}

class __AbstractCubeQueryRequest_SortingConfiguration extends ColumnBasedComparator_AbstractSortingConfiguration {

    public $rootName = NULL;
    public $leafName = NULL;

    public function __construct($rootName, $leafName, $isSortAscending) {
        parent::__construct($isSortAscending);
        $this->rootName = $rootName;
        $this->leafName = $leafName;
    }

    public function getColumnName() {
        return ParameterNameHelper::assemble($this->rootName, $this->leafName);
    }
}
