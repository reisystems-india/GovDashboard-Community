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


// Theoretically this class has to be located in data_controller_ddl module
// This class is placed here because we need it when we query database and data_controller_ddl module could be turned off

class Sequence {

    public static function registerDataSource($datasourceName, $sequenceDataType = IntegerDataTypeHandler::DATA_TYPE, $default = TRUE) {
        $datasources = &drupal_static(__CLASS__ . '::datasources');

        if (isset($datasources)) {
            foreach ($datasources as $registration) {
                if ($registration->datasourceName == $datasourceName) {
                    throw new IllegalStateException(t(
                        'The sequence provider has been registered already: %datasourceName',
                        array('%datasourceName' => $datasourceName)));
                }
                // checking if there are any other default configuration
                if ($default && $registration->default) {
                    throw new IllegalStateException(t(
                        'Could not register %datasourceName sequence provider. Another default provider has been registered already: %previousDefaultDatasourceName',
                        array('%datasourceName' => $datasourceName, '%previousDefaultDatasourceName' => $registration->datasourceName)));
                }
            }
        }

        $registration = new __Sequence_Registration();
        $registration->datasourceName = $datasourceName;
        $registration->sequenceColumnType = new ColumnType($sequenceDataType);
        $registration->default = $default;

        $datasources[$datasourceName] = $registration;
    }

    public static function unregisterDataSource($datasourceName) {
        // checking that corresponding registration exists
        self::getRegistration($datasourceName);

        $datasources = &drupal_static(__CLASS__ . '::datasources');

        unset($datasources[$datasourceName]);
    }

    protected static function getDefaultRegistration() {
        $defaultRegistration = NULL;

        $datasources = &drupal_static(__CLASS__ . '::datasources');

        if (isset($datasources)) {
            foreach ($datasources as $registration) {
                if ($registration->default) {
                    if (isset($defaultRegistration)) {
                        throw new IllegalStateException(t(
                            'Several default sequence providers have been registered: [%datasourceNameA, %datasourceNameB]',
                            array('%datasourceNameA' => $defaultRegistration->datasourceName, '%datasourceNameB' => $registration->datasourceName)));
                    }

                    $defaultRegistration = $registration;
                }
            }
        }

        if (!isset($defaultRegistration)) {
            throw new IllegalStateException(t('Default sequence provider has not been registered'));
        }


        return $defaultRegistration;
    }

    protected static function getRegistration($datasourceName) {
        $datasources = &drupal_static(__CLASS__ . '::datasources');

        if (!isset($datasources[$datasourceName])) {
            throw new IllegalStateException(t(
                'The sequence provider has not been registered: %datasourceName',
                array('%datasourceName' => $datasourceName)));
        }

        return $datasources[$datasourceName];
    }

    public static function getSequenceColumnType($datasourceName = NULL) {
        $registration = isset($datasourceName) ? self::getRegistration($datasourceName) : self::getDefaultRegistration();

        return $registration->sequenceColumnType;
    }

    public static function getNextSequenceValue($sequenceName, $datasourceName = NULL) {
        $values = self::getNextSequenceValues($sequenceName, 1, $datasourceName);

        return $values[0];
    }

    public static function getNextSequenceValues($sequenceName, $quantity, $datasourceName = NULL) {
        $dataQueryController = data_controller_get_instance();

        $registration = isset($datasourceName) ? self::getRegistration($datasourceName) : self::getDefaultRegistration();

        return $dataQueryController->getNextSequenceValues($registration->datasourceName, $sequenceName, $quantity);
    }
}


class __Sequence_Registration extends AbstractObject {

    public $datasourceName = NULL;
    public $sequenceColumnType = NULL;
    public $default = NULL;
}