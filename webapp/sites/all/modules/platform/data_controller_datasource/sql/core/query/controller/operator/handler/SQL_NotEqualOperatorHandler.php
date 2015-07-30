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


class SQL_NotEqualOperatorHandler extends SQL_AbstractOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $value = $this->getParameterValue('value');

        if (!isset($value)) {
            return ' IS NOT NULL';
        }

        if (is_array($value)) {
            $values = NULL;
            foreach ($value as $v) {
                $values[] = $this->datasourceHandler->formatValue($columnDataType, $v);
            }

            $formattedValue = ' NOT IN (' . implode(', ', $values) . ')';
        }
        else {
            $formattedValue = ' != ' . $this->datasourceHandler->formatValue($columnDataType, $value);
        }

        return $formattedValue;
    }
}