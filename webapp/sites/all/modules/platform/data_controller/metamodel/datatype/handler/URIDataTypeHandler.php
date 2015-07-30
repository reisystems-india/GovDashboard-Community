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


class URIDataTypeHandler extends AbstractStringDataTypeHandler {

    const DATA_TYPE = 'URI';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('URI');
    }

    public function getHandlerType() {
        return DATA_TYPE__BUSINESS;
    }

    protected function isValueOfImpl(&$value) {
        return parent::isValueOfImpl($value) && (filter_var($value, FILTER_VALIDATE_URL) !== FALSE);
    }

    protected function isParsableImpl(&$value) {
        return parent::isParsableImpl($value) && (filter_var($value, FILTER_VALIDATE_URL) !== FALSE);
    }

    protected function castValueImpl($value) {
        $adjustedValue = filter_var($value, FILTER_VALIDATE_URL);
        if ($adjustedValue === FALSE) {
            throw new IllegalArgumentException(t('%value is not of %type type', array('%value' => $value, '%type' => self::DATA_TYPE)));
        }

        return $adjustedValue;
    }

    public function getStorageDataType() {
        return StringDataTypeHandler::DATA_TYPE;
    }
}
