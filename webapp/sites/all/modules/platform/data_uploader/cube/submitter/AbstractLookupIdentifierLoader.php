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


abstract class AbstractLookupIdentifierLoader extends AbstractObject {

    abstract public function selectLookupableColumns(RecordMetaData $recordMetaData);

    protected function prepareLookupValues(IndexedRecordsHolder $recordsHolder, array $lookupableColumns) {
        $columnsLookupValues = NULL;
        if (isset($lookupableColumns)) {
            foreach ($recordsHolder->records as $record) {
                foreach ($lookupableColumns as $columnIndex => $column) {
                    $columnValue = $record->columnValues[$columnIndex];
                    if (!isset($columnValue)) {
                        continue;
                    }

                    $dimensionLookupHandler = DimensionLookupFactory::getInstance()->getHandler($column->type->getLogicalApplicationType());

                    // storing unique set of values
                    $lookupKey = AbstractDimensionLookupHandler::prepareLookupKey($columnValue);
                    $columnsLookupValues[$columnIndex][$lookupKey] = $dimensionLookupHandler->prepareLookupValue($columnValue);
                }
            }
        }

        return $columnsLookupValues;
    }

    protected function checkMissingIdentifiers(ColumnMetaData $column, array $columnLookupValues) {
        $KEY__COMPOSITE_NAME = '<composite>';

        // calculating number of missing identifiers based on number of properties in lookup key
        $columnUsageStatistics = NULL;
        foreach ($columnLookupValues as $lookupValue) {
            if (isset($lookupValue->identifier)) {
                continue;
            }

            $columnNames = NULL;
            foreach ($lookupValue as $name => $value) {
                if (isset($value)) {
                    $columnNames[] = $name;
                }
            }

            $columnCount = count($columnNames);
            if ($columnCount > 0) {
                $key = ($columnCount > 1) ? $KEY__COMPOSITE_NAME : $columnNames[0];
                if (!isset($columnUsageStatistics[$columnCount][$key])) {
                    $columnUsageStatistics[$columnCount][$key] = 0;
                }
                $columnUsageStatistics[$columnCount][$key]++;
            }
        }

        // we do have uncompleted data
        if (isset($columnUsageStatistics)) {
            $showSingleColumnName = FALSE;

            $useInnerArray = FALSE;
            $variationsOfColumnCount = count($columnUsageStatistics);
            if ($variationsOfColumnCount === 1) {
                $columnNames = array_keys($columnUsageStatistics[key($columnUsageStatistics)]);
                $variationsOfColumnName = count($columnNames);
                if ($variationsOfColumnName == 1) {
                    $columnName = $columnNames[0];
                    if ($columnName == $KEY__COMPOSITE_NAME) {
                        $useInnerArray = TRUE;
                    }
                    else {
                        $showSingleColumnName = TRUE;
                    }
                }
                else {
                    $useInnerArray = TRUE;
                }
            }
            else {
                // we have different number of key properties
                $useInnerArray = TRUE;
            }

            $message = '';
            foreach ($columnLookupValues as $lookupValue) {
                if (isset($lookupValue->identifier)) {
                    continue;
                }

                $s = '';
                foreach ($lookupValue as $name => $value) {
                    if (isset($value)) {
                        if ($s != '') {
                            $s .= '; ';
                        }
                        if ($useInnerArray) {
                            $s .= $name . '=';
                        }
                        $s .= $value;
                    }
                }
                if ($useInnerArray) {
                    $s = '[' . $s . ']';
                }

                if ($message != '') {
                    $message .= ', ';
                }
                $message .= $s;
            }
            $message = ($showSingleColumnName ? ($column->publicName . ' = ') : '') . '[' . $message . ']';

            throw new IllegalArgumentException(t('Could not find identifiers for the following values: %values', array('%values' => $message)));
        }
    }

    public function load($datasetName, RecordMetaData $recordMetaData, IndexedRecordsHolder $recordsHolder) {
        // preparing columns for which we can lookup values
        $lookupableColumns = $this->selectLookupableColumns($recordMetaData);
        if (!isset($lookupableColumns)) {
            return;
        }

        // preparing required values for each lookup
        $columnsLookupValues = $this->prepareLookupValues($recordsHolder, $lookupableColumns);
        if (!isset($columnsLookupValues)) {
            return;
        }

        // loading identifier for each values
        foreach ($columnsLookupValues as $columnIndex => &$columnLookupValues) {
            $column = $lookupableColumns[$columnIndex];

            $dimensionLookupHandler = DimensionLookupFactory::getInstance()->getHandler($column->type->getLogicalApplicationType());
            $dimensionLookupHandler->prepareDatasetColumnLookupIds($datasetName, $column, $columnLookupValues);

            // checking if we loaded all values
            $this->checkMissingIdentifiers($column, $columnLookupValues);
        }
        unset($columnLookupValues);

        // replacing column values with corresponding ids
        foreach ($recordsHolder->records as $record) {
            foreach ($lookupableColumns as $columnIndex => $column) {
                $columnValue = $record->columnValues[$columnIndex];
                if (!isset($columnValue)) {
                    continue;
                }

                $lookupKey = AbstractDimensionLookupHandler::prepareLookupKey($columnValue);
                $record->columnValues[$columnIndex] = $columnsLookupValues[$columnIndex][$lookupKey]->identifier;
            }
        }
    }
}
