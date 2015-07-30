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


class StringDataTypeHandler extends AbstractStringDataTypeHandler {

    const DATA_TYPE = 'string';

    public function getName() {
        return self::DATA_TYPE;
    }

    public function getPublicName() {
        return t('String');
    }

    public static function checkValueAsWord($value) {
        if (!isset($value)) {
            return;
        }

        $result = preg_match('/^[a-zA-Z_]\w*$/', $value);
        if ($result === FALSE) {
            $error = preg_last_error();
            LogHelper::log_error(t(
                "'@value' could not be validated as a word: Regular expression error: @error",
                array('@value' => $value, '@error' => $error)));
        }
        elseif ($result == 0) {
            LogHelper::log_error(t("'@value' is not a word", array('@value' => $value)));
        }
        else {
            return;
        }

        throw new IllegalArgumentException(t('%value is not a word', array('%value' => $value)));
    }

    public function selectCompatible($datatype) {
        return self::DATA_TYPE;
    }

    public function getStorageDataType() {
        return self::DATA_TYPE;
    }
}
