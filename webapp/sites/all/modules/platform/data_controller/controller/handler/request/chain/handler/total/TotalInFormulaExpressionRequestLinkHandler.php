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

class TotalInFormulaExpressionRequestLinkHandler extends AbstractRequestLinkHandler {

    protected function prepareTotals4Request(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, AbstractQueryRequest $request, AbstractTotalInFormulaExpressionRequestProcessor $processor) {
        $request4Total = $processor->prepareRequest4Total($request);
        if (isset($request4Total)) {
            $totalResult = parent::queryCube($handler, $callcontext, $request4Total);

            if (isset($totalResult)) {
                $processor->updateTotals($request, $request4Total, $totalResult[0]);
            }
        }
    }

    public function queryDataset(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetQueryRequest $request) {
        $processor = new TotalInFormulaExpressionDatasetRequestProcessor();

        $this->prepareTotals4Request($handler, $callcontext, $request, $processor);

        return parent::queryDataset($handler, $callcontext, $request);
    }

    public function countDatasetRecords(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, DatasetCountRequest $request) {
        $processor = new TotalInFormulaExpressionDatasetRequestProcessor();

        $this->prepareTotals4Request($handler, $callcontext, $request, $processor);

        return parent::countDatasetRecords($handler, $callcontext, $request);
    }

    public function queryCube(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeQueryRequest $request) {
        $processor = new TotalInFormulaExpressionCubeRequestProcessor();

        $this->prepareTotals4Request($handler, $callcontext, $request, $processor);

        return parent::queryCube($handler, $callcontext, $request);
    }

    public function countCubeRecords(DataSourceQueryHandler $handler, DataControllerCallContext $callcontext, CubeCountRequest $request) {
        $processor = new TotalInFormulaExpressionCubeRequestProcessor();

        $this->prepareTotals4Request($handler, $callcontext, $request, $processor);

        return parent::countCubeRecords($handler, $callcontext, $request);
    }
}
