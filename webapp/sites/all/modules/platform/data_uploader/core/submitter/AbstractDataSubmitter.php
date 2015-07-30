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


abstract class AbstractDataSubmitter extends AbstractObject {

    public function init() {
        return TRUE;
    }

    public function doAfterLineParsed(array &$record) {}

    public function prepareMetaDataColumn(RecordMetaData $recordMetaData, ColumnMetaData $column, $originalColumnName) {}

    public function doBeforeProcessingRecords(RecordMetaData $recordMetaData, AbstractDataProvider $dataProvider) {
        return TRUE;
    }

    public function doBeforeRecordSubmitted(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        return TRUE;
    }

    public function submitRecord(RecordMetaData $recordMetaData, $recordNumber, array &$record) {}

    public function doAfterRecordSubmitted(RecordMetaData $recordMetaData, $recordNumber, array &$record) {}

    public function doAfterProcessingRecords(RecordMetaData $recordMetaData, $fileProcessedCompletely) {}

    public function finish() {}

    public function abort() {}
}