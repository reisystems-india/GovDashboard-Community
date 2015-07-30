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


class SpecialCharacterResultFormatter extends AbstractResultFormatter {

    protected $columnNames = NULL;

    public function __construct(array $columnNames = NULL, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        if (isset($columnNames)) {
            foreach ($columnNames as $columnName) {
                $this->columnNames[$columnName] = TRUE;
            }
        }
    }

    protected function formatColumnValueImpl($formattedColumnName, $columnValue) {
        $formattedColumnValue = parent::formatColumnValueImpl($formattedColumnName, $columnValue);

        if ((!isset($this->columnNames) || isset($this->columnNames[$formattedColumnName])) && is_string($formattedColumnValue)) {
            $formattedColumnValue = check_plain($formattedColumnValue);
        }

        return $formattedColumnValue;
    }
}
