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


abstract class AbstractDataAutoDetector extends AbstractDataSubmitter {

    protected $minimumRecordCount = NULL;
    protected $maximumRecordCount = NULL;

    protected $processedRecordCount = 0;

    public function __construct($minimumRecordCount = NULL, $maximumRecordCount = NULL) {
        parent::__construct();
        $this->minimumRecordCount = $minimumRecordCount;
        $this->maximumRecordCount = $maximumRecordCount;
    }

    abstract protected function submitRecordImpl(RecordMetaData $recordMetaData, $recordNumber, array &$record);

    final public function submitRecord(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        parent::submitRecord($recordMetaData, $recordNumber, $record);

        if (!isset($this->maximumRecordCount) || ($this->processedRecordCount < $this->maximumRecordCount)) {
            $this->submitRecordImpl($recordMetaData, $recordNumber, $record);

            $this->processedRecordCount++;
        }
    }

    abstract protected function doAfterProcessingRecordsImpl(RecordMetaData $recordMetaData, $fileProcessedCompletely);

    final public function doAfterProcessingRecords(RecordMetaData $recordMetaData, $fileProcessedCompletely) {
        parent::doAfterProcessingRecords($recordMetaData, $fileProcessedCompletely);

        // we need to process 'minimum' number of records or process whole file to proceed with data detection result finalization
        if (!isset($this->minimumRecordCount) || ($fileProcessedCompletely || ($this->processedRecordCount >= $this->minimumRecordCount))) {
            $this->doAfterProcessingRecordsImpl($recordMetaData, $fileProcessedCompletely);
        }
    }
}
