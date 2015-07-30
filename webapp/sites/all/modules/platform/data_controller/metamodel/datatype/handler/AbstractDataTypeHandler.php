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


abstract class AbstractDataTypeHandler extends AbstractObject implements DataTypeHandler {

    public function getHandlerType() {
        return DATA_TYPE__PRIMITIVE;
    }

    public function getFormat() {
        return NULL;
    }

    final public function isValueOf($value) {
        $adjustedValue = $this->adjustValue($value);

        return $this->isValueOfImpl($adjustedValue);
    }

    protected function isValueOfImpl(&$value) {
        return isset($value);
    }

    public function selectCompatible($datatype) {
        return NULL;
    }

    final public function isParsable($value) {
        $adjustedValue = $this->adjustValue($value);

        return $this->isParsableImpl($adjustedValue);
    }

    protected function isParsableImpl(&$value) {
        return isset($value);
    }

    final public function castValue($value) {
        $adjustedValue = $this->adjustValue($value);
        if (!isset($adjustedValue)) {
            return NULL;
        }

        return $this->castValueImpl($adjustedValue);
    }

    protected function castValueImpl($value) {
        return $value;
    }

    protected function adjustValue($value) {
        if (!isset($value)) {
            return NULL;
        }

        $adjustedValue = $value;
        if (is_string($adjustedValue)) {
            $adjustedValue = trim($adjustedValue);
            if ($adjustedValue === '') {
                return NULL;
            }

            $v = strtoupper($adjustedValue);
            if (($v === 'NULL') || ($v === 'N/A')) {
                return NULL;
            }
        }

        return $adjustedValue;
    }

    public function getStorageFormat() {
        return NULL;
    }

    public function castToStorageValue($value) {
        if (!isset($value)) {
            return NULL;
        }

        return $this->castToStorageValueImpl($value);
    }

    protected function castToStorageValueImpl($value) {
        return $value;
    }
}
