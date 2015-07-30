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


class DataQueryControllerUIRequestPreparer extends AbstractObject {

    public static function prepareColumns(array $columns = NULL) {
        if (isset($columns)) {
            sort($columns);
        }

        return $columns;
    }

    protected static function prepareParameterValue($parameterName, $value) {
        $preparedValue = NULL;

        $preparedValue[DataQueryControllerUIParameterNames::PARAMETER__COLUMN_NAME] = $parameterName;

        if ($value::OPERATOR__NAME != EqualOperatorHandler::OPERATOR__NAME) {
            $preparedValue[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_NAME] = $value::OPERATOR__NAME;
        }

        if (isset($value->metadata)) {
            foreach ($value->metadata->getParameters() as $operatorParameter) {
                $parameterValue = $value->{$operatorParameter->name};
                if (!$operatorParameter->required && ($parameterValue == $operatorParameter->defaultValue)) {
                    continue;
                }

                $preparedValue[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_VALUE][$operatorParameter->name] = $parameterValue;
            }
        }

        return $preparedValue;
    }

    public static function prepareParameter($parameterName, $value) {
        if (!isset($value)) {
            return NULL;
        }

        $preparedParameters = NULL;
        if (is_array($value)) {
            foreach ($value as $vi) {
                $preparedParameters[] = self::prepareParameterValue($parameterName, $vi);
            }
        }
        else {
            $preparedParameters[] = self::prepareParameterValue($parameterName, $value);
        }

        return $preparedParameters;
    }

    public static function prepareSortColumns(array $sortColumns = NULL) {
        return $sortColumns;
    }

    public static function prepareOffset($offset) {
        return $offset;
    }

    public static function prepareLimit($limit) {
        return $limit;
    }
}
