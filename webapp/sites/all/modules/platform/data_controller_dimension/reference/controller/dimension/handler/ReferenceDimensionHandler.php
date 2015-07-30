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


class ReferenceDimensionHandler extends AbstractDimensionHandler {

    public static function isLookupDataset(DatasetMetaData $logicalDataset) {
        $primaryKeyColumnNames = $logicalDataset->findKeyColumnNames();

        // primary key exists and it is single-columned
        return count($primaryKeyColumnNames) == 1;
    }

    public function isDataTypeSupported($datatype) {
        list($referencedDatasetName) = ReferencePathHelper::splitReference($datatype);

        return isset($referencedDatasetName);
    }

    protected function permitLookupDatasetStorageChanges(DataControllerCallContext $callcontext, DatasetMetaData $originalLogicalDataset, DatasetMetaData $modifiedLogicalDataset) {
        $metamodel = data_controller_get_metamodel();

        $originalPrimaryKeyColumnName = $originalLogicalDataset->getKeyColumn()->name;

        // checking if the primary key changed
        $doesPrimaryKeyMatch = FALSE;
        $modifiedPrimaryKeyColumnNames = $modifiedLogicalDataset->findKeyColumnNames();
        if (count($modifiedPrimaryKeyColumnNames) == 1) {
            $modifiedPrimaryKeyColumnName = reset($modifiedPrimaryKeyColumnNames);
            $doesPrimaryKeyMatch = $originalPrimaryKeyColumnName == $modifiedPrimaryKeyColumnName;
        }
        if ($doesPrimaryKeyMatch) {
            return;
        }

        // checking if this dataset was used as lookup somewhere
        foreach ($metamodel->datasets as $dataset) {
            foreach ($dataset->getColumns(FALSE, TRUE) as $column) {
                if ($column->type->getReferencedDatasetName() == $originalLogicalDataset->name) {
                    throw new IllegalArgumentException(t(
                        '%datasetName dataset is referenced by other datasets. Changes to the primary key is not permitted unless the references are removed first',
                        array('%datasetName' => $modifiedLogicalDataset->publicName)));
                }
            }
        }
    }

    protected function permitNonLookupDatasetStorageChanges(DataControllerCallContext $callcontext, DatasetMetaData $originalLogicalDataset, DatasetMetaData $modifiedLogicalDataset) {
        // it needs to be update dataset structure operation
        if (!isset($callcontext->changeAction)) {
            return;
        }

        // checking if we try to exclude some columns
        if (!isset($callcontext->changeAction->excludedColumns)) {
            return;
        }

        // checking if we try to exclude reference column
        // If we do we need to delete persistence storage
        // That would delete reference to lookup dataset and in the future the system will not generate unexpected errors related to incorrect data in excluded column
        foreach ($callcontext->changeAction->excludedColumns as $excludedColumn) {
            $originalLogicalColumn = $originalLogicalDataset->getColumn($excludedColumn->name);
            if ($originalLogicalColumn->type->getReferencedDatasetName() == NULL) {
                continue;
            }

            if (!isset($callcontext->changeAction->updatedDataTypeExcludedColumns[$excludedColumn->name])) {
                $callcontext->changeAction->updatedDataTypeExcludedColumns[$excludedColumn->name] = $excludedColumn;
            }
        }
    }

    public function permitDatasetStorageChanges(DataControllerCallContext $callcontext, DatasetMetaData $originalLogicalDataset, DatasetMetaData $modifiedLogicalDataset) {
        parent::permitDatasetStorageChanges($callcontext, $originalLogicalDataset, $modifiedLogicalDataset);

        if (self::isLookupDataset($originalLogicalDataset)) {
            $this->permitLookupDatasetStorageChanges($callcontext, $originalLogicalDataset, $modifiedLogicalDataset);
        }
        else {
            $this->permitNonLookupDatasetStorageChanges($callcontext, $originalLogicalDataset, $modifiedLogicalDataset);
        }
    }

    public function permitDatasetStorageTruncation(DataControllerCallContext $callcontext, DatasetMetaData $logicalDataset) {
        parent::permitDatasetStorageTruncation($callcontext, $logicalDataset);

        if (!self::isLookupDataset($logicalDataset)) {
            return;
        }

        $dataQueryController = data_controller_get_instance();
        $metamodel = data_controller_get_metamodel();

        // checking of the reference datasets have any NOT NULL data in reference columns
        $populatedReferenceDatasetPublicNames = NULL;
        foreach ($metamodel->datasets as $dataset) {
            foreach ($dataset->getColumns(FALSE, TRUE) as $column) {
                if ($column->persistence != ColumnMetaData::PERSISTENCE__STORAGE_CREATED) {
                    continue;
                }
                if ($column->type->getReferencedDatasetName() == $logicalDataset->name) {
                    $recordCount = $dataQueryController->countDatasetRecords(
                        $dataset->name,
                        array($column->name => OperatorFactory::getInstance()->initiateHandler(NotEmptyOperatorHandler::OPERATOR__NAME)));
                    if ($recordCount > 0) {
                        $populatedReferenceDatasetPublicNames[] = $dataset->publicName;
                        break;
                    }
                }
            }
        }

        // we should not allow to truncate the dataset if there is any data in any reference datasets
        if (isset($populatedReferenceDatasetPublicNames)) {
            throw new IllegalArgumentException(t(
                "%datasetName dataset is referenced by other datasets. The dataset truncation is not permitted unless records in %referenceDatasetNames datasets are deleted first",
                array(
                    '%datasetName' => $logicalDataset->publicName,
                    '%referenceDatasetNames' => ArrayHelper::serialize($populatedReferenceDatasetPublicNames))));
        }
    }

    public function createDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {
        parent::createDimensionStorage($callcontext, $datasourceStructureHandler, $logicalDataset, $columnName);

        $metamodel = data_controller_get_metamodel();

        $column = $logicalDataset->getColumn($columnName);

        $referencedLogicalDatasetName = $column->type->getReferencedDatasetName();
        $referencedCubeName = $referencedLogicalDatasetName;

        $referencedCube = $metamodel->getCube($referencedCubeName);

        $datasetName = StarSchemaNamingConvention::getFactsRelatedName($logicalDataset->name);

        $request = new UpdateDatasetStorageRequest($datasetName);
        $request->addOperation(new CreateColumnReferenceOperation($columnName, $referencedCube->factsDatasetName));
        LogHelper::log_debug($request);
        $datasourceStructureHandler->updateDatasetStorage($callcontext, $request);
    }

    public function dropDimensionStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $logicalDataset, $columnName) {
        $datasetName = StarSchemaNamingConvention::getFactsRelatedName($logicalDataset->name);

        $request = new UpdateDatasetStorageRequest($datasetName);
        $request->addOperation(new DropColumnReferenceOperation($columnName));
        LogHelper::log_debug($request);
        $datasourceStructureHandler->updateDatasetStorage($callcontext, $request);

        parent::dropDimensionStorage($callcontext, $datasourceStructureHandler, $logicalDataset, $columnName);
    }
}
