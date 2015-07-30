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


class DataTypeUIMetaDataAssembler extends AbstractObject {

    public static function registerDataTypeMapping(DataTypeUIMapping $datatypeMapping) {
        $datatypeMappings = &drupal_static(__CLASS__ . '::datatypeMappings');

        if (isset($datatypeMappings[$datatypeMapping->datatype])) {
            throw new IllegalStateException(t(
                'UI Mapping for the data type has already been provided: %datatype',
                array('%datatype' => $datatypeMapping->datatype)));
        }

        $datatypeMappings[$datatypeMapping->datatype] = $datatypeMapping;
    }

    public function assemble() {
        $rootDataType = new DataTypeUIMetaData();
        $rootDataType->publicName = t('Data Types');
        $rootDataType->description = t('Supported data types');

        $registeredDataTypes = $this->prepareRegisteredDataTypes();
        if (isset($registeredDataTypes)) {
            $this->processRegisteredDataTypes($rootDataType, $registeredDataTypes);
        }

        $lookupDataTypes = $this->prepareLookupDataTypes();
        if (isset($lookupDataTypes)) {
            $rootLookupDataType = new DataTypeUIMetaData();
            $rootLookupDataType->publicName = t('Datasets');
            $rootLookupDataType->description = t('Supported referenceable datasets');

            $rootDataType->registerElement($rootLookupDataType);

            $this->processLookupDataTypes($rootLookupDataType, $lookupDataTypes);
        }

        return $rootDataType;
    }

    protected function prepareRegisteredDataTypes() {
        return DataTypeFactory::getInstance()->getSupportedDataTypes();
    }

    protected function processRegisteredDataTypes(DataTypeUIMetaData $rootDataType, array $registeredDataTypes) {
        $datatypeMappings = &drupal_static(__CLASS__ . '::datatypeMappings');

        foreach ($registeredDataTypes as $datatype => $datatypePublicName) {
            // checking if we have any mappings for the element
            $datatypeMapping = isset($datatypeMappings[$datatype]) ? $datatypeMappings[$datatype] : NULL;

            $element = $rootDataType->findElement($datatype);
            if (!isset($element)) {
                $element = new DataTypeUIMetaData();
                $element->name = $datatype;

                // looking for parent element
                $parentElement = $rootDataType;
                $parentDataTypeName = isset($datatypeMapping) ? $datatypeMapping->parentDataType : NULL;
                if (isset($parentDataTypeName)) {
                    $parentElement = $rootDataType->findElement($parentDataTypeName);

                    // creating 'empty' holder for the parent element
                    if (!isset($parentElement)) {
                        $parentElement = new DataTypeUIMetaData();
                        $parentElement->name = $parentDataTypeName;
                        $parentElement->publicName = ucwords(strtolower($parentDataTypeName));
                        $parentElement->description = $parentElement->publicName;

                        $rootDataType->registerElement($parentElement);
                    }

                    $element->parentName = $parentElement->name;
                }

                $parentElement->registerElement($element);
            }
            $element->publicName = $datatypePublicName;
            $element->description = $element->publicName;
            $element->isSelectable = TRUE;
            if (isset($datatypeMapping)) {
                $element->isVisible = $datatypeMapping->isVisible;
                $element->isParentShownOnSelect = $datatypeMapping->isParentShownOnSelect;
                $element->isAutomaticallyExpanded = $datatypeMapping->isAutomaticallyExpanded;
                $element->isKeyCompatible = $datatypeMapping->isKeyCompatible;
                $element->isFormulaExpressionCompatible = $datatypeMapping->isFormulaExpressionCompatible;
            }
        }
    }

    protected function prepareLookupDataTypes() {
        $lookupDataTypes = NULL;

        $metamodel = data_controller_get_metamodel();

        $datasourceName = gd_datasource_find_active();

        // selecting datasets which can be used as lookup
        foreach ($metamodel->datasets as $dataset) {
            $accessible = FALSE;
            if ($dataset->isPublic()) {
                // we need to include public datasets
                $accessible = TRUE;
            }
            elseif ($dataset->isProtected() && isset($datasourceName) && ($dataset->datasourceName == $datasourceName)) {
                // including protected datasets if they belong to active data source
                $accessible = TRUE;
            }
            if (!$accessible) {
                continue;
            }

            $primaryKeyColumnIndex = NULL;
            foreach ($dataset->getColumns() as $column) {
                if (!$column->isVisible()) {
                    continue;
                }

                if ($column->isKey()) {
                    // Composite key is not supported for lookup. There is no need to check rest of the columns
                    if (isset($primaryKeyColumnIndex)) {
                        continue 2;
                    }
                    else {
                        $primaryKeyColumnIndex = $column->columnIndex;
                    }
                }
            }
            // the dataset has to have single column primary key
            if (!isset($primaryKeyColumnIndex)) {
                continue;
            }

            // checking if the dataset is used as source for a cube. If yes, ignore it
            if ($metamodel->findCubeByDatasetName($dataset->name) != NULL) {
                continue;
            }

            $lookupDataTypes[] = $dataset->name;
        }

        return $lookupDataTypes;
    }

    protected function processLookupDataTypes(DataTypeUIMetaData $rootDataType, array $lookupDataTypes) {
        $metamodel = data_controller_get_metamodel();

        $datatypeMappings = &drupal_static(__CLASS__ . '::datatypeMappings');

        foreach ($lookupDataTypes as $datasetName) {
            $dataset = $metamodel->getDataset($datasetName);

            $datasetElement = new DataTypeUIMetaData();
            $datasetElement->name = $dataset->name;
            $datasetElement->publicName = $dataset->publicName;
            $datasetElement->description = $dataset->description;

            // processing primary key and columns which contain unique values
            foreach ($dataset->getColumns() as $column) {
                if (!$column->isVisible()) {
                    continue;
                }

                if (!isset($column->type->applicationType)) {
                    continue;
                }

                if (!$column->isKey()) {
                    if (!isset($datatypeMappings[$column->type->applicationType])) {
                        continue;
                    }

                    $datatypeMapping = $datatypeMappings[$column->type->applicationType];
                    if (!$datatypeMapping->isVisible) {
                        continue;
                    }
                    if (!$datatypeMapping->isKeyCompatible) {
                        continue;
                    }
                }

                $columnElement = new DataTypeUIMetaData();
                $columnElement->name = ReferencePathHelper::assembleReference($dataset->name, $column->name);
                $columnElement->publicName = $column->publicName;
                $columnElement->description = $column->description;
                $columnElement->isSelectable = TRUE;
                $columnElement->parentName = $datasetElement->name;
                $columnElement->isParentShownOnSelect = TRUE;
                $columnElement->isKeyCompatible = TRUE;
                $datasetElement->registerElement($columnElement);
            }

            $rootDataType->registerElement($datasetElement);
        }
    }
}
