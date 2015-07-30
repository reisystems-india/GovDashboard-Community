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


class DatasetExportHelper {

    public static function getExportDatasetName ( $datasetName, MetaModel $metamodel ) {
        $logicalDatasetName = StarSchemaNamingConvention::findFactsOwner($datasetName);

        if (isset($logicalDatasetName)) {
            $dataset = $metamodel->getDataset($logicalDatasetName);
            return isset($dataset->uuid) ? StarSchemaNamingConvention::getFactsRelatedName($dataset->uuid) : null;
        } else {
            $dataset = $metamodel->getDataset($datasetName);
            return isset($dataset->uuid) ? $dataset->uuid : null;
        }
    }

    public static function getExportColumnName ( $uiMetaDataName, MetaModel $metamodel ) {

        if ( trim($uiMetaDataName) == '' ) {
            $message = t('Empty columnName discovered');
            drupal_set_message($message, 'warning');
            LogHelper::log_warn($message);

            return $uiMetaDataName;
        }


        list($elementNameSpace, $name) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($uiMetaDataName);
        switch ( $elementNameSpace ) {

            case AbstractAttributeUIMetaData::NAME_SPACE:
                list($referencedDimensionName, $dimensionColumnName) = ParameterNameHelper::split($name);
                list($datasetName, $dimensionName) = ReferencePathHelper::splitReference($referencedDimensionName);
                if (isset($datasetName)) {
                    $adjustedReferencedDimensionName = ReferencePathHelper::assembleReference(self::getExportDatasetName($datasetName,$metamodel), $dimensionName);
                    $name = ParameterNameHelper::assemble($adjustedReferencedDimensionName, $dimensionColumnName);
                }
                break;

            case AbstractMeasureUIMetaData::NAME_SPACE:
                list($datasetName, $measureName) = ReferencePathHelper::splitReference($name);
                if (isset($datasetName)) {
                    $name = ReferencePathHelper::assembleReference(self::getExportDatasetName($datasetName,$metamodel), $measureName);
                }
                break;

            case FormulaUIMetaData::NAME_SPACE:
                list($datasetName, $formulaName) = ReferencePathHelper::splitReference($name);
                if (isset($datasetName)) {
                    $name = ReferencePathHelper::assembleReference(self::getExportDatasetName($datasetName,$metamodel), $formulaName);
                }
                break;

            default:
                $message = t('Unsupported UI Meta Data name space: @uiMetaDataName', array('@uiMetaDataName' => $uiMetaDataName));
                LogHelper::log_error($message);
                throw new UnsupportedOperationException($message);
        }

        return AbstractDatasetUIMetaDataGenerator::prepareElementUIMetaDataName($elementNameSpace, $name);
    }

}