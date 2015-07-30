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


class ColumnMetaData extends AbstractMetaData {

    const PERSISTENCE__NO_STORAGE = 0;
    const PERSISTENCE__STORAGE_CREATED = 1;

    public $alias = NULL;
    /**
     * @var ColumnType
     */
    public $type = NULL;

    public $persistence = NULL;
    public $columnIndex = NULL;
    public $source = NULL;

    public $key = NULL;
    public $visible = NULL;

    public $branches = NULL;

    public function __construct() {
        parent::__construct();
        $this->type = $this->initiateType();
    }

    public function __clone() {
        parent::__clone();

        $this->type = clone $this->type;
        $this->branches = ArrayHelper::copy($this->branches);
    }

    public function findBranch($columnName) {
        if (isset($this->branches)) {
            foreach ($this->branches as $branch) {
                if ($branch->name === $columnName) {
                    return $branch;
                }

                $nestedBranch = $branch->findBranch($columnName);
                if (isset($nestedBranch)) {
                    return $nestedBranch;
                }
            }
        }

        return NULL;
    }

    public function initializeFrom($sourceColumn) {
        parent::initializeFrom($sourceColumn);

        $sourceType = ObjectHelper::getPropertyValue($sourceColumn, 'type');
        if (isset($sourceType)) {
            $this->initializeTypeFrom($sourceType);
        }
    }

    public function initializeTypeFrom($sourceType, $replace = FALSE) {
        if ($replace) {
            $this->type = $this->initiateType();
        }

        if (isset($sourceType)) {
            ObjectHelper::mergeWith($this->type, $sourceType, TRUE);
        }
    }

    public function initiateType() {
        return new ColumnType();
    }

    public function isKey() {
        return isset($this->key) ? $this->key : FALSE;
    }

    public function isVisible() {
        return isset($this->visible) ? $this->visible : TRUE;
    }

    public function isPhysical() {
        return ($this->persistence == ColumnMetaData::PERSISTENCE__NO_STORAGE)
            || ($this->persistence == ColumnMetaData::PERSISTENCE__STORAGE_CREATED);
    }
}
