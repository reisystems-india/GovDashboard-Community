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


abstract class AbstractColumnBasedComparator extends AbstractValueComparator {

    private $sortingConfigurations = NULL;

    public function registerSortingConfiguration(ColumnBasedComparator_AbstractSortingConfiguration $sortingConfiguration) {
        $this->sortingConfigurations[] = $sortingConfiguration;
    }

    public function registerSortingConfigurations(array $sortingConfigurations = NULL) {
        if (!isset($sortingConfigurations)) {
            return;
        }

        foreach ($sortingConfigurations as $sortingConfiguration) {
            $this->sortingConfigurations[] = $sortingConfiguration;
        }
    }

    abstract protected function getColumnValue($record, $columnName);

    public function compare($recordA, $recordB) {
        foreach ($this->sortingConfigurations as $sortingConfiguration) {
            $columnName = $sortingConfiguration->getColumnName();

            $a = $this->getColumnValue($recordA, $columnName);
            $b = $this->getColumnValue($recordB, $columnName);

            $result = compare_values($a, $b, $sortingConfiguration->isSortAscending);
            if ($result != 0) {
                return $result;
            }
        }

        return 0;
    }
}

abstract class ColumnBasedComparator_AbstractSortingConfiguration extends AbstractObject {

    const SORT_DIRECTION_DELIMITER__DESCENDING = '-';

    public $isSortAscending = NULL;

    public function __construct($isSortAscending = TRUE) {
        parent::__construct();
        $this->isSortAscending = $isSortAscending;
    }

    abstract public function getColumnName();

    public static function parseDirectionalColumnName($directionalColumnName) {
        $isSortAscending = TRUE;

        $columnName = $directionalColumnName;
        if ($directionalColumnName{0} == self::SORT_DIRECTION_DELIMITER__DESCENDING) {
            $isSortAscending = FALSE;
            $columnName = substr($columnName, 1);
        }

        return array($columnName, $isSortAscending);
    }

    public static function assembleDirectionalColumnName($columnName, $isSortAscending) {
        return ($isSortAscending ? '' : self::SORT_DIRECTION_DELIMITER__DESCENDING) . $columnName;
    }
}


class ColumnBasedComparator_DefaultSortingConfiguration extends ColumnBasedComparator_AbstractSortingConfiguration {

    public $columnName = NULL;

    public function __construct($columnName, $isSortAscending = TRUE) {
        parent::__construct($isSortAscending);
        $this->columnName = $columnName;
    }

    public function getColumnName() {
        return $this->columnName;
    }
}
