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


class PostgreSQLQueryStatementExecutionCallback extends AbstractPDOQueryStatementExecutionCallback {

    protected function generateColumnTypeImpl(ColumnMetaData $column) {
        switch ($column->type->databaseType) {
            // ----- support for standard types
            case 'varchar':
            case 'text':
            case 'bpchar':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'bit':
            case 'int2':
            case 'int4':
            case 'int8':
                $column->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
                break;
            case 'float4':
            case 'float8':
            case 'numeric':
            case 'money':
                $column->type->applicationType = NumberDataTypeHandler::DATA_TYPE;
                break;
            case 'bool':
                $column->type->applicationType = BooleanDataTypeHandler::DATA_TYPE;
                break;
            case 'date':
                $column->type->applicationType = DateDataTypeHandler::DATA_TYPE;
                break;
            case 'time':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'timestamp':
                $column->type->applicationType = DateTimeDataTypeHandler::DATA_TYPE;
                break;

            // ----- support for entity names
            case 'name':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
        }
    }
}
