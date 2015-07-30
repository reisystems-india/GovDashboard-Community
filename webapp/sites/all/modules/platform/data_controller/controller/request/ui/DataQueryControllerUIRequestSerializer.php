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


class DataQueryControllerUIRequestSerializer extends AbstractObject {

    public function serializeValue($name, $value) {
        if (!isset($value)) {
            return NULL;
        }

        $serializedValues = NULL;

        $serializedName = isset($name) ? $name : '';

        if (is_array($value)) {
            foreach ($value as $itemKey => $itemValue) {
                $serializedItemValue = self::serializeValue(NULL, $itemValue);
                foreach ($serializedItemValue as $k => $v) {
                    $key = $serializedName . '[' . $itemKey . ']' . $k;
                    $serializedValues[$key] =  $v;
                }
            }
        }
        else {
            $key = $serializedName;

            $serializedValue = $value;
            if (is_bool($serializedValue)) {
                $serializedValue = $serializedValue ? 'true' : 'false';
            }

            $serializedValues[$key] = $serializedValue;
        }

        return $serializedValues;
    }
}
