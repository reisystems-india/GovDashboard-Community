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

class DataSourceCubeQueryRequestPreparer extends AbstractDataSourceQueryRequestPreparer {

    public function prepareQueryRequest(DataQueryControllerCubeRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $cube = $metamodel->getCubeByDatasetName($request->datasetName);

        $datasourceRequest = new CubeQueryRequest($cube->name);

        // needs to be called before any additional methods are called
        $datasourceRequest->addOptions($request->options);

        $this->prepareRequestColumns($datasourceRequest, $cube, $request->columns);
        $this->prepareRequestQueries($datasourceRequest, $cube, $request->parameters);
        $datasourceRequest->addOrderByColumns($request->orderBy);
        $datasourceRequest->setPagination($request->limit, $request->startWith);

        return $datasourceRequest;
    }

    public function prepareCountRequest(DataQueryControllerCubeRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $cube = $metamodel->getCubeByDatasetName($request->datasetName);

        $datasourceRequest = new CubeCountRequest($cube->name);

        // needs to be called before any additional methods are called
        $datasourceRequest->addOptions($request->options);

        $this->prepareRequestColumns($datasourceRequest, $cube, $request->columns);
        $this->prepareRequestQueries($datasourceRequest, $cube, $request->parameters);

        return $datasourceRequest;
    }

    protected function detectParameterKind(AbstractCubeQueryRequest $request, CubeMetaData $cube, $parameterName) {
        $metamodel = data_controller_get_metamodel();

        list($rootName, $leafName) = ParameterNameHelper::split($parameterName);

        list($referencedDatasetName, $referencedRootName) = ReferencePathHelper::splitReference($rootName);
        // checking that referenced cube exists
        $referencedCube = isset($referencedDatasetName)
            ? $metamodel->getCubeByDatasetName($referencedDatasetName)
            : NULL;

        $selectedCube = isset($referencedCube) ? $referencedCube : $cube;
        $selectedRequest = isset($referencedCube) ? $request->registerReferencedRequest($referencedCube->name) : $request;

        // detecting type of the parameter: dimension or measure
        $isDimension = $isMeasure = FALSE;
        if (isset($leafName)) {
            // if dimension column exists - dimension exists too :)
            $isDimension = TRUE;
        }
        else {
            // trying to find a measure
            $measure = $selectedCube->findMeasure($referencedRootName);
            if (isset($measure)) {
                $isMeasure = TRUE;
            }
            else {
                $formula = $request->findFormula($referencedRootName);
                if (isset($formula)) {
                    if (isset($formula->isMeasure) && $formula->isMeasure) {
                        $isMeasure = TRUE;
                    }
                    else {
                        $isDimension = TRUE;
                    }
                }
            }
            // trying to find a dimension
            $dimension = $selectedCube->findDimension($referencedRootName);
            if (isset($dimension)) {
                $isDimension = TRUE;
            }
        }
        if ($isDimension && $isMeasure) {
            throw new IllegalArgumentException(t(
                'The parameter refers to both a dimension and a measure: %parameterName',
                array('%parameterName' => $parameterName)));
        }

        if ($isDimension) {
            if (isset($referencedCube)) {
                throw new IllegalArgumentException(t('Referenced dimensions are not supported yet'));
            }
        }

        return array($selectedRequest, $isDimension, $isMeasure, $referencedRootName, $leafName);
    }

    protected function prepareRequestColumns(AbstractCubeQueryRequest $request, CubeMetaData $cube, array $parameterNames) {
        foreach ($parameterNames as $requestColumnIndex => $parameterName) {
            list($selectedRequest, $isDimension, $isMeasure, $referencedRootName, $leafName) = $this->detectParameterKind($request, $cube, $parameterName);

            if (!$isDimension && !$isMeasure) {
                throw new IllegalArgumentException(t(
                    'The parameter is neither a dimension nor a measure: %parameterName',
                    array('%parameterName' => $parameterName)));
            }

            if ($isDimension) {
                $dimensionName = $referencedRootName;
                $dimensionColumnName = $leafName;

                // registering the dimension
                if (isset($dimensionColumnName)) {
                    $selectedRequest->addDimensionColumn($requestColumnIndex, $dimensionName, $dimensionColumnName);
                }
                else {
                    $selectedRequest->addDimension($requestColumnIndex, $dimensionName);
                }
            }

            if ($isMeasure) {
                $measureName = $referencedRootName;

                // adding the measure
                $selectedRequest->addMeasure($requestColumnIndex, $measureName);
            }
        }
    }

    protected function prepareRequestQueries(AbstractCubeQueryRequest $request, CubeMetaData $cube, array $parameterValues = NULL) {
        if (!isset($parameterValues)) {
            return;
        }

        foreach ($parameterValues as $parameterName => $values) {
            list($selectedRequest, $isDimension, $isMeasure, $referencedRootName, $leafName) = $this->detectParameterKind($request, $cube, $parameterName);

            $isParameterProcessed = FALSE;
            if ($isDimension) {
                $dimensionName = $referencedRootName;
                $dimensionColumnName = $leafName;

                if (isset($dimensionColumnName)) {
                    $selectedRequest->addDimensionColumnQueryValues($dimensionName, $dimensionColumnName, $values);
                }
                else {
                    $dimension = $cube->findDimension($dimensionName);
                    $columnName = isset($dimension) ? $dimension->attributeColumnName : $dimensionName;
                    $selectedRequest->addFactsDatasetColumnQueryValues($columnName, $values);
                }

                $isParameterProcessed = TRUE;
            }

            if ($isMeasure) {
                $measureName = $referencedRootName;

                $selectedRequest->addMeasureQueryValues($measureName, $values);

                $isParameterProcessed = TRUE;
            }

            if (!$isParameterProcessed) {
                $columnName = $referencedRootName;

                $selectedRequest->addFactsDatasetColumnQueryValues($columnName, $values);
            }
        }
    }
}
