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


class StringHelper {

    public static function compare() {
        $values = func_get_args();
        if ((count($values) === 1) && is_array($values[0])) {
            $values = $values[0];
        }

        $selectedValue = NULL;
        $isValueSelected = FALSE;

        foreach ($values as $value) {
            $updatedValue = self::trim($value);

            if ($isValueSelected) {
                if (isset($selectedValue)) {
                    if (!isset($updatedValue) || ($selectedValue != $updatedValue)) {
                        return FALSE;
                    }
                }
                elseif (isset($updatedValue)) {
                    return FALSE;
                }
            }
            else {
                // selecting first value which is compared to
                $selectedValue = $updatedValue;
                $isValueSelected = TRUE;
            }
        }

        return TRUE;
    }

    public static function trim($value) {
        if (isset($value)) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = NULL;
                }
            }
        }

        return $value;
    }

    public static function indent($s, $offset, $indentBlockStart) {
        $indent = str_pad('', $offset);

        return ($indentBlockStart ? $indent : '') . str_replace("\n", "\n$indent", $s);
    }
}