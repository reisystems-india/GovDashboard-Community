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


class PDOExecuteQueryStatementImpl extends AbstractExecuteQueryStatementImpl {

    public function execute(
            DataSourceHandler $handler,
            DataControllerCallContext $callcontext,
            $connection, $sql,
            __SQLDataSourceHandler__AbstractQueryCallbackProxy $callbackInstance) {

        $statement = $connection->query($sql);
        try {
            $result = $callbackInstance->callback($callcontext, $connection, $statement);
        }
        catch (Exception $e) {
            $statement->closeCursor();
            throw $e;
        }
        $statement->closeCursor();

        return $result;
    }
}

abstract class AbstractPDOQueryStatementExecutionCallback extends AbstractQueryStatementExecutionCallback {

    public function fetchAllRecords($connection, $statement, &$records) {
        $records = $statement->fetchAll(PDO::FETCH_NUM);
        if (count($records) == 0) {
            $records = NULL;
        }
    }

    public function getColumnCount($connection, $statement) {
        return $statement->columnCount();
    }

    protected function getColumnNativeType($statementColumnMetaData) {
        return $statementColumnMetaData['native_type'];
    }

    /**
     * @param $connection
     * @param $statement
     * @param $columnIndex
     * @return ColumnMetaData
     */
    public function getColumnMetaData($connection, $statement, $columnIndex) {
        $statementColumnMetaData = $statement->getColumnMeta($columnIndex);
        if ($statementColumnMetaData === FALSE) {
            return FALSE;
        }

        $column = new ColumnMetaData();
        $column->name = $statementColumnMetaData['name'];
        $column->type->databaseType = $this->getColumnNativeType($statementColumnMetaData);

        // TODO add support for $column->type->length, ...->precision, ...->scale

        return $column;
    }
}