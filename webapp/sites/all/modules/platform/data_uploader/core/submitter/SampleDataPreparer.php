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


class SampleDataPreparer extends AbstractSubsetDataSubmitter {

    protected $provideInAssociativeArray = NULL;
    public $records = NULL;

    public function __construct($provideInAssociativeArray = TRUE, $skipRecordCount = 0, $limitRecordCount = NULL) {
        parent::__construct($skipRecordCount, $limitRecordCount);
        $this->provideInAssociativeArray = $provideInAssociativeArray;
    }

    public function submitRecord(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        parent::submitRecord($recordMetaData, $recordNumber, $record);

        if ($this->skippedRecordCount < $this->skipRecordCount) {
            $this->skippedRecordCount++;
            return;
        }

        if (isset($this->limitRecordCount) && ($this->processedRecordCount >= $this->limitRecordCount)) {
            return;
        }

        $this->records[] = $record;
        $this->processedRecordCount++;
    }

    public function doAfterProcessingRecords(RecordMetaData $recordMetaData, $fileProcessedCompletely) {
        parent::doAfterProcessingRecords($recordMetaData, $fileProcessedCompletely);

        $datatypeFactory = DataTypeFactory::getInstance();

        // converting sample data to appropriate type & reformatting array structure
        if (isset($this->records)) {
            $columns = $recordMetaData->getColumns(FALSE);
            foreach ($this->records as &$record) {
                foreach ($columns as $column) {
                    $value = isset($record[$column->columnIndex]) ? $record[$column->columnIndex] : NULL;

                    $index = $column->columnIndex;
                    if ($this->provideInAssociativeArray) {
                        unset($record[$column->columnIndex]);

                        $index = $column->name;
                    }

                    if (isset($value)) {
                        if (!isset($column->type->applicationType)) {
                            throw new IllegalStateException(t(
                                'Could not prepare %value for preview of %columnName column because column data type is not defined',
                                array('%columnName' => $column->publicName, '%value' => $value)));
                        }

                        $record[$index] = $datatypeFactory->getHandler($column->type->applicationType)->castValue($value);
                    }
                    else {
                        $record[$index] = NULL;
                    }
                }
            }
            unset($record);
        }
    }
}
