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


abstract class SQK_AbstractBoundaryOperatorHandler extends SQL_AbstractOperatorHandler {

    protected static $OPERATOR_VARIABLE_NAME__CALCULATED = 'boundary:value';

    abstract protected function isSortAscending();

    protected function adjustCalculatedValue($value) {
        return $value;
    }

    protected function shouldValueBeSkipped($value) {
        return $value->isSubsetBased()
            // skipping instances which are based on subset of data when their weight is greater than of this operator
            ? (isset($value->weight) && ($value->weight > $this->operatorHandler->weight))
            : FALSE;
    }

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $boundaryValue = $this->prepareBoundaryValue($callcontext, $request, $datasetName, $columnName, $columnDataType);

        $operator = OperatorFactory::getInstance()->initiateHandler(EqualOperatorHandler::OPERATOR__NAME, $boundaryValue);
        $sqlOperatorHandler = SQLOperatorFactory::getInstance()->getHandler($this->datasourceHandler, $operator);

        return $sqlOperatorHandler->format($callcontext, $request, $datasetName, $columnName, $columnDataType);
    }

    public function prepareBoundaryValue(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName) {
        $boundaryValue = NULL;
        if ($this->operatorHandler->wasValueCalculated(self::$OPERATOR_VARIABLE_NAME__CALCULATED)) {
            // the value has been calculated already
            $boundaryValue = $this->operatorHandler->getCalculatedValue(self::$OPERATOR_VARIABLE_NAME__CALCULATED);
        }
        else {
            if ($request instanceof AbstractCubeQueryRequest) {
                $boundaryValue = $this->selectBoundary4CubeRequest($callcontext, $request, $datasetName, $columnName);
            }
            elseif ($request instanceof AbstractDatasetQueryRequest) {
                $boundaryValue = $this->selectBoundary4DatasetRequest($callcontext, $request, $datasetName, $columnName);
            }
            else {
                throw new UnsupportedOperationException(t('%classname class is not supported', array('%classname' => get_class($request))));
            }

            $this->operatorHandler->setCalculatedValue(self::$OPERATOR_VARIABLE_NAME__CALCULATED, $boundaryValue);
        }

        return $boundaryValue;
    }

    protected function selectBoundary4DatasetRequest(DataControllerCallContext $callcontext, AbstractDatasetQueryRequest $request, $datasetName, $columnName) {
        // looking for an index where this instance is used. Rest of the queries will be ignored.
        // Data for ignored queries will be calculated during subsequent requests to different instances of this class
        $selectedIndex = NULL;
        if (isset($request->queries)) {
            foreach ($request->queries as $index => $query) {
                foreach ($query as $values) {
                    foreach ($values as $value) {
                        if ($this->operatorHandler === $value) {
                            $selectedIndex = $index;
                            break 3;
                        }
                    }
                }
            }
        }

        $expressionRequest = new DatasetQueryRequest($request->getDatasetName());
        // needs to be called before any additional methods are called
        $expressionRequest->addOptions($request->options);
        // returning only observing column
        $expressionRequest->addColumn($columnName);
        // excluding records with NULL value for the observing column
        $expressionRequest->addQueryValue(
            0,
            $columnName, OperatorFactory::getInstance()->initiateHandler(NotEqualOperatorHandler::OPERATOR__NAME, NULL));
        // adding support for queries except this operator handler
        if (isset($selectedIndex)) {
            foreach ($request->queries[$selectedIndex] as $name => $values) {
                foreach ($values as $value) {
                    if ($this->shouldValueBeSkipped($value)) {
                        continue;
                    }

                    if ($this->operatorHandler === $value) {
                        continue;
                    }

                    $expressionRequest->addQueryValue(0, $name, $value);
                }
            }
        }
        else {
            // we have this situation because we call this operator for a request where this operator is not used
        }
        // sorting data
        $expressionRequest->addOrderByColumn(
            ColumnBasedComparator_AbstractSortingConfiguration::assembleDirectionalColumnName($columnName, $this->isSortAscending()));
        // limiting response to one record
        $expressionRequest->setPagination(1, 0);

        return $this->processDatasetExpressionRequest($callcontext, $expressionRequest, $columnName);
    }

    protected function processDatasetExpressionRequest(DataControllerCallContext $callcontext, DatasetQueryRequest $expressionRequest, $columnName) {
        $result = $this->datasourceHandler->queryDataset($callcontext, $expressionRequest);

        return isset($result) ? $this->adjustCalculatedValue($result[0][$columnName]) : NULL;
    }

    protected function selectBoundary4CubeRequest(DataControllerCallContext $callcontext, AbstractCubeQueryRequest $request, $datasetName, $columnName) {
        $isSortAscending = $this->isSortAscending();

        $resultColumnName = NULL;

        // preparing new cube meta data
        $expressionRequest = new CubeQueryRequest($request->getCubeName());
        // needs to be called before any additional methods are called
        $expressionRequest->addOptions($request->options);

        // copying ONLY some query objects (excluding at least a reference to this operator)
        // -- dimension queries
        $dimensionQueries = $request->findDimensionQueries();
        if (isset($dimensionQueries)) {
            foreach ($dimensionQueries as $query) {
                foreach ($query->columns as $queryColumn) {
                    foreach ($queryColumn->values as $value) {
                        if ($this->shouldValueBeSkipped($value)) {
                            continue;
                        }

                        // updating request configuration for the value supported by this class
                        if ($this->operatorHandler === $value) {
                            $resultColumnName = ParameterNameHelper::assemble($query->name, $queryColumn->name);

                            // returning only observing column of the dimension
                            $expressionRequest->addDimensionColumn(0, $query->name, $queryColumn->name);
                            // ... and excluding NULL values from evaluation
                            $expressionRequest->addDimensionColumnQueryValue(
                                $query->name, $queryColumn->name,
                                OperatorFactory::getInstance()->initiateHandler(NotEqualOperatorHandler::OPERATOR__NAME, NULL));
                            // sorting data
                            $expressionRequest->addOrderByColumn(
                                ColumnBasedComparator_AbstractSortingConfiguration::assembleDirectionalColumnName($resultColumnName, $isSortAscending));
                        }
                        else {
                            $expressionRequest->addDimensionColumnQueryValue($query->name, $queryColumn->name, $value);
                        }
                    }
                }
            }
        }
        // -- facts dataset column queries
        $factsDatasetColumnQueries = $request->findFactsDatasetColumnQueries();
        if (isset($factsDatasetColumnQueries)) {
            foreach ($factsDatasetColumnQueries as $query) {
                $values = NULL;
                foreach ($query->values as $value) {
                    if ($this->shouldValueBeSkipped($value)) {
                        continue;
                    }
                    if ($this->operatorHandler === $value) {
                        $metamodel = data_controller_get_metamodel();
                        $cube = $metamodel->getCube($expressionRequest->getCubeName());

                        // finding dimension associated with this fact column
                        $selectedDimension = NULL;
                        if (isset($cube->dimensions)) {
                            foreach ($cube->dimensions as $dimension) {
                                if ($dimension->attributeColumnName == $query->name) {
                                    $selectedDimension = $dimension;

                                    break;
                                }
                            }
                        }
                        if (!isset($selectedDimension)) {
                            throw new IllegalArgumentException(t(
                                'Boundary-related operator cannot be applied to the facts dataset column: %columnName',
                                array('%columnName' => $query->name)));
                        }

                        $resultColumnName = ParameterNameHelper::assemble($selectedDimension->name);

                        // returning only observing column from facts dataset
                        $expressionRequest->addDimension(0, $selectedDimension->name);
                        // ... and excluding NULL values from evaluation
                        $expressionRequest->addFactsDatasetColumnQueryValue(
                            $query->name,
                            OperatorFactory::getInstance()->initiateHandler(NotEqualOperatorHandler::OPERATOR__NAME, NULL));
                        // sorting data
                        $expressionRequest->addOrderByColumn(
                            ColumnBasedComparator_AbstractSortingConfiguration::assembleDirectionalColumnName($resultColumnName, $isSortAscending));
                    }
                    else {
                        $values[] = $value;
                    }
                }
                if (isset($values)) {
                    $expressionRequest->addFactsDatasetColumnQueryValues($query->name, $values);
                }
            }
        }

        // -- measure queries
        $measureQueries = $request->findMeasureQueries();
        if (isset($measureQueries)) {
            foreach ($measureQueries as $query) {
                foreach ($query->values as $value) {
                    if ($this->shouldValueBeSkipped($value)) {
                        throw new IllegalArgumentException(t('Boundary-related operator cannot be applied to measures'));
                    }
                }
                $expressionRequest->queries[] = clone $query;
            }
        }

        // limiting response to one record
        $expressionRequest->setPagination(1, 0);

        return $this->processCubeExpressionRequest($callcontext, $expressionRequest, $resultColumnName);
    }

    protected function processCubeExpressionRequest(DataControllerCallContext $callcontext, CubeQueryRequest $expressionRequest, $resultColumnName) {
        $result = $this->datasourceHandler->queryCube($callcontext, $expressionRequest);

        $selectedValue = $this->processCubeExpressionRequestResult($resultColumnName, $result);
        if (isset($selectedValue)) {
            $selectedValue = $this->adjustCalculatedValue($selectedValue);
        }

        return $selectedValue;
    }

    protected function processCubeExpressionRequestResult($resultColumnName, array $result = NULL) {
        return isset($result) ? $result[0][$resultColumnName] : NULL;
    }
}
