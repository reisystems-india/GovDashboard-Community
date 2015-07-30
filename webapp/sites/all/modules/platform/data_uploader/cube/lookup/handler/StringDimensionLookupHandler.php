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


class StringDimensionLookupHandler extends AbstractDimensionLookupHandler {

    public function prepareLookupValue($value) {
        $lookupValue = new DimensionLookupHandler__LookupValue();
        $lookupValue->setPropertyValue('value', $value);

        return $lookupValue;
    }

    public function prepareDatasetColumnLookupIds($datasetName, ColumnMetaData $column, array &$lookupValues) {
        $lookupDatasetName = StarSchemaNamingConvention::getAttributeRelatedName($datasetName, $column->name);
        $sequenceName = $lookupDatasetName;

        $lookupValueColumn = new ColumnMetaData();
        $lookupValueColumn->name = 'value';
        $lookupValueColumn->initializeTypeFrom($column->type);

        $this->prepareIdentifiers($lookupDatasetName, array($lookupValueColumn), NULL, $sequenceName, $lookupValues);
    }

    public function prepareDimension(MetaModel $metamodel, DatasetMetaData $dataset, $columnName, CubeMetaData $cube) {
        $logicalColumn = $dataset->getColumn($columnName);
        $column = $cube->factsDataset->getColumn($columnName);
        $dimension = $cube->getDimension($columnName);

        // preparing the dimension properties
        $dimension->attributeColumnName = $columnName;
        $dimension->setDatasetName(StarSchemaNamingConvention::getAttributeRelatedName($dataset->name, $columnName));

        // preparing dimension dataset
        $dimension->dataset = new DatasetMetaData();
        $dimension->dataset->name = $dimension->datasetName;
        $dimension->dataset->publicName = $dataset->publicName . " [$logicalColumn->publicName]";
        $dimension->dataset->description = t("Lookup table to store unique values from '@columnName' column", array('@columnName' => $logicalColumn->publicName));
        $dimension->dataset->datasourceName = $dataset->datasourceName;
        $dimension->dataset->source = StarSchemaNamingConvention::getAttributeRelatedName($dataset->source, $columnName);
        $dimension->dataset->markAsPrivate();
        // adding dimension dataset aliases
        if (isset($dataset->aliases)) {
            foreach ($dataset->aliases as $alias) {
                $dimension->dataset->aliases[] = StarSchemaNamingConvention::getAttributeRelatedName($alias, $columnName);
            }
        }

        // adding key column
        $keyColumn = $dimension->dataset->registerColumn($columnName);
        $keyColumn->publicName = $logicalColumn->publicName;
        $keyColumn->description = t("System generated ID to identify each unique value from '@columnName' column", array('@columnName' => $logicalColumn->publicName));
        $keyColumn->initializeTypeFrom(Sequence::getSequenceColumnType());
        $keyColumn->key = TRUE;
        $keyColumn->visible = FALSE;
        
        // adding 'value' column
        $valueColumn = $dimension->dataset->registerColumn('value');
        $valueColumn->publicName = $logicalColumn->publicName;
        $valueColumn->description = t("Actual value from '@columnName' column", array('@columnName' => $logicalColumn->publicName));
        $valueColumn->initializeTypeFrom($logicalColumn->type);

        // facts dataset column contains a reference to lookup
        $column->initializeTypeFrom(Sequence::getSequenceColumnType());
        $column->type->logicalApplicationType = StringDataTypeHandler::DATA_TYPE;

        // marking that the dimension dataset object contains complete meta data & registering it in meta model
        $dimension->dataset->markAsComplete();
        $metamodel->registerDataset($dimension->dataset);

        // adding a reference to the dimension dataset
        $referenceName = $dimension->datasetName;
        $metamodel->registerSimpleReferencePoint($referenceName, $dimension->datasetName, $columnName);
        $metamodel->registerSimpleReferencePoint($referenceName, $cube->factsDatasetName, $columnName);
    }

    public function unprepareDimension(MetaModel $metamodel, DatasetMetaData $dataset, $columnName) {
        $datasetName = StarSchemaNamingConvention::getAttributeRelatedName($dataset->name, $columnName);

        $metamodel->unregisterDataset($datasetName);
    }

    public function adjustReferencePointColumn(AbstractMetaModel $metamodel, $datasetName, $columnName) {
        // FIXME we should work only with one way to find a cube
        $cube = $metamodel->findCubeByDatasetName($datasetName);
        if (!isset($cube)) {
            $cube = $metamodel->getCube($datasetName);
        }

        $dimension = $cube->getDimension($columnName);

        $shared = FALSE;

        return array($dimension->datasetName, 'value', $shared);
    }
}
