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


class PrimaryKeyAutoDetector extends AbstractColumnUniquenessAutoDetector {

    protected static $PRIMARY_KEY_COLUMN_INDEX = 0;

    protected $acceptableDataTypes = NULL;

    public function __construct(array $acceptableDataTypes, $minimumRecordCount = NULL, $maximumRecordCount = NULL) {
        parent::__construct($minimumRecordCount, $maximumRecordCount);
        $this->acceptableDataTypes = $acceptableDataTypes;
    }

    protected function checkColumnUniqueness(RecordMetaData $recordMetaData, $columnIndex, $columnValue) {
        // 01/22/2013 decided to support only first column as possible primary key.
        // Previous solution could select any unique column from the middle of the dataset and that was incorrect in most cases
        return ($columnIndex == self::$PRIMARY_KEY_COLUMN_INDEX) && parent::checkColumnUniqueness($recordMetaData, $columnIndex, $columnValue);
    }

    protected function doAfterProcessingRecordsImpl(RecordMetaData $recordMetaData, $fileProcessedCompletely) {
        if (isset($this->columnUniqueValues[self::$PRIMARY_KEY_COLUMN_INDEX]) && ($this->columnUniqueValues[self::$PRIMARY_KEY_COLUMN_INDEX] !== FALSE)) {
            $column = $recordMetaData->getColumnByIndex(self::$PRIMARY_KEY_COLUMN_INDEX);

            // checking if the column type is acceptable
            if (isset($column->type->applicationType) && in_array($column->type->applicationType, $this->acceptableDataTypes)) {
                $column->key = TRUE;
            }
        }
    }
}
