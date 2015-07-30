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


class MySQLFormatDateValueImpl extends AbstractFormatDateValueImpl {

    protected function adjustFormat($format, $acceptTZ) {
        $adjustedFormat = '';

        // http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
        for ($i = 0, $len = strlen($format); $i < $len; $i++) {
            $specifier = $format[$i];

            $mysqlSpecifier = $specifier;
            switch ($specifier) {
                case 'Y':
                case 'm':
                case 'd':
                case 'h':
                case 'H':
                case 'i':
                case 's':
                    $mysqlSpecifier = '%' . $specifier;
                    break;
                case 'a':
                    $mysqlSpecifier = '%p';
                    break;
                case '0':
                    // there is no support for Time Zone in date formatting functions
                    $mysqlSpecifier = $acceptTZ ? '' : '';
                    break;
            }

            $adjustedFormat .= $mysqlSpecifier;
        }

        return $adjustedFormat;
    }

    public function formatStringToDateImpl(DataSourceHandler $handler, $formattedValue, $adjustedFormat, $datatype) {
        // Returns datetime, date or time value
        return "STR_TO_DATE($formattedValue, '$adjustedFormat')";
    }

    protected function addMonthsExpression(DataSourceHandler $handler, $value, $numberOfMonths) {
        return "DATE_ADD($value, INTERVAL $numberOfMonths MONTH)";
    }

    public function getDateTimeToDateExpression(DataSourceHandler $handler) {
        return "DATE(<columnName>)";
    }

    public function getDateToYearExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "YEAR($date)";
    }

    public function getDateToQuarterSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "QUARTER($date)";
    }

    public function getDateToMonthExpression(DataSourceHandler $handler) {
        return "DATE(DATE_SUB(<columnName>, INTERVAL (DAY(<columnName>) - 1) DAY))";
    }

    public function getDateToMonthSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "MONTH($date)";
    }

    public function getDateToMonthShortNameExpression(DataSourceHandler $handler) {
        return "DATE_FORMAT(<columnName>, '%b')";
    }

    public function getDateToMonthNameExpression(DataSourceHandler $handler) {
        return "MONTHNAME(<columnName>)";
    }

    public function getDateToDayExpression(DataSourceHandler $handler) {
        return "DAY(<columnName>)";
    }

    public function getDateToDayOfWeekSeriesExpression(DataSourceHandler $handler) {
        return "DAYOFWEEK(<columnName>)";
    }

    public function getDateToDayOfWeekShortNameExpression(DataSourceHandler $handler) {
        return "DATE_FORMAT(<columnName>, '%a')";
    }

    public function getDateToDayOfWeekNameExpression(DataSourceHandler $handler) {
        return "DATE_FORMAT(<columnName>, '%W')";
    }

    public function getDateTimeToTimeExpression(DataSourceHandler $handler) {
        return "TIME(<columnName>)";
    }

    public function getTimeToHourExpression(DataSourceHandler $handler) {
        return "HOUR(<columnName>)";
    }

    public function getTimeToMinuteExpression(DataSourceHandler $handler) {
        return "MINUTE(<columnName>)";
    }

    public function getTimeToSecondExpression(DataSourceHandler $handler) {
        return "SECOND(<columnName>)";
    }
}
