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


abstract class AbstractValueBasedOperatorHandler extends AbstractSingleParameterBasedOperatorHandler {

    public function __construct($configuration, $value = NULL) {
        parent::__construct($configuration);

        $adjustedValue = is_array($value) ? ArrayHelper::trim($value) : StringHelper::trim($value);
        if (is_array($adjustedValue) && count($adjustedValue) === 1) {
            $adjustedValue = $adjustedValue[0];
        }

        $parameterName = $this->getParameterName();
        $this->$parameterName = $adjustedValue;
    }

    public function getParameterDataType() {
        $parameterName = $this->getParameterName();
        $value = $this->$parameterName;

        return is_array($value)
            ? DataTypeFactory::getInstance()->autoDetectCompatibleDataType($value)
            : DataTypeFactory::getInstance()->autoDetectDataType($value);
    }
}

class ValueBasedOperatorHandler extends AbstractValueBasedOperatorHandler {

    protected function getParameterName() {
        return 'value';
    }
}

abstract class AbstractSingleValueBasedOperatorHandler extends AbstractSingleParameterBasedOperatorHandler {

    public function __construct($configuration, $value = NULL) {
        parent::__construct($configuration);

        if (is_array($value)) {
            throw new IllegalArgumentException(t(
            	'Only single value is supported for the operator: [%value]',
                array('%value' => implode(', ', $value))));
        }

        $parameterName = $this->getParameterName();
        $this->$parameterName = StringHelper::trim($value);
    }

    public function getParameterDataType() {
        $parameterName = $this->getParameterName();
        $value = $this->$parameterName;

        return DataTypeFactory::getInstance()->autoDetectDataType($value);
    }
}

class SingleValueBasedOperatorHandler extends AbstractSingleValueBasedOperatorHandler {

    protected function getParameterName() {
        return 'value';
    }
}

class ValueBasedOperatorMetaData extends AbstractOperatorMetaData {

    protected function initiateParameters() {
        return array(new OperatorParameter('value', 'Value'));
    }
}
