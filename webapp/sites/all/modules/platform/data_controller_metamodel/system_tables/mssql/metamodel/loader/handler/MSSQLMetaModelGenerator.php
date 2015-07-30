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


class MSSQLMetaModelGenerator extends AbstractInformationSchemaMetaModelGenerator {

    protected function isDataSourceAcceptable(DataSourceMetaData $datasource, array $filters = NULL) {
        return ($datasource->type == MSSQLDataSource::TYPE) && parent::isDataSourceAcceptable($datasource, $filters);
    }

    protected function loadTableComments(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        // TODO add support for schema owner
        $sql =
            'SELECT st.name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       sep.value AS ' . self::CN_COMMENT . "\n" .
            "  FROM sys.tables st\n" .
            "       LEFT JOIN sys.extended_properties sep ON st.object_id = sep.major_id AND sep.minor_id = 0 AND sep.name = 'MS_Description'\n" .
            ' WHERE sep.value IS NOT NULL';

        return $this->executeQuery($datasource, 'table.comment', $sql);
    }

    protected function loadColumnComments(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        // TODO add support for schema owner
        $sql =
            'SELECT st.name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       sc.name AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       sep.value AS ' . self::CN_COMMENT . "\n" .
            " from sys.tables st
            inner join sys.columns sc on st.object_id = sc.object_id
            left join sys.extended_properties sep on st.object_id = sep.major_id
                                                 and sc.column_id = sep.minor_id
                                                 and sep.name = 'MS_Description'
                    where  sep.value is not null";

        return $this->executeQuery($datasource, 'column.comment', $sql);
    }
}
