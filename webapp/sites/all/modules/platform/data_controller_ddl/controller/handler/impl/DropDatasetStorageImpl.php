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


class DropDatasetStorageImpl extends AbstractDatasetStorageImpl {

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function dropColumnStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        if (isset($observers)) {
            // dropping physical storage of the dataset columns
            foreach ($dataset->getColumns(FALSE) as $column) {
                if ($column->persistence != ColumnMetaData::PERSISTENCE__STORAGE_CREATED) {
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
                                    $dataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                            }
                        }

                        $column->persistence = ColumnMetaData::PERSISTENCE__NO_STORAGE;
                        $column->used = FALSE;

                        if (isset($observers)) {
                            foreach ($observers as $observer) {
                                $observer->dropColumnStorage(
                                    $callcontext, $this->datasourceStructureHandler,
                                    $dataset, $column->name, DatasetStorageObserver::STAGE__AFTER);
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
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function dropDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        MetaModelFactory::getInstance()->startGlobalModification();
        try {
            $transaction = db_transaction();
            try {
                if (isset($observers)) {
                    foreach ($observers as $observer) {
                        $observer->unregisterDataset($callcontext, $dataset, DatasetStorageObserver::STAGE__BEFORE);
                    }
                }

                // dropping physical storage of the dataset
                $request = new DatasetStorageRequest($dataset->name);
                LogHelper::log_debug($request);
                $this->datasourceStructureHandler->dropDatasetStorage($callcontext, $request);

                $dataset->used = FALSE;

                if (isset($observers)) {
                    foreach ($observers as $observer) {
                        $observer->unregisterDataset($callcontext, $dataset, DatasetStorageObserver::STAGE__AFTER);
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

    public function execute(DataControllerCallContext $callcontext, $datasetName, array $observers = NULL) {
        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        $this->initialize($callcontext, $dataset, $observers);
        $this->validate($callcontext, $dataset, $observers);

        $this->dropColumnStorage($callcontext, $dataset, $observers);
        $this->dropDatasetStorage($callcontext, $dataset, $observers);

        $this->finalize($callcontext, $dataset, $observers);
    }
}
