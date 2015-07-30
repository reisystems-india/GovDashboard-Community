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


abstract class AbstractDataSourceHandler extends AbstractObject implements DataSourceHandler {

    private $datasourceType = NULL;
    private $extensionConfigurations = NULL;
    private $extensionInstances = NULL;
    private $originalDataType4ReferencedDataTypes = NULL;

    public function __construct($datasourceType, $extensionConfigurations) {
        parent::__construct();
        $this->datasourceType = $datasourceType;
        $this->extensionConfigurations = $extensionConfigurations;
    }

    public function getDataSourceType() {
        return $this->datasourceType;
    }

    public function getExtension($functionalityName) {
        if (isset($this->extensionInstances[$functionalityName])) {
            return $this->extensionInstances[$functionalityName];
        }

        $extensionClassName = isset($this->extensionConfigurations[$functionalityName])
            ? $this->extensionConfigurations[$functionalityName]
            : NULL;
        if (!isset($extensionClassName)) {
            throw new IllegalStateException(t(
                '%functionalityName function is not implemented for %datasourceType data source type',
                array('%datasourceType' => $this->getDataSourceType(), '%functionalityName' => $functionalityName)));
        }

        $extensionInstance = new $extensionClassName();

        $this->extensionInstances[$functionalityName] = $extensionInstance;

        return $extensionInstance;
    }

    public function getMaximumEntityNameLength() {
        return $this->getExtension('maximumEntityNameLength')->getLength($this);
    }

    protected function adjustReferencedDataType4Casting($datasetName, $columnName) {
        throw new UnsupportedOperationException();
    }

    protected function prepareDataType4Casting($datatype) {
        if (isset($datatype)) {
            list($datasetName, $columnName) = ReferencePathHelper::splitReference($datatype);
            if (isset($datasetName)) {
                $adjustedDataType = isset($this->originalDataType4ReferencedDataTypes[$datatype])
                    ? $this->originalDataType4ReferencedDataTypes[$datatype]
                    : NULL;

                if (!isset($adjustedDataType)) {
                    $adjustedDataType = $this->adjustReferencedDataType4Casting($datasetName, $columnName);
                    $this->originalDataType4ReferencedDataTypes[$datatype] = $adjustedDataType;
                }

                return $adjustedDataType;
            }
        }

        return $datatype;
    }

    protected function formatStringValue($value) {
        throw new UnsupportedOperationException();
    }

    public function formatDateValue($formattedValue, $format, $datatype) {
        return $this->getExtension('formatDateValue')->formatStringToDate($this, $formattedValue, $format, $datatype);
    }

    public function formatValue($datatype, $value) {
        $adjustedDataType = $this->prepareDataType4Casting($datatype);

        $datatypeHandler = DataTypeFactory::getInstance()->getHandler($adjustedDataType);

        // converting value to format 'common' for server side
        $castValue = $datatypeHandler->castValue($value);
        // converting value to format which is appropriate for storage
        $storageValue = $datatypeHandler->castToStorageValue($castValue);
        if (isset($storageValue)) {
            $datatypeHandler = DataTypeFactory::getInstance()->getHandler($adjustedDataType);

            // database-specific storage value adjustment
            $storageDataType = $datatypeHandler->getStorageDataType();
            switch ($storageDataType) {
                case StringDataTypeHandler::DATA_TYPE:
                    $formattedValue = $this->formatStringValue($storageValue);
                    break;
                case IntegerDataTypeHandler::DATA_TYPE:
                case NumberDataTypeHandler::DATA_TYPE:
                case CurrencyDataTypeHandler::DATA_TYPE:
                case PercentDataTypeHandler::DATA_TYPE:
                    $formattedValue = $storageValue;
                    break;
                case DateDataTypeHandler::DATA_TYPE:
                case TimeDataTypeHandler::DATA_TYPE:
                case DateTimeDataTypeHandler::DATA_TYPE:
                    $formattedValue = $this->formatStringValue($storageValue);
                    $formattedValue = $this->formatDateValue($formattedValue, $datatypeHandler->getStorageFormat(), $storageDataType);
                    break;
                case BooleanDataTypeHandler::DATA_TYPE:
                    $formattedValue = is_int($storageValue) ? $storageValue : $this->formatStringValue($storageValue);
                    break;
                default:
                    throw new UnsupportedOperationException(t(
                        "Unsupported data type %datatype to format the value: %value",
                        array('%datatype' => $adjustedDataType, '%value' => $value)));
            }
        }
        else {
            $formattedValue = 'NULL';
        }

        return $formattedValue;
    }

    public function startTransaction($datasourceName) {
        $this->errorTransactionNotSupported($datasourceName);
    }

    public function commitTransaction($datasourceName) {
        $this->errorTransactionNotSupported($datasourceName);
    }

    public function rollbackTransaction($datasourceName) {
        $this->errorTransactionNotSupported($datasourceName);
    }

    protected function errorTransactionNotSupported($datasourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $datasource = $environment_metamodel->getDataSource($datasourceName);

        throw new UnsupportedOperationException(t(
            'Transaction support is not available for the data source: %datasourceName',
            array('%datasourceName' => $datasource->publicName)));
    }
}
