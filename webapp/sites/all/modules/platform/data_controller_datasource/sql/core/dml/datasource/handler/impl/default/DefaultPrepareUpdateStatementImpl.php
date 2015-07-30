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


class DefaultPrepareUpdateStatementImpl extends AbstractPrepareUpdateStatementImpl {

    protected function prepareColumnExpressions(array $columnValues, $delimiter) {
        $s = NULL;

        foreach ($columnValues as $columnName => $value) {
            if (isset($s)) {
                $s .= $delimiter;
            }

            $s .=  $columnName . ' = ' . $value;
        }

        return $s;
    }

    public function prepare(DataSourceHandler $handler, DataSourceMetaData $datasource, $tableName, array $setColumnValues = NULL, array $whereColumnValues = NULL) {
        // we do not need to update any columns. Just ignoring this request
        if (!isset($setColumnValues)) {
            return NULL;
        }

        $assembledTableName = assemble_database_entity_name($handler, $datasource->name, $tableName);

        $sql = "UPDATE $assembledTableName SET " . $this->prepareColumnExpressions($setColumnValues, ', ');
        if (isset($whereColumnValues)) {
            $sql .= ' WHERE ' . $this->prepareColumnExpressions($whereColumnValues, ' AND ');
        }

        return $sql;
    }
}
