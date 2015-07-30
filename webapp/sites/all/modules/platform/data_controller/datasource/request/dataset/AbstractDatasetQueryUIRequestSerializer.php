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


abstract class AbstractDatasetQueryUIRequestSerializer extends AbstractQueryUIRequestSerializer {

    protected function serialize(AbstractQueryRequest $request) {
        $parameters = parent::serialize($request);

        // serializing query parameters
        if (isset($request->queries)) {
            foreach ($request->queries as $index => $query) {
                $parameterName = DataQueryControllerUIParameterNames::PARAMETERS;
                if ($index != 0) {
                    $parameterName .= $index;
                }

                if (isset($query)) {
                    $queryParameters = NULL;
                    foreach ($query as $name => $value) {
                        ArrayHelper::merge($queryParameters, DataQueryControllerUIRequestPreparer::prepareParameter($name, $value));
                    }

                    ArrayHelper::merge($parameters, $this->serializeValue($parameterName, $queryParameters));
                }
            }
        }

        return $parameters;
    }
}
