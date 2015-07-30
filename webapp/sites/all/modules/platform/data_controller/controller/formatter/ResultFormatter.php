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

interface ResultFormatterConfiguration {

    function printFormattingPath();

    function adjustDatasetQueryRequest(DataControllerCallContext $callcontext, DatasetQueryRequest $request);
    function adjustDatasetCountRequest(DataControllerCallContext $callcontext, DatasetCountRequest $request);
    function adjustCubeQueryRequest(DataControllerCallContext $callcontext, CubeQueryRequest $request);
    function adjustCubeCountRequest(DataControllerCallContext $callcontext, CubeCountRequest $request);

    // TODO add usage
    function isClientSortingRequired();
    // TODO add usage
    function isClientPaginationRequired();
}


interface ResultFormatter extends ResultFormatterConfiguration {

    function start();
    function formatColumnName($columnName, $matchRequired = FALSE);
    function formatColumnNames(array $columnNames = NULL, $matchRequired = FALSE);
    function setRecordColumnValue(array &$record = NULL, $columnName, $columnValue);
    function registerRecord(array &$records = NULL, $record);
    function finish(array &$records = NULL);

    function formatRecords(array $records = NULL);
}
