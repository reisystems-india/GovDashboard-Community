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


class SQL_NotInRangeOperatorHandler extends SQL_AbstractOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $fromValue = $this->getParameterValue('from');
        $toValue = $this->getParameterValue('to');

        $formattedFromValue = isset($fromValue) ? $this->datasourceHandler->formatValue($columnDataType, $fromValue) : NULL;
        $formattedToValue = isset($toValue) ? $this->datasourceHandler->formatValue($columnDataType, $toValue) : NULL;

        $formattedValue = NULL;
        if (isset($formattedFromValue) && isset($formattedToValue)) {
            $formattedValue = " NOT BETWEEN $formattedFromValue AND $formattedToValue";
        }
        elseif (isset($formattedFromValue)) {
            $formattedValue = ' < ' . $formattedFromValue;
        }
        elseif (isset($formattedToValue)) {
            $formattedValue = ' > ' . $formattedToValue;
        }
        else {
            $formattedValue = ' IS NOT NULL';
        }

        return $formattedValue;
    }
}
