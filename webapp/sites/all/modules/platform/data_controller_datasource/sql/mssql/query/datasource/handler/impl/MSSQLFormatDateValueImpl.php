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


class MSSQLFormatDateValueImpl extends AbstractFormatDateValueImpl {

    protected function adjustFormat($format, $acceptTZ) {
        $adjustedFormat = '';

        // http://msdn.microsoft.com/en-us/library/8kb3ddd4(v=vs.110).aspx
        for ($i = 0, $len = strlen($format); $i < $len; $i++) {
            $specifier = $format[$i];

            $MSSQLSpecifier = $specifier;
            switch ($specifier) {
                case 'Y':
                    $MSSQLSpecifier = 'yyyy';
                    break;
                case 'm':
                    $MSSQLSpecifier = 'MM';
                    break;
                case 'd':
                    $MSSQLSpecifier = 'dd';
                    break;
                case 'h':
                    $MSSQLSpecifier = 'hh';
                    break;
                case 'H':
                    $MSSQLSpecifier = 'HH';
                    break;
                case 'i':
                    $MSSQLSpecifier = 'mm';
                    break;
                case 's':
                    $MSSQLSpecifier = 'ss';
                    break;
                case 'a':
                    $MSSQLSpecifier = 'tt';
                    break;
                case '0':
                    $MSSQLSpecifier = $acceptTZ ? 'K' : '';
                    break;
            }

            $adjustedFormat .= $MSSQLSpecifier;
        }

        return $adjustedFormat;
    }

    public function formatStringToDateImpl(DataSourceHandler $handler, $formattedValue, $adjustedFormat, $datatype) {
        $expression = NULL;

        switch ($datatype) {
            case DateDataTypeHandler::DATA_TYPE:
                $expression = "CAST($formattedValue AS DATE)";
                break;
            case TimeDataTypeHandler::DATA_TYPE:
                $expression = "CAST($formattedValue AS TIME)";
                break;
            case DateTimeDataTypeHandler::DATA_TYPE:
                $expression = "CAST($formattedValue AS DATETIME2)";
                break;
        }

        return $expression;
    }

    protected function addMonthsExpression(DataSourceHandler $handler, $value, $numberOfMonths) {
        return "DATEADD(MONTH, $numberOfMonths, $value)";
    }

    public function getDateTimeToDateExpression(DataSourceHandler $handler) {
        return "DATEADD(DAY, DATEDIFF(DAY, 0, <columnName>), 0)";
    }

    public function getDateToYearExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "YEAR($date)";
    }

    public function getDateToQuarterSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "DATEPART(QUARTER, $date)";
    }

    public function getDateToMonthExpression(DataSourceHandler $handler) {
        return "DATEADD(DAY, DATEDIFF(DAY, 0, <columnName>) - DAY(<columnName>) + 1, 0)";
    }

    public function getDateToMonthSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "MONTH($date)";
    }

    public function getDateToMonthShortNameExpression(DataSourceHandler $handler) {
        return "FORMAT(<columnName>, 'MMM')";
    }

    public function getDateToMonthNameExpression(DataSourceHandler $handler) {
        return "DATENAME(MONTH, <columnName>)";
    }

    public function getDateToDayExpression(DataSourceHandler $handler) {
        return "DAY(<columnName>)";
    }

    public function getDateToDayOfWeekSeriesExpression(DataSourceHandler $handler) {
        return "DATEPART(WEEKDAY, <columnName>)";
    }

    public function getDateToDayOfWeekShortNameExpression(DataSourceHandler $handler) {
        return "FORMAT(<columnName>, 'ddd')";
    }

    public function getDateToDayOfWeekNameExpression(DataSourceHandler $handler) {
        return "DATENAME(WEEKDAY, <columnName>)";
    }

    public function getDateTimeToTimeExpression(DataSourceHandler $handler) {
        return "CAST(<columnName> AS TIME)";
    }

    public function getTimeToHourExpression(DataSourceHandler $handler) {
        return "DATEPART(HOUR, <columnName>)";
    }

    public function getTimeToMinuteExpression(DataSourceHandler $handler) {
        return "DATEPART(MINUTE, <columnName>)";
    }

    public function getTimeToSecondExpression(DataSourceHandler $handler) {
        return "DATEPART(SECOND, <columnName>)";
    }
}
