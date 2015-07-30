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


class DataQueryControllerUIRequestParser extends AbstractObject {

    public static function parseColumns(array $columns = NULL) {
        return $columns;
    }

    public static function parseParameters(array $parameters = NULL) {
        if (!isset($parameters)) {
            return NULL;
        }

        $adjustedParameters = NULL;

        foreach ($parameters as $parameterIndex => $parameterProperties) {
            if (!is_array($parameterProperties)) {
                $parameterProperties = array($parameterProperties);
            }

            $isParameterPropertyDetected = FALSE;

            $parameterName = NULL;
            if (is_int($parameterIndex)) {
                if (!isset($parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__COLUMN_NAME])) {
                    throw new IllegalArgumentException(t(
                        'Could not find corresponding column name for the parameter: %parameterIndex',
                        array('%parameterIndex' => $parameterIndex)));
                }
                $parameterName = $parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__COLUMN_NAME];

                unset($parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__COLUMN_NAME]);
                $isParameterPropertyDetected = TRUE;
            }
            else {
                $parameterName = $parameterIndex;
            }

            $operatorName = NULL;
            if (isset($parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_NAME])) {
                $operatorName = $parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_NAME];

                unset($parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_NAME]);
                $isParameterPropertyDetected = TRUE;
            }
            else {
                $operatorName = EqualOperatorHandler::OPERATOR__NAME;
            }

            $parameterValues = NULL;
            if (isset($parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_VALUE])) {
                $parameterValues = $parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_VALUE];

                unset($parameterProperties[DataQueryControllerUIParameterNames::PARAMETER__OPERATOR_VALUE]);
                $isParameterPropertyDetected = TRUE;
            }

            if (!$isParameterPropertyDetected) {
                $parameterValues = $parameterProperties;
                // marking that all properties are processed in the variable
                $parameterProperties = NULL;
            }

            // some properties are left and we do not know what to do with them
            if (count($parameterProperties) > 0) {
                throw new IllegalArgumentException(t(
                    'Unsupported keys for parameter definition: %unsupportedKeys',
                    array('%unsupportedKeys' => implode(', ', array_keys($parameterProperties)))));
            }

            $operatorValues = NULL;
            if (isset($parameterValues)) {
                if (ArrayHelper::isIndexed($parameterValues)) {
                    $operatorValues = $parameterValues;
                }
                else {
                    // named operator value parameters are provided
                    // we need to order the values in the same order as the parameters defined for the operator
                    $operatorMetaData = OperatorFactory::getInstance()->getOperatorMetaData($operatorName);
                    $operatorParameterMetaDatas = $operatorMetaData->getParameters();
                    if (isset($operatorParameterMetaDatas)) {
                        foreach ($operatorParameterMetaDatas as $operatorParameterMetaData) {
                            $name = $operatorParameterMetaData->name;

                            $value = NULL;
                            if (isset($parameterValues[$name])) {
                                $value = $parameterValues[$name];

                                unset($parameterValues[$name]);
                            }
                            $operatorValues[] = $value;
                        }
                    }

                    // some named parameters are not recognized
                    if (count($parameterValues) > 0) {
                        throw new IllegalArgumentException(t(
                            'Unsupported keys for parameter value definition: %unsupportedKeys',
                            array('%unsupportedKeys' => implode(', ', array_keys($parameterValues)))));
                    }
                }
            }

            $operator = OperatorFactory::getInstance()->initiateHandler($operatorName, $operatorValues);

            if (isset($adjustedParameters[$parameterName])) {
                $previousOperator = $adjustedParameters[$parameterName];
                if (is_array($previousOperator)) {
                    $adjustedParameters[$parameterName][] = $operator;
                }
                else {
                    $adjustedParameters[$parameterName] = array($previousOperator, $operator);
                }
            }
            else {
                $adjustedParameters[$parameterName] = $operator;
            }
        }

        return $adjustedParameters;
    }

    public static function parseSortColumns(array $sortColumns = NULL) {
        return $sortColumns;
    }

    public static function parseOffset($offset) {
        return $offset;
    }

    public static function parseLimit($limit) {
        return $limit;
    }
}
