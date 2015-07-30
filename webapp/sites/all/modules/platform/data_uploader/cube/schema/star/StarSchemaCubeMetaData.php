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


// TODO do we really need to extend this class from CubeMetaData. It has just static methods
// TODO use $logicalDataset instead of $dataset
class StarSchemaCubeMetaData extends CubeMetaData {

    const ATTRIBUTE = 'Attribute';
    const FACT = 'Fact';

    // cube generation functionality was split into two functions registerFromDataset() and initializeFromDataset() to support inter-cube references
    public static function registerFromDataset(MetaModel $metamodel, DatasetMetaData $dataset) {
        $cubeName = $dataset->name;

        $cube = new StarSchemaCubeMetaData();

        // preparing cube properties
        $cube->name = $cubeName;
        $cube->publicName = $dataset->publicName;

        $cube->factsDatasetName = StarSchemaNamingConvention::getFactsRelatedName($dataset->name);

        $metamodel->registerCube($cube);
    }

    public static function containsCategory(ColumnMetaData $column, $category) {
        if (!isset($column->type->applicationType)) {
            return FALSE;
        }

        $type = $column->type->applicationType;
        if (($type == NumberDataTypeHandler::DATA_TYPE)
            || ($type == CurrencyDataTypeHandler::DATA_TYPE)
            || ($type == PercentDataTypeHandler::DATA_TYPE)) {
            return $category == self::FACT;
        }
        elseif ($type == IntegerDataTypeHandler::DATA_TYPE) {
            $calculatedCategory = $column->isKey() ? self::ATTRIBUTE : self::FACT;

            // if logical application type is a reference this column is an attribute
            if (($calculatedCategory != self::ATTRIBUTE) && isset($column->type->logicalApplicationType)) {
                list($referencedDatasetName) = ReferencePathHelper::splitReference($column->type->logicalApplicationType);
                if (isset($referencedDatasetName)) {
                    $calculatedCategory = self::ATTRIBUTE;
                }
            }

            return $category == $calculatedCategory;
        }
        else {
            return $category == self::ATTRIBUTE;
        }
    }

    public static function initializeDimensionFromColumn(MetaModel $metamodel, CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        $logicalColumn = $dataset->getColumn($columnName);

        if ($logicalColumn->isVisible()) {
            // preparing dimension
            $dimension = $cube->registerDimension($logicalColumn->name);
            $dimension->publicName = $logicalColumn->publicName;
            $dimension->description = $logicalColumn->description;

            $handler = DimensionLookupFactory::getInstance()->getHandler($logicalColumn->type->getLogicalApplicationType());
            $handler->prepareDimension($metamodel, $dataset, $logicalColumn->name, $cube);
            $dimension->used = $logicalColumn->used;

            // ********** adding measure which counts unique values
            $attributeName = ParameterNameHelper::assemble($dimension->name);
            // adding measure
            $measureName = StarSchemaNamingConvention::getAttributeRelatedMeasureName(
                $attributeName, StarSchemaNamingConvention::$MEASURE_NAME_SUFFIX__DISTINCT_COUNT);
            $measure = $cube->registerMeasure($measureName);
            $measure->publicName = t("Distinct count for '@columnName' column", array('@columnName' => $logicalColumn->publicName));
            $measure->description = t(
                "System generated measure for '@columnName' column to count distinct values",
                array('@columnName' => $logicalColumn->publicName));
            $measure->additivity = MeasureAdditivity::NON_ADDITIVE;
            $measure->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
            $measure->used = $logicalColumn->used;
            // preparing expression
            list($measure->function, $measure->functionError) = self::assembleExpression($cube, $logicalColumn, 'COUNT(DISTINCT ', ')');
        }
    }

    public static function initializeMeasuresFromColumn(CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        $column = $dataset->getColumn($columnName);

        if ($column->isVisible() && $column->isUsed() && self::containsCategory($column, self::FACT)) {
            // adding column-specific measures
            self::registerFactMeasure($cube, $column, 'AVG', MeasureAdditivity::NON_ADDITIVE,
                // for integer field result of AVG function in most cases contains decimals
                (($column->type->applicationType == IntegerDataTypeHandler::DATA_TYPE) ? NumberDataTypeHandler::DATA_TYPE : NULL));
            self::registerFactMeasure($cube, $column, 'MAX', MeasureAdditivity::SEMI_ADDITIVE);
            self::registerFactMeasure($cube, $column, 'MIN', MeasureAdditivity::SEMI_ADDITIVE);
            self::registerFactMeasure($cube, $column, 'SUM', MeasureAdditivity::ADDITIVE,
                // for integer field result of SUM function could be greater than 2147483647 (2^31 - 1)
                (($column->type->applicationType == IntegerDataTypeHandler::DATA_TYPE) ? NumberDataTypeHandler::DATA_TYPE : NULL));
        }
    }

    public static function initializeFromColumn(MetaModel $metamodel, CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        self::initializeDimensionFromColumn($metamodel, $cube, $dataset, $columnName);
        self::initializeMeasuresFromColumn($cube, $dataset, $columnName);
    }

    public static function initializeFromDataset(MetaModel $metamodel, DatasetMetaData $dataset) {
        $cubeName = $dataset->name;
        $cube = $metamodel->unregisterCube($cubeName);

        // preparing cube properties
        $cube->description = $dataset->description;

        // preparing facts dataset
        $factsDataset = $cube->initiateFactsDataset();
        $factsDataset->name = $cube->factsDatasetName;
        $factsDataset->publicName = $dataset->publicName;
        $factsDataset->description = $dataset->description;
        $factsDataset->datasourceName = $dataset->datasourceName;
        $factsDataset->markAsPrivate();
        $factsDataset->source = StarSchemaNamingConvention::getFactsRelatedName($dataset->source);
        // calculating facts dataset aliases
        if (isset($dataset->aliases)) {
            foreach ($dataset->aliases as $alias) {
                $factsDataset->aliases[] = StarSchemaNamingConvention::getFactsRelatedName($alias);
            }
        }

        // preparing facts dataset columns and cube dimensions
        foreach ($dataset->getColumns(FALSE) as $logicalColumn) {
            // we need to preserve original column index
            $column = $factsDataset->initiateColumn();
            $column->name = $logicalColumn->name;
            $column->columnIndex = $logicalColumn->columnIndex;
            $column->publicName = $logicalColumn->publicName;
            $column->description = $logicalColumn->description;
            $column->key = $logicalColumn->key;
            $column->persistence = $logicalColumn->persistence;
            $column->source = $logicalColumn->source;
            $column->visible = $logicalColumn->visible;
            $column->used = $logicalColumn->used;
            $column->branches = ArrayHelper::copy($logicalColumn->branches);
            $factsDataset->registerColumnInstance($column);

            self::initializeDimensionFromColumn($metamodel, $cube, $dataset, $logicalColumn->name);

            if (isset($column->type->applicationType)) {
                $column->type->logicalApplicationType = $logicalColumn->type->applicationType;
            }
            else {
                // setting column type to original column type
                $column->type->applicationType = $logicalColumn->type->applicationType;
            }
        }

        // preparing cube measures
        // we do that in a separate loop because a calculated column can point to another calculated column with higher column index
        foreach ($dataset->getColumns(FALSE) as $logicalColumn) {
            self::initializeMeasuresFromColumn($cube, $dataset, $logicalColumn->name);
        }
        
        // marking that the facts dataset object contains complete meta data & registering it in meta model
        $factsDataset->markAsComplete();
        $metamodel->registerDataset($factsDataset);

        // preparing cube measures
        self::registerCubeMeasures($cube);

        $metamodel->registerCube($cube);

        return $cube;
    }

    public static function enableByColumn(CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        $column = $dataset->getColumn($columnName);

        $cube->getDimension($columnName)->used = TRUE;

        if (self::containsCategory($column, self::FACT)) {
            // we need to recreate measures
            self::deinitializeColumnMeasures($cube, $dataset, $columnName);
            self::initializeMeasuresFromColumn($cube, $dataset, $columnName);
        }
    }

    public static function disableByColumn(CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        $column = $dataset->getColumn($columnName);

        $cube->getDimension($columnName)->used = FALSE;

        if (self::containsCategory($column, self::FACT)) {
            self::disableFactMeasure($cube, $columnName, 'AVG');
            self::disableFactMeasure($cube, $columnName, 'MIN');
            self::disableFactMeasure($cube, $columnName, 'MAX');
            self::disableFactMeasure($cube, $columnName, 'SUM');
        }
    }

    public static function deinitializeColumnMeasures(CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        $column = $dataset->getColumn($columnName);
        if (self::containsCategory($column, self::FACT)) {
            self::unregisterFactMeasure($cube, $columnName, 'AVG');
            self::unregisterFactMeasure($cube, $columnName, 'MAX');
            self::unregisterFactMeasure($cube, $columnName, 'MIN');
            self::unregisterFactMeasure($cube, $columnName, 'SUM');
        }
    }

    public static function deinitializeByColumn(CubeMetaData $cube, DatasetMetaData $dataset, $columnName) {
        $metamodel = data_controller_get_metamodel();

        $column = $dataset->getColumn($columnName);

        $handler = DimensionLookupFactory::getInstance()->getHandler($column->type->getLogicalApplicationType());
        $handler->unprepareDimension($metamodel, $dataset, $column->name);

        // removing dimension
        $dimension = $cube->unregisterDimension($columnName);

        // removing measure which counts unique values
        $attributeName = ParameterNameHelper::assemble($dimension->name);
        $measureName = StarSchemaNamingConvention::getAttributeRelatedMeasureName(
            $attributeName, StarSchemaNamingConvention::$MEASURE_NAME_SUFFIX__DISTINCT_COUNT);
        $measure = $cube->findMeasure($measureName);
        if (isset($measure)) {
            $cube->unregisterMeasure($measureName);
        }

        self::deinitializeColumnMeasures($cube, $dataset, $columnName);
    }

    public static function deinitializeByDataset(MetaModel $metamodel, DatasetMetaData $dataset) {
        $cubeName = $dataset->name;

        $cube = $metamodel->unregisterCube($cubeName);

        // de-initializing dimensions
        foreach ($dataset->getColumns() as $column) {
            self::deinitializeByColumn($cube, $dataset, $column->name);
        }

        $metamodel->unregisterDataset($cube->factsDatasetName);
    }

    public static function adjustReferencePointColumn(AbstractMetaModel $metamodel, DatasetReferencePointColumn $referencePointColumn) {
        $datasetName = $referencePointColumn->datasetName;
        $dataset = $metamodel->getDataset($datasetName);

        $column = $dataset->getColumn($referencePointColumn->columnName);

        $handler = DimensionLookupFactory::getInstance()->getHandler($column->type->getLogicalApplicationType());

        list($adjustedDatasetName, $adjustedColumnName, $shared) = $handler->adjustReferencePointColumn($metamodel, $dataset->name, $referencePointColumn->columnName);

        if ($adjustedDatasetName === $referencePointColumn->datasetName) {
            if ($adjustedColumnName === $referencePointColumn->columnName) {
                // we do not need to change anything
            }
            else {
                throw new UnsupportedOperationException();
            }
        }
        else {
            $referencePointColumn->datasetName = $adjustedDatasetName;
            $referencePointColumn->columnName = $adjustedColumnName;
        }

        $referencePointColumn->shared = $shared;
    }

    protected static function assembleExpression(CubeMetaData $cube, ColumnMetaData $column, $functionPrefix, $functionSuffix) {
        $functionError = NULL;

        $expression = FALSE;
        if ($column->persistence == FormulaMetaData::PERSISTENCE__CALCULATED) {
            if ($column->isUsed()) {
                try {
                    $expressionAssembler = new FormulaExpressionAssembler($cube->factsDataset);
                    $expression = $expressionAssembler->assemble($column);
                }
                catch (Exception $e) {
                    $message = t(
                        "@functionName measure assembled with errors. @error",
                        array('@functionName' => $functionPrefix . $functionSuffix, '@error' => $e->getMessage()));

                    $functionError = $message;

                    LogHelper::log_warn($message);
                }
            }
            else {
                $functionError = t(
                    "@functionName measure was not assembled for the unused column",
                    array('@functionName' => $functionPrefix . $functionSuffix));
            }
        }
        else {
            $formulaExpressionParser = new FormulaExpressionParser(SQLFormulaExpressionHandler::LANGUAGE__SQL);
            $expression = $formulaExpressionParser->assemble($column->name);
        }
        if ($expression !== FALSE) {
            $expression = $functionPrefix . $expression . $functionSuffix;
        }

        return array($expression, $functionError);
    }

    protected static function registerFactMeasure(CubeMetaData $cube, ColumnMetaData $column, $functionName, $additivity, $selectedApplicationType = NULL) {
        $measureName = StarSchemaNamingConvention::getFactRelatedMeasureName($column->name, $functionName);

        $measure = $cube->registerMeasure($measureName);
        $measure->publicName = t($functionName);
        $measure->description = t(
            "System generated '@functionName' measure for '@columnName' column",
            array('@functionName' => $measure->publicName, '@columnName' => $column->publicName));
        $measure->used = FALSE;

        list($measure->function, $measure->functionError) = self::assembleExpression($cube, $column, $functionName . '(', ')');
        if (isset($measure->function)) {
            $measure->used = TRUE;
        }
        $measure->additivity = $additivity;

        $measure->type->applicationType = isset($selectedApplicationType) ? $selectedApplicationType : $column->type->applicationType;
        $measure->type->scale = $column->type->scale;
    }

    protected static function disableFactMeasure(CubeMetaData $cube, $columnName, $functionName) {
        $measureName = StarSchemaNamingConvention::getFactRelatedMeasureName($columnName, $functionName);

        $measure = $cube->findMeasure($measureName);
        if (isset($measure)) {
            $measure->used = FALSE;
        }
    }

    protected static function unregisterFactMeasure(CubeMetaData $cube, $columnName, $functionName) {
        $measureName = StarSchemaNamingConvention::getFactRelatedMeasureName($columnName, $functionName);

        $measure = $cube->findMeasure($measureName);
        if (isset($measure)) {
            $cube->unregisterMeasure($measureName);
        }
    }

    public static function registerCubeMeasures(CubeMetaData $cube) {
        $measureRecordCount = $cube->registerMeasure(StarSchemaNamingConvention::$MEASURE_NAME__RECORD_COUNT);
        $measureRecordCount->publicName = t('Record Count');
        $measureRecordCount->description = t('System generated measure to count records');
        $measureRecordCount->function = 'COUNT(*)';
        $measureRecordCount->additivity = MeasureAdditivity::ADDITIVE;
        $measureRecordCount->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
    }
}
