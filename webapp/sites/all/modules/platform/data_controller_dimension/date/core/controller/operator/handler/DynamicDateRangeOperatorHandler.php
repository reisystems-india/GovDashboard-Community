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


class DynamicDateRangeOperatorHandler extends AbstractDynamicRangeOperatorHandler {

    const OPERATOR__NAME = 'date:range.dynamic';
}

class DynamicMonthRangeOperatorHandler extends AbstractDynamicRangeOperatorHandler {

    const OPERATOR__NAME = 'date:month:range.dynamic';
}

class DynamicQuarterRangeOperatorHandler extends AbstractDynamicRangeOperatorHandler {

    const OPERATOR__NAME = 'date:quarter:range.dynamic';
}

class DynamicFiscalQuarterRangeOperatorHandler extends AbstractDynamicRangeOperatorHandler {

    const OPERATOR__NAME = 'date:quarter.fiscal:range.dynamic';
}

class DynamicYearRangeOperatorHandler extends AbstractDynamicRangeOperatorHandler {

    const OPERATOR__NAME = 'date:year:range.dynamic';
}

class DynamicFiscalYearRangeOperatorHandler extends AbstractDynamicRangeOperatorHandler {

    const OPERATOR__NAME = 'date:year.fiscal:range.dynamic';
}
