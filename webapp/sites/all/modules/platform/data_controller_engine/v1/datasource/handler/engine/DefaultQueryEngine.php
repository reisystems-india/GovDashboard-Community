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


class DefaultQueryEngine extends AbstractQueryEngine {

    protected static $TABLE_ALIAS__REFERENCED = 'r';

    public function newSelectStatement() {
        return new Statement();
    }

    /*
     * Utility function for prepareSelectedCubeQueryStatement() method.
     * Collects information about selected columns and applied conditions for each dataset for further join operation
     */
    private function registerDatasetConfig(array &$datasetConfigs = NULL, $index, DatasetMetaData $dataset = NULL, $columnName = NULL, AbstractConditionSection $condition = NULL) {
        if (isset($datasetConfigs[$index])) {
            $datasetConfig = $datasetConfigs[$index];
        }
        else {
            // TODO create a class
            $datasetConfig = new stdClass();
            $datasetConfig->dataset = NULL;
            $datasetConfig->usedColumnNames = NULL;
            $datasetConfig->conditions = NULL;

            $datasetConfigs[$index] = $datasetConfig;
        }

        if (isset($dataset)) {
            if (isset($datasetConfig->dataset)) {
                if ($datasetConfig->dataset->name !== $dataset->name) {
                    throw new IllegalStateException(t(
                    	'Inconsistent dataset configuration: [@datasetName, @tableDatasetName]',
                        array('@datasetName' => $dataset->publicName, '@tableDatasetName' => $datasetConfig->dataset->publicName)));
                }
            }
            else {
                $datasetConfig->dataset = $dataset;
            }
        }

        if (isset($columnName)) {
            ArrayHelper::addUniqueValue($datasetConfig->usedColumnNames, $columnName);
        }

        if (isset($condition)) {
            $datasetConfig->conditions[] = $condition;
        }
    }

    // TODO eliminate this function??? It needs to be part of SQL generation functionality ... or not
    public function prepareStatementGenerationContext(AbstractCubeQueryRequest $request, CubeMetaData $cube) {
        $metamodel = data_controller_get_metamodel();

        $generationContext = new __DefaultQueryEngine_StatementGenerationContext();

        // checking if we use any non-additive measures in this request
        $foundNonAdditiveMeasure = FALSE;
        foreach ($cube->measures as $measure) {
            $measureName = $measure->name;
            $additivity = $measure->getAdditivity();

            if (($additivity == MeasureAdditivity::ADDITIVE) || ($additivity == MeasureAdditivity::SEMI_ADDITIVE)) {
                continue;
            }

            $selectedMeasure = $request->findMeasure($measureName);
            $queriedMeasure = $request->findMeasureQuery($measureName);
            if (!isset($selectedMeasure) && !isset($queriedMeasure)) {
                continue;
            }

            $foundNonAdditiveMeasure = TRUE;
            break;
        }

        // checking if only columns with non-unique values were requested for some dimensions
        if (isset($cube->dimensions)) {
            foreach ($cube->dimensions as $dimension) {
                $dimensionName = $dimension->name;

                $selectedDimension = $request->findDimension($dimensionName);
                $queriedDimension = $request->findDimensionQuery($dimensionName);
                if (!isset($selectedDimension) && !isset($queriedDimension)) {
                    continue;
                }

                // checking if at least one returned column is unique
                $selectedUniqueColumn = NULL;
                if (isset($selectedDimension)) {
                    $selectedColumnNames = $selectedDimension->getColumnNames();
                    if (isset($selectedColumnNames)) {
                        // 02/26/2014 it is possible to have columns but not lookup dataset. the case is when we connect with extension table (PK-to-PK connection)
                        if (isset($dimension->datasetName)) {
                            $selectedUniqueColumn = FALSE;
                        }
                        else {
                            // 07/30/2014 it could be just a column expression ($column->branches)
                            foreach ($selectedColumnNames as $selectedColumnName) {
                                // TODO do not try to optimize query generation for 'complex' references
                                $references = ReferencePathHelper::splitReferencePath($selectedColumnName);
                                if (count($references) > 1) {
                                    $selectedUniqueColumn = FALSE;
                                    break;
                                }
                            }
                        }
                    }
                }

                $queriedByColumns = FALSE;
                if (isset($queriedDimension)) {
                    $queriedColumnNames = $queriedDimension->getColumnNames();
                    if (isset($queriedColumnNames)) {
                        if (isset($dimension->datasetName)) {
                            $queriedByColumns = TRUE;
                        }
                    }
                }

                $joinWithLookup = FALSE;
                if (!isset($selectedUniqueColumn) && !$queriedByColumns) {
                    // there is not need to join with lookup. We do not work with any columns
                    $joinWithLookup = NULL;
                }
                elseif ($foundNonAdditiveMeasure) {
                    $joinWithLookup = TRUE;
                }
                elseif ($queriedByColumns) {
                    $joinWithLookup = TRUE;
                }
                elseif ($selectedUniqueColumn === FALSE) {
                    $joinWithLookup = TRUE;
                }

                // FIXME to temporary resolve an issue when joining datasets. We need to change a way SQL engine works
                if (isset($request->referencedRequests) || $request->referenced) {
                    $joinWithLookup = TRUE;
                }

                $generationContext->dimensionJoinPhase[__DefaultQueryEngine_StatementGenerationContext::DIMENSION_JOIN_PHASE__GROUPING_INITIAL][$dimensionName] =
                    isset($joinWithLookup) ? $joinWithLookup : FALSE;
                $generationContext->dimensionJoinPhase[__DefaultQueryEngine_StatementGenerationContext::DIMENSION_JOIN_PHASE__GROUPING_WITH_LOOKUP_AFTER][$dimensionName] =
                    isset($joinWithLookup) ? !$joinWithLookup : FALSE;
            }
        }

        return $generationContext;
    }

    protected function detectColumnNameOwner(ColumnMetaData $column, array $columnNames = NULL) {
        $factsColumnNames = $dimensionColumnNames = NULL;

        foreach ($columnNames as $columnName) {
            if ($column->findBranch($columnName) == NULL) {
                $dimensionColumnNames[] = $columnName;
            }
            else {
                $factsColumnNames[] = $columnName;
            }

        }

        return array($factsColumnNames, $dimensionColumnNames);
    }

    /*
     * Prepares a statement object which represents a request to facts table
     */
    protected function prepareSelectedCubeQueryStatement(AbstractSQLDataSourceQueryHandler $datasourceHandler, DataControllerCallContext $callcontext, AbstractCubeQueryRequest $request) {
        $metamodel = data_controller_get_metamodel();

        // loading cube configuration
        $cubeName = $request->getCubeName();
        $cube = $metamodel->getCube($cubeName);
        $factsDataset = $metamodel->getDataset($cube->factsDatasetName);

        $generationContext = $this->prepareStatementGenerationContext($request, $cube);

        $TABLE_ALIAS__SOURCE = 's';

        // to store configuration for each accessed table
        $datasetConfigs = NULL;

        // preparing facts dataset configuration
        $this->registerDatasetConfig($datasetConfigs, 0, $factsDataset, NULL, NULL);

        $columnReferenceFactory = new CompositeColumnReferenceFactory(array(
            $cube->factsDataset,
            new FormulaReferenceFactory($request->getFormulas())));
        $expressionAssembler = new FormulaExpressionAssembler($columnReferenceFactory);

        // statement for aggregation portion of final sql
        $aggrStatement = new Statement();

        // adding support for facts dataset column queries
        $factsDatasetColumnQueries = $request->findFactsDatasetColumnQueries();
        if (isset($factsDatasetColumnQueries)) {
            foreach ($factsDatasetColumnQueries as $queryColumn) {
                foreach ($queryColumn->values as $value) {
                    $this->registerDatasetConfig($datasetConfigs, 0, NULL, $queryColumn->name, NULL);
                    $aggrStatement->conditions[] = new WhereConditionSection(
                        $TABLE_ALIAS__SOURCE . '0',
                        $queryColumn->name,
                        new ExactConditionSectionValue(
                            $datasourceHandler->formatOperatorValue($callcontext, $request, $factsDataset->name, $queryColumn->name, $value)));
                }
            }
        }

        $possibleDimensions = NULL;
        // adding dimensions from a cube
        if (isset($cube->dimensions)) {
            foreach ($cube->dimensions as $dimension) {
                $possibleDimensions[$dimension->name] = $dimension;
            }
        }
        // creating 'virtual' dimensions from formulas
        if (isset($request->dimensions)) {
            foreach ($request->dimensions as $selectedDimension) {
                // it is predefined dimension
                if (isset($possibleDimensions[$selectedDimension->name])) {
                    continue;
                }

                // it could be a 'virtual' dimension defined by a formula
                $formula = $request->findFormula($selectedDimension->name);
                if (!isset($formula)) {
                    continue;
                }

                // defining 'virtual dimension'
                $formulaDimension = new DimensionMetaData();
                $formulaDimension->name = $selectedDimension->name;
                $formulaDimension->attributeColumnName = $selectedDimension->name;

                $possibleDimensions[$formulaDimension->name] = $formulaDimension;
            }
        }

        // FIXME why do we start with 1?
        $tableIndex = 1 + count($possibleDimensions);

        // preparing list of columns which are required to group data for the aggregation
        $aggrSelectColumns = NULL;
        // preparing list of measures which are calculated in the aggregation, preparing support for measure conditions
        $aggrSelectMeasureColumns = NULL;

        if (isset($possibleDimensions)) {
            foreach ($possibleDimensions as $dimension) {
                $dimensionName = $dimension->name;

                $selectedDimension = $request->findDimension($dimensionName);
                $queriedDimension = $request->findDimensionQuery($dimensionName);
                if (!isset($selectedDimension) && !isset($queriedDimension)) {
                    continue;
                }

                $factsColumn = $factsDataset->findColumn($dimension->attributeColumnName);

                $selectedColumnNames = isset($selectedDimension) ? $selectedDimension->getColumnNames() : NULL;
                list($selectedFactsColumnNames, $selectedDimensionColumnNames) = isset($selectedColumnNames) && isset($factsColumn)
                    ? $this->detectColumnNameOwner($factsColumn, $selectedColumnNames)
                    : array(NULL, $selectedColumnNames);

                $queriedColumnNames = isset($queriedDimension) ? $queriedDimension->getColumnNames() : NULL;
                list($queriedFactsColumnNames, $queriedDimensionColumnNames) = isset($queriedColumnNames) && isset($factsColumn)
                    ? $this->detectColumnNameOwner($factsColumn, $queriedColumnNames)
                    : array(NULL, $queriedColumnNames);

                $isJoinWithDimensionDatasetRequired = (isset($queriedDimension) && isset($queriedColumnNames) && (isset($dimension->datasetName) || isset($queriedDimensionColumnNames)))
                    || (
                        isset($generationContext->dimensionJoinPhase[__DefaultQueryEngine_StatementGenerationContext::DIMENSION_JOIN_PHASE__GROUPING_INITIAL][$dimensionName])
                            && $generationContext->dimensionJoinPhase[__DefaultQueryEngine_StatementGenerationContext::DIMENSION_JOIN_PHASE__GROUPING_INITIAL][$dimensionName]);

                $dimensionDataset = NULL;
                if (isset($dimension->datasetName)) {
                    $dimensionDataset = $metamodel->getDataset($dimension->datasetName);
                }
                elseif ($isJoinWithDimensionDatasetRequired) {
                    // 02/26/2014 there could be a case when dimension dataset does not exist but we try to connect with extension table using PK-to-PK connection
                    $dimensionDataset = $factsDataset;
                }
                $keyColumnName = isset($dimensionDataset)
                    ? $dimensionDataset->getKeyColumn()->name
                    : (isset($dimension->key) ? $dimension->key : NULL);

                // joining with dimension dataset ... if necessary
                if ($isJoinWithDimensionDatasetRequired) {
                    // registering the dimension dataset
                    $tableIndex--;
                    $this->registerDatasetConfig($datasetConfigs, $tableIndex, $dimensionDataset, NULL, NULL);

                    if (isset($queriedDimensionColumnNames)) {
                        foreach ($queriedDimension->columns as $queryColumn) {
                            if (!isset($queryColumn->name)) {
                                continue;
                            }
                            if (!in_array($queryColumn->name, $queriedDimensionColumnNames)) {
                                continue;
                            }

                            $this->registerDatasetConfig($datasetConfigs, $tableIndex, NULL, $queryColumn->name, NULL);
                            foreach ($queryColumn->values as $value) {
                                $aggrStatement->conditions[] = new WhereConditionSection(
                                    $TABLE_ALIAS__SOURCE . $tableIndex,
                                    $queryColumn->name,
                                    new ExactConditionSectionValue(
                                        $datasourceHandler->formatOperatorValue($callcontext, $request, $dimensionDataset->name, $queryColumn->name, $value)));
                            }
                        }
                    }

                    // selected columns are part of the dimension dataset
                    if (isset($selectedDimensionColumnNames)) {
                        foreach ($selectedDimensionColumnNames as $columnName) {
                            $responseColumnName = ParameterNameHelper::assemble($dimensionName, $columnName);
                            $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                                $datasourceHandler->getMaximumEntityNameLength(),
                                ($request->referenced ? ReferencePathHelper::assembleReference($factsDataset->name, $dimensionName) : $dimensionName),
                                $columnName);
                            $callcontext->columnMapping[$databaseColumnName] = $responseColumnName;

                            $this->registerDatasetConfig($datasetConfigs, $tableIndex, $dimensionDataset, $columnName, NULL);

                            $aggrSelectColumns[$tableIndex][] = new ColumnSection($columnName, $databaseColumnName);
                        }
                    }
                }

                if (isset($selectedFactsColumnNames) || isset($selectedDimension->requestColumnIndex)) {
                    $responseColumnName = ParameterNameHelper::assemble($dimensionName);
                    $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                        $datasourceHandler->getMaximumEntityNameLength(),
                        ($request->referenced ? ReferencePathHelper::assembleReference($factsDataset->name, $dimensionName) : $dimensionName));
                    $callcontext->columnMapping[$databaseColumnName] = $responseColumnName;

                    // selected columns are part of facts table
                    if (isset($selectedFactsColumnNames)) {
                        foreach ($selectedFactsColumnNames as $columnName) {
                            $responseSelectedColumnName = ParameterNameHelper::assemble($dimensionName, $columnName);
                            $databaseSelectedColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                                $datasourceHandler->getMaximumEntityNameLength(),
                                ($request->referenced ? ReferencePathHelper::assembleReference($factsDataset->name, $dimensionName) : $dimensionName),
                                $columnName);
                            $callcontext->columnMapping[$databaseSelectedColumnName] = $responseSelectedColumnName;

                            $this->registerDatasetConfig($datasetConfigs, 0, NULL, $columnName, NULL);

                            $aggrSelectColumns[0][] = new ColumnSection($columnName, $databaseSelectedColumnName);
                        }
                    }
                    elseif (isset($selectedDimension->requestColumnIndex)) {
                        $formula = $request->findFormula($dimension->name);
                        if (isset($formula)) {
                            $expression = $expressionAssembler->assemble($formula);

                            $column = new CompositeColumnSection($expression, $databaseColumnName);
                            if (isset($formula->isMeasure) && !$formula->isMeasure) {
                                $aggrSelectColumns[0][] = $column;
                            }
                            else {
                                $aggrSelectMeasureColumns[] = $column;
                            }
                        }
                        else {
                            $column = new ColumnSection(
                                DataSourceColumnNameHelper::generateFromParameterElements($datasourceHandler->getMaximumEntityNameLength(), $dimension->attributeColumnName),
                                $databaseColumnName);
                            $aggrSelectColumns[0][] = $column;
                        }

                        $this->registerDatasetConfig($datasetConfigs, 0, NULL, $dimension->attributeColumnName, NULL);
                    }
                }

                // adding facts table conditions
                if (isset($queriedDimension)) {
                    foreach ($queriedDimension->columns as $queryColumn) {
                        if (isset($queryColumn->name)) {
                            if (!isset($queriedFactsColumnNames) || !in_array($queryColumn->name, $queriedFactsColumnNames)) {
                                continue;
                            }
                        }

                        $queryColumnName = isset($queryColumn->name) ? $queryColumn->name : $dimension->attributeColumnName;

                        $this->registerDatasetConfig($datasetConfigs, 0, NULL, $queryColumnName, NULL);
                        foreach ($queryColumn->values as $value) {
                            $aggrStatement->conditions[] = new WhereConditionSection(
                                $TABLE_ALIAS__SOURCE . 0,
                                $queryColumnName,
                                new ExactConditionSectionValue(
                                    $datasourceHandler->formatOperatorValue($callcontext, $request, $factsDataset->name, $queryColumnName, $value)));
                        }
                    }
                }

                // linking the dimension dataset with master source
                if ($isJoinWithDimensionDatasetRequired) {
                    $this->registerDatasetConfig($datasetConfigs, 0, NULL, $dimension->attributeColumnName, NULL);
                    $this->registerDatasetConfig(
                        $datasetConfigs,
                        $tableIndex, NULL,
                        $keyColumnName,
                        new JoinConditionSection(
                            $keyColumnName,
                            new TableColumnConditionSectionValue($TABLE_ALIAS__SOURCE . '0', $dimension->attributeColumnName)));
                }
            }
        }

        // preparing a list of required measures
        $selectedMeasureNames = NULL;
        if (isset($request->measures)) {
            foreach ($request->measures as $measure) {
                $selectedMeasureNames[$measure->name] = TRUE;
            }
        }
        $measureQueries = $request->findMeasureQueries();
        if (isset($measureQueries)) {
            foreach ($measureQueries as $query) {
                $selectedMeasureNames[$query->name] = TRUE;
            }
        }
        // adding measures to the statement
        if (isset($selectedMeasureNames)) {
            foreach ($selectedMeasureNames as $measureName => $flag) {
                $measureExpression = NULL;
                // checking cube first
                $cubeMeasure = $cube->findMeasure($measureName);
                if (isset($cubeMeasure)) {
                    $measureExpression = $cubeMeasure->getFunction();
                }
                else {
                    $formula = $request->findFormula($measureName);
                    $measureExpression = $expressionAssembler->assemble($formula);
                }

                $selectedMeasure = $request->findMeasure($measureName);
                $queriedMeasure = $request->findMeasureQuery($measureName);

                if ($request->referenced) {
                    $measureName = ReferencePathHelper::assembleReference($factsDataset->name, $measureName);
                }
                $databaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                    $datasourceHandler->getMaximumEntityNameLength(), $measureName);

                $columnSection = new CompositeColumnSection($measureExpression, $databaseColumnName);

                if (isset($selectedMeasure)) {
                    $callcontext->columnMapping[$databaseColumnName] = $measureName;
                    $aggrSelectMeasureColumns[] = $columnSection;
                }

                if (isset($queriedMeasure)) {
                    foreach ($queriedMeasure->values as $value) {
                        $aggrStatement->havingConditions[] = new HavingConditionSection(
                            $columnSection,
                            new ExactConditionSectionValue(
                                $datasourceHandler->formatOperatorValue($callcontext, $request, $factsDataset->name, NULL, $value)));
                    }
                }

                // looking for possible columns in the measure function. We need to retrieve those from the database
                $columnNames = $columnSection->parseColumns();
                if (isset($columnNames)) {
                    foreach ($columnNames as $columnName) {
                        $this->registerDatasetConfig($datasetConfigs, 0, NULL, $columnName, NULL);
                    }
                }
            }
        }

        // sorting configuration to support joins in correct order
        ksort($datasetConfigs, SORT_NUMERIC);

        // preparing dataset source statements
        foreach ($datasetConfigs as $orderIndex => $datasetConfig) {
            $tableStatement = $datasourceHandler->prepareDatasetSourceStatement($callcontext, $request, $datasetConfig->dataset, $datasetConfig->usedColumnNames);

            // adding join conditions
            if (isset($datasetConfig->conditions)) {
                foreach ($datasetConfig->conditions as $condition) {
                    $tableStatement->getColumnTable($condition->subjectColumnName)->conditions[] = $condition;
                }
            }

            // BLOCK 1: finding tables to which we want to attach columns which participate in aggregation
            // the code would be simpler if we supported several aliases per column
            // if we move the logic to BLOCK 3 getColumnTable(, TRUE) will not work
            $selectedAggregationTables = NULL;
            if (isset($aggrSelectColumns[$orderIndex])) {
                $tableSelectColumns = $aggrSelectColumns[$orderIndex];
                foreach ($tableSelectColumns as $aggrColumnIndex => $tableSelectColumn) {
                    // FIXME check other places to understand why I need to use parameter with TRUE value
                    // looking for a table in the statement which provides the column for SELECT section
                    if ($tableSelectColumn instanceof CompositeColumnSection) {
                        $tableSection = $tableStatement->tables[0];
                    }
                    else {
                        $tableSection = $tableStatement->getColumnTable($tableSelectColumn->name, TRUE);
                    }

                    $selectedAggregationTables[$orderIndex][$aggrColumnIndex] = $tableSection;
                }
            }

            // BLOCK 2: we do not need to return any columns from the table by default
            foreach ($tableStatement->tables as $table) {
                if (isset($table->columns)) {
                    foreach ($table->columns as $column) {
                        $column->visible = FALSE;
                    }
                }
                else {
                    $table->columns = array(); // We do not need any columns
                }
            }

            // preparing measures which we want to return. Adding those measures to facts table
            if (($orderIndex == 0) && isset($aggrSelectMeasureColumns)) {
                foreach ($aggrSelectMeasureColumns as $tableSelectMeasureColumn) {
                    $columnNames = $tableSelectMeasureColumn->parseColumns();
                    // searching which table contains the column
                    $tableSection = NULL;
                    if (isset($columnNames)) {
                        foreach ($columnNames as $columnName) {
                            $formattedColumnAlias = DataSourceColumnNameHelper::generateFromParameterElements(
                                $datasourceHandler->getMaximumEntityNameLength(), $columnName);
                            foreach ($tableStatement->tables as $table) {
                                if ($table->findColumnByAlias($formattedColumnAlias) != NULL) {
                                    if (isset($tableSection)) {
                                        if ($tableSection->alias !== $table->alias) {
                                            // FIXME we should not have such functionality
                                            // checking if the same column is used for several times in a table under different aliases
                                            $tableSectionColumns = $tableSection->findColumns($formattedColumnAlias);
                                            $tableColumns = $table->findColumns($formattedColumnAlias);
                                            $isTableSelected = FALSE;
                                            if (($tableSectionColumns > 0) && ($tableColumns > 0)) {
                                                if ($tableSectionColumns > $tableColumns) {
                                                    $tableSection = $table;
                                                    $isTableSelected = TRUE;
                                                }
                                                elseif ($tableColumns > $tableSectionColumns) {
                                                    $isTableSelected = TRUE;
                                                }
                                            }

                                            if (!$isTableSelected) {
                                                throw new UnsupportedOperationException(t('Aggregation function bases on several tables'));
                                            }
                                        }
                                    }
                                    else {
                                        $tableSection = $table;
                                    }
                                }
                            }
                        }
                    }
                    if (!isset($tableSection)) {
                        $tableSection = $tableStatement->tables[0];
                    }

                    $tableSelectMeasureColumn->attachTo($tableSection);
                }
            }

            // updating join statement table aliases
            $sourceTableAlias = $TABLE_ALIAS__SOURCE . $orderIndex;
            foreach ($tableStatement->tables as $table) {
                $oldTableAlias = $table->alias;
                $newTableAlias = $sourceTableAlias . (isset($oldTableAlias) ? '_' . $oldTableAlias : '');
                $tableStatement->updateTableAlias($oldTableAlias, $newTableAlias);

                // TODO Review. Probably is not needed anymore. Updating statement conditions which are used to join levels
                foreach ($datasetConfigs as $nextOrderIndex => $nextDatasetConfig) {
                    if (($nextOrderIndex <= $orderIndex) || !isset($nextDatasetConfig->conditions)) {
                        continue;
                    }

                    foreach ($nextDatasetConfig->conditions as $condition) {
                        if (($condition instanceof JoinConditionSection)
                                && ($condition->joinValue instanceof TableColumnConditionSectionValue)
                                && ($condition->joinValue->tableAlias === $sourceTableAlias)
                                && (($table->findColumn($condition->joinValue->columnName) != NULL) || (count($tableStatement->tables) === 1))) {
                            $condition->joinValue->tableAlias = $newTableAlias;
                        }
                    }
                }

                // updating aggregation statement conditions
                if (isset($aggrStatement->conditions)) {
                    foreach ($aggrStatement->conditions as $condition) {
                        if ($condition->subjectTableAlias != $sourceTableAlias) {
                            continue;
                        }

                        $tableColumn = $table->findColumn($condition->subjectColumnName);
                        if (!isset($tableColumn)) {
                            continue;
                        }

                        // checking if any other table in the statement support the column as an alias
                        $otherColumnFound = FALSE;
                        foreach ($tableStatement->tables as $subjectColumnTable) {
                            $subjectColumn = $subjectColumnTable->findColumnByAlias($condition->subjectColumnName);
                            if (isset($subjectColumn) && ($subjectColumn instanceof ColumnSection)) {
                                if ($subjectColumnTable->alias != $table->alias) {
                                    $condition->subjectTableAlias = $sourceTableAlias . (isset($subjectColumnTable->alias) ? '_' . $subjectColumnTable->alias : '');
                                    $condition->subjectColumnName = $subjectColumn->name;

                                    $otherColumnFound = TRUE;
                                }
                            }
                        }

                        if (!$otherColumnFound) {
                            $condition->subjectTableAlias = $newTableAlias;
                            if ($tableColumn instanceof ColumnSection) {
                                // $condition->subjectColumnName = $tableColumn->name;
                            }
                        }
                    }
                }
            }

            // BLOCK 3: preparing the table columns which we want to return
            if (isset($aggrSelectColumns[$orderIndex])) {
                $tableSelectColumns = $aggrSelectColumns[$orderIndex];
                foreach ($tableSelectColumns as $aggrColumnIndex => $tableSelectColumn) {
                    $tableSection = $selectedAggregationTables[$orderIndex][$aggrColumnIndex];

                    $relatedConditions = NULL;
                    if (isset($aggrStatement->conditions)) {
                        foreach ($aggrStatement->conditions as $condition) {
                            if (($tableSelectColumn instanceof ColumnSection)
                                    && ($condition->subjectColumnName == $tableSelectColumn->name)
                                    && ($condition->subjectTableAlias == $tableSection->alias)) {
                                $relatedConditions[] = $condition;
                            }
                        }
                    }

                    $attachedColumn = $tableSelectColumn->attachTo($tableSection);
                    if (isset($relatedConditions)) {
                        foreach ($relatedConditions as $relatedCondition) {
                            $relatedCondition->subjectColumnName = $attachedColumn->alias;
                        }
                    }

                    $aggrStatement->groupByColumns[] = new GroupByColumnSection($attachedColumn);
                }
            }

            $aggrStatement->merge($tableStatement);
        }

        return $aggrStatement;
    }

    protected function prepareReferencedCubeQueryStatement(
        AbstractSQLDataSourceQueryHandler $datasourceHandler, DataControllerCallContext $callcontext,
            Statement $combinedStatement, array $datasetMappedCubeRequests, ReferenceLink $link) {

        if (!isset($link->nestedLinks)) {
            return;
        }

        $metamodel = data_controller_get_metamodel();

        foreach ($link->nestedLinks as $referencedLink) {
            $referencedRequest = NULL;
            if (isset($datasetMappedCubeRequests[$referencedLink->dataset->name])) {
                $referencedRequest = clone $datasetMappedCubeRequests[$referencedLink->dataset->name];
            }
            else {
                // checking if there is corresponding cube for the referenced dataset
                $possibleReferencedCube = $metamodel->findCubeByDatasetName($referencedLink->dataset->name);
                if (isset($possibleReferencedCube)) {
                    $referencedRequest = new CubeQueryRequest($possibleReferencedCube->name);
                    $referencedRequest->referenced = TRUE;

                    $datasetMappedCubeRequests[$possibleReferencedCube->factsDatasetName] = $referencedRequest;
                }
            }

            if (isset($referencedRequest)) {
                // preparing parent cube
                $parentRequest = $datasetMappedCubeRequests[$link->dataset->name];
                $parentCubeName = $parentRequest->getCubeName();
                $parentCube = $metamodel->getCube($parentCubeName);

                // preparing referenced cube
                $referencedCubeName = $referencedRequest->getCubeName();
                $referencedCube = $metamodel->getCube($referencedCubeName);

                // adding required dimensions
                $joinConditions = NULL;
                foreach ($referencedLink->parentColumnNames as $columnIndex => $parentColumnName) {
                    // looking for a dimension in parent cube
                    $parentDimension = $parentCube->getDimensionByAttributeColumnName($parentColumnName);

                    // looking for a dimension in referenced cube
                    $referencedColumnName = $referencedLink->columnNames[$columnIndex];
                    $referencedDimension = $referencedCube->getDimensionByAttributeColumnName($referencedColumnName);

                    // checking if this dimension is part of query portion of parent request
                    $parentRequestDimensionQuery = $parentRequest->findDimensionQuery($parentDimension->name);
                    if (isset($parentRequestDimensionQuery)) {
                        // copying the query request to referenced cube
                        $referencedRequestDimensionQuery = new __AbstractCubeQueryRequest_DimensionQuery($referencedDimension->name);
                        $referencedRequestDimensionQuery->columns = $parentRequestDimensionQuery->columns;
                        $referencedRequest->importDimensionQueryFrom($referencedRequestDimensionQuery);
                    }

                    // checking if there is a related query for parent column name
                    $parentRequestFactsDatasetColumnQuery = $parentRequest->findFactsDatasetColumnQuery($parentColumnName);
                    if (isset($parentRequestFactsDatasetColumnQuery)) {
                        // copying the query request to referenced cube
                        $referencedRequest->addFactsDatasetColumnQueryValues($referencedColumnName, $parentRequestFactsDatasetColumnQuery->values);
                    }

                    // checking if this dimension is part of parent request
                    $parentRequestDimension = $parentRequest->findDimension($parentDimension->name);
                    if (!isset($parentRequestDimension)) {
                        // because this dimension is not in list of returned columns we should not use it to link with referenced cube
                        continue;
                    }

                    $referencedRequestDimension = $referencedRequest->addDimension(
                        NULL, // TODO support requestColumnIndex here
                        $referencedDimension->name);

                    $columnNames = $parentRequestDimension->getColumnNames();
                    if (isset($columnNames)) {
                        foreach ($columnNames as $columnName) {
                            $referencedRequestDimension->registerColumnName(
                                NULL, // TODO support requestColumnIndex here
                                $columnName);
                        }
                    }
                    else {
                        // to help the following loop to link cubes by dimension
                        $columnNames = array(NULL);
                    }

                    foreach ($columnNames as $columnName) {
                        $parentDatabaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                            $datasourceHandler->getMaximumEntityNameLength(),
                            ($parentRequest->referenced ? ReferencePathHelper::assembleReference($parentCube->factsDatasetName, $parentDimension->name) : $parentDimension->name),
                            $columnName);

                        $referencedDatabaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                            $datasourceHandler->getMaximumEntityNameLength(),
                            ReferencePathHelper::assembleReference($referencedCube->factsDatasetName, $referencedDimension->name),
                            $columnName);

                        $joinConditions[] = new JoinConditionSection(
                            $referencedDatabaseColumnName, new TableColumnConditionSectionValue(self::$TABLE_ALIAS__REFERENCED . $link->linkId, $parentDatabaseColumnName));
                    }
                }
                if (!isset($joinConditions)) {
                    throw new IllegalArgumentException(t(
                        'There is no common columns to join %datasetNameA and %datasetNameB datasets',
                        array('%datasetNameA' => $parentCube->publicName, '%datasetNameB' => $referencedCube->publicName)));
                }

                // preparing aggregation statement for referenced cube
                $referencedAggregationStatement = $this->prepareSelectedCubeQueryStatement($datasourceHandler, $callcontext, $referencedRequest);
                list($isSubqueryRequired, $assembledReferencedCubeSections) = $referencedAggregationStatement->prepareSections(NULL);
                $referencedCubeSubquerySection = new SubquerySection(
                    Statement::assemble($isSubqueryRequired, NULL, $assembledReferencedCubeSections, SelectStatementPrint::INDENT__LEFT_OUTER_JOIN__SUBQUERY, FALSE),
                    self::$TABLE_ALIAS__REFERENCED . $referencedLink->linkId);

                // preparing columns which are returned by referenced aggregation
                foreach ($referencedAggregationStatement->tables as $table) {
                    if (!isset($table->columns)) {
                        continue;
                    }

                    foreach ($table->columns as $column) {
                        if (!$column->visible) {
                            continue;
                        }

                        $referencedCubeSubquerySection->columns[] = new ColumnSection($column->alias);
                    }
                }

                // linking with parent cube
                foreach ($joinConditions as $joinCondition) {
                    // we do not need to return columns which are used to join with parent cube
                    $referencedCubeSubquerySection->getColumn($joinCondition->subjectColumnName)->visible = FALSE;

                    $referencedCubeSubquerySection->conditions[] = $joinCondition;
                }

                // adding to resulting statement
                $combinedStatement->tables[] = $referencedCubeSubquerySection;

                // applying referenced cubes measure conditions on resulting statement as well
                $measureQueries = $referencedRequest->findMeasureQueries();
                if (isset($measureQueries)) {
                    foreach ($measureQueries as $measureQuery) {
                        $measureName = ReferencePathHelper::assembleReference($referencedCube->factsDatasetName, $measureQuery->name);
                        $measureDatabaseColumnName = DataSourceColumnNameHelper::generateFromParameterElements(
                            $datasourceHandler->getMaximumEntityNameLength(), $measureName);

                        foreach ($measureQuery->values as $value) {
                            $combinedStatement->conditions[] = new WhereConditionSection(
                                self::$TABLE_ALIAS__REFERENCED . $referencedLink->linkId,
                                $measureDatabaseColumnName,
                                    new ExactConditionSectionValue(
                                        $datasourceHandler->formatOperatorValue($callcontext, $referencedRequest, $referencedCube->factsDatasetName, NULL, $value)));
                        }
                    }
                }
            }
            else {
                throw new UnsupportedOperationException(t('Cube joins using intermediate dataset is not supported yet'));

                // preparing statement for intermediate dataset
                $requiredColumnNames = $referencedLink->columnNames;
                $referencedIntermediateDatasetStatement = $datasourceHandler->prepareDatasetSourceStatement($callcontext, NULL, $referencedLink->dataset, $requiredColumnNames);

                // adding condition to join with parent statement
                $referencedIntermediateDatasetTableSection = $referencedIntermediateDatasetStatement->tables[0];
                foreach ($referencedLink->columnNames as $columnIndex => $referencedColumnName) {
                    $referencedDatabaseColumnName = $referencedColumnName;

                    $parentColumnName = $referencedLink->parentColumnNames[$columnIndex];
                    $parentDatabaseColumnName = $parentColumnName;

                    $referencedIntermediateDatasetTableSection->conditions[] = new JoinConditionSection(
                        $referencedDatabaseColumnName, new TableColumnConditionSectionValue(self::$TABLE_ALIAS__REFERENCED . $link->linkId, $parentDatabaseColumnName));
                }

                $combinedStatement->merge($referencedIntermediateDatasetStatement);
            }

            // recursively check nested levels
            $this->prepareReferencedCubeQueryStatement($datasourceHandler, $callcontext, $combinedStatement, $datasetMappedCubeRequests, $referencedLink);
        }
    }

    public function generateStatement(AbstractSQLDataSourceQueryHandler $datasourceHandler, DataControllerCallContext $callcontext, AbstractCubeQueryRequest $request) {
        $statement = $this->prepareSelectedCubeQueryStatement($datasourceHandler, $callcontext, $request);
        if (!isset($request->referencedRequests)) {
            return $statement;
        }

        $combinedStatement = new Statement();

        $metamodel = data_controller_get_metamodel();

        $cubeName = $request->getCubeName();
        $cube = $metamodel->getCube($cubeName);

        $datasetMappedCubeRequests = array($cube->factsDatasetName => $request);

        // preparing list of reference paths
        $referencePaths = NULL;
        foreach ($request->referencedRequests as $referencedRequest) {
            $referencedCubeName = $referencedRequest->getCubeName();
            $referencedCube = $metamodel->getCube($referencedCubeName);
            $referencedDatasetName = $referencedCube->factsDatasetName;

            $referencePath = ReferencePathHelper::assembleReference($referencedDatasetName, NULL);
            $referencePaths[$referencePath] = TRUE; // TRUE - required reference

            $datasetMappedCubeRequests[$referencedDatasetName] = $referencedRequest;
        }

        // finding ways to link the referenced cubes
        $linkBuilder = new ReferenceLinkBuilder();
        $link = $linkBuilder->prepareReferenceBranches($cube->factsDatasetName, $referencePaths);

        // preparing primary cube aggregation statement
        list($isSubqueryRequired, $assembledPrimaryCubeAggregationSections) = $statement->prepareSections(NULL);
        $primaryCubeAggregationTableSection = new SubquerySection(
            Statement::assemble($isSubqueryRequired, NULL, $assembledPrimaryCubeAggregationSections, SelectStatementPrint::INDENT__SUBQUERY, FALSE),
            self::$TABLE_ALIAS__REFERENCED . $link->linkId);

        // adding columns which are returned by primary aggregation
        foreach ($statement->tables as $table) {
            if (!isset($table->columns)) {
                continue;
            }

            foreach ($table->columns as $column) {
                if (!$column->visible) {
                    continue;
                }

                $primaryCubeAggregationTableSection->columns[] = new ColumnSection($column->alias);
            }
        }

        // registering primary cube statement in resulting statement
        $combinedStatement->tables[] = $primaryCubeAggregationTableSection;

        $this->prepareReferencedCubeQueryStatement($datasourceHandler, $callcontext, $combinedStatement, $datasetMappedCubeRequests, $link);

        return $combinedStatement;
    }
}


class __DefaultQueryEngine_StatementGenerationContext extends AbstractCallContext {

    const DIMENSION_JOIN_PHASE__GROUPING_INITIAL = 'grouping.I';
    const DIMENSION_JOIN_PHASE__GROUPING_WITH_LOOKUP_AFTER = 'grouping.II';

    public $dimensionJoinPhase = NULL;
}