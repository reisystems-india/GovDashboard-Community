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


class TableResultFormatter extends AbstractResultFormatter {

    private $columnNames = NULL;
    private $assembledColumnNames = NULL;

    public function __construct(array $columnNames = NULL, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        $this->columnNames = $columnNames;
    }

    protected function registerRecordImpl(array &$records = NULL, $record) {
        parent::registerRecordImpl($records, $record);

        $formattedRecord = NULL;
        if (isset($this->columnNames)) {
            // using predefined column names
            foreach ($this->columnNames as $index => $columnName) {
                $formattedRecord[$index] = isset($record[$columnName]) ? $record[$columnName] : NULL;
            }
        }
        else {
            // checking if we need to add additional columns
            foreach ($record as $columnName => $columnValue) {
                if (!isset($this->assembledColumnNames[$columnName])) {
                    $index = count($this->assembledColumnNames);

                    // registering new columns
                    $this->assembledColumnNames[$columnName] = $index;
                    // adding NULL values for the column for previous records
                    if (isset($records)) {
                        foreach ($records as &$record) {
                            $record[$index] = NULL;
                        }
                        unset($record);
                    }
                }
            }

            foreach ($this->assembledColumnNames as $columnName => $index) {
                $formattedRecord[$index] = isset($record[$columnName]) ? $record[$columnName] : NULL;
            }
        }

        $records[] = $formattedRecord;

        return TRUE;
    }

    protected function finishImpl(array &$records = NULL) {
        parent::finishImpl($records);

        if (!isset($records)) {
            return;
        }

        // first row in a table is list of column names
        $columnNames = $this->columnNames;
        if (!isset($columnNames)) {
            foreach ($this->assembledColumnNames as $columnName => $index) {
                $columnNames[$index] = $columnName;
            }
        }
        array_unshift($records, $columnNames);
    }
}
