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


class DatasetQueryRequest extends AbstractDatasetQueryRequest {

    public $columns = NULL;

    public function addColumn($column) {
        $isFormula = $this->findFormula($column) != NULL;
        ReferencePathHelper::checkReference($column, TRUE, !$isFormula);

        ArrayHelper::addUniqueValue($this->columns, $column);
    }

    public function addColumns($columns) {
        if (!isset($columns)) {
            return;
        }

        if (is_array($columns)) {
            foreach ($columns as $column) {
                $this->addColumn($column);
            }
        }
        else {
            $this->addColumn($columns);
        }
    }
}