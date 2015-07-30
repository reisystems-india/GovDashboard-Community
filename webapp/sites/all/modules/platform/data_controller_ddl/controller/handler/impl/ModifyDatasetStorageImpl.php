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


class ModifyDatasetStorageImpl extends AbstractCreateDatasetStorageImpl {

    protected function checkIfDataTypeCompatible(ColumnType $originalDataType, ColumnType $modifiedDataType) {
        $isOriginalDataTypeNumber = ($originalDataType->applicationType == NumberDataTypeHandler::DATA_TYPE)
            || ($originalDataType->applicationType == CurrencyDataTypeHandler::DATA_TYPE)
            || ($originalDataType->applicationType == PercentDataTypeHandler::DATA_TYPE);
        $isModifiedDataTypeNumber = ($modifiedDataType->applicationType == NumberDataTypeHandler::DATA_TYPE)
            || ($modifiedDataType->applicationType == CurrencyDataTypeHandler::DATA_TYPE)
            || ($modifiedDataType->applicationType == PercentDataTypeHandler::DATA_TYPE);

        return $isOriginalDataTypeNumber && $isModifiedDataTypeNumber;
    }

    protected function gatherChanges(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        foreach ($modifiedDataset->getColumns(FALSE) as $modifiedColumn) {
            $originalColumn = $originalDataset->findColumn($modifiedColumn->name);
            if (!isset($originalColumn)) {
                // preparing new included & excluded columns
                if ($modifiedColumn->isUsed()) {
                    $callcontext->changeAction->newIncludedColumns[$modifiedColumn->name] = $modifiedColumn;
                }
                else {
                    $callcontext->changeAction->newExcludedColumns[$modifiedColumn->name] = $modifiedColumn;
                }

                // checking if new column marked as part of primary key
                if ($modifiedColumn->isKey()) {
                    $callcontext->changeAction->isKeyUpdated = TRUE;
                }
            }
        }

        foreach ($originalDataset->getColumns(FALSE) as $originalColumn) {
            $modifiedColumn = $modifiedDataset->findColumn($originalColumn->name);
            if (isset($modifiedColumn)) {
                $isColumnUpdated = FALSE;

                // preparing restored columns
                if (!$originalColumn->isUsed() && $modifiedColumn->isUsed()) {
                    $callcontext->changeAction->restoredColumns[$originalColumn->name] = $originalColumn;
                }

                // preparing excluded columns
                if ($originalColumn->isUsed() && !$modifiedColumn->isUsed()) {
                    $callcontext->changeAction->excludedColumns[$originalColumn->name] = $originalColumn;
                    // excluding a column which is part of primary key
                    if ($originalColumn->isKey()) {
                        $callcontext->changeAction->isKeyUpdated = TRUE;
                    }
                }

                // preparing relocated columns
                if ($originalColumn->columnIndex != $modifiedColumn->columnIndex) {
                    $callcontext->changeAction->updatedIndexColumns[$originalColumn->name] = $originalColumn;
                }

                // preparing columns with changed data type
                if (($originalColumn->type->applicationType != $modifiedColumn->type->applicationType)
                        || ($originalColumn->type->getLogicalApplicationType() != $modifiedColumn->type->getLogicalApplicationType())) {
                    // marking that the column data type was updated
                    if ($modifiedColumn->isUsed()) {
                        $callcontext->changeAction->updatedDataTypeIncludedColumns[$originalColumn->name] = $originalColumn;
                        // updating type a column which is part of the primary key
                        if ($originalColumn->isKey()) {
                            $callcontext->changeAction->isKeyUpdated = TRUE;
                        }
                    }
                    else {
                        $callcontext->changeAction->updatedDataTypeExcludedColumns[$originalColumn->name] = $originalColumn;
                    }
                    // checking if old & new types are compatible
                    if ($this->checkIfDataTypeCompatible($originalColumn->type, $modifiedColumn->type)) {
                        $callcontext->changeAction->updatedDataTypeCompatibleColumns[$originalColumn->name] = $originalColumn;
                    }

                    $isColumnUpdated = TRUE;
                }

                // checking if any column properties were updated
                if (($originalColumn->publicName != $modifiedColumn->publicName)
                        || ($originalColumn->description != $modifiedColumn->description)
                        || ($originalColumn->source != $modifiedColumn->source)) {
                    $isColumnUpdated = TRUE;
                }

                // checking if a column in primary key was added or removed
                if ($originalColumn->isKey() != $modifiedColumn->isKey()) {
                    $isColumnUpdated = TRUE;
                    $callcontext->changeAction->isKeyUpdated = TRUE;
                }

                // preparing updated columns
                if ($isColumnUpdated) {
                    $callcontext->changeAction->updatedColumns[$originalColumn->name] = $originalColumn;
                }
            }
            else {
                // preparing deleted columns
                $callcontext->changeAction->deletedColumns[$originalColumn->name] = $originalColumn;

                // deleting a column which is part of the primary key
                if ($originalColumn->isKey()) {
                    $callcontext->changeAction->isKeyUpdated = TRUE;
                }
            }
        }

        // preparing updated dataset
        if (($originalDataset->publicName != $modifiedDataset->publicName)
                || ($originalDataset->description != $modifiedDataset->description)
                || (compare_values($originalDataset->source, $modifiedDataset->source) != 0)
                || (compare_values($originalDataset->aliases, $modifiedDataset->aliases) != 0)) {
            $callcontext->changeAction->isDatasetUpdated = TRUE;
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalDataset
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function disableColumns(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        $columns = NULL;
        ArrayHelper::merge($columns, $callcontext->changeAction->updatedDataTypeIncludedColumns);
        ArrayHelper::merge($columns, $callcontext->changeAction->excludedColumns);
        ArrayHelper::merge($columns, $callcontext->changeAction->deletedColumns);
        if (isset($columns)) {
            MetaModelFactory::getInstance()->startGlobalModification();
            try {
                $transaction = db_transaction();
                try {
                    foreach ($columns as $column) {
                        if (!$column->isUsed()) {
                            continue;
                        }

                        $column->used = FALSE;

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->disableColumn($callcontext, $originalDataset, $column->name);
                            }
                        }
                    }
                }
                catch (Exception $e) {
                    $transaction->rollback();
                    throw $e;
                }
            }
            catch (Exception $e) {
                MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
                throw $e;
            }
            MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalDataset
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function dropDatasetKey(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        if ($callcontext->changeAction->isKeyUpdated) {
            $originalDatasetKeyColumnNames = $originalDataset->findKeyColumnNames();
            if (isset($originalDatasetKeyColumnNames)) {
                MetaModelFactory::getInstance()->startGlobalModification();
                try {
                    $transaction = db_transaction();
                    try {
                        foreach ($originalDatasetKeyColumnNames as $columnName) {
                            $originalColumn = $originalDataset->getColumn($columnName);
                            $originalColumn->key = FALSE;

                            // marking the column as updated because we unmarked the flag related to primary key
                            if (!isset($callcontext->changeAction->deletedColumns[$originalColumn->name])) {
                                $callcontext->changeAction->updatedColumns[$originalColumn->name] = $originalColumn;
                            }
                        }

                        $this->executeDatasetUpdateOperations(
                            $callcontext,
                            $originalDataset,
                            array(new DropDatasetKeyOperation($originalDatasetKeyColumnNames)));

                        if (isset($observers)) {
                            foreach ($originalDatasetKeyColumnNames as $columnName) {
                                foreach ($observers as $observer) {
                                    $observer->updateColumnParticipationInDatasetKey($callcontext, $originalDataset, $columnName);
                                }
                            }
                        }
                    }
                    catch (Exception $e) {
                        $transaction->rollback();
                        throw $e;
                    }
                }
                catch (Exception $e) {
                    MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
                    throw $e;
                }
                MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
            }
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalDataset
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function dropColumnStorage(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        $columns = NULL;
        ArrayHelper::merge($columns, $callcontext->changeAction->updatedDataTypeIncludedColumns);
        ArrayHelper::merge($columns, $callcontext->changeAction->updatedDataTypeExcludedColumns);
        ArrayHelper::merge($columns, $callcontext->changeAction->deletedColumns);
        if (isset($columns)) {
            foreach ($columns as $column) {
                if ($column->persistence != ColumnMetaData::PERSISTENCE__STORAGE_CREATED) {
                    continue;
                }
                // we do not need to delete storage for this column because it is compatible with new type
                if (isset($callcontext->changeAction->updatedDataTypeCompatibleColumns[$column->name])) {
                    continue;
                }

                MetaModelFactory::getInstance()->startGlobalModification();
                try {
                    $transaction = db_transaction();
                    try {
                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->dropColumnStorage(
                                    $callcontext, $this->datasourceStructureHandler,
                                    $originalDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                            }
                        }
                        $this->executeDatasetUpdateOperations($callcontext, $originalDataset, array(new DropColumnOperation($column->name)));

                        $column->persistence = ColumnMetaData::PERSISTENCE__NO_STORAGE;

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->dropColumnStorage(
                                    $callcontext, $this->datasourceStructureHandler,
                                    $originalDataset, $column->name, DatasetStorageObserver::STAGE__AFTER);
                            }
                        }
                    }
                    catch (Exception $e) {
                        $transaction->rollback();
                        throw $e;
                    }
                }
                catch (Exception $e) {
                    MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
                    throw $e;
                }
                MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
            }
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalDataset
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function updateColumnList(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        MetaModelFactory::getInstance()->startGlobalModification();
        try {
            $transaction = db_transaction();
            try {
                // deleting columns
                if (isset($callcontext->changeAction->deletedColumns)) {
                    foreach ($callcontext->changeAction->deletedColumns as $column) {
                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->unregisterColumn($callcontext, $originalDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                            }
                        }
                        $originalDataset->unregisterColumn($column->name);
                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->unregisterColumn($callcontext, $originalDataset, $column->name, DatasetStorageObserver::STAGE__AFTER);
                            }
                        }
                    }
                }

                // updating column index for existing columns
                if (isset($callcontext->changeAction->updatedIndexColumns)) {
                    foreach ($callcontext->changeAction->updatedIndexColumns as $column) {
                        $modifiedColumn = $modifiedDataset->getColumn($column->name);
                        $column->columnIndex = $modifiedColumn->columnIndex;
                    }
                    if (isset($observers)) {
                        foreach ($callcontext->changeAction->updatedIndexColumns as $column) {
                            foreach ($observers as $observer) {
                                $observer->relocateColumn($callcontext, $originalDataset, $column->name);
                            }
                        }
                    }
                }

                $createdColumnNames = NULL;
                // registering new included columns
                if (isset($callcontext->changeAction->newIncludedColumns)) {
                    foreach ($callcontext->changeAction->newIncludedColumns as $index => $includedColumn) {
                        $column = $originalDataset->initializeColumnFrom($includedColumn);
                        // temporary marking all new columns as disabled until we create their storage
                        $column->used = FALSE;
                        // replacing column from modified dataset with new column from original dataset
                        $callcontext->changeAction->newIncludedColumns[$index] = $column;

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->registerColumn($callcontext, $originalDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                            }
                        }

                        $createdColumnNames[] = $column->name;
                    }
                }
                // registering new excluded columns
                if (isset($callcontext->changeAction->newExcludedColumns)) {
                    foreach ($callcontext->changeAction->newExcludedColumns as $index => $excludedColumn) {
                        $column = $originalDataset->initializeColumnFrom($excludedColumn);
                        // replacing column from modified dataset with new column from original dataset
                        $callcontext->changeAction->newExcludedColumns[$index] = $column;

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->registerColumn($callcontext, $originalDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                            }
                        }

                        $createdColumnNames[] = $column->name;
                    }
                }
                // executing final stage in column creation
                if (isset($createdColumnNames)) {
                    if (isset($observers)) {
                        foreach ($createdColumnNames as $createdColumnName) {
                            foreach ($observers as $observer) {
                                $observer->registerColumn($callcontext, $originalDataset, $createdColumnName, DatasetStorageObserver::STAGE__AFTER);
                            }
                        }
                    }
                }
            }
            catch (Exception $e) {
                $transaction->rollback();
                throw $e;
            }
        }
        catch (Exception $e) {
            MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
            throw $e;
        }
        MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalDataset
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function createColumnStorage(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        $unpersistedColumns = NULL;
        ArrayHelper::merge($unpersistedColumns, $callcontext->changeAction->newIncludedColumns);
        ArrayHelper::merge($unpersistedColumns, $callcontext->changeAction->restoredColumns);
        ArrayHelper::merge($unpersistedColumns, $callcontext->changeAction->updatedDataTypeIncludedColumns);
        if (isset($unpersistedColumns)) {
            foreach ($unpersistedColumns as $column) {
                if ($column->persistence != ColumnMetaData::PERSISTENCE__NO_STORAGE) {
                    continue;
                }
                // original storage was not deleted because it was compatible with new storage
                if (isset($callcontext->changeAction->updatedDataTypeCompatibleColumns[$column->name])) {
                    continue;
                }

                $modifiedColumn = $modifiedDataset->getColumn($column->name);

                MetaModelFactory::getInstance()->startGlobalModification();
                try {
                    $transaction = db_transaction();
                    try {
                        $column->initializeTypeFrom($modifiedColumn->type, TRUE);

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->createColumnStorage(
                                    $callcontext, $this->datasourceStructureHandler,
                                    $originalDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                            }
                        }

                        $this->executeDatasetUpdateOperations($callcontext, $originalDataset, array(new CreateColumnOperation($column->name)));

                        $column->persistence = ColumnMetaData::PERSISTENCE__STORAGE_CREATED;

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->createColumnStorage(
                                    $callcontext, $this->datasourceStructureHandler,
                                    $originalDataset, $column->name, DatasetStorageObserver::STAGE__AFTER);
                            }
                        }
                    }
                    catch (Exception $e) {
                        $transaction->rollback();
                        throw $e;
                    }
                }
                catch (Exception $e) {
                    MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
                    throw $e;
                }
                MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
            }
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $originalDataset
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function updateProperties(DataControllerCallContext $callcontext, DatasetMetaData $originalDataset, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        $justPersistedColumns = NULL;
        ArrayHelper::merge($justPersistedColumns, $callcontext->changeAction->newIncludedColumns);
        ArrayHelper::merge($justPersistedColumns, $callcontext->changeAction->restoredColumns);
        ArrayHelper::merge($justPersistedColumns, $callcontext->changeAction->updatedDataTypeIncludedColumns);

        $columns = $justPersistedColumns;
        ArrayHelper::merge($columns, $callcontext->changeAction->updatedColumns);
        if ($callcontext->changeAction->isDatasetUpdated || isset($columns) || $callcontext->changeAction->isKeyUpdated) {
            MetaModelFactory::getInstance()->startGlobalModification();
            try {
                $transaction = db_transaction();
                try {
                    if (isset($columns)) {
                        foreach ($columns as $column) {
                            if (isset($justPersistedColumns[$column->name])) {
                                $column->used = TRUE;
                            }

                            if (isset($callcontext->changeAction->updatedColumns[$column->name])) {
                                $modifiedColumn = $modifiedDataset->getColumn($column->name);

                                $column->publicName = $modifiedColumn->publicName;
                                $column->description = $modifiedColumn->description;
                                $column->source = $modifiedColumn->source;
                                $column->key = $modifiedColumn->key;
                            }

                            if (isset($observers)) {
                                foreach ($observers as $observer) {
                                    $observer->updateColumn($callcontext, $originalDataset, $column->name);
                                }
                            }
                        }
                    }

                    if ($callcontext->changeAction->isDatasetUpdated) {
                        $originalDataset->publicName = $modifiedDataset->publicName;
                        $originalDataset->description = $modifiedDataset->description;
                        $originalDataset->initializeSourceFrom($modifiedDataset->source, TRUE);
                        $originalDataset->initializeAliasesFrom($modifiedDataset->aliases, TRUE);

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->updateDataset($callcontext, $originalDataset);
                            }
                        }
                    }

                    if ($callcontext->changeAction->isKeyUpdated) {
                        $modifiedDatasetKeyColumnNames = $modifiedDataset->findKeyColumnNames();
                        if (isset($modifiedDatasetKeyColumnNames)) {
                            $this->executeDatasetUpdateOperations(
                                $callcontext,
                                $originalDataset,
                                array(new CreateDatasetKeyOperation()));
                        }
                    }
                }
                catch (Exception $e) {
                    $transaction->rollback();
                    throw $e;
                }
            }
            catch (Exception $e) {
                MetaModelFactory::getInstance()->finishGlobalModification(FALSE);
                throw $e;
            }
            MetaModelFactory::getInstance()->finishGlobalModification(TRUE);
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $modifiedDataset
     * @param DatasetStorageObserver[] $observers
     */
    public function execute(DataControllerCallContext $callcontext, DatasetMetaData $modifiedDataset, array $observers = NULL) {
        $dataQueryController = data_controller_get_instance();

        $this->revertIneligibleColumnPropertyValues($modifiedDataset);

        $callcontext->changeAction = new DatasetStorageChangeAction();

        $originalDataset = $dataQueryController->getDatasetMetaData($modifiedDataset->name);

        $this->gatherChanges($callcontext, $originalDataset, $modifiedDataset, $observers);

        if ($callcontext->changeAction->isUpdated()) {
            $this->initialize($callcontext, $originalDataset, $observers);
            $this->validate($callcontext, $modifiedDataset, $observers);

            LogHelper::log_debug($callcontext->changeAction);

            $this->disableColumns($callcontext, $originalDataset, $modifiedDataset, $observers);
            $this->dropDatasetKey($callcontext, $originalDataset, $modifiedDataset, $observers);
            $this->dropColumnStorage($callcontext, $originalDataset, $modifiedDataset, $observers);
            $this->updateColumnList($callcontext, $originalDataset, $modifiedDataset, $observers);
            $this->createColumnStorage($callcontext, $originalDataset, $modifiedDataset, $observers);
            $this->updateProperties($callcontext, $originalDataset, $modifiedDataset, $observers);

            $this->finalize($callcontext, $originalDataset, $observers);
        }
    }
}
