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


class HCatalogMetaModelGenerator extends AbstractSystemTableMetaModelGenerator {

    private $statementExecutionCallback = NULL;

    public function __construct() {
        parent::__construct();
        $this->statementExecutionCallback = new HiveQueryStatementExecutionCallback();
    }

    protected function isDataSourceAcceptable(DataSourceMetaData $datasource, array $filters = NULL) {
        return ($datasource->type == WebHCatDataSource::TYPE) && parent::isDataSourceAcceptable($datasource, $filters);
    }

    protected function loadTableNames(DataSourceMetaData $datasource) {
        $webhcatProxy = new WebHCat_CURLProxy($datasource);

        $handler = $webhcatProxy->initializeHandler('GET', "/templeton/v1/ddl/database/{$datasource->database}/table");
        $executor = new SingleCURLHandlerExecutor($handler);
        $response = $executor->execute();

        return (count($response['tables']) > 0) ? $response['tables'] : NULL;
    }

    protected function loadColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $tableColumnsProperties = NULL;

        $webhcatProxy = new WebHCat_CURLProxy($datasource);

        // loading list of available tables
        $tableNames = $this->loadTableNames($datasource);
        if (isset($tableNames)) {
            // preparing requests to load table structure
            $tableHandlers = NULL;
            foreach ($tableNames as $tableName) {
                $tableHandlers[] = $webhcatProxy->initializeHandler('GET', "/templeton/v1/ddl/database/{$datasource->database}/table/$tableName");
            }

            // processing available tables
            $executor = new MultipleCURLHandlerExecutor($tableHandlers);
            $executor->start();
            while (($handler = $executor->findCompletedHandler()) !== FALSE) {
                $responseColumns = $executor->processResponse($handler);
                if (!isset($responseColumns['columns'])) {
                    continue;
                }

                foreach ($responseColumns['columns'] as $columnIndex => $responseColumn) {
                    $tableColumnsProperties[] = array(
                        self::CN_TABLE_NAME => $responseColumns['table'],
                        self::CN_COLUMN_NAME => $responseColumn['name'],
                        self::CN_COLUMN_INDEX => $columnIndex,
                        self::CN_COLUMN_TYPE => $responseColumn['type']);
                }
            }
        }

        return $tableColumnsProperties;
    }

    protected function generateColumnApplicationType(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, ColumnMetaData $column) {
        $this->statementExecutionCallback->generateColumnType($column);
    }
}
