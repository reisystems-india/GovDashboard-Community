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


class AssembledSections extends AbstractObject {

    public $select = NULL;
    public $from = NULL;
    public $where = NULL;
    public $groupBy = NULL;
    public $having = NULL;

    public function __construct($select, $from, $where, $groupBy, $having) {
        parent::__construct();
        $this->select = $select;
        $this->from = $from;
        $this->where = $where;
        $this->groupBy = $groupBy;
        $this->having = $having;
    }
}


class Statement extends AbstractObject implements SelectStatement {

    protected static $TABLE_ALIAS__SOURCE = 'a';

    public $tables = NULL;
    public $conditions = NULL;
    public $groupByColumns = NULL;
    public $havingConditions = NULL;

    public function newTable($name, $alias = NULL) {
        $table = new TableSection($name, $alias);

        $this->tables[] = $table;

        return $table;
    }

    public function newSubquery($sql, $alias = NULL) {
        $subquery = new SubquerySection($sql, $alias);

        $this->tables[] = $subquery;

        return $subquery;
    }

    public function merge(Statement $statement) {
        ArrayHelper::merge($this->tables, $statement->tables);
        ArrayHelper::merge($this->conditions, $statement->conditions);
        ArrayHelper::merge($this->groupByColumns, $statement->groupByColumns);
        ArrayHelper::merge($this->havingConditions, $statement->havingConditions);
    }

    public function addTableAliasPrefix($prefix) {
        if (isset($this->tables)) {
            foreach ($this->tables as $table) {
                $oldTableAlias = $table->alias;
                $newTableAlias = $prefix . (isset($oldTableAlias) ? '_' . $oldTableAlias : '');

                $this->updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }
    }

    public function updateTableAlias($oldTableAlias, $newTableAlias) {
        if ($oldTableAlias === $newTableAlias) {
            return;
        }

        if (isset($this->tables)) {
            foreach ($this->tables as $table) {
                $table->event_updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }
        if (isset($this->conditions)) {
            foreach ($this->conditions as $condition) {
                $condition->event_updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }
        if (isset($this->groupByColumns)) {
            foreach ($this->groupByColumns as $groupByColumn) {
                $groupByColumn->event_updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }
        if (isset($this->havingConditions)) {
            foreach ($this->havingConditions as $condition) {
                $condition->event_updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }
    }

    public function getTable($tableAlias) {
        if (isset($this->tables)) {
            foreach ($this->tables as $table) {
                if ($table->alias === $tableAlias) {
                    return $table;
                }
            }
        }

        LogHelper::log_debug($this);
        throw new IllegalArgumentException(t('Could not find table by the alias: %tableAlias', array('%tableAlias' => $tableAlias)));
    }

    public function findColumnTable($columnName, $visibleOnly = FALSE) {
        // if we have only one table for the statement we do not check if the column is available or not.
        // We do not have any other choice. There is no table left to support the column.
        // If column is not correct SQL statement will fail during execution
        if (count($this->tables) === 1) {
            return $this->tables[0];
        }

        $potentialTableIndexes = NULL;
        $exactKeyedMatchSourceTables = NULL;
        $exactMatchSourceTables = NULL;
        $potentialMatchSourceTables = NULL;
        foreach ($this->tables as $tableIndex => $table) {
            if (isset($table->columns)) {
                $potentialTableIndex = NULL;

                $columnByAlias = $table->findColumnByAlias($columnName);
                if ($visibleOnly && isset($columnByAlias) && !$columnByAlias->visible) {
                    $potentialTableIndex = $tableIndex;
                    $columnByAlias = NULL;
                }

                $column = $table->findColumn($columnName);
                if ($visibleOnly && isset($column) && !$column->visible) {
                    $potentialTableIndex = $tableIndex;
                    $column = NULL;
                }

                $selectedColumn = $column;
                if (isset($column)) {
                    if (isset($columnByAlias)) {
                        if (isset($columnByAlias->key) && $columnByAlias->key) {
                            $selectedColumn = $columnByAlias;
                        }
                    }
                }
                else {
                    $selectedColumn = $columnByAlias;
                }

                if (isset($selectedColumn)) {
                    if (isset($selectedColumn->key) && $selectedColumn->key) {
                        $exactKeyedMatchSourceTables[] = $table;
                    }
                    else {
                        $exactMatchSourceTables[] = $table;
                    }
                }
                elseif (isset($potentialTableIndex)) {
                    $potentialTableIndexes[] = $potentialTableIndex;
                }
            }
            else {
                $potentialMatchSourceTables[] = $table;
            }
        }

        $selectedTable = NULL;

        $exactKeyedMatchCount = count($exactKeyedMatchSourceTables);
        if ($exactKeyedMatchCount === 0) {
            $exactMatchCount = count($exactMatchSourceTables);
            if ($exactMatchCount === 0) {
                if (count($potentialMatchSourceTables) === 1) {
                    $selectedTable = $potentialMatchSourceTables[0];
                }
            }
            elseif ($exactMatchCount === 1) {
                $selectedTable = $exactMatchSourceTables[0];
            }
            else {
                // selecting first for now
                $selectedTable = $exactMatchSourceTables[0];
            }
        }
        elseif ($exactKeyedMatchCount === 1) {
            $selectedTable = $exactKeyedMatchSourceTables[0];
        }
        else {
            // selecting first for now
            $selectedTable = $exactKeyedMatchSourceTables[0];
        }

        if (!isset($selectedTable)) {
            $selectedTable = $this->findColumnTableByReferencePath($columnName);
        }

        if (!isset($selectedTable) && (count($potentialTableIndexes) == 1)) {
            $potentialTableIndex = $potentialTableIndexes[0];
            $selectedTable = $this->tables[$potentialTableIndex];
        }

        return $selectedTable;
    }

    protected function findColumnTableByReferencePath($referencePath) {
        $tableCount = count($this->tables);

        $references = ReferencePathHelper::splitReferencePath($referencePath);
        $references = array_reverse($references);

        $tableIndex = NULL;
        for ($i = 0, $refcount = count($references); $i < $refcount - 1; $i++) {
            $reference = $references[$i];
            list($referencedDatasetName, $referencedColumnName) = ReferencePathHelper::splitReference($reference);
            if (!isset($referencedDatasetName)) {
                continue;
            }

            $nextReference = $references[$i + 1];
            list($nextReferencedDatasetName, $nextReferencedColumnName) = ReferencePathHelper::splitReference($nextReference);
            if (!isset($nextReferencedDatasetName)) {
                continue;
            }

            $branchIndex = NULL;
            for ($j = (isset($tableIndex) ? $tableIndex : 0); $j < $tableCount; $j++) {
                $table = $this->tables[$j];
                if ($table->dataset->name == $referencedDatasetName) {
                    for ($k = $j + 1; $k < $tableCount; $k++) {
                        $nextTable = $this->tables[$k];
                        if (($nextTable->dataset->name == $nextReferencedDatasetName)
                                && ($nextTable->conditions[0]->joinValue->tableAlias == $table->alias)
                                && ($nextTable->conditions[0]->joinValue->columnName == $referencedColumnName)) {
                            $branchIndex = $k;
                            break 2;
                        }
                    }
                }
            }
            if (!isset($branchIndex)) {
                return NULL;
            }
            $tableIndex = $branchIndex;
        }

        return isset($tableIndex) ? $this->tables[$tableIndex] : NULL;
    }

    public function getColumnTable($columnName, $visibleOnly = FALSE) {
        $table = $this->findColumnTable($columnName, $visibleOnly);
        if (!isset($table)) {
            LogHelper::log_debug($this);
            throw new IllegalArgumentException(t('Could not identify %columnName column in this statement', array('%columnName' => $columnName)));
        }

        return $table;
    }

    public function prepareSections(array $requestedColumnNames = NULL) {
        $retrieveAllColumns = !isset($requestedColumnNames);

        $tableCount = count($this->tables);
        if ($tableCount === 0) {
            throw new IllegalStateException(t('Tables have not been defined for this statement'));
        }

        if ($tableCount === 1) {
            $table = $this->tables[0];
            if ($table instanceof SubquerySection) {
                if (!isset($table->columns) && !isset($this->conditions) && !isset($this->groupByColumns) && !isset($this->havingConditions)) {
                    return array(TRUE, new AssembledSections(NULL, $table->body, NULL, NULL, NULL));
                }
            }
        }

        // we need to find each column in order to avoid using sub select
        $isColumnAccessible = TRUE;

        $columnNameUsage = NULL;
        if (!$retrieveAllColumns) {
            // preparing tables which support each requested column
            foreach ($requestedColumnNames as $columnName) {
                $columnNameUsage[$columnName] = array();
            }

            // preparing tables which support 'where' subject columns for which we did not select table alias
            if (isset($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    if (!isset($condition->subjectTableAlias)) {
                        $columnNameUsage[(($condition instanceof AbstractConditionSection) ? $condition->subjectColumnName : NULL)] = array();
                    }
                }
            }
        }

        // if the statement contains just one table we do not need to worry about columns
        if (!$retrieveAllColumns) {
            if (($tableCount === 1) && (($table = $this->tables[0]) instanceof TableSection)) {
                if (isset($columnNameUsage)) {
                    foreach ($columnNameUsage as &$usage) {
                        $usage[] = $table;
                    }
                    unset($usage);
                }
            }
            else {
                // calculating how many tables support each column
                for ($i = 0; ($i < $tableCount) && $isColumnAccessible; $i++) {
                    $table = $this->tables[$i];
                    if (isset($table->columns)) {
                        foreach ($table->columns as $column) {
                            if (isset($columnNameUsage[$column->alias])) {
                                $columnNameUsage[$column->alias][] = $table;
                            }
                        }
                    }
                    else {
                        if ($table instanceof TableSection) {
                            // list of columns is not provided for the table
                            // I hope that is because the table is just transitioning table to access data from other table
                            // I do not want to use $isColumnAccessible = FALSE until we have a use case
                        }
                        else {
                            // there is no access to columns for this type of table
                            $isColumnAccessible = FALSE;
                        }
                    }
                }

                // checking how many tables support each column
                if (isset($columnNameUsage)) {
                    foreach ($columnNameUsage as $sourceTables) {
                        if (count($sourceTables) !== 1) {
                            $isColumnAccessible = FALSE;
                            break;
                        }
                    }
                }
            }
        }

        $useTableNameAsAlias = $tableCount > 1;

        $indentSectionElement = str_pad('', SelectStatementPrint::INDENT__SECTION_ELEMENT);

        $indexedSelect = $unindexedSelect = NULL;
        if ($isColumnAccessible && !$retrieveAllColumns) {
            // all columns are linked to corresponding tables
            foreach ($requestedColumnNames as $columnName) {
                $sourceTable = $columnNameUsage[$columnName][0];
                $column = $sourceTable->findColumnByAlias($columnName);
                if (isset($column) && !$column->visible) {
                    continue;
                }

                $columnTableAlias = $sourceTable->prepareColumnTableAlias($useTableNameAsAlias);

                // if there is no such columns we return just requested column name
                if (isset($column)) {
                    $assembledColumn = $column->assemble($columnTableAlias);
                    if (isset($column->requestColumnIndex)) {
                        $indexedSelect[$column->requestColumnIndex][] = $assembledColumn;
                    }
                    else {
                        $unindexedSelect[] = $assembledColumn;
                    }
                }
                else {
                    $unindexedSelect[] = ColumnNameHelper::combineColumnName($columnTableAlias, $columnName);
                }
            }
        }
        else {
            foreach ($this->tables as $table) {
                // we ignore a table which provides list of columns but the list is empty
                if (isset($table->columns) && (count($table->columns) === 0)) {
                    continue;
                }

                $columnTableAlias = $table->prepareColumnTableAlias($useTableNameAsAlias);
                if (isset($table->columns)) {
                    // returning only columns which are configured for the table
                    foreach ($table->columns as $column) {
                        if (!$column->visible) {
                            continue;
                        }

                        $assembledColumn = $column->assemble($columnTableAlias);
                        if (isset($column->requestColumnIndex)) {
                            $indexedSelect[$column->requestColumnIndex][] = $assembledColumn;
                        }
                        else {
                            $unindexedSelect[] = $assembledColumn;
                        }
                    }
                }
                else {
                    // returning all columns from the table
                    $unindexedSelect[] = ColumnNameHelper::combineColumnName($columnTableAlias, '*');
                }
            }
        }
        // sorting select columns by request column index. If the index is not provided, corresponding column is placed at the end of the list
        $sortedSelect = array();
        if (isset($indexedSelect)) {
            ksort($indexedSelect);
            foreach ($indexedSelect as $assembledColumns) {
                $sortedSelect = array_merge($sortedSelect, $assembledColumns);
            }
        }
        if (isset($unindexedSelect)) {
            $sortedSelect= array_merge($sortedSelect, $unindexedSelect);
        }
        $select = (count($sortedSelect) > 0) ? implode(",\n$indentSectionElement", $sortedSelect) : NULL;

        $from = NULL;
        for ($i = 0; $i < $tableCount; $i++) {
            $table = $this->tables[$i];
            if ($i > 0) {
                $from .= "\n" . $indentSectionElement . 'LEFT OUTER JOIN ';
            }
            $from .= $table->assemble();
            // assembling join conditions
            if ($i === 0) {
                if (isset($table->conditions)) {
                    throw new IllegalStateException(t('Join conditions should not be defined for %tableName table', array('%tableName' => $table->name)));
                }
            }
            else {
                if (isset($table->conditions)) {
                    $from .= ' ON ';
                    for ($j = 0, $c = count($table->conditions); $j < $c; $j++) {
                        $condition = $table->conditions[$j];
                        if ($j > 0) {
                            $from .= ' AND ';
                        }

                        $from .= $condition->assemble($this, $table, $useTableNameAsAlias);
                    }
                }
                else {
                    throw new IllegalStateException(t('Join conditions were not defined for %tableName table', array('%tableName' => $table->name)));
                }
            }
        }

        $where = NULL;
        if (isset($this->conditions)) {
            foreach ($this->conditions as $condition) {
                if (isset($where)) {
                    $where .= "\n   AND ";
                }

                if (isset($condition->subjectTableAlias)) {
                    $subjectTable = $this->getTable($condition->subjectTableAlias);
                }
                else {
                    // We do not have table alias
                    // Solution: find a column in a table and use the table alias and the column name to generate the condition
                    if (isset($columnNameUsage)) {
                        $sourceTables = $columnNameUsage[(($condition instanceof AbstractConditionSection) ? $condition->subjectColumnName : NULL)];
                        $subjectTable = (count($sourceTables) === 1) ? $sourceTables[0] : NULL;
                    }
                    else {
                        $subjectTable = $this->findColumnTable($condition->subjectColumnName);
                    }
                    if (!isset($subjectTable)) {
                        throw new IllegalStateException(t(
                        	'Condition for %columnName column cannot be prepared',
                            array('%columnName' => $condition->subjectColumnName)));
                    }
                }

                $where .= $condition->assemble($this, $subjectTable, $useTableNameAsAlias);
            }
        }

        $groupBy = NULL;
        if (isset($this->groupByColumns)) {
            foreach ($this->groupByColumns as $groupByColumn) {
                if (isset($groupBy)) {
                    $groupBy .= ', ';
                }
                $groupBy .= $groupByColumn->assemble(NULL);
            }
        }

        $having = NULL;
        if (isset($this->havingConditions)) {
            foreach ($this->havingConditions as $havingCondition) {
                if (isset($having)) {
                    $having .= "\n   AND ";
                }
                $having .= $havingCondition->assemble($this, FALSE);
            }
        }

        $isSubqueryRequired = !$isColumnAccessible;

        return array($isSubqueryRequired, new AssembledSections($select, $from, $where, $groupBy, $having));
    }

    public static function assemble($isSubqueryRequired, array $columnNames = NULL, AssembledSections $assembledSections = NULL, $indent = 0, $indentBlockStart = TRUE) {
        if (isset($assembledSections->select)) {
            $sql = "SELECT $assembledSections->select"
                . "\n  FROM $assembledSections->from"
                . (isset($assembledSections->where) ? "\n WHERE $assembledSections->where" : '')
                . (isset($assembledSections->groupBy) ? "\n GROUP BY $assembledSections->groupBy" : '')
                . (isset($assembledSections->having) ? "\nHAVING $assembledSections->having" : '');
        }
        else {
            if (isset($assembledSections->where) || isset($assembledSections->groupBy) || isset($assembledSections->having)) {
                throw new UnsupportedOperationException(t("Additional sections could not be added to assembled SQL statement"));
            }

            $sql = $assembledSections->from;
        }

        if ($isSubqueryRequired) {
            $assembledSubquerySections = new AssembledSections(
                self::$TABLE_ALIAS__SOURCE . '.' . (isset($columnNames) ? implode(', ' . self::$TABLE_ALIAS__SOURCE . '.', $columnNames) : '*'),
                '(' . StringHelper::indent($sql, SelectStatementPrint::INDENT__SUBQUERY, FALSE) . ') ' . self::$TABLE_ALIAS__SOURCE,
                NULL,
                NULL,
                NULL);
            $sql = self::assemble(FALSE, NULL, $assembledSubquerySections, $indent, $indentBlockStart);
        }
        else {
            $sql = StringHelper::indent($sql, $indent, $indentBlockStart);
        }

        return $sql;
    }
}
