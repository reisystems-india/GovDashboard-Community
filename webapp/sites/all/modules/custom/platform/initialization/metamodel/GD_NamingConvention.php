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


class GD_NamingConvention {

    public static $ACCOUNT_NAME__DEFAULT = 'gd';

    public static $PREFIX_NAME__DATAMART = 'dm';
    public static $PREFIX_NAME__DATASET = 'd';
    public static $PREFIX_NAME__COLUMN = 'c_';
    public static $PREFIX_NAME__COLUMN_CALCULATED = 'cc_';
    public static $PREFIX_NAME__REFERENCE = 'ref';

    public static $LENGTH_NAME__DATASET = 6;

    public static function generateEntityName($prefix) {
        return uniqid($prefix);
    }

    public static function generateDataMartName() {
        return self::generateEntityName(self::$PREFIX_NAME__DATAMART);
    }

    public static function generateDatasetName() {
        $sequenceName = 'dataset.name';

        $datasetSuffixId = Sequence::getNextSequenceValue($sequenceName);
        $datasetSuffix = str_pad(base_convert($datasetSuffixId, 10, 36), self::$LENGTH_NAME__DATASET, '0', STR_PAD_LEFT);

        return self::$PREFIX_NAME__DATASET . $datasetSuffix;
    }

    public static function generateReferenceName() {
        return self::generateEntityName(self::$PREFIX_NAME__REFERENCE);
    }
}
