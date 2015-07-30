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


abstract class AbstractTableSection extends AbstractSection implements SelectStatement_TableReference {

    public $alias = NULL;
    public $columns = NULL;
    public $conditions = NULL;

    public function __construct($alias) {
        parent::__construct();
        $this->alias = $alias;
    }

    public function newColumn($name) {
        $column = new ColumnSection($name);

        $this->columns[] = $column;

        return $column;
    }

    public function newCalculatedColumn($sql, $alias = NULL) {
        $column = new CompositeColumnSection($sql, $alias);

        $this->columns[] = $column;

        return $column;
    }

    public function event_updateTableAlias($oldTableAlias, $newTableAlias) {
        parent::event_updateTableAlias($oldTableAlias, $newTableAlias);

        if ($oldTableAlias === $this->alias) {
            $this->alias = $newTableAlias;
        }

        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                $column->event_updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }

        if (isset($this->conditions)) {
            foreach ($this->conditions as $condition) {
                $condition->event_updateTableAlias($oldTableAlias, $newTableAlias);
            }
        }
    }

    public function findColumns($columnName) {
        $columns = NULL;

        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column instanceof ColumnSection) {
                    if ($column->name === $columnName) {
                        $columns[] = $column;
                    }
                }
            }
        }

        return $columns;
    }

    public function findColumn($columnName) {
        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column instanceof ColumnSection) {
                    if ($column->name === $columnName) {
                        return $column;
                    }
                }
                elseif ($column->alias === $columnName) {
                    return $column;
                }
            }
        }

        // looking for a column by alias
        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column instanceof ColumnSection) {
                    if ($column->alias === $columnName) {
                        return $column;
                    }
                }
            }
        }

        return NULL;
    }

    public function getColumn($columnName) {
        $column = $this->findColumn($columnName);
        if (!isset($column)) {
            LogHelper::log_debug($this->columns);
            throw new IllegalArgumentException(t(
            	'%columnName column has not been registered for the table: %tableAlias',
                array('%columnName' => $columnName, '%tableAlias' => $this->alias)));
        }

        return $column;
    }

    /**
     * @param $columnAlias
     * @return AbstractColumnSection
     */
    public function findColumnByAlias($columnAlias) {
        if (isset($this->columns)) {
            foreach ($this->columns as $column) {
                if ($column->alias === $columnAlias) {
                    return $column;
                }
            }
        }

        return NULL;
    }

    abstract public function prepareColumnTableAlias($useTableNameAsAlias);

    abstract public function assemble();
}

class TableSection extends AbstractTableSection implements SelectStatement_Table {

    public $name = NULL;

    public function __construct($name, $alias = NULL) {
        parent::__construct($alias);
        $this->name = $name;
    }

    public function prepareColumnTableAlias($useTableNameAsAlias) {
        return isset($this->alias) ? $this->alias : ($useTableNameAsAlias ? $this->name : NULL);
    }

    protected function assembleTableName() {
        return $this->name;
    }

    public function assemble() {
        return $this->assembleTableName() . (isset($this->alias) ? (' ' . $this->alias)  : '');
    }
}

// TODO delete the class once ReferenceDimensionDatasetAssembler is deleted. Review code in TableDatasetSourceTypeHandler too
class DatasetSection extends TableSection {

    public $dataset = NULL;

    public function __construct(DatasetMetaData $dataset, $alias = NULL) {
        parent::__construct($dataset->source, $alias);
        $this->dataset = $dataset;
    }

    protected function assembleTableName() {
        $assembledTableName = $this->name;

        $index = strpos($assembledTableName, '.');
        if ($index === FALSE) {
            $environment_metamodel = data_controller_get_environment_metamodel();

            $datasourceName = $this->dataset->datasourceName;
            $datasource = $environment_metamodel->getDataSource($datasourceName);

            $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

            $assembledTableName = assemble_database_entity_name($datasourceQueryHandler, $datasource->name, $assembledTableName);
        }

        return $assembledTableName;
    }

    public function findColumn($columnName) {
        list($referencedDatasetName, $referencedColumnName) = ReferencePathHelper::splitReference($columnName);

        return isset($referencedDatasetName)
            ? (($this->dataset->name == $referencedDatasetName) ? parent::findColumn($referencedColumnName) : NULL)
            : parent::findColumn($referencedColumnName);
    }
}

class SubquerySection extends AbstractTableSection implements SelectStatement_SubQuery {

    public $body = NULL;

    public function __construct($body, $alias = NULL) {
        parent::__construct($alias);
        $this->body = $body;
    }

    public function prepareColumnTableAlias($useTableNameAsAlias) {
        $columnTableAlias = $this->alias;
        if (!isset($columnTableAlias) && $useTableNameAsAlias) {
            throw new IllegalStateException(t('Sub query is required to have an alias'));
        }

        return $columnTableAlias;
    }

    public function assemble() {
        return '(' . $this->body . ')' . (isset($this->alias) ? (' ' . $this->alias)  : '');
    }
}
