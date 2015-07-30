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


class ColumnNamePreparer extends AbstractDataSubmitter {

    private $maximumColumnNameLength = NULL;
    private $columnPrefixName = NULL;

    protected $nameGenerator = NULL;
    protected $publicNameGenerator = NULL;

    public function __construct($maximumColumnNameLength = NULL, $columnPrefixName = NULL) {
        parent::__construct();
        $this->maximumColumnNameLength = $maximumColumnNameLength;
        $this->columnPrefixName = $columnPrefixName;

        $this->nameGenerator = new ColumnNameGenerator($columnPrefixName);
        $this->publicNameGenerator = new ColumnPublicNameGenerator();
    }

    protected function adjustColumnName(RecordMetaData $recordMetaData, $columnName) {
        if (!isset($columnName)) {
            return FALSE;
        }

        $adjustedColumnName = $columnName;

        // trimming the column name a bit ... if necessary
        if (isset($this->maximumColumnNameLength)) {
            if (strlen($adjustedColumnName) > $this->maximumColumnNameLength) {
                $adjustedColumnName = ColumnNameTruncator::shortenName($adjustedColumnName, strlen($adjustedColumnName) - $this->maximumColumnNameLength);
            }
            if (strlen($adjustedColumnName) > $this->maximumColumnNameLength) {
                return FALSE;
            }
        }
        
        // checking if the name already exists ... and it should not to successfully proceed further
        if ($recordMetaData->findColumn($adjustedColumnName) != NULL) {
            return NULL;
        }

        return $adjustedColumnName;
    }

    public function prepareMetaDataColumn(RecordMetaData $recordMetaData, ColumnMetaData $column, $originalColumnName) {
        parent::prepareMetaDataColumn($recordMetaData, $column, $originalColumnName);

        // at first we try to prepare public name ...
        $columnPublicName = isset($originalColumnName) ? $this->publicNameGenerator->generate($originalColumnName) : NULL;

        // ... then we use that 'cleaned' public name to generate column name
        $columnName = isset($columnPublicName) ? $this->nameGenerator->generate($columnPublicName) : NULL;

        // checking if the name is too long
        $adjustedColumnName = $this->adjustColumnName($recordMetaData, $columnName);
        if (isset($adjustedColumnName)) {
            if ($adjustedColumnName === FALSE) {
                // there is nothing can be done to support meaningful name
                $columnName = NULL;
            }
            else {
                // the name was adjusted and is ready to be used
                $columnName = $adjustedColumnName;
            }
        }
        else {
            // trying to add numeric suffix
            $nameIndex = 2;
            while (TRUE) {
                $adjustedColumnName = $this->adjustColumnName($recordMetaData, $columnName . '_' . $nameIndex);
                if (isset($adjustedColumnName)) {
                    if ($adjustedColumnName === FALSE) {
                        // there is nothing can be done to support meaningful name
                        $columnName = NULL;
                    }
                    else {
                        // the name was adjusted and is ready to be used
                        $columnName = $adjustedColumnName;
                    }
                    break;
                }

                $nameIndex++;
            }
        }

        // generating indexed column name
        if (!isset($columnName)) {
            $index = 0;
            do {
                $index++;
                $columnName = (isset($this->columnPrefixName) ? $this->columnPrefixName : 'c') . $index;
            }
            while ($recordMetaData->findColumn($columnName) != NULL);

            // if length of 'hardcoded' name is greater than allowed we cannot proceed any further
            if (isset($this->maximumColumnNameLength) && (strlen($columnName) > $this->maximumColumnNameLength)) {
                throw new UnsupportedOperationException(t(
                    'System name cannot be generated for %columnName column. Maximum allowed length is %maximumColumnNameLength',
                    array('%columnName' => $originalColumnName, '%maximumColumnNameLength' => $this->maximumColumnNameLength)));
            }
        }

        $column->name = $columnName;
    }
}
