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


class MSSQLQueryStatementExecutionCallback extends AbstractPDOQueryStatementExecutionCallback {

    protected $fractionDigits = NULL;

    public function __construct() {
        parent::__construct();
        $this->fractionDigits = StringHelper::trim($this->numberFormatter->getAttribute(NumberFormatter::FRACTION_DIGITS));
        if ($this->fractionDigits === FALSE) {
            throw new IllegalStateException(t('Cannot detect OS fraction digits'));
        }
    }

    protected function getColumnNativeType($statementColumnMetaData) {
        $type = $statementColumnMetaData['sqlsrv:decl_type'];

        // making adjustments
        if ($type == 'int identity') {
            $type = 'int';
        }

        return $type;
    }

    protected function generateColumnTypeImpl(ColumnMetaData $column) {
        // http://msdn.microsoft.com/en-us/library/ms187752.aspx
        switch ($column->type->databaseType) {
            case 'uniqueidentifier': // 16-byte GUID
            case 'char':
            case 'text':
            case 'varchar':
            case 'nchar':
            case 'ntext':
            case 'nvarchar':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'bigint':
            case 'int':
            case 'smallint':
            case 'tinyint':
            case 'bit':
                $column->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
                break;
            case 'decimal':
            case 'numeric':
            case 'float':
            case 'real':
                $column->type->applicationType = NumberDataTypeHandler::DATA_TYPE;
                break;
            case 'money':
            case 'smallmoney':
                // money types support 4 fractional digits by default
                if (isset($column->type->scale) && isset($this->fractionDigits) && ($column->type->scale > $this->fractionDigits)) {
                    $column->type->scale = $this->fractionDigits;
                }
                $column->type->applicationType = CurrencyDataTypeHandler::DATA_TYPE;
                break;
            case 'date':
                $column->type->applicationType = DateDataTypeHandler::DATA_TYPE;
                break;
            case 'timestamp': // deprecated
            case 'datetime':
            case 'datetime2':
                $column->type->applicationType = DateTimeDataTypeHandler::DATA_TYPE;
                break;
            case 'time':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'bit':
                $column->type->applicationType = BooleanDataTypeHandler::DATA_TYPE;
                break;

            case 'binary':
            case 'image':
            case 'varbinary':
            case 'cursor':
            case 'hierarchyid':
            case 'sql_variant':
            case 'table':
            case 'datetimeoffset':
            case 'smalldatetime':
            case 'xml':
            case 'geography':
            case 'geometry':
                break;
        }
    }
}
