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


class CubeUIMetaDataGenerator extends AbstractDatasetUIMetaDataGenerator {

    protected function prepareContext(__CubeUIMetaDataGenerator_CallContext $callcontext, CubeMetaData $cube) {
        // preparing list of facts dataset column names
        $callcontext->usedColumnNames = NULL;
        foreach ($cube->factsDataset->getColumns() as $column) {
            $callcontext->usedColumnNames[$column->name] = FALSE;
        }

        // preparing list of cube measure names
        $callcontext->usedMeasureNames = NULL;
        if (isset($cube->measures)) {
            foreach ($cube->measures as $measureName => $measure) {
                if (!$measure->isUsed()) {
                    continue;
                }

                $callcontext->usedMeasureNames[$measureName] = FALSE;
            }
        }
    }

    protected function generateAttributes(__CubeUIMetaDataGenerator_CallContext $callcontext, DatasetUIMetaData $datasetUIMetaData, CubeMetaData $cube, $referencePath) {
        $metamodel = data_controller_get_metamodel();

        $dimensions = $cube->getDimensions();
        if (!isset($dimensions)) {
            return;
        }

        $datasetStack = array($cube->factsDatasetName => TRUE);

        $keyColumn = $cube->factsDataset->findKeyColumn(FALSE);

        // preparing root element for 'distinct count' measures
        $distinctCountRootUIMetaData = new RootElementUIMetaData();
        $distinctCountRootUIMetaData->name = self::prepareReferencedElementName(
            $referencePath, $cube->factsDatasetName, StarSchemaNamingConvention::$MEASURE_NAME_SUFFIX__DISTINCT_COUNT);
        $distinctCountRootUIMetaData->publicName = t('Distinct Count');
        $distinctCountRootUIMetaData->description = t('List of columns for which count of distinct values could be calculated');
        $distinctCountRootUIMetaData->isSelectable = FALSE;

        foreach ($dimensions as $dimension) {
            $attributeColumn = $cube->factsDataset->getColumn($dimension->attributeColumnName);

            $dimensionUIMetaData = new AttributeUIMetaData();
            $dimensionUIMetaData->name = self::prepareAttributeUIMetaDataName($referencePath, $cube->factsDatasetName, $dimension->name, NULL);
            $dimensionUIMetaData->publicName = $dimension->publicName;
            $dimensionUIMetaData->description = $dimension->description;
            $dimensionUIMetaData->columnIndex = $attributeColumn->columnIndex;
            $dimensionUIMetaData->type = clone $attributeColumn->type;
            $dimensionUIMetaData->datasetName = $cube->factsDatasetName;

            // fixing public name (borrowing public name from corresponding facts column)
            if (($dimension->name == $dimensionUIMetaData->publicName) && ($attributeColumn->name != $attributeColumn->publicName)) {
                $dimensionUIMetaData->publicName = $attributeColumn->publicName;
            }

            // registering 'Distinct Count' measure for this dimension
            $this->prepareAttributeDistinctCountMeasure(
                $callcontext,
                $distinctCountRootUIMetaData,
                $referencePath, $cube,
                $attributeColumn, ParameterNameHelper::assemble($dimension->name));

            // registering the dimension-specific measures
            $this->prepareColumnMeasures($callcontext, $dimensionUIMetaData, $referencePath, $cube, $dimension->attributeColumnName);

            // supporting extension tables (PK - to - PK)
            if (isset($keyColumn) && ($keyColumn->name == $attributeColumn->name)) {
                $this->prepareColumnUIMetaData4ExtensionDataset($datasetStack, $dimensionUIMetaData, $referencePath, $cube, $dimension->name, NULL, $cube->factsDataset, $keyColumn);
            }

            if (isset($dimension->datasetName)) {
                $dimensionDataset = $metamodel->getDataset($dimension->datasetName);

                $dimensionDatasetUIMetaData = $dimensionUIMetaData;

                // creating additional root element if the attribute has branches
                if (count($attributeColumn->branches) > 0) {
                    $dimensionDatasetUIMetaData = new AttributeColumnUIMetaData();
                    $dimensionDatasetUIMetaData->name =
                        self::prepareAttributeUIMetaDataName($referencePath, $cube, $dimension->name, $dimensionDataset->getKeyColumn()->name) . '/*dimensionDataset*/';
                    $dimensionDatasetUIMetaData->publicName = $dimensionDataset->publicName;
                    $dimensionDatasetUIMetaData->description = $dimensionDataset->description;
                    $dimensionDatasetUIMetaData->isSelectable = FALSE;

                    $dimensionUIMetaData->registerElement($dimensionDatasetUIMetaData);
                }

                // supporting hierarchical dimension
                $this->generateColumnUIMetaData4DimensionDataset($datasetStack, $dimensionDatasetUIMetaData, $referencePath, $cube, $dimension->name, NULL, $dimension->datasetName);
            }

            // registering all column branches for the dimension
            $this->prepareAttributeColumnUIMetaData4ColumnBranch($datasetStack, $dimensionUIMetaData, $referencePath, $cube, $dimension->name, NULL, NULL, $attributeColumn);

            $datasetUIMetaData->registerAttribute($dimensionUIMetaData);

            $callcontext->usedColumnNames[$dimension->attributeColumnName] = TRUE;
        }

        if (count($distinctCountRootUIMetaData->elements) > 0) {
            $datasetUIMetaData->registerMeasure($distinctCountRootUIMetaData);
        }
    }

    protected function generateDatasetMeasures(__CubeUIMetaDataGenerator_CallContext $callcontext, DatasetUIMetaData $datasetUIMetaData, CubeMetaData $cube, $referencePath) {
        if (isset($callcontext->usedMeasureNames)) {
            // preparing list of dataset-wide measures (those are measures which were not used by any columns)
            foreach ($callcontext->usedMeasureNames as $measureName => $used) {
                if ($used) {
                    continue;
                }

                // we do not need to show several 'Record Count' measures
                if (isset($referencePath) && ($measureName === StarSchemaNamingConvention::$MEASURE_NAME__RECORD_COUNT)) {
                    continue;
                }

                // checking if the measure could be linked to facts
                $possibleFactColumnNames = StarSchemaNamingConvention::preparePossibleOwners4Measure($measureName);
                if (isset($possibleFactColumnNames)) {
                    foreach ($possibleFactColumnNames as $possibleFactColumnName) {
                        $possibleFactUIMetaDataName = self::prepareColumnUIMetaDataName($referencePath, $cube->factsDatasetName, $possibleFactColumnName);
                        $attributeUIMetaData = $datasetUIMetaData->findAttribute($possibleFactUIMetaDataName);
                        if (isset($attributeUIMetaData)) {
                            $this->prepareAttributeCustomMeasure($attributeUIMetaData, $referencePath, $cube, $measureName);
                            continue 2;
                        }
                    }
                }

                // adding dataset custom measure
                $measure = $cube->measures[$measureName];

                $measureUIMetaData = new CubeMeasureUIMetaData();
                $measureUIMetaData->name = self::prepareMeasureUIMetaDataName($referencePath, $cube, $measureName);
                $measureUIMetaData->publicName = $measure->publicName;
                $measureUIMetaData->description = $measure->description;
                $measureUIMetaData->type = clone $measure->type;

                $datasetUIMetaData->registerMeasure($measureUIMetaData);
            }
        }
    }

    protected function fixAttributeUIMetaData(AbstractDataElementUIMetaData $elementUIMetaData) {
        // processing the nested elements
        foreach ($elementUIMetaData->elements as $element) {
            $this->fixAttributeUIMetaData($element);
        }

        // it has only one visible nested element
        $nestedElementUIMetaData = NULL;
        foreach ($elementUIMetaData->elements as $element) {
            if ($element->isVisible) {
                if (isset($nestedElementUIMetaData)) {
                    return;
                }
                $nestedElementUIMetaData = $element;
            }
        }
        if (!isset($nestedElementUIMetaData)) {
            // if it has no children and not selectable then we hide it
            if (!$elementUIMetaData->isSelectable) {
                $elementUIMetaData->isVisible = FALSE;
            }

            return;
        }

        // the element type needs to be set and be of type INTEGER
        if (isset($elementUIMetaData->type)) {
            if ($elementUIMetaData->type->applicationType != IntegerDataTypeHandler::DATA_TYPE) {
                return;
            }
        }

        // combining the element and its nested element
        $elementUIMetaData->name = $nestedElementUIMetaData->name;

        $publicNamePrefixIndex = strpos($nestedElementUIMetaData->publicName, $elementUIMetaData->publicName);
        $elementUIMetaData->publicName = (($publicNamePrefixIndex === FALSE) || ($publicNamePrefixIndex > 0))
            ? ("{$elementUIMetaData->publicName} [$nestedElementUIMetaData->publicName]")
            : $nestedElementUIMetaData->publicName;

        if (isset($nestedElementUIMetaData->description)) {
            if (isset($elementUIMetaData->description)) {
                $descriptionPrefixIndex = strpos($nestedElementUIMetaData->description, $elementUIMetaData->description);
                $elementUIMetaData->description = (($descriptionPrefixIndex === FALSE) || ($descriptionPrefixIndex > 0))
                    ? ("{$nestedElementUIMetaData->description} ({$elementUIMetaData->description})")
                    : $nestedElementUIMetaData->description;
            }
            else {
                $elementUIMetaData->description = $nestedElementUIMetaData->description;
            }
        }
        $elementUIMetaData->isSelectable = $nestedElementUIMetaData->isSelectable;
        $elementUIMetaData->elements = $nestedElementUIMetaData->elements;
        $elementUIMetaData->type = $nestedElementUIMetaData->type;
        $elementUIMetaData->datasetName = $nestedElementUIMetaData->datasetName;
    }

    protected function fixCubeUIMetaData(DatasetUIMetaData $datasetUIMetaData) {
        if (isset($datasetUIMetaData->attributes)) {
            foreach ($datasetUIMetaData->attributes as $attribute) {
                $this->fixAttributeUIMetaData($attribute);
            }
        }
    }

    public function generate(DatasetUIMetaData $datasetUIMetaData, CubeMetaData $cube, $referencePath = NULL) {
        $callcontext = new __CubeUIMetaDataGenerator_CallContext();

        $datasetUIMetaData->name = $cube->name;
        $datasetUIMetaData->publicName = $cube->publicName;
        $datasetUIMetaData->description = $cube->description;

        $this->prepareContext($callcontext, $cube);
        $this->generateAttributes($callcontext, $datasetUIMetaData, $cube, $referencePath);
        $this->generateDatasetMeasures($callcontext, $datasetUIMetaData, $cube, $referencePath);

        $this->fixCubeUIMetaData($datasetUIMetaData);
    }

    // *************************************************************************
    // *  Prepare attribute
    // *************************************************************************
    protected function generateColumnUIMetaData4DimensionDataset(array $datasetStack, AbstractAttributeUIMetaData $parentUIMetaData, $referencePath, CubeMetaData $cube, $dimensionName, $dimensionReferencePath, $datasetName) {
        $metamodel = data_controller_get_metamodel();

        list($adjustedDatasetName) = gd_data_controller_metamodel_adjust_dataset_name($datasetName);
        $dataset = $metamodel->getDataset($adjustedDatasetName);

        if (isset($datasetStack[$dataset->name]) && $datasetStack[$dataset->name]) {
            return;
        }
        $datasetStack[$dataset->name] = TRUE;

        $eligibleColumnNames = NULL;
        foreach ($dataset->getColumns() as $column) {
            if (!$column->isVisible()) {
                continue;
            }

            $eligibleColumnNames[] = $column->name;

            if ($column->isKey()) {
                list($referencedDatasetName) = ReferencePathHelper::splitReference($column->type->getLogicalApplicationType());
                if (isset($referencedDatasetName) && !isset($datasetStack[$referencedDatasetName])) {
                    $datasetStack[$referencedDatasetName] = FALSE;
                }
            }
        }

        foreach ($eligibleColumnNames as $eligibleColumnName) {
            $column = $dataset->getColumn($eligibleColumnName);

            if ($column->isKey()) {
                $this->prepareColumnUIMetaData4ExtensionDataset($datasetStack, $parentUIMetaData, $referencePath, $cube, $dimensionName, $dimensionReferencePath, $dataset, $column);
            }
            $attributeUIMetaData = $this->prepareAttributeColumnUIMetaData($datasetStack, $referencePath, $cube, $dimensionName, $dimensionReferencePath, $dataset, $column);

            $parentUIMetaData->registerElement($attributeUIMetaData);
        }
    }

    protected function prepareColumnUIMetaData4ExtensionDataset(array $datasetStack, AbstractAttributeUIMetaData $parentUIMetaData, $referencePath, CubeMetaData $cube, $dimensionName, $dimensionReferencePath, DatasetMetaData $dataset, ColumnMetaData $column) {
        $metamodel = data_controller_get_metamodel();

        $reference = $metamodel->findReference($dataset->name);
        if (!isset($reference)) {
            return;
        }

        // preparing list of the dataset extensions
        $extensionDatasets = NULL;
        foreach ($reference->points as $referencePoint) {
            foreach ($referencePoint->columns as $referencePointColumn) {
                if ($referencePointColumn->datasetName == $dataset->name) {
                    continue;
                }
                if (isset($extensionDatasets[$referencePointColumn->datasetName])) {
                    continue;
                }

                $referencedDataset = $metamodel->getDataset($referencePointColumn->datasetName);
                // FIXME excluding logical dataset. We should not have mix of logical and physical datasets in one reference
                if ($referencedDataset->sourceType == StarSchemaDatasetSourceTypeHandler::SOURCE_TYPE) {
                    continue;
                }
                // is it being processed?
                if (isset($datasetStack[$referencedDataset->name])) {
                    continue;
                }

                $referencedKeyColumn = $referencedDataset->findKeyColumn(FALSE);
                if (isset($referencedKeyColumn)
                        && (!isset($referencePointColumn->columnName) || ($referencePointColumn->columnName == $referencedKeyColumn->name))) {
                    $extensionDatasets[$referencePointColumn->datasetName] = $referencedDataset;
                }
            }
        }
        if (!isset($extensionDatasets)) {
            return;
        }

        // processing the extensions
        $adjustedExtensionDatasetNames = NULL;
        foreach ($extensionDatasets as $extensionDataset) {
            list($adjustedExtensionDatasetName) = gd_data_controller_metamodel_adjust_dataset_name($extensionDataset->name);
            $adjustedExtensionDatasetNames[$extensionDataset->name] = $metamodel->getDataset($adjustedExtensionDatasetName)->name;
        }

        foreach ($extensionDatasets as $extensionDataset) {
            $datasetUIMetaData = new AttributeColumnUIMetaData();
            $datasetUIMetaData->publicName = $extensionDataset->publicName;
            $datasetUIMetaData->description = $extensionDataset->description;
            $datasetUIMetaData->isSelectable = FALSE;

            $extensionReferencePath = isset($dimensionReferencePath)
                ? self::prepareReferencedElementName($dimensionReferencePath, $dataset->name, $column->name)
                : ReferencePathHelper::assembleReference($dataset->name, $column->name);

            $dimensionColumnName = self::prepareReferencedElementName($extensionReferencePath, $extensionDataset->name, $extensionDataset->getKeyColumn()->name);

            $datasetUIMetaData->name = self::prepareAttributeUIMetaDataName($referencePath, $cube->factsDatasetName, $dimensionName, $dimensionColumnName);

            $this->generateColumnUIMetaData4DimensionDataset($datasetStack, $datasetUIMetaData, $referencePath, $cube, $dimensionName, $extensionReferencePath, $extensionDataset->name);

            $parentUIMetaData->registerElement($datasetUIMetaData);
        }
    }

    protected function prepareAttributeColumnUIMetaData4ColumnBranch(array $datasetStack, AbstractAttributeUIMetaData $parentUIMetaData, $referencePath, CubeMetaData $cube, $dimensionName, $dimensionReferencePath, DatasetMetaData $dataset = NULL, ColumnMetaData $column) {
        if (isset($column->branches)) {
            foreach ($column->branches as $branch) {
                $branchAttributeUIMetaData = $this->prepareAttributeColumnUIMetaData(
                    $datasetStack, $referencePath, $cube, $dimensionName, $dimensionReferencePath, $dataset, $branch);
                $parentUIMetaData->registerElement($branchAttributeUIMetaData);
            }
        }
    }

    protected function prepareAttributeColumnUIMetaData(array $datasetStack, $referencePath, CubeMetaData $cube, $dimensionName, $dimensionReferencePath, DatasetMetaData $dataset = NULL, ColumnMetaData $column) {
        $dimensionColumnName = isset($dimensionReferencePath)
            ? self::prepareReferencedElementName($dimensionReferencePath, $dataset->name, $column->name)
            : $column->name;

        $attributeUIMetaData = new AttributeColumnUIMetaData();
        $attributeUIMetaData->name = self::prepareAttributeUIMetaDataName($referencePath, $cube->factsDatasetName, $dimensionName, $dimensionColumnName);
        $attributeUIMetaData->publicName = $column->publicName;
        $attributeUIMetaData->description = $column->description;
        $attributeUIMetaData->columnIndex = $column->columnIndex;
        $attributeUIMetaData->type = clone $column->type;
        $attributeUIMetaData->datasetName = $cube->factsDatasetName;

        if ($column->isKey()) {
            $attributeUIMetaData->isSelectable = FALSE;
        }

        if (!$column->isUsed()) {
            $attributeUIMetaData->isSelectable = FALSE;
        }

        if (!$column->isKey()) {
            $this->prepareAttributeColumnUIMetaData4ColumnBranch($datasetStack, $attributeUIMetaData, $referencePath, $cube, $dimensionName, $dimensionReferencePath, $dataset, $column);
        }

        list($referencedDatasetName) = ReferencePathHelper::splitReference($column->type->getLogicalApplicationType());
        if (isset($referencedDatasetName)) {
            $branchReferencePath = NULL;
            if (isset($dataset)) {
                $branchReferencePath = isset($dimensionReferencePath)
                    ? self::prepareReferencedElementName($dimensionReferencePath, $dataset->name, $column->name)
                    : ReferencePathHelper::assembleReference($dataset->name, $column->name);
            }
            $this->generateColumnUIMetaData4DimensionDataset(
                $datasetStack, $attributeUIMetaData, $referencePath, $cube, $dimensionName, $branchReferencePath, $referencedDatasetName);
        }

        return $attributeUIMetaData;
    }

    // *************************************************************************
    // *  Prepare measure
    // *************************************************************************
    protected function prepareAttributeDistinctCountMeasure(__CubeUIMetaDataGenerator_CallContext $callcontext, AbstractRootElementUIMetaData $attributeUIMetaData, $referencePath, CubeMetaData $cube, ColumnMetaData $column, $attributeName) {
        $measureName = StarSchemaNamingConvention::getAttributeRelatedMeasureName(
            $attributeName, StarSchemaNamingConvention::$MEASURE_NAME_SUFFIX__DISTINCT_COUNT);
        // the measure has been used already
        if (!isset($callcontext->usedMeasureNames[$measureName]) || $callcontext->usedMeasureNames[$measureName]) {
            return;
        }

        $measure = $cube->findMeasure($measureName);
        if (!isset($measure)) {
            return;
        }

        $measureUIMetaData = new AttributeMeasureUIMetaData();
        $measureUIMetaData->name = self::prepareMeasureUIMetaDataName($referencePath, $cube, $measureName);
        $measureUIMetaData->publicName = $column->publicName;
        $measureUIMetaData->description = $measure->description;
        $measureUIMetaData->type = clone $measure->type;

        $attributeUIMetaData->registerElement($measureUIMetaData);

        // marking that the measure is used by this attribute
        $callcontext->usedMeasureNames[$measureName] = TRUE;
    }

    protected function prepareColumnMeasures(__CubeUIMetaDataGenerator_CallContext $callcontext, AbstractAttributeUIMetaData $attributeUIMetaData, $referencePath, CubeMetaData $cube, $columnName) {
        if (isset($cube->measures)) {
            foreach ($cube->measures as $measureName => $measure) {
                if (!$measure->isUsed()) {
                    continue;
                }

                // it has been used already
                if ($callcontext->usedMeasureNames[$measureName]) {
                    continue;
                }

                $owners = StarSchemaNamingConvention::preparePossibleOwners4Measure($measureName);
                if (count($owners) != 1) {
                    continue;
                }

                $owner = $owners[0];
                if ($columnName != $owner) {
                    continue;
                }

                $measureUIMetaData = new FactMeasureUIMetaData();
                $measureUIMetaData->name = self::prepareMeasureUIMetaDataName($referencePath, $cube, $measureName);
                $measureUIMetaData->publicName = $measure->publicName;
                $measureUIMetaData->description = $measure->description;
                $measureUIMetaData->type = clone $measure->type;
                $measureUIMetaData->isSelectable = $measure->isUsed();

                // there is a problem with the measure function
                if (isset($measure->functionError)) {
                    $measureUIMetaData->description = $measure->functionError;
                    $measureUIMetaData->type = NULL;
                }

                $attributeUIMetaData->registerElement($measureUIMetaData);

                // marking that the measure as used by this fact
                $callcontext->usedMeasureNames[$measureName] = TRUE;
            }
        }
    }

    protected function prepareAttributeCustomMeasure(AbstractAttributeUIMetaData $attributeUIMetaData, $referencePath, CubeMetaData $cube, $measureName) {
        $measure = $cube->getMeasure($measureName);
        $measureUIMetaData = new FactMeasureUIMetaData();
        $measureUIMetaData->name = self::prepareMeasureUIMetaDataName($referencePath, $cube, $measureName);
        $measureUIMetaData->publicName = $measure->publicName;
        $measureUIMetaData->description = $measure->description;
        $measureUIMetaData->type = clone $measure->type;

        $attributeUIMetaData->registerElement($measureUIMetaData);
    }

    // *************************************************************************
    // *  Prepare element name
    // *************************************************************************
    public static function prepareAttributeUIMetaDataName($referencePath, $datasetName, $dimensionName, $dimensionColumnName) {
        return self::prepareElementUIMetaDataName(
            AbstractAttributeUIMetaData::NAME_SPACE,
            ParameterNameHelper::assemble(
                self::prepareReferencedElementName($referencePath, $datasetName, $dimensionName),
                $dimensionColumnName));
    }

    public static function prepareMeasureUIMetaDataName($referencePath, CubeMetaData $cube, $measureName) {
        return self::prepareElementUIMetaDataName(
            AbstractMeasureUIMetaData::NAME_SPACE,
            self::prepareReferencedElementName($referencePath, $cube->factsDatasetName, $measureName));
    }
}


class __CubeUIMetaDataGenerator_CallContext extends AbstractCallContext {

    public $usedColumnNames = NULL;
    public $usedMeasureNames = NULL;
}