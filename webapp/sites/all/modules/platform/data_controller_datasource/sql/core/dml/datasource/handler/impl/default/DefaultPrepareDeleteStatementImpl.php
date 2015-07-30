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


class DefaultPrepareDeleteStatementImpl extends AbstractPrepareDeleteStatementImpl {

    public function prepare(DataSourceHandler $handler, DataSourceMetaData $datasource, $tableName, array $keys = NULL) {
        $sqls = NULL;

        $assembledTableName = assemble_database_entity_name($handler, $datasource->name, $tableName);

        if (isset($keys)) {
            $header = "DELETE FROM $assembledTableName";

            foreach ($keys as $key) {
                $s = NULL;

                foreach ($key as $columnName => $value) {
                    if (isset($s)) {
                        $s .= ' AND ';
                    }
                    else {
                        $s = ' WHERE ';
                    }

                    $s .=  $columnName;

                    if (is_array($value)) {
                        if (count($value) == 1) {
                            $s .= ' = ' . $value[0];
                        }
                        else {
                            $s .= ' IN (' . implode(', ', $value) . ')';
                        }
                    }
                    else {
                        $s .= ' = ' . $value;
                    }
                }

                $sqls[] = $header . $s;
            }
        }

        return $sqls;
    }
}
