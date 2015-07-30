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


abstract class AbstractColumnUniquenessAutoDetector extends AbstractDataAutoDetector {

    protected $columnUniqueValues = NULL;

    protected function checkColumnUniqueness(RecordMetaData $recordMetaData, $columnIndex, $columnValue) {
        return !isset($this->columnUniqueValues[$columnIndex][$columnValue]);
    }

    protected function submitRecordImpl(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        foreach ($recordMetaData->getColumns(FALSE) as $columnIndex => $column) {
            // we do not need to work with the column. It does not contain unique values
            if (isset($this->columnUniqueValues[$columnIndex]) && ($this->columnUniqueValues[$columnIndex] === FALSE)) {
                continue;
            }

            $unique = FALSE;
            // if the column contains NULL we should not consider it as containing unique values
            if (isset($record[$columnIndex])) {
                $columnValue = $record[$columnIndex];
                if ($this->checkColumnUniqueness($recordMetaData, $columnIndex, $columnValue)) {
                    $this->columnUniqueValues[$columnIndex][$columnValue] = TRUE;
                    $unique = TRUE;
                }
            }
            // we do not need further processing for the column
            if (!$unique) {
                $this->columnUniqueValues[$columnIndex] = FALSE;
            }
        }
    }
}
