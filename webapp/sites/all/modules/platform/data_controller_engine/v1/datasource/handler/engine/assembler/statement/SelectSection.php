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


class ColumnNameHelper {

    /*
     * Splits column name into table alias and table column name
     */
    public static function splitColumnName($columnName) {
        $index = strrpos($columnName, '.');

        return ($index === FALSE)
            ? array(NULL, $columnName)
            : array(substr($columnName, 0, $index), substr($columnName, $index + 1));
    }

    /*
     * Combines table alias and table column name
     */
    public static function combineColumnName($tableAlias, $columnName) {
        return isset($tableAlias) ? $tableAlias . '.' . $columnName : $columnName;
    }
}

abstract class AbstractColumnSection extends AbstractSection {

    public $requestColumnIndex = NULL;

    abstract public function assemble($tableAlias);
}

abstract class AbstractSelectColumnSection extends AbstractColumnSection implements SelectStatement_ColumnReference {

    public $alias = NULL;
    public $visible = TRUE;

    public function __construct($alias) {
        parent::__construct();
        $this->alias = $alias;
    }

    public function attachTo(AbstractTableSection $table) {}

    public function assemble($tableAlias) {
        $columnName = $this->assembleColumnName($tableAlias);

        return $columnName . (isset($this->alias) ? (' AS ' . $this->alias) : '');
    }

    abstract public function assembleColumnName($tableAlias);
}


class ColumnSection extends AbstractSelectColumnSection implements SelectStatement_Column {

    public $name = NULL;
    public $key = NULL;

    // TODO in the future it should be a reference to a table which contains this column
    // This functionality is used to support table alias in GROUP BY section which has a reference to this column
    // This functionality will be deleted when we implement more comprehensive object model for SQL generation
    protected $tableAlias = NULL;

    public function __construct($name, $alias = NULL) {
        parent::__construct(isset($alias) ? $alias : $name);
        $this->name = $name;
    }

    public function event_updateTableAlias($oldTableAlias, $newTableAlias) {
        parent::event_updateTableAlias($oldTableAlias, $newTableAlias);

        if ($this->tableAlias == $oldTableAlias) {
            $this->tableAlias = $newTableAlias;
        }
    }

    public function attachTo(AbstractTableSection $table) {
        list(, $columnName) = ReferencePathHelper::splitReference($this->name);

        $attachedColumn = $table->findColumnByAlias($columnName);
        if (isset($attachedColumn)) {
            if (isset($this->alias)) {
                $attachedColumn->alias = $this->alias;
            }
            $attachedColumn->visible = $this->visible;
        }
        else {
            $attachedColumn = $this;

            $table->columns[] = $attachedColumn;
        }

        return $attachedColumn;
    }

    public function assemble($tableAlias) {
        return $this->assembleColumnName($tableAlias) . (($this->name === $this->alias) ? '' : (' AS ' . $this->alias));
    }

    public function assembleColumnName($tableAlias) {
        $selectedTableAlias = $this->tableAlias;

        if (isset($selectedTableAlias)) {
            if (isset($tableAlias) && ($selectedTableAlias != $tableAlias)) {
                throw new UnsupportedOperationException();
            }
        }
        else {
            $this->tableAlias = $tableAlias;
            $selectedTableAlias = $tableAlias;
        }

        list(, $columnName) = ReferencePathHelper::splitReference($this->name);

        return ColumnNameHelper::combineColumnName($selectedTableAlias, $columnName);
    }
}


class CompositeColumnSection extends AbstractSelectColumnSection implements SelectStatement_CalculatedColumn {

    private $function = NULL;

    public function __construct($function, $alias) {
        parent::__construct($alias);
        $this->function = $function;
    }

    public function parseColumns() {
        $columnNames = NULL;

        $callback = new FormulaExpressionParserEventNotification__ColumnNameCollector();

        $parser = new FormulaExpressionParser(SQLFormulaExpressionHandler::LANGUAGE__SQL);
        $parser->parse($this->function, array($callback, 'collectColumnNames'), $columnNames);

        return $columnNames;
    }

    public function attachTo(AbstractTableSection $table) {
        $callback = new ColumnStatementCompositeEntityParser__ColumnNameUpdater($table, FALSE);

        $parser = new FormulaExpressionParser(SQLFormulaExpressionHandler::LANGUAGE__SQL);
        $this->function = $parser->parse($this->function, array($callback, 'updateColumnNames'));

        if (isset($table->alias)) {
            $this->event_updateTableAlias(NULL, $table->alias);
        }

        $table->columns[] = $this;

        return $this;
    }

    public function event_updateTableAlias($oldTableAlias, $newTableAlias) {
        parent::event_updateTableAlias($oldTableAlias, $newTableAlias);

        $callback = new ColumnStatementCompositeEntityParser__TableAliasUpdater($oldTableAlias, $newTableAlias, FALSE);

        $parser = new FormulaExpressionParser(SQLFormulaExpressionHandler::LANGUAGE__SQL);
        $this->function = $parser->parse($this->function, array($callback, 'updateTableAlias'));
    }

    public function assembleColumnName($tableAlias) {
        $callback = new ColumnStatementCompositeEntityParser__TableAliasUpdater(NULL, $tableAlias, TRUE);

        $parser = new FormulaExpressionParser(SQLFormulaExpressionHandler::LANGUAGE__SQL);
        return $parser->parse($this->function, array($callback, 'updateTableAlias'));
    }
}

