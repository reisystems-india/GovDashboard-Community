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


class DatasetUIMetaDataGenerator extends AbstractDatasetUIMetaDataGenerator {

    public function generate(DatasetUIMetaData $datasetUIMetaData, DatasetMetaData $dataset, $referencePath = NULL) {
        // to prevent recursive references
        $datasetStack = array($dataset->name => TRUE);

        $datasetUIMetaData->name = $dataset->name;
        $datasetUIMetaData->publicName = $dataset->publicName;
        $datasetUIMetaData->description = $dataset->description;

        foreach ($dataset->getColumns() as $column) {
            if (!$column->isVisible()) {
                continue;
            }

            $columnUIMetaData = $this->prepareColumnUIMetaData($datasetStack, $referencePath, $dataset, $column->name);
            $datasetUIMetaData->registerAttribute($columnUIMetaData);
        }
    }

    protected function prepareColumnUIMetaData(array $datasetStack, $referencePath, DatasetMetaData $dataset, $columnName) {
        $column = $dataset->getColumn($columnName);

        $columnUIMetaData = new AttributeUIMetaData();
        $columnUIMetaData->name = self::prepareColumnUIMetaDataName($referencePath, $dataset->name, $column->name);
        $columnUIMetaData->publicName = $column->publicName;
        $columnUIMetaData->description = $column->description;
        $columnUIMetaData->columnIndex = $column->columnIndex;
        $columnUIMetaData->type = clone $column->type;

        list($referencedDatasetName) = ReferencePathHelper::splitReference($column->type->getLogicalApplicationType());
        if (isset($referencedDatasetName)) {
            $metamodel = data_controller_get_metamodel();
            $referencedDataset = $metamodel->getDataset($referencedDatasetName);

            if (!isset($datasetStack[$referencedDataset->name])) {
                $datasetStack[$referencedDataset->name] = TRUE;

                $branchReferencePath = isset($referencePath)
                    ? self::prepareReferencedElementName($referencePath, $dataset->name, $column->name)
                    : ReferencePathHelper::assembleReference($dataset->name, $column->name);

                foreach ($referencedDataset->getColumns() as $referencedColumn) {
                    if (!$referencedColumn->isVisible()) {
                        continue;
                    }

                    $referencedColumnMetaData = $this->prepareColumnUIMetaData($datasetStack, $branchReferencePath, $referencedDataset, $referencedColumn->name);
                    if ($referencedColumn->isKey()) {
                        if (count($referencedColumnMetaData->elements) == 0) {
                            continue;
                        }

                        $referencedColumnMetaData->publicName = $referencedDataset->publicName;
                        $referencedColumnMetaData->description = $referencedDataset->description;
                        $referencedColumnMetaData->isSelectable = FALSE;
                    }

                    $columnUIMetaData->registerElement($referencedColumnMetaData);
                }
            }
        }

        return $columnUIMetaData;
    }
}
