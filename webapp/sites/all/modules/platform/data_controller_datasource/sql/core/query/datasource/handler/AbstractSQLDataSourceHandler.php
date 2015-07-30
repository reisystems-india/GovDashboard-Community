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


abstract class AbstractSQLDataSourceHandler extends AbstractDataSourceHandler implements SQLDataSourceHandler {

    const STATEMENT_EXECUTION_MODE__PROCEED = 'proceed';
    // this mode is used then it is necessary to generate but not execute the generated statement
    const STATEMENT_EXECUTION_MODE__IGNORE = 'ignore';

    public static $STATEMENT_EXECUTION_MODE = self::STATEMENT_EXECUTION_MODE__PROCEED;

    public function getDataSourceOwner($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        return $this->getExtension('datasourceOwner')->prepare($this, $datasource);
    }

    public function concatenateValues(array $formattedValues) {
        return $this->getExtension('concatenateValues')->concatenate($this, $formattedValues);
    }

    public function formatStringValue($value) {
        // replacing ' with '' and \ with \\ then surround the value with quotes
        return '\'' . str_replace(array('\'', '\\'), array('\'\'', '\\\\'), $value) . '\'';
    }

    public function formatOperatorValue(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, OperatorHandler $value) {
        $handler = SQLOperatorFactory::getInstance()->getHandler($this, $value);
        return $handler->format($callcontext, $request, $datasetName, $columnName);
    }

    public function startTransaction($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        $sql = $this->getExtension('startTransaction')->generate($this, $datasource);
        LogHelper::log_info(new StatementLogMessage('transaction.begin', $sql));
        $this->executeStatement($datasource, $sql);
    }

    public function commitTransaction($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        $sql = $this->getExtension('commitTransaction')->generate($this, $datasource);
        LogHelper::log_info(new StatementLogMessage('transaction.commit', $sql));
        $this->executeStatement($datasource, $sql);
    }

    public function rollbackTransaction($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        $sql = $this->getExtension('rollbackTransaction')->generate($this, $datasource);
        LogHelper::log_info(new StatementLogMessage('transaction.rollback', $sql));
        $this->executeStatement($datasource, $sql);
    }

    protected function getConnection(DataSourceMetaData $datasource) {
        $transaction = TransactionManager::getInstance()->getTransaction($datasource->name);
        $connectionName = $transaction->assembleResourceName(get_class($datasource), $datasource->name);

        $connection = $transaction->findResource($connectionName);
        if (!isset($connection)) {
            $connection = $this->getExtension('initializeConnection')->initialize($this, $datasource);

            if (!$datasource->isTemporary()) {
                $transaction->registerResource($connectionName, $connection);
                $transaction->registerActionCallback(new ConnectionTransactionActionCallback($datasource->name));
            }
        }

        return $connection;
    }

    public function executeStatement(DataSourceMetaData $datasource, $sql) {
        $affectedRecordCount = 0;
        if (self::$STATEMENT_EXECUTION_MODE == self::STATEMENT_EXECUTION_MODE__PROCEED) {
            $connection = $this->getConnection($datasource);

            $timeStart = microtime(TRUE);
            $affectedRecordCount = $this->getExtension('executeStatement')->execute($this, $connection, $sql);
            LogHelper::log_notice(t(
                'Database execution time for @statementCount statement(s): !executionTime',
                array(
                    '@statementCount' => count($sql),
                    '!executionTime' => LogHelper::formatExecutionTime($timeStart))));
        }

        return $affectedRecordCount;
    }
}


class ConnectionTransactionActionCallback extends AbstractObject implements TransactionActionCallback, ResourceTransactionActionCallback {

    private $datasourceName = NULL;

    public function __construct($datasourceName) {
        parent::__construct();
        $this->datasourceName = $datasourceName;
    }

    protected function getDataSourceHandler() {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($this->datasourceName);

        return DataSourceQueryFactory::getInstance()->getHandler($datasource->type);
    }

    public function start() {
        $this->getDataSourceHandler()->startTransaction($this->datasourceName);
    }

    public function commit() {
        $this->getDataSourceHandler()->commitTransaction($this->datasourceName);
    }

    public function rollback() {
        $this->getDataSourceHandler()->rollbackTransaction($this->datasourceName);
    }
}
