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


class GD_DatasetPreviewResultFormatter extends AbstractResultFormatter {

    private $dataset = NULL;

    public function __construct(DatasetMetaData $dataset, ResultFormatter $parent = NULL) {
        parent::__construct($parent);
        $this->dataset = $dataset;
    }

    protected function formatColumnNameImpl($uiMetaDataName) {
        $columnName = NULL;

        $formattedUIMetaDataName = parent::formatColumnNameImpl($uiMetaDataName);

        list($elementNameSpace, $elementName) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($formattedUIMetaDataName);
        switch ($elementNameSpace) {
            case AbstractAttributeUIMetaData::NAME_SPACE:
                list($dimensionName, $dimensionColumnName) = ParameterNameHelper::split($elementName);
                // TODO do we need to check for resource?
                list(, $columnName) = ReferencePathHelper::splitReference($dimensionName);

                $column = $this->dataset->getColumn($dimensionName);
                if ($column->type->getReferencedDatasetName() != NULL) {
                    // this column should not be used
                    if (isset($dimensionColumnName) && $column->type->getReferencedColumnName() != $dimensionColumnName) {
                        $columnName = NULL;
                    }
                }

                break;
            case AbstractMeasureUIMetaData::NAME_SPACE:
                throw new IllegalArgumentException(t('Measures are not supported by this result formatter'));
        }

        return $columnName;
    }
}
