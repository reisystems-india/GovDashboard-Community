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


class CreateDatasetStorageImpl extends AbstractCreateDatasetStorageImpl {

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $newDataset
     * @param DatasetStorageObserver[] $observers
     */
    protected function createDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $newDataset, array $observers = NULL) {
        // creating physical storage for the dataset
        $request = new DatasetStorageRequest($newDataset->name);
        LogHelper::log_debug($request);
        $this->datasourceStructureHandler->createDatasetStorage($callcontext, $request);

        if (isset($observers)) {
            // notifying that storage for the dataset had been created
            foreach ($observers as $observer) {
                $observer->registerDataset($callcontext, $newDataset, DatasetStorageObserver::STAGE__BEFORE);
            }
            foreach ($observers as $observer) {
                $observer->registerDataset($callcontext, $newDataset, DatasetStorageObserver::STAGE__AFTER);
            }

            // notifying that columns have already been created
            foreach ($newDataset->getColumns(FALSE) as $column) {
                foreach ($observers as $observer) {
                    $observer->registerColumn($callcontext, $newDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                }
                foreach ($observers as $observer) {
                    $observer->registerColumn($callcontext, $newDataset, $column->name, DatasetStorageObserver::STAGE__AFTER);
                }

                if ($column->isUsed() && ($column->persistence == ColumnMetaData::PERSISTENCE__NO_STORAGE)) {
                    foreach ($observers as $observer) {
                        $observer->createColumnStorage(
                            $callcontext, $this->datasourceStructureHandler,
                            $newDataset, $column->name, DatasetStorageObserver::STAGE__BEFORE);
                    }

                    $column->persistence = ColumnMetaData::PERSISTENCE__STORAGE_CREATED;

                    foreach ($observers as $observer) {
                        $observer->createColumnStorage(
                            $callcontext, $this->datasourceStructureHandler,
                            $newDataset, $column->name, DatasetStorageObserver::STAGE__AFTER);
                    }
                }
            }
        }
    }

    public function execute(DataControllerCallContext $callcontext, DatasetMetaData $newDataset, array $observers = NULL) {
        $this->revertIneligibleColumnPropertyValues($newDataset);

        $this->initialize($callcontext, $newDataset, $observers);

        $this->validate($callcontext, $newDataset, $observers);

        $this->createDatasetStorage($callcontext, $newDataset, $observers);

        $this->finalize($callcontext, $newDataset, $observers);
    }
}
