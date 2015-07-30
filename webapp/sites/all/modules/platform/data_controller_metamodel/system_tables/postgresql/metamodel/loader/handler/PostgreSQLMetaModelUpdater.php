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


class PostgreSQLMetaModelUpdater extends AbstractSystemTableMetaModelUpdater {

    protected $statementExecutionCallback = NULL;

    public function __construct() {
        parent::__construct();
        $this->statementExecutionCallback = new PostgreSQLQueryStatementExecutionCallback();
    }

    protected function executeQuery(DataSourceMetaData $datasource, $operationName, $sql) {
        LogHelper::log_info(new StatementLogMessage("metamodel.system.{$operationName}[{$datasource->type}][{$datasource->name}]", $sql));

        $executionCallContext = new DataControllerCallContext();

        $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

        return $datasourceQueryHandler->executeQuery($executionCallContext, $datasource, $sql);
    }

    protected function isDataSourceAcceptable(DataSourceMetaData $datasource, array $filters = NULL) {
        return ($datasource->type == PostgreSQLDataSource::TYPE) && parent::isDataSourceAcceptable($datasource, $filters);
    }

    protected function generateColumnApplicationType(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, ColumnMetaData $column) {
        $this->statementExecutionCallback->generateColumnType($column);
    }

    protected function loadColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $tableNames = NULL;
        foreach ($callcontext->datasets as $dataset) {
            $tableNames[] = $dataset->sourse;
        }
        if (!isset($tableNames)) {
            return NULL;
        }

        $sql =
            'SELECT c.relname AS ' . self::CN_TABLE_NAME . ",\n" .
            '       a.attname AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       a.attnum AS ' . self::CN_COLUMN_INDEX . ",\n" .
            '       t.typname AS ' . self::CN_COLUMN_TYPE .
            "  FROM pg_class c INNER JOIN pg_namespace ns ON ns.oid = c.relnamespace\n" .
            "       INNER JOIN pg_attribute a ON a.attrelid = c.oid\n" .
            "       INNER JOIN pg_type t ON t.oid = a.atttypid\n" .
            " WHERE c.relkind IN ('r','v')\n" .
            "   AND ns.nspname = '$datasource->schema'\n" .
            "   AND a.attnum > 0\n" .
            '   AND c.relname IN (' .  ArrayHelper::serialize($tableNames) . ')';

        return $this->executeQuery($datasource, 'table.column', $sql);
    }

    protected function prepareDatasets4Update(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource) {
        foreach ($metamodel->datasets as $dataset) {
            // the dataset should belong to the selected data source
            if ($dataset->datasourceName !== $datasource->name) {
                continue;
            }

            // the dataset has to be of type table
            if (DatasetSourceTypeFactory::getInstance()->detectSourceType($dataset) !== TableDatasetSourceTypeHandler::SOURCE_TYPE) {
                continue;
            }

            // whole dataset meta data was prepared using different method. There is nothing else needs to be done
            if ($dataset->isComplete()) {
                continue;
            }

            // for now supporting datasets without table owner
            if (TableReferenceHelper::findTableOwner($dataset->source) != NULL) {
                continue;
            }

            // there could be several datasets for one table
            $tableAccessKey = $this->adjustTableName($dataset->source);
            $callcontext->datasets[$tableAccessKey][] = $dataset;
        }
    }
}
