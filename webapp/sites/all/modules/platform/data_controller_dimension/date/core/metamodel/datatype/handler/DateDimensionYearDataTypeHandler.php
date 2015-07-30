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


abstract class AbstractDateDimensionYearDataTypeHandler extends AbstractIntegerDataTypeHandler {

    public static $YEAR_MINIMUM = 1900;
    public static $YEAR_MAXIMUM = 2100;

    protected function isValueInRange($year) {
        return ($year >= self::$YEAR_MINIMUM) && ($year <= self::$YEAR_MAXIMUM);
    }

    protected function isValueOfImpl(&$value) {
        $isValueOf = parent::isValueOfImpl($value);

        if ($isValueOf) {
            $isValueOf = $this->isValueInRange($value);
        }

        return $isValueOf;
    }

    public function selectCompatible($datatype) {
        if (($datatype === IntegerDataTypeHandler::DATA_TYPE)
                || ($datatype === NumberDataTypeHandler::DATA_TYPE)) {
            return $datatype;
        }

        return parent::selectCompatible($datatype);
    }

    protected function isParsableImpl(&$value) {
        $isParsable = parent::isParsableImpl($value);
        if ($isParsable) {
            $year = $this->castValue($value);
            $isParsable = $this->isValueInRange($year);
        }

        return $isParsable;
    }

    public function getStorageDataType() {
        return IntegerDataTypeHandler::DATA_TYPE;
    }
}

class DateDimensionYearDataTypeHandler extends AbstractDateDimensionYearDataTypeHandler {

    const DATA_TYPE = 'date2:year';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Year');
    }

    public function selectCompatible($datatype) {
        if (($datatype === IntegerDataTypeHandler::DATA_TYPE) || ($datatype === NumberDataTypeHandler::DATA_TYPE)) {
            return $datatype;
        }

        return parent::selectCompatible($datatype);
    }
}

class DateDimensionFiscalYearDataTypeHandler extends AbstractDateDimensionYearDataTypeHandler {

    const DATA_TYPE = 'date:year.fiscal';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Fiscal Year');
    }

    public function selectCompatible($datatype) {
        return ($datatype == DateDimensionYearDataTypeHandler::DATA_TYPE)
            ? $datatype
            : parent::selectCompatible($datatype);
    }
}
