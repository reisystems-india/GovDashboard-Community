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


class CubeQueryUIRequestSerializer extends AbstractQueryUIRequestSerializer {

    public function serialize(AbstractQueryRequest $request) {
        $parameters = parent::serialize($request);

        $columns = NULL;
        // preparing dimension-related column
        if (isset($request->dimensions)) {
            foreach ($request->dimensions as $dimension) {
                if (isset($dimension->requestColumnIndex)) {
                    $columns[] = ParameterNameHelper::assemble($dimension->name);
                }
                if (isset($dimension->columns)) {
                    foreach ($dimension->columns as $column) {
                        $columns[] = ParameterNameHelper::assemble($dimension->name, $column->name);
                    }
                }
            }
        }
        // preparing measure-related columns
        if (isset($request->measures)) {
            foreach ($request->measures as $measure) {
                $columns[] = $measure->name;
            }
        }
        // serializing columns
        if (isset($columns)) {
            ArrayHelper::merge(
                $parameters,
                $this->serializeValue(
                    DataQueryControllerUIParameterNames::COLUMNS,
                    DataQueryControllerUIRequestPreparer::prepareColumns($columns)));
        }

        // serializing query
        if (isset($request->queries)) {
            $queryParameters = NULL;
            foreach ($request->queries as $query) {
                if ($query instanceof __AbstractCubeQueryRequest_DimensionQuery) {
                    if (isset($query->columns)) {
                        foreach ($query->columns as $column) {
                            ArrayHelper::merge(
                                $queryParameters,
                                DataQueryControllerUIRequestPreparer::prepareParameter(
                                    ParameterNameHelper::assemble($query->name, $column->name),
                                    $column->values));
                        }
                    }
                }
                elseif ($query instanceof __AbstractCubeQueryRequest_FactsDatasetColumnQuery) {
                    ArrayHelper::merge($queryParameters, DataQueryControllerUIRequestPreparer::prepareParameter($query->name, $query->values));
                }
                elseif ($query instanceof __AbstractCubeQueryRequest_MeasureQuery) {
                    ArrayHelper::merge($queryParameters, DataQueryControllerUIRequestPreparer::prepareParameter($query->name, $query->values));
                }
            }
            ArrayHelper::merge($parameters, $this->serializeValue(DataQueryControllerUIParameterNames::PARAMETERS, $queryParameters));
        }

        return $parameters;
    }
}
