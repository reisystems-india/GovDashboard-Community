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


class FunctionDateDimensionMetaDataGenerator extends AbstractObject {

    protected static $CONFIGURATIONS = array(
        array('name' => 'date', 'publicName' => 'Date ONLY', 'datatype' => DateDataTypeHandler::DATA_TYPE, 'part' => 'time', 'methodName' => 'getDateTimeToDateExpression'),

        array('name' => 'year', 'publicName' => 'Year (calendar)', 'datatype' => DateDimensionYearDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToYearExpression'),

        array('name' => 'year_fiscal', 'publicName' => 'Year (fiscal)', 'datatype' => DateDimensionFiscalYearDataTypeHandler::DATA_TYPE, 'part' => 'date:fiscal', 'methodName' => 'getDateToFiscalYearExpression'),

        array('name' => 'quarter', 'publicName' => 'Quarter (calendar)', 'datatype' => DateDimensionQuarterDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToQuarterExpression', 'elements' => array(
            array('name' => 'quarter_series', 'publicName' => 'Series', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToQuarterSeriesExpression'),
            array('name' => 'quarter_name', 'publicName' => 'Name', 'datatype' => StringDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToQuarterNameExpression'))),

        array('name' => 'quarter_fiscal', 'publicName' => 'Quarter (fiscal)', 'datatype' => DateDimensionQuarterDataTypeHandler::DATA_TYPE, 'part' => 'date:fiscal', 'methodName' => 'getDateToFiscalQuarterExpression', 'elements' => array(
            array('name' => 'quarter_series_fiscal', 'publicName' => 'Series', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'date:fiscal', 'methodName' => 'getDateToFiscalQuarterSeriesExpression'),
            array('name' => 'quarter_name_fiscal', 'publicName' => 'Name', 'datatype' => StringDataTypeHandler::DATA_TYPE, 'part' => 'date:fiscal', 'methodName' => 'getDateToFiscalQuarterNameExpression'))),

        array('name' => 'month', 'publicName' => 'Month', 'datatype' => DateDimensionMonthDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToMonthExpression', 'elements' => array(
            array('name' => 'month_series', 'publicName' => 'Series (calendar)', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToMonthSeriesExpression'),
            array('name' => 'month_series_fiscal', 'publicName' => 'Series (fiscal)', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'date:fiscal', 'methodName' => 'getDateToFiscalMonthSeriesExpression'),
            array('name' => 'month_name_short', 'publicName' => 'Short Name', 'datatype' => StringDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToMonthShortNameExpression'),
            array('name' => 'month_name', 'publicName' => 'Name', 'datatype' => StringDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToMonthNameExpression'))),

        array('name' => 'day', 'publicName' => 'Day', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToDayExpression'),

        array('name' => 'day_of_week', 'publicName' => 'Day of Week', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToDayOfWeekSeriesExpression', 'elements' => array(
            array('name' => 'day_of_week_name_short', 'publicName' => 'Short Name', 'datatype' => StringDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToDayOfWeekShortNameExpression'),
            array('name' => 'day_of_week_name', 'publicName' => 'Name', 'datatype' => StringDataTypeHandler::DATA_TYPE, 'part' => 'date', 'methodName' => 'getDateToDayOfWeekNameExpression'))),

        array('name' => 'time', 'publicName' => 'Time ONLY', 'datatype' => TimeDataTypeHandler::DATA_TYPE, 'part' => 'time', 'methodName' => 'getDateTimeToTimeExpression'),

        array('name' => 'hour', 'publicName' => 'Hour', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'time', 'methodName' => 'getTimeToHourExpression'),

        array('name' => 'minute', 'publicName' => 'Minute', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'time', 'methodName' => 'getTimeToMinuteExpression'),

        array('name' => 'second', 'publicName' => 'Second', 'datatype' => IntegerDataTypeHandler::DATA_TYPE, 'part' => 'time', 'methodName' => 'getTimeToSecondExpression'));

    protected $datasourceHandler = NULL;

    protected $dateFormatExtensions = NULL;

    public function __construct(DataSourceQueryHandler $datasourceHandler) {
        parent::__construct();
        $this->datasourceHandler = $datasourceHandler;
    }

    public function generate(ColumnMetaData $column) {
        $allowDate = $allowTime = FALSE;
        if (isset($column->type->applicationType)) {
            switch ($column->type->applicationType) {
                case DateTimeDataTypeHandler::DATA_TYPE:
                    $allowDate = $allowTime = TRUE;
                    break;
                case DateDataTypeHandler::DATA_TYPE:
                    $allowDate = TRUE;
                    break;
                case TimeDataTypeHandler::DATA_TYPE:
                    $allowTime = TRUE;
                    break;
            }
        }
        if ($allowDate || $allowTime) {
            foreach (self::$CONFIGURATIONS as $configuration) {
                $this->generateFunction($column->name, $column, $configuration, $allowDate, $allowTime);
            }
        }
    }

    public function clean(ColumnMetaData $column) {
        if (!isset($column->branches)) {
            return;
        }

        foreach (self::$CONFIGURATIONS as $configuration) {
            $rootBranchName = $column->name . '__' . $configuration['name'];

            // removing root branch (nested branches are deleted automatically)
            foreach ($column->branches as $index => $rootBranch) {
                if ($rootBranch->name == $rootBranchName) {
                    unset($column->branches[$index]);
                    break;
                }
            }
        }
    }

    protected function generateFunction($columnPrefix, ColumnMetaData $parentColumn, $configuration, $allowDate, $allowTime) {
        if (($configuration['part'] == 'date') && !$allowDate) {
            return;
        }
        if (($configuration['part'] == 'date:fiscal') && (!$allowDate || (FiscalYearConfiguration::$FIRST_MONTH == 1))) {
            return;
        }
        if (($configuration['part'] == 'time') && !$allowTime) {
            return;
        }

        // preparing extension for date value formatting
        $key = $this->datasourceHandler->getDataSourceType();
        if (!isset($this->dateFormatExtensions[$key])) {
            $this->dateFormatExtensions[$key] = $this->datasourceHandler->getExtension('formatDateValue');
        }
        $dateFormatExtension = $this->dateFormatExtensions[$key];

        // preparing expression for calculated column
        $extensionMethodName = $configuration['methodName'];
        try {
            $expression = $dateFormatExtension->$extensionMethodName($this->datasourceHandler);
        }
        catch (UnsupportedOperationException $e) {
            $expression = FALSE;
        }

        if ($expression === FALSE) {
            $column = new ColumnMetaData();
            $column->used = FALSE;
        }
        else {
            $formulaExpressionParser = new FormulaExpressionParser(SQLFormulaExpressionHandler::LANGUAGE__SQL);

            $column = new FormulaMetaData();
            $column->type->applicationType = $configuration['datatype'];
            $column->source = str_replace(
                array('<columnName>', '<fiscalYearOffset>'),
                array(
                    $formulaExpressionParser->assemble($columnPrefix),
                    (12 - FiscalYearConfiguration::$FIRST_MONTH + 1)),
                $expression);
        }
        $column->name = $columnPrefix . '__' . $configuration['name'];
        $column->publicName = t($configuration['publicName']);
        $column->loaderName = get_class($this);

        // processing nested elements
        if (isset($configuration['elements'])) {
            foreach ($configuration['elements'] as $nestedConfiguration) {
                $this->generateFunction($columnPrefix, $column, $nestedConfiguration, $allowDate, $allowTime);
            }
        }

        if (($expression !== FALSE) || (count($column->branches) != 0)) {
            $parentColumn->branches[] = $column;
        }
    }
}
