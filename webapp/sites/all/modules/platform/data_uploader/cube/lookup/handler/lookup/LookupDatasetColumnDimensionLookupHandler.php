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


class LookupDatasetColumnDimensionLookupHandler extends AbstractDimensionLookupHandler {

    protected $datasetName = NULL;
    protected $columnName = NULL;

    public function __construct($datatype) {
        parent::__construct($datatype);

        list($this->datasetName, $this->columnName) = ReferencePathHelper::splitReference($datatype);
    }

    public function prepareLookupValue($value) {
        $lookupValue = new DimensionLookupHandler__LookupValue();
        $lookupValue->setPropertyValue($this->columnName, $value);

        return $lookupValue;
    }

    public function prepareDatasetColumnLookupIds($datasetName, ColumnMetaData $column, array &$lookupValues) {
        $metamodel = data_controller_get_metamodel();

        $logicalDataset = $metamodel->getDataset($this->datasetName);
        $logicalColumn = $logicalDataset->getColumn($this->columnName);

        $cubeName = $logicalDataset->name;
        $cube = $metamodel->getCube($cubeName);
        $factsDataset = $metamodel->getDataset($cube->factsDatasetName);
        $attributeColumn = $factsDataset->getColumn($this->columnName);

        $lookupHandler = DimensionLookupFactory::getInstance()->getHandler($logicalColumn->type->getLogicalApplicationType());
        list($adjustedDatasetName, $adjustedColumnName) = $lookupHandler->adjustReferencePointColumn($metamodel, $factsDataset->name, $attributeColumn->name);

        $adjustedDataset = $metamodel->getDataset($adjustedDatasetName);
        $adjustedColumn = $adjustedDataset->getColumn($adjustedColumnName);

        // this request is part of chain of references
        $adjustedLookupValues = NULL;
        if (($factsDataset->name != $adjustedDataset->name) || ($attributeColumn->name != $adjustedColumnName)) {
            $nestedLookupHandler = DimensionLookupFactory::getInstance()->getHandler($adjustedColumn->type->getLogicalApplicationType());
            // preparing list of lookup object for nested reference
            $nestedLookupValues = NULL;
            foreach ($lookupValues as $lookupKey => $lookupValue) {
                $value = $lookupValue->getPropertyValue($attributeColumn->name);
                $nestedLookupValues[$lookupKey] = $nestedLookupHandler->prepareLookupValue($value);
            }
            // loading identifiers from nested reference
            if ($logicalColumn->type->getReferencedDatasetName() == NULL) {
                $nestedLookupHandler->loadIdentifiers($adjustedDataset->name, array($adjustedColumn), $nestedLookupValues);
            }
            else {
                $nestedLookupHandler->prepareDatasetColumnLookupIds($adjustedDataset->name, $adjustedColumn, $nestedLookupValues);
            }

            // using loaded identifiers
            foreach ($lookupValues as $lookupKey => $lookupValue) {
                $nestedLookupValue = $nestedLookupValues[$lookupKey];
                if (!isset($nestedLookupValue->identifier)) {
                    continue;
                }

                $adjustedLookupKey = self::prepareLookupKey($nestedLookupValue->identifier);
                $lookupValue->setPropertyValue($attributeColumn->name, $nestedLookupValue->identifier);

                $adjustedLookupValues[$adjustedLookupKey] = $lookupValue;
            }
        }
        else {
            $adjustedLookupValues = $lookupValues;
        }

        // do not use prepareIdentifiers() because new records will be inserted
        if (isset($adjustedLookupValues)) {
            $this->loadIdentifiers($factsDataset->name, array($attributeColumn), $adjustedLookupValues);
        }
    }

    public function prepareDimension(MetaModel $metamodel, DatasetMetaData $dataset, $columnName, CubeMetaData $cube) {
        $column = $cube->factsDataset->getColumn($columnName);
        $dimension = $cube->getDimension($columnName);

        $referencedCube = $metamodel->findCube($this->datasetName);
        // TODO DHS Management Cube (we do not have cubes for lookups)
        $referencedDatasetName = isset($referencedCube) ? $referencedCube->factsDatasetName : $this->datasetName;

        // preparing dimension properties
        $dimension->attributeColumnName = $columnName;

        // preparing dimension dataset
        $dimension->setDatasetName($this->datasetName);
        $dimension->dataset = $metamodel->getDataset($dimension->datasetName);

        // facts dataset column contains a reference to lookup
        // TODO DHS Management Cube (column type has been already prepared)
        if (!isset($column->type->applicationType)) {
            $column->initializeTypeFrom(Sequence::getSequenceColumnType());
        }

        // adding a reference to dimension dataset
        $referenceName = $referencedDatasetName;
        $metamodel->registerSimpleReferencePoint($referenceName, $referencedDatasetName, NULL);
        $metamodel->registerSimpleReferencePoint($referenceName, $cube->factsDatasetName, $columnName);
        // ... to support retrieving properties of the dimension dataset
        $metamodel->registerSimpleReferencePoint($referenceName, $dimension->datasetName, $dimension->dataset->getKeyColumn()->name);
    }

    public function adjustReferencePointColumn(AbstractMetaModel $metamodel, $datasetName, $columnName) {
        // FIXME we should work only with one way to find a cube
        $cube = $metamodel->findCubeByDatasetName($this->datasetName);
        if (!isset($cube)) {
            $cube = $metamodel->getCube($this->datasetName);
        }

        $adjustedDatasetName = $cube->factsDatasetName;
        $adjustedDataset = $metamodel->getDataset($adjustedDatasetName);

        $adjustedColumnName = $adjustedDataset->getKeyColumn()->name;

        $shared = TRUE;
        return array($adjustedDatasetName, $adjustedColumnName, $shared);
    }
}
