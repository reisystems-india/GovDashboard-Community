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


class ParameterNameHelper {

    const DELIMITER__DEFAULT = '.';

    public static function split($parameterName, $delimiter = self::DELIMITER__DEFAULT) {
        $elements = explode($delimiter, $parameterName);
        $elementCount = count($elements);

        if (($elementCount < 1) || ($elementCount > 2)) {
            throw new IllegalArgumentException(t(
                'Parameter name should contain one or two parts (root, leaf): %name',
                array('%name' => $parameterName)));
        }

        for ($i = 0, $limit = (2 - $elementCount); $i < $limit; $i++) {
            $elements[] = NULL;
        }

        return $elements;
    }

    public static function assemble($rootName, $leafName = NULL, $delimiter = self::DELIMITER__DEFAULT) {
        if (!isset($rootName)) {
            throw new IllegalArgumentException(t('Element name has not been provided'));
        }

        $name = $rootName;

        if (isset($leafName)) {
            $name .= $delimiter . $leafName;
        }

        return $name;
    }

    public static function replaceDelimiter($parameterName, $newDelimiter, $oldDelimiter = self::DELIMITER__DEFAULT) {
        $elements = self::split($parameterName, $oldDelimiter);

        return self::assemble($elements[0], $elements[1], $newDelimiter);
    }
}
