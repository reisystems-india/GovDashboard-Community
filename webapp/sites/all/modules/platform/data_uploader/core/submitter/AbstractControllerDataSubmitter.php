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


abstract class AbstractControllerDataSubmitter extends AbstractDataSubmitter {

    public static $BATCH_SIZE = 500;

    protected $datasetName = NULL;

    protected $recordsHolder = NULL;

    public $insertedRecordCount = 0;
    public $updatedRecordCount = 0;
    public $deletedRecordCount = 0;

    public function __construct($datasetName) {
        parent::__construct();
        $this->datasetName = $datasetName;

        $this->recordsHolder = new IndexedRecordsHolder();
    }

    public function setVersion($version) {
        $this->recordsHolder->version = $version;
    }

    abstract protected function submitRecordBatch(RecordMetaData $recordMetaData);

    public function submitRecord(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        parent::submitRecord($recordMetaData, $recordNumber, $record);

        $recordInstance = $this->recordsHolder->initiateRecordInstance();
        $recordInstance->recordIdentifier = $recordNumber;
        $recordInstance->initializeFrom($record);
        $this->recordsHolder->registerRecordInstance($recordInstance);

        if (count($this->recordsHolder->records) >= self::$BATCH_SIZE) {
            $this->submitRecordBatch($recordMetaData);
            unset($this->recordsHolder->records);
        }
    }

    public function doAfterProcessingRecords(RecordMetaData $recordMetaData, $fileProcessedCompletely) {
        parent::doAfterProcessingRecords($recordMetaData, $fileProcessedCompletely);

        if (isset($this->recordsHolder->records)) {
            $this->submitRecordBatch($recordMetaData);
            unset($this->recordsHolder->records);
        }
    }
}