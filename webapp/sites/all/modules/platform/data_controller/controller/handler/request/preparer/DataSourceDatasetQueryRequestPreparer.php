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

class DataSourceDatasetQueryRequestPreparer extends AbstractDataSourceQueryRequestPreparer {

    public function prepareQueryRequest(DataQueryControllerDatasetRequest $request) {
        $datasourceRequest = new DatasetQueryRequest($request->datasetName);

        // needs to be called before any additional methods are called
        $datasourceRequest->addOptions($request->options);

        $datasourceRequest->addCompositeQueryValues($request->parameters);
        $datasourceRequest->addColumns($request->columns);
        $datasourceRequest->addOrderByColumns($request->orderBy);
        $datasourceRequest->setPagination($request->limit, $request->startWith);

        return $datasourceRequest;
    }

    public function prepareCountRequest(DataQueryControllerDatasetRequest $request) {
        $datasourceRequest = new DatasetCountRequest($request->datasetName);

        // needs to be called before any additional methods are called
        $datasourceRequest->addOptions($request->options);

        $datasourceRequest->addCompositeQueryValues($request->parameters);

        return $datasourceRequest;
    }
}
