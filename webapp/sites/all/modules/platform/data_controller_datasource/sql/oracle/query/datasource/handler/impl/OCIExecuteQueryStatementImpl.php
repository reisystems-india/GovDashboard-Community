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


class OCIExecuteQueryStatementImpl extends AbstractExecuteQueryStatementImpl {

    public static $PREFERCH_RECORD_COUNT = 10000;

    public function execute(
            DataSourceHandler $handler,
            DataControllerCallContext $callcontext,
            $connection, $sql,
            __SQLDataSourceHandler__AbstractQueryCallbackProxy $callbackInstance) {

        $statement = OCIImplHelper::oci_parse($connection, $sql);
        try {
            if (self::$PREFERCH_RECORD_COUNT > 0) {
                OCIImplHelper::oci_set_prefetch($connection, $statement, self::$PREFERCH_RECORD_COUNT);
            }
            OCIImplHelper::oci_execute($connection, $statement, OCI_NO_AUTO_COMMIT);
            $result = $callbackInstance->callback($callcontext, $connection, $statement);
        }
        catch (Exception $e) {
            OCIImplHelper::oci_free_statement($connection, $statement);

            throw $e;
        }
        OCIImplHelper::oci_free_statement($connection, $statement);

        return $result;
    }
}

class OracleQueryStatementExecutionCallback extends AbstractQueryStatementExecutionCallback {

    public function fetchAllRecords($connection, $statement, &$records) {
        OCIImplHelper::oci_fetch_all($connection, $statement, $records, 0, -1, OCI_FETCHSTATEMENT_BY_ROW + OCI_NUM);
    }

    public function getColumnCount($connection, $statement) {
        return OCIImplHelper::oci_num_fields($connection, $statement);
    }

    public function getColumnMetaData($connection, $statement, $columnIndex) {
        $columnNumber = $columnIndex + 1;

        $column = new ColumnMetaData();
        $column->name = strtolower(OCIImplHelper::oci_field_name($connection, $statement, $columnNumber));
        // preparing the column type
        $column->type->databaseType = OCIImplHelper::oci_field_type($connection, $statement, $columnNumber);
        switch ($column->type->databaseType) {
            case 'CHAR':
            case 'VARCHAR2':
                $column->type->length = OCIImplHelper::oci_field_size($connection, $statement, $columnNumber);
                break;
            case 'NUMBER':
                $column->type->precision = OCIImplHelper::oci_field_precision($connection, $statement, $columnNumber);
                $column->type->scale = OCIImplHelper::oci_field_scale($connection, $statement, $columnNumber);
                break;
        }

        return $column;
    }

    protected function generateColumnTypeImpl(ColumnMetaData $column) {
        switch ($column->type->databaseType) {
            case 'CHAR':
            case 'VARCHAR2':
            case 'CLOB':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'NUMBER':
                $column->type->applicationType = NumberDataTypeHandler::DATA_TYPE;

                if ($column->type->precision == 0) {
                    // it is calculated (not physical) column of type 'NUMBER'
                }
                else {
                    if ($column->type->scale == 0) {
                        if ($column->type->precision <= 10) {
                            $column->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
                        }
                    }
                }

                break;
            case 'DATE':
            case 'TIMESTAMP':
                $column->type->applicationType = DateTimeDataTypeHandler::DATA_TYPE;
                break;
        }

        // more 'complex' case of timestamp
        if (!isset($column->type->applicationType)) {
            $index = strpos($column->type->databaseType, 'TIMESTAMP(');
            if ($index === 0) {
                $column->type->applicationType = DateTimeDataTypeHandler::DATA_TYPE;
            }
        }

        // interval data type
        if (!isset($column->type->applicationType)) {
            $index = strpos($column->type->databaseType, 'INTERVAL ');
            if ($index === 0) {
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
            }
        }
    }
}
