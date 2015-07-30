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


abstract class AbstractDateDimensionQuarterDataTypeHandler extends DateDataTypeHandler {

    public function selectCompatible($datatype) {
        return ($datatype == DateDataTypeHandler::DATA_TYPE)
            ? $datatype
            : parent::selectCompatible($datatype);
    }
}

class DateDimensionQuarterDataTypeHandler extends AbstractDateDimensionQuarterDataTypeHandler {

    const DATA_TYPE = 'date:quarter';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Quarter');
    }
}

class DateDimensionFiscalQuarterDataTypeHandler extends AbstractDateDimensionQuarterDataTypeHandler {

    const DATA_TYPE = 'date:quarter.fiscal';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('Fiscal Quarter');
    }

    public function selectCompatible($datatype) {
        return ($datatype == DateDimensionQuarterDataTypeHandler::DATA_TYPE)
            ? $datatype
            : parent::selectCompatible($datatype);
    }
}
