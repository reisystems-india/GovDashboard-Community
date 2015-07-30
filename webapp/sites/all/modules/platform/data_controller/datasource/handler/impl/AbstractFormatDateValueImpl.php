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


abstract class AbstractFormatDateValueImpl extends AbstractObject {

    protected $adjustedFormats = NULL;

    abstract protected function adjustFormat($format, $acceptTZ);

    public function prepareFormat($format, $acceptTZ = TRUE) {
        if (isset($this->adjustedFormats[$format])) {
            return $this->adjustedFormats[$format];
        }

        $adjustedFormat = $this->adjustFormat($format, $acceptTZ);

        $this->adjustedFormats[$format] = $adjustedFormat;

        return $adjustedFormat;
    }

    abstract protected function formatStringToDateImpl(DataSourceHandler $handler, $formattedValue, $adjustedFormat, $datatype);

    public function formatStringToDate(DataSourceHandler $handler, $formattedValue, $format, $datatype) {
        $adjustedFormat = $this->prepareFormat($format);

        if (($datatype != DateDataTypeHandler::DATA_TYPE)
                && ($datatype != TimeDataTypeHandler::DATA_TYPE)
                && ($datatype != DateTimeDataTypeHandler::DATA_TYPE)) {
            throw new IllegalArgumentException(t(
                'Unsupported data type for date- and/or time-related formatting: %datatype',
                array('%datatype' => $datatype)));
        }

        return $this->formatStringToDateImpl($handler, $formattedValue, $adjustedFormat, $datatype);
    }

    protected function addMonthsExpression(DataSourceHandler $handler, $value, $numberOfMonths) {
        throw new UnsupportedOperationException();
    }

    protected function getQuarterAndYearToDateExpression(DataSourceHandler $handler, $quarter, $year) {
        $d = $handler->getExtension('concatenateValues')->concatenate($handler, array("(($quarter - 1) * 3 + 1)", "'/01/'", $year));

        return $this->formatStringToDate($handler, "($d)", 'm/d/Y', DateDataTypeHandler::DATA_TYPE);
    }

    protected function getQuarterNameExpression(DataSourceHandler $handler, $quarter) {
        return '(' . $handler->getExtension('concatenateValues')->concatenate($handler, array("'Q'", $quarter)) . ')';
    }

    public function getDateTimeToDateExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToYearExpression(DataSourceHandler $handler, $date = '<columnName>') {
        throw new UnsupportedOperationException();
    }

    public function getDateToFiscalYearExpression(DataSourceHandler $handler) {
        $m = $this->getDateToMonthExpression($handler);
        $newM = $this->addMonthsExpression($handler, $m, '<fiscalYearOffset>');

        return $this->getDateToYearExpression($handler, $newM);
    }

    public function getDateToQuarterExpression(DataSourceHandler $handler) {
        $q = $this->getDateToQuarterSeriesExpression($handler);
        $y = $this->getDateToYearExpression($handler);

        return $this->getQuarterAndYearToDateExpression($handler, $q, $y);
    }

    public function getDateToQuarterSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        throw new UnsupportedOperationException();
    }

    public function getDateToQuarterNameExpression(DataSourceHandler $handler) {
        $q = $this->getDateToQuarterSeriesExpression($handler);

        return $this->getQuarterNameExpression($handler, $q);
    }

    public function getDateToFiscalQuarterExpression(DataSourceHandler $handler) {
        $m = $this->getDateToMonthExpression($handler);
        $newM = $this->addMonthsExpression($handler, $m, '<fiscalYearOffset>');
        $newQ = $this->getDateToQuarterSeriesExpression($handler, $newM);
        $newY = $this->getDateToYearExpression($handler, $newM);

        return $this->getQuarterAndYearToDateExpression($handler, $newQ, $newY);
    }

    public function getDateToFiscalQuarterSeriesExpression(DataSourceHandler $handler) {
        $m = $this->getDateToMonthExpression($handler);
        $newM = $this->addMonthsExpression($handler, $m, '<fiscalYearOffset>');

        return $this->getDateToQuarterSeriesExpression($handler, $newM);
    }

    public function getDateToFiscalQuarterNameExpression(DataSourceHandler $handler) {
        $q = $this->getDateToFiscalQuarterSeriesExpression($handler);

        return $this->getQuarterNameExpression($handler, $q);
    }

    public function getDateToMonthExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToMonthSeriesExpression(DataSourceHandler $handler, $date = '<columnName>') {
        throw new UnsupportedOperationException();
    }

    public function getDateToFiscalMonthSeriesExpression(DataSourceHandler $handler) {
        $m = $this->getDateToMonthExpression($handler);
        $newM = $this->addMonthsExpression($handler, $m, '<fiscalYearOffset>');

        return $this->getDateToMonthSeriesExpression($handler, $newM);
    }

    public function getDateToMonthShortNameExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToMonthNameExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToDayExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToDayOfWeekSeriesExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToDayOfWeekShortNameExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateToDayOfWeekNameExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getDateTimeToTimeExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getTimeToHourExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getTimeToMinuteExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }

    public function getTimeToSecondExpression(DataSourceHandler $handler) {
        throw new UnsupportedOperationException();
    }
}
