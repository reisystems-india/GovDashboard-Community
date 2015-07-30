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


class MySQLQueryStatementExecutionCallback extends AbstractPDOQueryStatementExecutionCallback {

    protected function generateColumnTypeImpl(ColumnMetaData $column) {
        switch ($column->type->databaseType) {
            case 'BLOB':       // 252 - blob (text)
            case 'VAR_STRING': // 253 - varchar
            case 'STRING':     // 254 - char (binary)
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'TINY':       // 1 - tiny
            case 'LONG':       // 3 - int
            case 'LONGLONG':   // 8 - bigint
                $column->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
                break;
            case 'DOUBLE':     // 5 - double
            case 'NEWDECIMAL': // 246 - DECIMAL(15, 2) ???
                $column->type->applicationType = NumberDataTypeHandler::DATA_TYPE;
                break;
            case 'DATE':       // 10 - date
                $column->type->applicationType = DateDataTypeHandler::DATA_TYPE;
                break;
            case 'TIME':       // 11 - time
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'TIMESTAMP':  // 7 - timestamp
            case 'DATETIME':   // 12 - datetime
                $column->type->applicationType = DateTimeDataTypeHandler::DATA_TYPE;
                break;
        }
    }
}
