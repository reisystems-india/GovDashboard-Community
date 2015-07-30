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


class HiveExecuteQueryStatementImpl extends AbstractExecuteQueryStatementImpl {

    public function execute(
            DataSourceHandler $handler,
            DataControllerCallContext $callcontext,
            $connection, $sql,
            __SQLDataSourceHandler__AbstractQueryCallbackProxy $callbackInstance) {

        $datasource = $connection->datasource;
        $outputFolder = uniqid('dp', TRUE);

        $webhcatProxy = new WebHCat_CURLProxy($datasource);

        // executing the sql statement
        $statementHandler = $webhcatProxy->initializeHandler(
            'POST',
            '/templeton/v1/hive',
            array('execute' => $sql, 'statusdir' => $outputFolder, 'define=hive.cli.print.header' => 'true'));
        $executor = new SingleCURLHandlerExecutor($statementHandler);
        $responseJob = $executor->execute();
        // ... and preparing job identifier
        if (!isset($responseJob['id'])) {
            LogHelper::log_debug($responseJob);
            throw new IllegalStateException(t(
                'Job ID is not available: %error',
                array('%error' => (isset($responseJob['info']['stderr']) ? $responseJob['info']['stderr']: 'error message is not provided'))));
        }
        $jobId = $responseJob['id'];

        // waiting for the execution job to complete
        $jobHandler = $webhcatProxy->initializeHandler('GET', "/templeton/v1/queue/$jobId");
        $executor = new SingleCURLHandlerExecutor($jobHandler);
        $responseJobStatus = NULL;
        while (TRUE) {
            $responseJobStatus = $executor->execute();
            if ($responseJobStatus['completed'] == 'done') {
                break;
            }
            usleep(1000000);
        }
        if ($responseJobStatus['exitValue'] != 0) {
            throw new IllegalStateException(t(
                '%resourceId execution completed unsuccessfully: %errorCode',
                array('%resourceId' => $jobHandler->resourceId, '%errorCode' => $responseJobStatus['exitValue'])));
        }

        $webhdfsProxy = new WebHDFS_CURLProxy($datasource);

        // reading result of the execution
        $data = NULL;
        while (TRUE) {
            $resultHandler = $webhdfsProxy->initializeHandler(
                'GET',
                "/webhdfs/v1/user/{$datasource->username}/$outputFolder/stdout",
                array('op' => 'OPEN'));
            $executor = new SingleCURLHandlerExecutor($resultHandler);
            $data = $executor->execute();
            // the file should contain at least column names
            if (isset($data)) {
                break;
            }
            // it looks like the result is empty. That happens because the file is not flushed yet
            usleep(10000);
        }

        // deleting the output folder. We do not need it any more
        $resultHandler = $webhdfsProxy->initializeHandler(
            'DELETE',
            "/webhdfs/v1/user/{$datasource->username}/$outputFolder",
            array('op' => 'DELETE', 'recursive' => 'true'));
        $executor = new SingleCURLHandlerExecutor($resultHandler);
        $executor->execute();

        // parsing data
        $parsedDataProvider = new SampleDataPreparer(FALSE);

        $parser = new DelimiterDataParser("\t");
        $parser->isHeaderPresent = TRUE;
        $parser->parse(
            new StreamDataProvider($data),
            array(
                new ColumnNamePreparer(),
                new ColumnPublicNamePreparer(),
                new ColumnTypeAutoDetector(),
                $parsedDataProvider));

        // calculating column database type
        foreach ($parser->metadata->getColumns() as $column) {
            $databaseType = NULL;
            switch ($column->type->applicationType) {
                case StringDataTypeHandler::DATA_TYPE:
                    $databaseType = 'string';
                    break;
                case IntegerDataTypeHandler::DATA_TYPE:
                    $databaseType = 'int';
                    break;
                default:
                    throw new UnsupportedOperationException(t(
                        'Cannot provide data type mapping for %columnName column: %datatype',
                        array('%columnName' => $column->name, '%datatype' => $column->type->applicationType)));
            }

            $column->type->databaseType = $databaseType;
        }

        $statement = new HiveStatement($parser->metadata, $parsedDataProvider->records);

        return $callbackInstance->callback($callcontext, $connection, $statement);
    }
}


class HiveStatement extends AbstractObject {

    protected $metadata = NULL;
    protected $data = NULL;

    public function __construct(RecordMetaData $metadata, array $data = NULL) {
        parent::__construct();
        $this->metadata = $metadata;
        $this->data = $data;
    }

    public function columnCount() {
        return $this->metadata->getColumnCount();
    }

    public function getColumnMeta($columnIndex) {
        $column = $this->metadata->getColumnByIndex($columnIndex);

        return array('name' => $column->name, 'type' => $column->type->databaseType);
    }

    public function fetchAll(&$records) {
        $records = $this->data;
    }
}


class HiveQueryStatementExecutionCallback extends AbstractQueryStatementExecutionCallback {

    public function fetchAllRecords($connection, $statement, &$records) {
        $statement->fetchAll($records);
    }

    public function getColumnCount($connection, $statement) {
        return $statement->columnCount();
    }

    public function getColumnMetaData($connection, $statement, $columnIndex) {
        $statementColumnMetaData = $statement->getColumnMeta($columnIndex);
        if ($statementColumnMetaData === FALSE) {
            return FALSE;
        }

        $column = new ColumnMetaData();
        $column->name = $statementColumnMetaData['name'];
        $column->type->databaseType = $statementColumnMetaData['type'];

        return $column;
    }

    protected function generateColumnTypeImpl(ColumnMetaData $column) {
        switch ($column->type->databaseType) {
            case 'string':
                $column->type->applicationType = StringDataTypeHandler::DATA_TYPE;
                break;
            case 'int':
                $column->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
                break;
        }
    }
}
