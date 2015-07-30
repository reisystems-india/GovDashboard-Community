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


class ColumnStatementCompositeEntityParser__ColumnNameAdjuster extends AbstractObject {

    private $removeDelimiters;

    public function __construct($removeDelimiters) {
        parent::__construct();
        $this->removeDelimiters = $removeDelimiters;
    }

    public function adjustCallbackObject(ParserCallback $callback, &$callerSession) {
        $callback->removeDelimiters = $this->removeDelimiters;
    }
}

class ColumnStatementCompositeEntityParser__ColumnNameUpdater extends ColumnStatementCompositeEntityParser__ColumnNameAdjuster {

    private $table;

    public function __construct(AbstractTableSection $table, $removeMarkerDelimiters) {
        parent::__construct($removeMarkerDelimiters);
        $this->table = $table;
    }

    public function updateColumnNames(ParserCallback $callback, &$callerSession) {
        list($tableAlias, $columnName) = ColumnNameHelper::splitColumnName($callback->marker);

        $column = $this->table->findColumnByAlias($columnName);
        if (isset($column)) {
            $callback->marker = ColumnNameHelper::combineColumnName($tableAlias, $column->name);
        }

        $this->adjustCallbackObject($callback, $callerSession);
    }
}

class ColumnStatementCompositeEntityParser__TableAliasUpdater extends ColumnStatementCompositeEntityParser__ColumnNameAdjuster {

    private $oldTableAlias;
    private $newTableAlias;

    public function __construct($oldTableAlias, $newTableAlias, $removeMarkerDelimiters) {
        parent::__construct($removeMarkerDelimiters);
        $this->oldTableAlias = $oldTableAlias;
        $this->newTableAlias = $newTableAlias;
    }

    public function updateTableAlias(ParserCallback $callback, &$callerSession) {
        list($tableAlias, $columnName) = ColumnNameHelper::splitColumnName($callback->marker);

        $updateAllowed = FALSE;
        if (isset($tableAlias)) {
            if (isset($this->oldTableAlias) && ($tableAlias === $this->oldTableAlias)) {
                $updateAllowed = TRUE;
            }
        }
        elseif (!isset($this->oldTableAlias)) {
            $updateAllowed = TRUE;
        }

        if ($updateAllowed) {
            $callback->marker = ColumnNameHelper::combineColumnName($this->newTableAlias, $columnName);
        }

        $this->adjustCallbackObject($callback, $callerSession);
    }
}
