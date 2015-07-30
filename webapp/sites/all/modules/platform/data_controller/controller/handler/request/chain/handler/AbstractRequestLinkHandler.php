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

abstract class AbstractRequestLinkHandler extends AbstractObject implements RequestLinkHandler {

    private $next = NULL;

    public function __construct(RequestLinkHandler $next = NULL) {
        parent::__construct();
        $this->next = $next;
    }

    public function loadDatasetMetaData(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $this->getNextLink()->loadDatasetMetaData($handler, $callcontext, $dataset);
    }

    public function prepareCubeMetaData(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeMetaData $cube) {
        $this->getNextLink()->prepareCubeMetaData($handler, $callcontext, $cube);
    }

    public function getNextSequenceValues(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, SequenceRequest $request) {
        return $this->getNextLink()->getNextSequenceValues($handler, $callcontext, $request);
    }

    public function queryDataset(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetQueryRequest $request) {
        return $this->getNextLink()->queryDataset($handler, $callcontext, $request);
    }

    public function countDatasetRecords(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetCountRequest $request) {
        return $this->getNextLink()->countDatasetRecords($handler, $callcontext, $request);
    }

    public function queryCube(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeQueryRequest $request) {
        return $this->getNextLink()->queryCube($handler, $callcontext, $request);
    }

    public function countCubeRecords(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeCountRequest $request) {
        return $this->getNextLink()->countCubeRecords($handler, $callcontext, $request);
    }

    public function getNextLink() {
        if (!isset($this->next)) {
            throw new UnsupportedOperationException(t('Undefined link in request chain'));
        }

        return $this->next;
    }
}
