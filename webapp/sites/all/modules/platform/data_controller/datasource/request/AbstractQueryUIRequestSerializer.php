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


abstract class AbstractQueryUIRequestSerializer extends DataQueryControllerUIRequestSerializer {

    protected function serialize(AbstractQueryRequest $request) {
        $parameters = NULL;

        // serializing columns names to sort result
        if (isset($request->sortingConfigurations)) {
            $sortColumns = NULL;
            foreach ($request->sortingConfigurations as $sortingConfiguration) {
                $sortColumns[] = ColumnBasedComparator_AbstractSortingConfiguration::assembleDirectionalColumnName(
                    $sortingConfiguration->getColumnName(), $sortingConfiguration->isSortAscending);
            }
            ArrayHelper::merge(
                $parameters,
                $this->serializeValue(
                    DataQueryControllerUIParameterNames::SORT,
                    DataQueryControllerUIRequestPreparer::prepareSortColumns($sortColumns)));
        }
        // serializing record offset
        if (isset($request->startWith) && ($request->startWith > 0)) {
            $parameters[DataQueryControllerUIParameterNames::OFFSET] = $request->startWith;
        }
        // serializing record limit
        if (isset($request->limit)) {
            $parameters[DataQueryControllerUIParameterNames::LIMIT] = $request->limit;
        }

        return $parameters;
    }
}
