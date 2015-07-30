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

abstract class AbstractSQLDataSourceQueryHandler extends AbstractSQLDataSourceHandler implements DataSourceQueryHandler {

    protected function adjustReferencedDataType4Casting($datasetName, $columnName) {
        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);
        $column = $dataset->getColumn($columnName);

        return $column->type->applicationType;
    }

    protected function executeQueryStatement(DataControllerCallContext $callcontext, DataSourceMetaData $datasource, $sql, __SQLDataSourceHandler__AbstractQueryCallbackProxy $callbackInstance) {
        $result = NULL;
        if (self::$STATEMENT_EXECUTION_MODE == self::STATEMENT_EXECUTION_MODE__PROCEED) {
            $connection = $this->getConnection($datasource);

            $timeStart = microtime(TRUE);
            $result = $this->getExtension('executeQueryStatement')->execute($this, $callcontext, $connection, $sql, $callbackInstance);
            LogHelper::log_notice(t('Database execution time: !executionTime', array('!executionTime' => LogHelper::formatExecutionTime($timeStart))));
        }

        return $result;
    }

    /*
     * Prepares SQL statement which returns required columns.
     * For performance reason number of returned records is set to 0.
     * Each SQL-driven database supports its own method to get column meta data.
     */
    public function loadDatasetMetaData(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $queryRequest = new DatasetQueryRequest($dataset->name);
        // we do not need to return any records
        $queryRequest->setPagination(0);

        $statements = $this->prepareDatasetQueryStatements($callcontext, $queryRequest);
        $sql = $this->assembleDatasetQueryStatements($callcontext, $queryRequest, $statements);
        // applying pagination
        $this->applyPagination($queryRequest, $sql);

        $loadedDatasetMetaData = $this->processDatasetMetaData($callcontext, $datasource, $sql);

        // processing loaded columns
        $dataset->initializeColumnsFrom($loadedDatasetMetaData->columns);
    }

    protected function processDatasetMetaData(DataControllerCallContext $callcontext, DataSourceMetaData $datasource, $sql) {
        LogHelper::log_info(new StatementLogMessage('metadata.dataset', $sql));

        $metadata = $this->executeQueryStatement(
            $callcontext, $datasource, $sql,
            new __SQLDataSourceHandler__QueryMetaDataCallbackProxy($this->prepareQueryStatementExecutionCallbackInstance()));

        return $metadata;
    }

    public function prepareCubeMetaData(DataControllerCallContext $callcontext, CubeMetaData $cube) {
        $measureNames = NULL;
        if (isset($cube->measures)) {
            foreach ($cube->measures as $measure) {
                if ($measure->isComplete()) {
                    continue;
                }

                $measureNames[] = $measure->name;
            }
        }
        // we need at least one incomplete measure to proceed
        if (!isset($measureNames)) {
            return;
        }

        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($cube->factsDataset->datasourceName);

        $queryRequest = new CubeQueryRequest($cube->name);
        // requesting all measures to get their type
        foreach ($measureNames as $requestColumnIndex => $measureName) {
            $queryRequest->addMeasure($requestColumnIndex, $measureName);
        }
        // we do not need to return any records, we just analyze structure
        $queryRequest->setPagination(0);

        $aggrStatement = $this->prepareCubeQueryStatement($callcontext, $queryRequest);
        list($isSubqueryRequired, $assembledAggregationSections) = $aggrStatement->prepareSections(NULL);
        $sql = Statement::assemble(
            $isSubqueryRequired,
            NULL, // assembling all columns
            $assembledAggregationSections);
        // applying pagination
        $this->applyPagination($queryRequest, $sql);

        $measureDatasetMetaData = $this->processDatasetMetaData($callcontext, $datasource, $sql);

        // processing all measures and setting up types
        foreach ($measureNames as $measureName) {
            $column = $measureDatasetMetaData->getColumn($measureName);
            $cube->getMeasure($measureName)->initializeTypeFrom($column->type);
        }
    }

    public function getNextSequenceValues(DataControllerCallContext $callcontext, SequenceRequest $request) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($request->datasourceName);

        $sql = "SELECT dp_get_next_sequence_id('$request->sequenceName', $request->quantity) AS last_sequential_id";

        LogHelper::log_info(new StatementLogMessage('sequence', $sql));
        $result = $this->executeQuery($callcontext, $datasource, $sql);

        $lastSequentialId = $result[0]['last_sequential_id'];

        $ids = NULL;
        for ($i = $request->quantity - 1; $i >= 0; $i--) {
            $ids[] = $lastSequentialId - $i;
        }

        return $ids;
    }

    /*
     * Prepares one or several statement objects based on request
     */
    protected function prepareDatasetQueryStatements(DataControllerCallContext $callcontext, AbstractDatasetQueryRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $datasetName = $request->getDatasetName();

        $dataset = $metamodel->getDataset($datasetName);

        $requestedColumns = ($request instanceof DatasetCountRequest)
            ? array() // we do not need to return any columns from this dataset
            : $request->columns;

        // preparing list of columns which are accessed by this request
        $usedColumnNames = $requestedColumns;
        if (isset($usedColumnNames) && isset($request->queries)) {
            foreach ($request->queries as $query) {
                foreach ($query as $columnName => $values) {
                    ArrayHelper::addUniqueValue($usedColumnNames, $columnName);
                }
            }
        }

        // preparing dataset source
        $baseStatement = $this->prepareDatasetSourceStatement($callcontext, $request, $dataset, $usedColumnNames);

        // updating aliases for all composite columns
        foreach ($baseStatement->tables as $table) {
            if (isset($table->columns)) {
                foreach ($table->columns as $column) {
                    if ($column instanceof CompositeColumnSection) {
                        $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements($this->getMaximumEntityNameLength(), $column->alias);
                        if ($column->alias != $databaseColumnName) {
                            $callcontext->columnMapping[$databaseColumnName] = $column->alias;
                            $column->alias = $databaseColumnName;
                        }
                    }
                }
            }
        }

        if (isset($request->queries)) {
            $statements = NULL;
            foreach ($request->queries as $query) {
                $statement = clone $baseStatement;
                // adding additional conditions
                foreach ($query as $columnName => $values) {
                    // detecting data type for the column
                    $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements($this->getMaximumEntityNameLength(), $columnName);
                    $table = $statement->getColumnTable($databaseColumnName, TRUE);
                    $column = $table->findColumnByAlias($databaseColumnName);

                    foreach ($values as $value) {
                        $conditionValue = new ExactConditionSectionValue($this->formatOperatorValue($callcontext, $request, $dataset->name, $columnName, $value));
                        $statement->conditions[] = ($column instanceof CompositeColumnSection)
                            ? new CompositeWhereConditionSection($table->alias, $column, $conditionValue)
                            : new WhereConditionSection($table->alias, (isset($column) ? $column->name : $databaseColumnName), $conditionValue);
                    }
                }
                $statements[] = $statement;
            }

            return $statements;
        }
        else {
            return array($baseStatement);
        }
    }

    /*
     * Assembles each query statement and combines resulting SQL using UNION operator
     */
    protected function assembleDatasetQueryStatements(DataControllerCallContext $callcontext, DatasetQueryRequest $request, array $statements) {
        $sql = '';

        // preparing column names
        $columnNames = NULL;
        if (isset($request->columns)) {
            foreach ($request->columns as $columnName) {
                $columnNames[] = DataSourceColumnNameHelper::generateFromParameterElements($this->getMaximumEntityNameLength(), $columnName);
            }
        }

        for ($i = 0, $count = count($statements); $i < $count; $i++) {
            $statement = $statements[$i];
            list($isSubqueryRequired, $assembledSections) = $statement->prepareSections($columnNames);

            if ($i > 0) {
                $sql .= "\n UNION\n";
            }

            $sql .= Statement::assemble($isSubqueryRequired, $columnNames, $assembledSections);
        }

        return $sql;
    }

    /*
     * Queries dataset.
     * SQL is generated using data from request object. Result is formatted by a formatter
     */
    public function queryDataset(DataControllerCallContext $callcontext, DatasetQueryRequest $request) {
        $datasetName = $request->getDatasetName();
        LogHelper::log_info(t('Querying SQL-based dataset: @datasetName', array('@datasetName' => $datasetName)));

        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $this->getExtension('adjustRequest')->adjustDatasetQueryRequest($this, $request);

        $dataset = $metamodel->getDataset($datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $statements = $this->prepareDatasetQueryStatements($callcontext, $request);
        $sql = $this->assembleDatasetQueryStatements($callcontext, $request, $statements);

        // applying sorting
        if (isset($request->sortingConfigurations)) {
            $adjustedColumns = NULL;
            foreach ($request->sortingConfigurations as $sortingConfiguration) {
                // TODO try to use the same functionality for list and cube requests
                $adjustedColumn = DataSourceColumnNameHelper::generateFromParameterElements(
                    $this->getMaximumEntityNameLength(), $sortingConfiguration->getColumnName());
                // adjusting direction of the sorting
                if (!$sortingConfiguration->isSortAscending) {
                    $adjustedColumn = $adjustedColumn . ' DESC';
                }

                $adjustedColumns[] = $adjustedColumn;
            }

            if (count($adjustedColumns) > 0) {
                $sql .= "\n ORDER BY " . implode(', ', $adjustedColumns);
            }
        }

        // applying pagination
        $this->applyPagination($request, $sql);

        LogHelper::log_info(new StatementLogMessage('dataset.query', $sql));
        return $this->executeQuery($callcontext, $datasource, $sql);
    }

    /*
     * Counts dataset records.
     * Note: a formatter is not used by this implementation
     */
    public function countDatasetRecords(DataControllerCallContext $callcontext, DatasetCountRequest $request) {
        $datasetName = $request->getDatasetName();
        LogHelper::log_notice(t('Counting SQL-based dataset records: @datasetName', array('@datasetName' => $datasetName)));

        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $this->getExtension('adjustRequest')->adjustDatasetCountRequest($this, $request);

        $dataset = $metamodel->getDataset($datasetName);
        $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

        $statements = $this->prepareDatasetQueryStatements($callcontext, $request);

        return $this->countRecords($callcontext, $datasource, $statements);
    }

    protected function prepareCubeQueryStatement(DataControllerCallContext $callcontext, AbstractCubeQueryRequest $request) {
        $engine = QueryEngineFactory::getInstance()->getHandler();

        return $engine->generateStatement($this, $callcontext, $request);
    }

    public function queryCube(DataControllerCallContext $callcontext, CubeQueryRequest $request) {
        $cubeName = $request->getCubeName();
        LogHelper::log_info(t('Querying SQL-based cube: @cubeName', array('@cubeName' => $cubeName)));

        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $this->getExtension('adjustRequest')->adjustCubeQueryRequest($this, $request);

        $callcontext->columnMapping = NULL;

        $cube = $metamodel->getCube($cubeName);

        $factsDataset = $metamodel->getDataset($cube->factsDatasetName);
        $datasource = $environment_metamodel->getDataSource($factsDataset->datasourceName);

        $engine = QueryEngineFactory::getInstance()->getHandler();
        $generationContext = $engine->prepareStatementGenerationContext($request, $cube);

        // aliases for tables
        $TABLE_ALIAS__JOIN = 'j';
        $tableJoinIndex = 0;

        // preparing statement which aggregates data
        $aggrStatement = $this->prepareCubeQueryStatement($callcontext, $request);
        list($isSubqueryRequired, $assembledAggregationSections) = $aggrStatement->prepareSections(NULL);

        // assembling portion of SQL which is responsible for aggregation
        if (isset($request->referencedRequests)) {
            $joinStatement = $aggrStatement;
            // changing alias of first table. This new alias is expected the following code to join with lookup tables
            $joinStatement->updateTableAlias($joinStatement->tables[0]->alias, $TABLE_ALIAS__JOIN);
        }
        else {
            $joinStatement = new Statement();
            $aggregationTableSection = new SubquerySection(
                Statement::assemble($isSubqueryRequired, NULL, $assembledAggregationSections, SelectStatementPrint::INDENT__SUBQUERY, FALSE),
                $TABLE_ALIAS__JOIN);
            $joinStatement->tables[] = $aggregationTableSection;
        }

        // adding support for dimension columns
        if (isset($request->dimensions)) {
            foreach ($request->dimensions as $requestDimension) {
                $dimensionName = $requestDimension->name;
                $dimension = $cube->findDimension($dimensionName);

                // we do not need to map the column. It was done in prepareCubeQueryStatement()
                $dimensionDatabaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements($this->getMaximumEntityNameLength(), $dimensionName);

                // adding support for dimension column
                $dimensionColumn = new ColumnSection($dimensionDatabaseColumnName);
                $dimensionColumn->requestColumnIndex = $requestDimension->requestColumnIndex;
                $dimensionColumn->visible = isset($requestDimension->requestColumnIndex);

                $aggregationTableSection->columns[] = $dimensionColumn;

                if (!isset($requestDimension->columns)) {
                    continue;
                }

                // preparing list of columns which are accessed by this dataset
                $usedColumnNames = NULL;
                $dimensionColumnAliasMapping = NULL;
                foreach ($requestDimension->columns as $requestColumn) {
                    $responseColumnName = ParameterNameHelper::assemble($dimensionName, $requestColumn->name);
                    $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                        $this->getMaximumEntityNameLength(), $dimensionName, $requestColumn->name);
                    $callcontext->columnMapping[$databaseColumnName] = $responseColumnName;

                    ArrayHelper::addUniqueValue($usedColumnNames, $requestColumn->name);
                    $dimensionColumnAliasMapping[$requestColumn->name] = $databaseColumnName;
                }

                $isJoinWithDimensionDatasetRequired = $generationContext->dimensionJoinPhase[__DefaultQueryEngine_StatementGenerationContext::DIMENSION_JOIN_PHASE__GROUPING_WITH_LOOKUP_AFTER][$dimensionName];
                if ($isJoinWithDimensionDatasetRequired) {
                    $tableJoinIndex++;
                    $dimensionTableAlias = $TABLE_ALIAS__JOIN . $tableJoinIndex;

                    $dimensionDataset = $metamodel->getDataset($dimension->datasetName);

                    $isDimensionKeyColumnAdded = ArrayHelper::addUniqueValue($usedColumnNames, $dimension->key);

                    $dimensionStatement = $this->prepareDatasetSourceStatement($callcontext, $request, $dimensionDataset, $usedColumnNames);

                    // updating dimension statement table aliases
                    $dimensionStatement->addTableAliasPrefix($dimensionTableAlias);

                    foreach ($dimensionStatement->tables as $table) {
                        if (!isset($table->columns)) {
                            $table->columns = array(); // We do not need any columns
                        }
                    }

                    // updating dimension statement column aliases
                    foreach ($requestDimension->columns as $requestColumn) {
                        $oldColumnAlias = $requestColumn->name;
                        $newColumnAlias = $dimensionColumnAliasMapping[$oldColumnAlias];

                        $dimensionTableSection = $dimensionStatement->getColumnTable($oldColumnAlias, TRUE);
                        $dimensionColumnSection = $dimensionTableSection->findColumnByAlias($oldColumnAlias);
                        if (isset($dimensionColumnSection)) {
                            $dimensionColumnSection->alias = $newColumnAlias;
                        }
                        else {
                            $dimensionColumnSection = new ColumnSection($oldColumnAlias, $newColumnAlias);
                            $dimensionTableSection->columns[] = $dimensionColumnSection;
                        }
                        $dimensionColumnSection->requestColumnIndex = $requestColumn->requestColumnIndex;
                    }

                    // adding condition to join with 'main' statement
                    $dimensionKeyTableSection = $dimensionStatement->getColumnTable($dimension->key);
                    $dimensionKeyTableSection->conditions[] = new JoinConditionSection(
                        $dimension->key, new TableColumnConditionSectionValue($TABLE_ALIAS__JOIN, $dimensionDatabaseColumnName));
                    // merging with 'main' statement
                    $joinStatement->merge($dimensionStatement);

                    // we do not need to return dimension key column
                    if ($isDimensionKeyColumnAdded && isset($dimensionKeyTableSection)) {
                        // FIXME this code does not work in the following case:
                        //   - our lookup dataset is fact dataset
                        //   - we need to work with project_id column from that dataset
                        //   - the column is present in *_facts and contains numeric value
                        //   - the column is present in *_c_project_id table and contains numeric value
                        //   - column 'value' in *_c_project_id table assigned an alias project_id
                        //   - more about implementation is in ReferenceDimensionDatasetAssembler
                        //   - the code is partially fixed by using $visibleOnly parameter
                        $tableSection = $dimensionStatement->getColumnTable($dimension->key, TRUE);
                        $keyColumn = $tableSection->findColumnByAlias($dimension->key);
                        if (isset($keyColumn)) {
                            $keyColumn->visible = FALSE;
                        }
                    }
                }
                else {
                    foreach ($requestDimension->columns as $requestColumn) {
                        $oldColumnAlias = $requestColumn->name;
                        $newColumnAlias = $dimensionColumnAliasMapping[$oldColumnAlias];

                        $column = new ColumnSection($newColumnAlias);
                        $column->requestColumnIndex = $requestColumn->requestColumnIndex;

                        $aggregationTableSection->columns[] = $column;
                    }
                }
            }
        }

        $isJoinUsed = $tableJoinIndex > 0;

        if ($isJoinUsed) {
            // adding measures
            if (isset($request->measures)) {
                foreach ($request->measures as $requestMeasure) {
                    $measureName = $requestMeasure->name;

                    // we do not need to map the column. It was done in prepareCubeQueryStatement()
                    $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                        $this->getMaximumEntityNameLength(), $measureName);

                    $measureSection = new ColumnSection($databaseColumnName);
                    $measureSection->requestColumnIndex = $requestMeasure->requestColumnIndex;

                    $aggregationTableSection->columns[] = $measureSection;
                }
            }

            list($isSubqueryRequired, $assembledJoinSections) = $joinStatement->prepareSections(NULL);
            $sql = Statement::assemble($isSubqueryRequired, NULL, $assembledJoinSections);
        }
        else {
            $sql = Statement::assemble($isSubqueryRequired, NULL, $assembledAggregationSections);
        }

        // applying sorting
        if (isset($request->sortingConfigurations)) {
            $adjustedColumns = NULL;
            foreach ($request->sortingConfigurations as $sortingConfiguration) {
                // TODO try to use the same functionality for list and cube requests
                $adjustedColumn = DataSourceColumnNameHelper::generateFromParameterElements(
                    $this->getMaximumEntityNameLength(),
                    $sortingConfiguration->rootName, $sortingConfiguration->leafName);
                // adjusting direction of the sorting
                if (!$sortingConfiguration->isSortAscending) {
                    $adjustedColumn = $adjustedColumn . ' DESC';
                }

                $adjustedColumns[] = $adjustedColumn;
            }

            if (count($adjustedColumns) > 0) {
                $sql .= "\n ORDER BY " . implode(', ', $adjustedColumns);
            }
        }

        // applying pagination
        $this->applyPagination($request, $sql);

        // processing prepared sql and returning data
        LogHelper::log_info(new StatementLogMessage('cube.query', $sql));
        return $this->executeQuery($callcontext, $datasource, $sql);
    }

    /*
     * Counts cube records.
     * A statement is prepared to use facts table only. Joins with dimension (lookup) datasets are not performed
     * Note: a formatter is not used by this implementation
     */
    public function countCubeRecords(DataControllerCallContext $callcontext, CubeCountRequest $request) {
        $cubeName = $request->getCubeName();
        LogHelper::log_notice(t('Counting SQL-based cube records: @cubeName', array('@cubeName' => $cubeName)));

        $environment_metamodel = data_controller_get_environment_metamodel();
        $metamodel = data_controller_get_metamodel();

        $this->getExtension('adjustRequest')->adjustCubeCountRequest($this, $request);

        $cube = $metamodel->getCube($cubeName);

        $factsDataset = $metamodel->getDataset($cube->factsDatasetName);
        $datasource = $environment_metamodel->getDataSource($factsDataset->datasourceName);

        $statement = $this->prepareCubeQueryStatement($callcontext, $request);
        list($isSubqueryRequired, $assembledSections) = $statement->prepareSections(NULL);

        $statement = new Statement();
        $statement->tables[] = new SubquerySection(Statement::assemble($isSubqueryRequired, NULL, $assembledSections));

        return $this->countRecords($callcontext, $datasource, array($statement));
    }

    /*
     * Utility function to count number of records based on list of statements
     */
    protected function countRecords(DataControllerCallContext $callcontext, DataSourceMetaData $datasource, array $statements) {
        $countIdentifier = 'record_count';

        $TABLE_ALIAS__COUNT = 'c';
        $count = count($statements);

        $requestedColumnNames = array(); // No columns are requested

        $sql = '';
        for ($i = 0; $i < $count; $i++) {
            $statement = $statements[$i];

            list($isSubqueryRequired, $assembledSections) = $statement->prepareSections($requestedColumnNames);

            if ($i > 0) {
                $sql .= "\n UNION\n";
            }
            $sql .= ($isSubqueryRequired)
                ? "SELECT COUNT(*) AS $countIdentifier\n  FROM ("
                    . Statement::assemble(FALSE, NULL, $assembledSections, SelectStatementPrint::INDENT__SUBQUERY, FALSE)
                    . ') ' . $TABLE_ALIAS__COUNT
                : Statement::assemble(
                    FALSE, NULL,
                    new AssembledSections(
                        "COUNT(*) AS $countIdentifier",
                        $assembledSections->from,
                        $assembledSections->where,
                        $assembledSections->groupBy,
                        $assembledSections->having));
        }
        if ($count > 1) {
            $tableAlias = $TABLE_ALIAS__COUNT . '_sum';
            $sql = "SELECT SUM($tableAlias.$countIdentifier) AS $countIdentifier\n  FROM ("
                . StringHelper::indent($sql, SelectStatementPrint::INDENT__SUBQUERY, TRUE)
                . ") $tableAlias";
        }

        LogHelper::log_info(new StatementLogMessage('*.count', $sql));
        $records = $this->executeQuery($callcontext, $datasource, $sql);

        return $records[0][$countIdentifier];
    }

    // FIXME mark as protected once we delete ReferenceDimensionDatasetAssembler class
    public function assembleDatasetSourceStatement(AbstractQueryRequest $request, DatasetMetaData $dataset, array $columnNames = NULL) {
        $datasetSourceType = DatasetSourceTypeFactory::getInstance()->detectSourceType($dataset);

        $handler = DatasetSourceTypeFactory::getInstance()->getHandler($datasetSourceType);

        $statement = $handler->assemble($this, $request, $dataset, $columnNames);
        if (!isset($statement)) {
            throw new IllegalStateException(t(
            	'Could not prepare source statement for the dataset: %datasetName',
                array('%datasetName' => $dataset->publicName)));
        }

        return $statement;
    }

    protected function assembleConnectedDatasetSourceStatement(DataControllerCallContext $callcontext, AbstractQueryRequest $request, ReferenceLink $link, array $columnNames, array $linkExecutionStack = NULL) {
        $TABLE_ALIAS__LINK = 'l';

        $nestedLinkExecutionStack = $linkExecutionStack;
        $nestedLinkExecutionStack[] = $link;

        $selectedColumnNames = ReferenceLinkBuilder::selectReferencedColumnNames4ReferenceLink($nestedLinkExecutionStack, $columnNames);

        // some columns could be mapped to the same column in a dataset. Removing duplicates
        $datasetColumnNames = NULL;
        if (isset($selectedColumnNames)) {
            foreach ($selectedColumnNames as $selectedColumnName) {
                if (isset($datasetColumnNames) && in_array($selectedColumnName, $datasetColumnNames)) {
                    continue;
                }

                $datasetColumnNames[] = $selectedColumnName;
            }
        }

        $statement = $this->assembleDatasetSourceStatement($request, $link->dataset, $datasetColumnNames);

        $linkTableAliasPrefix = $TABLE_ALIAS__LINK . $link->linkId;
        $statement->addTableAliasPrefix($linkTableAliasPrefix);

        // adding columns which we use to join with parent dataset
        if (!$link->isRoot()) {
            foreach ($link->columnNames as $columnName) {
                $joinColumnAlias = DataSourceColumnNameHelper::generateFromParameterElements(
                    $this->getMaximumEntityNameLength(),
                    ReferencePathHelper::assembleReference($link->dataset->name, $columnName));
                $joinTable = $statement->findColumnTable($columnName);
                if (!isset($joinTable)) {
                    $joinTable = $statement->tables[0];
                }
                $joinColumn = $joinTable->findColumnByAlias($joinColumnAlias);
                if (!isset($joinColumn)) {
                    $joinTable->columns[] = new ColumnSection($columnName, $joinColumnAlias);
                }
            }
        }

        // adding columns which we use to join with nested datasets
        if (isset($link->nestedLinks)) {
            foreach ($link->nestedLinks as $nestedLink) {
                foreach ($nestedLink->parentColumnNames as $parentColumnName) {
                    $parentColumnAlias = DataSourceColumnNameHelper::generateFromParameterElements(
                        $this->getMaximumEntityNameLength(),
                        ReferencePathHelper::assembleReference($link->dataset->name, $parentColumnName));
                    $joinTable = $statement->getColumnTable($parentColumnName);
                    $joinColumn = $joinTable->findColumnByAlias($parentColumnAlias);
                    if (!isset($joinColumn)) {
                        $joinTable->columns[] = new ColumnSection($parentColumnName, $parentColumnAlias);
                    }
                }
            }
        }

        // collecting columns which we need to mark as invisible
        $shouldBeInvisibleColumns = NULL;
        foreach ($statement->tables as $table) {
            if (isset($table->columns)) {
                foreach ($table->columns as $column) {
                    $shouldBeInvisibleColumns[$table->alias][$column->alias] = $column;
                }
            }
            else {
                $table->columns = array(); // We do not need any columns
            }
        }

        // adding or making as visible columns which we need to return
        if (isset($selectedColumnNames)) {
            foreach ($selectedColumnNames as $originalColumnName => $selectedColumnName) {
                // we need to show only those columns which are requested.
                // All intermediate columns (which are used to link with nested datasets) will not be shown
                if (in_array($originalColumnName, $columnNames)) {
                    $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements($this->getMaximumEntityNameLength(), $originalColumnName);
                    $table = $statement->getColumnTable($selectedColumnName, TRUE);
                    $column = $table->findColumnByAlias($databaseColumnName);
                    if (isset($column)) {
                        $column->visible = TRUE;
                        unset($shouldBeInvisibleColumns[$table->alias][$column->alias]);
                    }
                    else {
                        $column = $table->findColumnByAlias($selectedColumnName);
                        if (isset($column)) {
                            // adding clone of the same column with another alias
                            $column = clone $column;
                            $column->visible = TRUE;
                            $column->alias = $databaseColumnName;
                        }
                        else {
                            $column = new ColumnSection($selectedColumnName, $databaseColumnName);
                        }

                        $table->columns[] = $column;
                    }

                    $callcontext->columnMapping[$databaseColumnName] = $originalColumnName;
                }
            }
        }

        if (isset($shouldBeInvisibleColumns)) {
            foreach ($shouldBeInvisibleColumns as $tableColumns) {
                foreach ($tableColumns as $column) {
                    $column->visible = FALSE;
                }
            }
        }

        // supporting nested links
        if (isset($link->nestedLinks)) {
            foreach ($link->nestedLinks as $nestedLink) {
                $nestedStatement = $this->assembleConnectedDatasetSourceStatement($callcontext, $request, $nestedLink, $columnNames, $nestedLinkExecutionStack);

                foreach ($nestedLink->parentColumnNames as $referencePointColumnIndex => $parentColumnName) {
                    // preparing parent table alias
                    $parentColumnAlias = DataSourceColumnNameHelper::generateFromParameterElements(
                        $this->getMaximumEntityNameLength(),
                        ReferencePathHelper::assembleReference($link->dataset->name, $parentColumnName));
                    $parentTableAlias = $statement->getColumnTable($parentColumnAlias)->alias;

                    // linking with parent
                    $nestedColumnName = $nestedLink->columnNames[$referencePointColumnIndex];
                    $nestedColumnAlias = DataSourceColumnNameHelper::generateFromParameterElements(
                        $this->getMaximumEntityNameLength(),
                        ReferencePathHelper::assembleReference($nestedLink->dataset->name, $nestedColumnName));
                    $nestedStatement->getColumnTable($nestedColumnAlias)->conditions[] = new JoinConditionSection(
                        $nestedColumnName,
                        new TableColumnConditionSectionValue($parentTableAlias, $parentColumnName));
                }

                $statement->merge($nestedStatement);
            }
        }

        return $statement;
    }

    // FIXME should be protected
    /*
     * Prepares a statement object for dataset source.
     * The statement generation based on dataset source type
     */
    public function prepareDatasetSourceStatement(DataControllerCallContext $callcontext, AbstractQueryRequest $request, DatasetMetaData $dataset, array $columnNames = NULL) {
        $statement = NULL;

        // preparing list of datasets which we need to work with
        $referencePaths = ReferenceLinkBuilder::selectReferencedColumnNames($columnNames);
        if (isset($referencePaths)) {
            $linkBuilder = new ReferenceLinkBuilder();
            $link = $linkBuilder->prepareReferenceBranches($dataset->name, $referencePaths);

            $statement = $this->assembleConnectedDatasetSourceStatement($callcontext, $request, $link, $columnNames);
        }
        else {
            $statement = $this->assembleDatasetSourceStatement($request, $dataset, $columnNames);
        }

        return $statement;
    }

    /*
     * Adds pagination to SQL statement.
     * Database specific extension has to be provided for this functionality to work
     */
    public function applyPagination(AbstractQueryRequest $request, &$sql) {
        if ((isset($request->startWith) && ($request->startWith > 0)) || isset($request->limit)) {
            $this->getExtension('applyPagination')->apply($this, $sql, $request->startWith, $request->limit);
        }
    }

    /*
     * Executes SELECT statement.
     * Database specific extension has to be provided for this functionality to work
     * Output is formatted using a formatter
     */
    public function executeQuery(DataControllerCallContext $callcontext, DataSourceMetaData $datasource, $sql) {
        $records = $this->executeQueryStatement(
            $callcontext, $datasource, $sql,
            new __SQLDataSourceHandler__QueryExecutionCallbackProxy($this->prepareQueryStatementExecutionCallbackInstance()));

        $count = count($records);
        LogHelper::log_info(t('Processed @count record(s)', array('@count' => $count)));
        LogHelper::log_debug($records);

        return $records;
    }

    /*
     * Preparing an instance of a callback class which is used to integrate with database native API
     */
    public function prepareQueryStatementExecutionCallbackInstance() {
        return $this->getExtension('executeQueryStatement_callback');
    }
}

abstract class __SQLDataSourceHandler__AbstractQueryCallbackProxy extends AbstractObject {

    protected $callback = NULL;

    public function __construct(AbstractQueryStatementExecutionCallback $callback) {
        parent::__construct();
        $this->callback = $callback;
    }

    public function prepareMetaData(DataControllerCallContext $callcontext, $connection, $statement) {
        $dataset = new DatasetMetaData();

        for ($i = 0, $columnCount = $this->callback->getColumnCount($connection, $statement); $i < $columnCount; $i++) {
            $column = $this->callback->getColumnMetaData($connection, $statement, $i);
            if ($column === FALSE) {
                throw new IllegalArgumentException(t('The column with the index does not exist: %columnIndex', array('%columnIndex' => $i)));
            }

            $column->name = strtolower($column->name);
            $column->columnIndex = $i;

            // preparing column type
            if (!isset($column->type->databaseType)) {
                throw new UnsupportedOperationException(t(
                    'Undefined database data type for the column: %columnName',
                    array('%columnName' => $column->name)));
            }
            $this->callback->generateColumnType($column);
            if (!isset($column->type->applicationType)) {
                throw new UnsupportedOperationException(t(
                    'Unsupported data type for %columnName column: %datatype',
                    array('%columnName' => $column->name, '%datatype' => $column->type->databaseType)));
            }

            // support for column mapping
            $column->alias = isset($callcontext->columnMapping[$column->name])
                ? $callcontext->columnMapping[$column->name]
                : $column->name;

            // FIXME eliminate the following block once direct connection is used for uploaded files
            // checking if the column is a system column which should be invisible
            if (substr_compare($column->name, DatasetSystemColumnNames::COLUMN_NAME_PREFIX, 0, strlen(DatasetSystemColumnNames::COLUMN_NAME_PREFIX)) === 0) {
                $column->visible = FALSE;
            }

            $dataset->registerColumnInstance($column);
        }

        return $dataset;

    }

    abstract public function callback(DataControllerCallContext $callcontext, $connection, $statement);
}

class __SQLDataSourceHandler__QueryMetaDataCallbackProxy extends __SQLDataSourceHandler__AbstractQueryCallbackProxy {

    public function callback(DataControllerCallContext $callcontext, $connection, $statement) {
        return $this->prepareMetaData($callcontext, $connection, $statement);
    }
}

class __SQLDataSourceHandler__QueryExecutionCallbackProxy extends __SQLDataSourceHandler__AbstractQueryCallbackProxy {

    public function callback(DataControllerCallContext $callcontext, $connection, $statement) {
        $datatypeFactory = DataTypeFactory::getInstance();

        $dataset = $this->prepareMetaData($callcontext, $connection, $statement);

        $records = NULL;
        $this->callback->fetchAllRecords($connection, $statement, $records);

        if (isset($records)) {
            foreach ($records as &$record) {
                foreach ($dataset->columns as $column) {
                    $record[$column->alias] = $datatypeFactory->getHandler($column->type->applicationType)->castValue($record[$column->columnIndex]);
                    unset($record[$column->columnIndex]);
                }
            }
        }

        return $records;
    }
}
