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


class SQL_DynamicDateRangeOperatorHandler extends SQL_AbstractDynamicRangeOperatorHandler {

    protected function getLatestOperatorName() {
        return LatestDateOperatorHandler::OPERATOR__NAME;
    }

    protected function offsetLatestValue($latestDateValue, $offset) {
        $datetime = new DateTime($latestDateValue);
        $datetime->sub(new DateInterval("P{$offset}D"));

        $dateDataTypeHandler = DataTypeFactory::getInstance()->getHandler(DateDataTypeHandler::DATA_TYPE);

        return $datetime->format($dateDataTypeHandler->getFormat());
    }
}

class SQL_DynamicMonthRangeOperatorHandler extends SQL_AbstractDynamicRangeOperatorHandler {

    protected function getLatestOperatorName() {
        return LatestMonthOperatorHandler::OPERATOR__NAME;
    }

    protected function offsetLatestValue($latestMonthValue, $offset) {
        $datetime = new DateTime($latestMonthValue);
        $datetime->sub(new DateInterval("P{$offset}M"));

        $dateDataTypeHandler = DataTypeFactory::getInstance()->getHandler(DateDataTypeHandler::DATA_TYPE);

        return $datetime->format($dateDataTypeHandler->getFormat());
    }
}

class SQL_DynamicQuarterRangeOperatorHandler extends SQL_AbstractDynamicRangeOperatorHandler {

    protected function getLatestOperatorName() {
        return LatestQuarterOperatorHandler::OPERATOR__NAME;
    }

    protected function offsetLatestValue($latestQuarterValue, $offset) {
        // converting number of quarters to number of months
        $monthOffset = $offset * 3;

        $datetime = new DateTime($latestQuarterValue);
        $datetime->sub(new DateInterval("P{$monthOffset}M"));

        $dateDataTypeHandler = DataTypeFactory::getInstance()->getHandler(DateDataTypeHandler::DATA_TYPE);

        return $datetime->format($dateDataTypeHandler->getFormat());
    }
}

class SQL_DynamicYearRangeOperatorHandler extends SQL_AbstractDynamicRangeOperatorHandler {

    protected function getLatestOperatorName() {
        return LatestYearOperatorHandler::OPERATOR__NAME;
    }

    protected function offsetLatestValue($latestYearValue, $offset) {
        return $latestYearValue - $offset;
    }
}
