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


class OracleFormatDateValueImpl extends AbstractFormatDateValueImpl {

    protected function adjustFormat($format, $acceptTZ) {
        $adjustedFormat = '';

        // http://docs.oracle.com/cd/B19306_01/server.102/b14200/sql_elements004.htm#i34924
        for ($i = 0, $len = strlen($format); $i < $len; $i++) {
            $specifier = $format[$i];

            $oracleSpecifier = $specifier;
            switch ($specifier) {
                case 'Y':
                    $oracleSpecifier = 'YYYY';
                    break;
                case 'm':
                    $oracleSpecifier = 'MM';
                    break;
                case 'd':
                    $oracleSpecifier = 'DD';
                    break;
                case 'h':
                    $oracleSpecifier = 'HH';
                    break;
                case 'H':
                    $oracleSpecifier = 'HH24';
                    break;
                case 'i':
                    $oracleSpecifier = 'MI';
                    break;
                case 's':
                    $oracleSpecifier = 'SS';
                    break;
                case 'a':
                    $oracleSpecifier = 'AM';
                    break;
                case '0':
                    $oracleSpecifier = $acceptTZ ? 'TZH:TZM' : '';
                    break;
            }

            $adjustedFormat .= $oracleSpecifier;
        }

        return $adjustedFormat;
    }

    public function formatStringToDateImpl(DataSourceHandler $handler, $formattedValue, $adjustedFormat, $datatype) {
        return "TO_DATE($formattedValue, '$adjustedFormat')";
    }

    protected function addMonthsExpression(DataSourceHandler $handler, $value, $numberOfMonths) {
        return "ADD_MONTHS($value, $numberOfMonths)";
    }

    public function getDateTimeToDateExpression(DataSourceHandler $handler) {
        return "TRUNC(<columnName>, 'DDD')";
    }

    public function getDateToYearExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "EXTRACT(YEAR FROM $date)";
    }

    public function getDateToQuarterSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "TO_CHAR($date, 'Q')";
    }

    public function getDateToMonthExpression(DataSourceHandler $handler) {
        return "TRUNC(<columnName>, 'MONTH')";
    }

    public function getDateToMonthSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        return "EXTRACT(MONTH FROM $date)";
    }

    public function getDateToMonthShortNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'MON')";
    }

    public function getDateToMonthNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'MONTH')";
    }

    public function getDateToDayExpression(DataSourceHandler $handler) {
        return "EXTRACT(DAY FROM <columnName>)";
    }

    public function getDateToDayOfWeekSeriesExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'D')";
    }

    public function getDateToDayOfWeekShortNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'DY')";
    }

    public function getDateToDayOfWeekNameExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>, 'DAY')";
    }

    public function getDateTimeToTimeExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName> ,'HH24:MI:SS')";
    }

    public function getTimeToHourExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName> ,'HH24')";
    }

    public function getTimeToMinuteExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>,'MI')";
    }

    public function getTimeToSecondExpression(DataSourceHandler $handler) {
        return "TO_CHAR(<columnName>,'SS')";
    }
}
