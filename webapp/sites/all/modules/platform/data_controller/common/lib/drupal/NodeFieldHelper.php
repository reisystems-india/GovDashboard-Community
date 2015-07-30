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


function get_node_field_value($node, $fieldName, $index = 0, $storageSuffixName = 'value', $required = FALSE) {
    $value = NULL;

    if (!isset($node->language)) {
        LogHelper::log_debug($node);
        throw new IllegalArgumentException(t(
            '%fieldName@index field is not accessible because language is not set for the node: %nodeId',
            array(
                '%nodeId' => $node->nid,
                '%fieldName' => $fieldName,
                '@index' => ((!isset($index) || ($index == 0)) ? '' : t('[%index]', array('%index' => $index))))));
    }

    $fieldValue = isset($node->$fieldName) ? $node->$fieldName : NULL;
    if (isset($fieldValue[$node->language])) {
        $fieldLocalizedValues = $fieldValue[$node->language];
        if (isset($index)) {
            // accessing individual value
            if (isset($fieldLocalizedValues[$index][$storageSuffixName])) {
                $value = StringHelper::trim($fieldLocalizedValues[$index][$storageSuffixName]);
            }
        }
        else {
            // we need to return an array of values
            foreach ($fieldLocalizedValues as $i => $fieldLocalizedValue) {
                $v = isset($fieldLocalizedValue[$storageSuffixName]) ? $fieldLocalizedValue[$storageSuffixName] : NULL;
                if (!isset($v)) {
                    $v = StringHelper::trim($v);
                }

                $value[$i] = $v;
            }
        }
    }
    
    if ($required && !isset($value)) {
        LogHelper::log_debug($node);
        throw new IllegalArgumentException(t(
            '%fieldName@index field has not been set for the node: %nodeId',
            array(
                '%nodeId' => $node->nid,
                '%fieldName' => $fieldName,
                '@index' => ((!isset($index) || ($index == 0)) ? '' : t('[%index]', array('%index' => $index))))));
    }

    return $value;
}

function get_node_field_node_ref($node, $fieldName, $index = 0, $storageSuffixName = 'nid', $required = FALSE) {
    return get_node_field_int_value($node, $fieldName, $index, $storageSuffixName, $required);
}

function get_node_field_int_value($node, $fieldName, $index = 0, $storageSuffixName = 'value', $required = FALSE) {
    $value = get_node_field_value($node, $fieldName, $index, $storageSuffixName, $required);

    $result = NULL;
    if (is_array($value)) {
        $vs = array();
        foreach ($value as $i => $v) {
            $vs[$i] = (int) $v;
        }
        $result = $vs;
    }
    elseif (isset($value)) {
        $result = (int) $value;
    }

    return $result;
}

function get_node_field_boolean_value($node, $fieldName, $index = 0, $storageSuffixName = 'value', $default = FALSE) {
    $value = get_node_field_int_value($node, $fieldName, $index, $storageSuffixName);

    $result = $default;
    if (is_array($value)) {
        $vs = array();
        foreach ($value as $i => $v) {
            $vs[$i] = $v == 1;
        }
        $result = $vs;
    }
    elseif (isset($value)) {
        $result = $value == 1;
    }

    return $result;
}

function validate_node_field_composite_value($node, $fieldName, $index, $value, $decodeFlag) {
    $decodedValue = json_decode($value, $decodeFlag);
    if (!isset($decodedValue)) {
        throw new IllegalArgumentException(t(
            'Could not decode value for %fieldName field for %nodeId node',
            array(
                '%nodeId' => $node->nid,
                '%fieldName' => ($fieldName . (($index == 0) ? '' : "[$index]")))));
    }

    return $decodedValue;
}

function get_node_field_object_value($node, $fieldName, $index = 0, $storageSuffixName = 'value', $required = FALSE) {
    $value = get_node_field_value($node, $fieldName, $index, $storageSuffixName, $required);

    $result = NULL;
    if (is_array($value)) {
        $vs = array();
        foreach ($value as $i => $v) {
            $vs[$i] = validate_node_field_composite_value($node, $fieldName, $index, $v, FALSE);
        }
        $result = $vs;
    }
    elseif (isset($value)) {
        $result = validate_node_field_composite_value($node, $fieldName, $index, $value, FALSE);
    }

    return $result;
}
