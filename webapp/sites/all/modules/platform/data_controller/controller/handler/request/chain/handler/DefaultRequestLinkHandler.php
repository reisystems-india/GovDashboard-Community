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

class DefaultRequestLinkHandler extends AbstractRequestLinkHandler {

    public function loadDatasetMetaData(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $handler->loadDatasetMetaData($callcontext, $dataset);
    }

    public function prepareCubeMetaData(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeMetaData $cube) {
        $handler->prepareCubeMetaData($callcontext, $cube);
    }

    public function getNextSequenceValues(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, SequenceRequest $request) {
        return $handler->getNextSequenceValues($callcontext, $request);
    }

    public function queryDataset(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetQueryRequest $request) {
        return $handler->queryDataset($callcontext, $request);
    }

    public function countDatasetRecords(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetCountRequest $request) {
        return $handler->countDatasetRecords($callcontext, $request);
    }

    public function queryCube(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeQueryRequest $request) {
        return $handler->queryCube($callcontext, $request);
    }

    public function countCubeRecords(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeCountRequest $request) {
        return $handler->countCubeRecords($callcontext, $request);
    }
}
