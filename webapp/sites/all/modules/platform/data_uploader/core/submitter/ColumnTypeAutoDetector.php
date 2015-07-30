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


class ColumnTypeAutoDetector extends AbstractDataAutoDetector {

    public function __construct($maximumRecordCount = NULL) {
        parent::__construct(NULL, $maximumRecordCount);
    }

    protected function submitRecordImpl(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        $datatypeFactory = DataTypeFactory::getInstance();

        foreach ($recordMetaData->getColumns(FALSE) as $column) {
            if (!isset($record[$column->columnIndex])) {
                continue;
            }
            $columnValue = $record[$column->columnIndex];

            // calculating length of the column value
            $column->type->length = MathHelper::max(
                (isset($column->type->length) ? $column->type->length : NULL),
                strlen($columnValue));

            if (isset($column->type->applicationType) && ($column->type->applicationType === StringDataTypeHandler::DATA_TYPE)) {
                continue;
            }

            $columnDataType = $datatypeFactory->autoDetectDataType($columnValue, DATA_TYPE__ALL);
            if (isset($column->type->applicationType)) {
                try {
                    $column->type->applicationType = $datatypeFactory->selectCompatibleDataType(
                        array($column->type->applicationType, $columnDataType));
                }
                catch (IncompatibleDataTypeException $e) {
                    // if the two types are incompatible only 'string' type can resolve the problem
                    $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                }
            }
            else  {
                $column->type->applicationType = $columnDataType;
            }

            // calculating scale for numeric columns
            $handler = $datatypeFactory->getHandler($column->type->applicationType);
            if ($handler instanceof AbstractNumberDataTypeHandler) {
                $numericColumnValue = $handler->castValue($columnValue);

                $decimalSeparatorIndex = strpos($numericColumnValue, $handler->decimalSeparatorSymbol);
                if ($decimalSeparatorIndex !== FALSE) {
                    $scale = strlen($numericColumnValue) - $decimalSeparatorIndex - 1;

                    if (!isset($column->type->scale) || ($column->type->scale < $scale)) {
                        $column->type->scale = $scale;
                    }
                }
            }
        }
    }

    protected function doAfterProcessingRecordsImpl(RecordMetaData $recordMetaData, $fileProcessedCompletely) {
        // 'fixing' values of some type definition properties
        foreach ($recordMetaData->getColumns(FALSE) as $column) {
            switch ($column->type->applicationType) {
                case CurrencyDataTypeHandler::DATA_TYPE:
                    $defaultCurrencyScale = 2;
                    if (!isset($column->type->scale) || ($column->type->scale < $defaultCurrencyScale)) {
                        $column->type->scale = $defaultCurrencyScale;
                    }
                    break;
                case PercentDataTypeHandler::DATA_TYPE:
                    if (isset($column->type->scale)) {
                        $column->type->scale -= 2;
                        if ($column->type->scale < 0) {
                            $column->type->scale = 0;
                        }
                    }
                    break;
            }
        }
    }
}
