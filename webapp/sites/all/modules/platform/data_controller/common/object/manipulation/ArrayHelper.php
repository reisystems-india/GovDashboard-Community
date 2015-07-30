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


class ArrayHelper {

    const COMPOSITE_KEY_DELIMITER = '|';

    public static function toArray($value) {
        $values = NULL;

        if (isset($value)) {
            if (is_array($value)) {
                // to support associative arrays and index array with random indexes
                foreach ($value as $v) {
                    $values[] = $v;
                }
            }
            else {
                $values[] = $value;
            }
        }

        return $values;
    }

    public static function isIndexed($enum = NULL) {
        if (!isset($enum)) {
            return NULL;
        }

        foreach ($enum as $key => $value) {
            if (!is_int($key)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public static function prepareCompositeKey(array $values) {
        return self::serialize($values, self::COMPOSITE_KEY_DELIMITER, FALSE, FALSE);
    }

    public static function addUniqueValue(array &$array = NULL, $value) {
        if (isset($value)) {
            if (is_array($value)) {
                LogHelper::log_error(t('[@value] should not be an array', array('@value' => implode(', ', $value))));
                throw new IllegalArgumentException(t('Value should not be an array'));
            }

            if (isset($array)) {
                if (!in_array($value, $array)) {
                    $array[] = $value;
                    return TRUE;
                }
            }
            else {
                $array[] = $value;
                return TRUE;
            }
        }

        return FALSE;
    }

    public static function addUniqueValues(array &$array = NULL, array $values = NULL) {
        if (isset($values)) {
            foreach ($values as $v) {
                self::addUniqueValue($array, $v);
            }
        }
    }

    public static function insertValue(array &$array = NULL, $position, $value) {
        if (isset($array)) {
            array_splice($array, $position, 0, $value);
        }
        else {
            $array[$position] = $value;
        }
    }

    public static function appendValue(array &$array = NULL, $value) {
        if (isset($value)) {
            if (is_array($value)) {
                self::merge($array, $value);
            }
            else {
                $array[] = $value;
            }
        }
    }

    public static function merge(array &$destinationArray = NULL, array $sourceArray = NULL) {
        if (!isset($sourceArray)) {
            return;
        }

        if (isset($destinationArray)) {
            $destinationArray = array_merge($destinationArray, $sourceArray);
        }
        else {
            $destinationArray = $sourceArray;
        }
    }

    public static function search(array $array = NULL, $propertyName, $needle) {
        if (isset($array)) {
            foreach ($array as $key => $item) {
                $v = ObjectHelper::getPropertyValue($item, $propertyName);
                if ($v == $needle) {
                    return $key;
                }
            }
        }

        return NULL;
    }

    public static function copy(array &$array = NULL) {
        $clonedArray = NULL;

        if (isset($array)) {
            // it is possible that the array was empty. I expect it was a reason for that. We do not want to convert it to NULL
            $clonedArray = array();

            foreach ($array as $key => $value) {
                $clonedArray[$key] = is_array($value)
                    ? self::copy($value)
                    : (is_object($value) ? clone $value : $value);
            }
        }

        return $clonedArray;
    }

    public static function trim($values) {
        $trimmedValues = NULL;

        if (isset($values)) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $trimmedValue = is_array($value) ? self::trim($value) : StringHelper::trim($value);
                    if (isset($trimmedValue)) {
                        $trimmedValues[StringHelper::trim($key)] = $trimmedValue;
                    }
                }
            }
            else {
                // provided value is not an array. Converting result to an array with one element
                $value = StringHelper::trim($values);
                if (isset($value)) {
                    $trimmedValues[] = $value;
                }
            }
        }

        return $trimmedValues;
    }

    public static function serialize($values = NULL, $delimiter = ', ', $addArrayBrackets = FALSE, $isStringQuoted = TRUE, $includeNullValue = TRUE) {
        if (!isset($values)) {
            return NULL;
        }

        $isIndexArray = self::isIndexed($values);

        $s = '';
        foreach ($values as $key => $value) {
            if (!isset($value) && !$includeNullValue) {
                continue;
            }

            if ($s != '') {
                $s .= $delimiter;
            }

            if (!$isIndexArray) {
                $s .= isset($key)
                    ? ((is_numeric($key) || !$isStringQuoted) ? $key : "'$key'")
                    : 'null';
                $s .= ' = ';
            }

            $s .= isset($value)
                ? ((is_array($value) || is_object($value))
                    ? self::serialize($value, $delimiter, TRUE, $isStringQuoted, $includeNullValue)
                    : ((is_numeric($value) || !$isStringQuoted) ? $value : "'$value'"))
                : 'null';
        }

        return $addArrayBrackets ? "[$s]" : $s;
    }
}
