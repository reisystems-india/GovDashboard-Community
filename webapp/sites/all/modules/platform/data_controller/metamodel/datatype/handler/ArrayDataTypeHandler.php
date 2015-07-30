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


class ArrayDataTypeHandler extends AbstractDataTypeHandler {

    const DATA_TYPE = 'array';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Array');
    }

    public function getHandlerType() {
        return DATA_TYPE__BUSINESS;
    }

    protected function isValueOfImpl(&$value) {
        return is_array($value);
    }

    protected function isParsableImpl(&$value) {
        $parsedValue = json_decode($value, TRUE);

        return is_array($parsedValue);
    }

    protected function castValueImpl($value) {
        $array = NULL;

        if (is_array($value)) {
            $array = $value;
        }
        elseif (is_object($value)) {
            $array = get_object_vars($value);
        }
        else {
            $array = json_decode($value, TRUE);
            if (!isset($array)) {
                LogHelper::log_debug($value);
                throw new IllegalArgumentException(t('Incorrect value of type ARRAY'));
            }
        }

        return $array;
    }

    public function getStorageDataType() {
        return StringDataTypeHandler::DATA_TYPE;
    }
}
