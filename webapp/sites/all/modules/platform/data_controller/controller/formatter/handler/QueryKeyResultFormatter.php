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


class QueryKeyResultFormatter extends AbstractResultFormatter {

    private $keyColumnNames = NULL;
    private $isColumnValueUnique = TRUE;

    public function __construct($keyColumnNames, $isColumnValueUnique = TRUE, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        $this->keyColumnNames = is_array($keyColumnNames) ? $keyColumnNames : array($keyColumnNames);
        $this->isColumnValueUnique = $isColumnValueUnique;
    }

    public function __clone() {
        parent::__clone();

        $this->keyColumnNames = ArrayHelper::copy($this->keyColumnNames);
    }

    protected function registerRecordImpl(array &$records = NULL, $record) {
        parent::registerRecordImpl($records, $record);

        $recordKey = NULL;
        foreach ($this->keyColumnNames as $keyColumnName) {
            $recordKey[] = isset($record[$keyColumnName]) ? $record[$keyColumnName] : NULL;
        }

        $key = ArrayHelper::prepareCompositeKey($recordKey);
        if (isset($records[$key])) {
            if ($this->isColumnValueUnique) {
                throw new IllegalArgumentException(t(
                	'Found several records for the key: %key',
                    array('%key' => ArrayHelper::serialize($recordKey, ', ', TRUE, TRUE))));
            }

            $records[$key][] = $record;
        }
        else {
            if ($this->isColumnValueUnique) {
                $records[$key] = $record;
            }
            else {
                $records[$key][] = $record;
            }
        }

        return TRUE;
    }
}
