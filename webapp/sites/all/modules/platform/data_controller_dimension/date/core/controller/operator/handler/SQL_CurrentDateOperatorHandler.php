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


abstract class SQL_AbstractCurrentDateOperatorHandler extends SQL_AbstractOperatorHandler {

    protected function formatDate(DateTime $datetime) {
        $dateDataTypeHandler = DataTypeFactory::getInstance()->getHandler(DateDataTypeHandler::DATA_TYPE);

        return $datetime->format($dateDataTypeHandler->getFormat());
    }

    protected function prepareExpressionImpl(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType, $date) {
        $operator = OperatorFactory::getInstance()->initiateHandler(EqualOperatorHandler::OPERATOR__NAME, $date);
        $sqlOperatorHandler = SQLOperatorFactory::getInstance()->getHandler($this->datasourceHandler, $operator);

        return $sqlOperatorHandler->format($callcontext, $request, $datasetName, $columnName, $columnDataType);
    }
}

class SQL_CurrentDateOperatorHandler extends SQL_AbstractCurrentDateOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $now = new DateTime();

        return $this->prepareExpressionImpl($callcontext, $request, $datasetName, $columnName, $columnDataType, $this->formatDate($now));
    }
}

class SQL_CurrentMonthOperatorHandler extends SQL_AbstractCurrentDateOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $proxy = new DateTimeProxy();

        // converting the date to the first day of corresponding month
        $dt = new DateTime();
        $dt->setDate($proxy->getYear(), $proxy->getMonth(), 1);

        return $this->prepareExpressionImpl($callcontext, $request, $datasetName, $columnName, $columnDataType, $this->formatDate($dt));
    }
}

class SQL_CurrentQuarterOperatorHandler extends SQL_AbstractCurrentDateOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $proxy = new DateTimeProxy();

        $dt = new DateTime();
        $dt->setDate(
            $proxy->getYear(),
            DateTimeProxy::getFirstMonthOfQuarter($proxy->getQuarter()),
            1);

        return $this->prepareExpressionImpl($callcontext, $request, $datasetName, $columnName, $columnDataType, $this->formatDate($dt));
    }
}

class SQL_CurrentFiscalQuarterOperatorHandler extends SQL_AbstractCurrentDateOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $proxy = new DateTimeProxy();

        list($fiscalYear, $fiscalMonth) = FiscalYearConfiguration::getAsFiscal($proxy->getYear(), $proxy->getMonth());
        $fiscalQuarter = DateTimeProxy::getQuarterByMonth($fiscalMonth);
        $firstMonthOfFiscalQuarter = DateTimeProxy::getFirstMonthOfQuarter($fiscalQuarter);

        $dt = new DateTime();
        $dt->setDate($fiscalYear, $firstMonthOfFiscalQuarter, 1);

        return $this->prepareExpressionImpl($callcontext, $request, $datasetName, $columnName, $columnDataType, $this->formatDate($dt));
    }
}

class SQL_CurrentYearOperatorHandler extends SQL_AbstractCurrentDateOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $proxy = new DateTimeProxy();

        return $this->prepareExpressionImpl($callcontext, $request, $datasetName, $columnName, $columnDataType, $proxy->getYear());
    }
}

class SQL_CurrentFiscalYearOperatorHandler extends SQL_AbstractCurrentDateOperatorHandler {

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $proxy = new DateTimeProxy();

        list($fiscalYear) = FiscalYearConfiguration::getAsFiscal($proxy->getYear(), $proxy->getMonth());

        return $this->prepareExpressionImpl($callcontext, $request, $datasetName, $columnName, $columnDataType, $fiscalYear);
    }
}
