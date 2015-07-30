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


class PostgreSQLFormatDateValueImpl extends AbstractFormatDateValueImpl {

    protected function adjustFormat($format, $acceptTZ) {
        $adjustedFormat = '';

        // http://www.postgresql.org/docs/9.3/static/functions-formatting.html
        for ($i = 0, $len = strlen($format); $i < $len; $i++) {
            $specifier = $format[$i];

            $postgreSpecifier = $specifier;
            switch ($specifier) {
                case 'Y':
                    $postgreSpecifier = 'YYYY';
                    break;
                case 'm':
                    $postgreSpecifier = 'MM';
                    break;
                case 'd':
                    $postgreSpecifier = 'DD';
                    break;
                case 'h':
                    $postgreSpecifier = 'HH';
                    break;
                case 'H':
                    $postgreSpecifier = 'HH24';
                    break;
                case 'i':
                    $postgreSpecifier = 'MI';
                    break;
                case 's':
                    $postgreSpecifier = 'SS';
                    break;
                case 'a':
                    $postgreSpecifier = 'AM';
                    break;
                case '0':
                    // there is no support for Time Zone in date formatting functions
                    $postgreSpecifier = $acceptTZ ? '' : '';
                    break;
            }

            $adjustedFormat .= $postgreSpecifier;
        }

        return $adjustedFormat;
    }

    public function formatStringToDateImpl(DataSourceHandler $handler, $formattedValue, $adjustedFormat, $datatype) {
        $expression = NULL;

        switch ($datatype) {
            case DateDataTypeHandler::DATA_TYPE:
                $expression = "TO_DATE($formattedValue, '$adjustedFormat')";
                break;
            case TimeDataTypeHandler::DATA_TYPE:
            case DateTimeDataTypeHandler::DATA_TYPE:
                $expression = "TO_TIMESTAMP($formattedValue, '$adjustedFormat')";
                break;
        }

        return $expression;
    }

    protected function addMonthsExpression(DataSourceHandler $handler, $value, $numberOfMonths) {
        return "$value + interval '1 month' * $numberOfMonths";
    }

    public function getDateTimeToDateExpression(DataSourceHandler $handler) {
        return "DATE_TRUNC('day', <columnName>)";
    }

    public function getDateToYearExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "EXTRACT(YEAR FROM $date)";
    }

    public function getDateToQuarterSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "EXTRACT(QUARTER FROM $date)";
    }

    public function getDateToMonthExpression(DataSourceHandler $handler) {
        return "DATE_TRUNC('month', <columnName> - interval '1 day' * (EXTRACT(DAY FROM <columnName>) - 1))";
    }

    public function getDateToMonthSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "EXTRACT(MONTH FROM $date)";
    }

    public function getDateToMonthShortNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'Mon')";
    }

    public function getDateToMonthNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'Month')";
    }

    public function getDateToDayExpression(DataSourceHandler $handler) {
        return "EXTRACT(DAY FROM <columnName>)";
    }

    public function getDateToDayOfWeekSeriesExpression(DataSourceHandler $handler) {
        return "(EXTRACT(DOW FROM <columnName>) + 1)";
    }

    public function getDateToDayOfWeekShortNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'Dy')";
    }

    public function getDateToDayOfWeekNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'Day')";
    }

    public function getDateTimeToTimeExpression(DataSourceHandler $handler) {
        return "CAST(<columnName> AS TIME)";
    }

    public function getTimeToHourExpression(DataSourceHandler $handler) {
        return "EXTRACT(HOUR FROM <columnName>)";
    }

    public function getTimeToMinuteExpression(DataSourceHandler $handler) {
        return "EXTRACT(MINUTE FROM <columnName>)";
    }

    public function getTimeToSecondExpression(DataSourceHandler $handler) {
        return "EXTRACT(SECOND FROM <columnName>)";
    }
}
