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


class SingleColumnResultFormatter extends AbstractResultFormatter {

    const STORAGE__VALUE = TRUE;
    const STORAGE__KEY = FALSE;

    private $columnName = NULL;
    private $storage = NULL;

    public function __construct($columnName = NULL, $storage = self::STORAGE__VALUE, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        $this->columnName = $columnName;
        $this->storage = $storage;
    }

    protected function registerRecordImpl(array &$records = NULL, $record) {
        parent::registerRecordImpl($records, $record);

        $value = NULL;
        if (isset($this->columnName)) {
            $value = isset($record[$this->columnName]) ? $record[$this->columnName] : NULL;
        }
        else {
            $count = count($record);
            switch ($count) {
                case 0:
                    // it is the same as NULL
                    break;
                case 1:
                    $value = reset($record);
                    break;
                default:
                    throw new IllegalArgumentException(t('Only one column is supported by this result formatter'));
            }
        }

        if (isset($value)) {
            if ($this->storage == self::STORAGE__KEY) {
                $records[$value] = TRUE;
            }
            elseif ($this->storage == self::STORAGE__VALUE) {
                $records[] = $value;
            }
        }

        return TRUE;
    }
}