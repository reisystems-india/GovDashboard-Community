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


class ObjectHelper {

    const EXISTING_PROPERTY_RULE__SKIP_IF_PRESENT = 'skip';
    const EXISTING_PROPERTY_RULE__ERROR_IF_PRESENT = 'error';
    const EXISTING_PROPERTY_RULE__OVERRIDE_IF_PRESENT = 'override';

    public static function getPropertyValue($source, $propertyName) {
        $value = NULL;

        if (is_object($source)) {
            if (isset($source->$propertyName)) {
                $value = $source->$propertyName;
            }
        }
        elseif (is_array($source)) {
            if (isset($source[$propertyName]))  {
                $value = $source[$propertyName];
            }
        }

        return $value;
    }

    public static function copySelectedProperties(&$instance, $source, $sourcePropertyNames) {
        if (is_object($source) || is_array($source)) {
            foreach ($sourcePropertyNames as $sourcePropertyName) {
                $sourceValue = self::getPropertyValue($source, $sourcePropertyName);
                if (isset($sourceValue)) {
                    if (!isset($instance)) {
                        $instance = new stdClass();
                    }
                    $instance->$sourcePropertyName = $sourceValue;
                }
            }
        }

        return $instance;
    }

    public static function mergeWith(&$instance, $source, $mergeCompositeProperty = FALSE, $existingPropertyRule = self::EXISTING_PROPERTY_RULE__ERROR_IF_PRESENT) {
        if (isset($source)) {
            if (is_object($source) || is_array($source)) {
                $isPropertyIndexed = ArrayHelper::isIndexed($source);
                foreach ($source as $name => $value) {
                    $value = StringHelper::trim($value);
                    $mergedValue = self::getPropertyValue($instance, $name);

                    if (is_object($value) || is_array($value)) {
                        if ($mergeCompositeProperty) {
                            self::mergeWith($mergedValue, $value, TRUE, $existingPropertyRule);
                        }
                    }
                    else {
                        if (isset($mergedValue) && isset($value)) {
                            if (($mergedValue != $value)) {
                                switch ($existingPropertyRule) {
                                    case self::EXISTING_PROPERTY_RULE__SKIP_IF_PRESENT:
                                        break;
                                    case self::EXISTING_PROPERTY_RULE__OVERRIDE_IF_PRESENT:
                                        $mergedValue = $value;
                                        break;
                                    default:
                                        LogHelper::log_error(t(
                                            "'@propertyName' property already contains value: @existingPropertyValue. Merge cannot be performed with new value: @newPropertyValue",
                                            array('@propertyName' => $name, '@existingPropertyValue' => $mergedValue, '@newPropertyValue' => $value)));
                                        throw new UnsupportedOperationException(t(
                                            '%propertyName property already contains value. Merge cannot be performed',
                                            array('%propertyName' => $name)));
                                }
                            }
                        }
                        else {
                            $mergedValue = $value;
                        }
                    }

                    if (!isset($instance)) {
                        $instance = $isPropertyIndexed ? array() : new stdClass();
                    }
                    if ($isPropertyIndexed) {
                        $instance[$name] = $mergedValue;
                    }
                    else {
                        $instance->$name = $mergedValue;
                    }
                }
            }
            else {
                if (isset($instance)) {
                    if (($instance != $source)) {
                        switch ($existingPropertyRule) {
                            case self::EXISTING_PROPERTY_RULE__SKIP_IF_PRESENT:
                                break;
                            case self::EXISTING_PROPERTY_RULE__OVERRIDE_IF_PRESENT:
                                $instance = $source;
                                break;
                            default:
                                LogHelper::log_error(t(
                                    "The instance already contains value: @existingPropertyValue. Assignment cannot be performed with new value: @newPropertyValue",
                                    array('@existingPropertyValue' => $instance, '@newPropertyValue' => $source)));
                                throw new UnsupportedOperationException(t('The instance already contains value. Assignment cannot be performed'));
                        }
                    }
                }
                else {
                    $instance = $source;
                }
            }
        }

        return $instance;
    }
}
