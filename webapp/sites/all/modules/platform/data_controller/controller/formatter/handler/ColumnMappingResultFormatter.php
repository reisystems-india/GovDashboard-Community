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


class ColumnMappingResultFormatter extends AbstractResultFormatter {

    private $columnMappings;

    public function __construct(array $columnMappings, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        $this->columnMappings = $columnMappings;
    }

    public function __clone() {
        parent::__clone();

        $this->columnMappings = ArrayHelper::copy($this->columnMappings);
    }

    public function getColumnMappings() {
        return $this->columnMappings;
    }

    protected function formatColumnNameImpl($columnName) {
        $formattedColumnName = parent::formatColumnNameImpl($columnName);

        return isset($this->columnMappings[$formattedColumnName]) ? $this->columnMappings[$formattedColumnName] : NULL;
    }
}