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


class TruncateDatasetStorageImpl extends AbstractDatasetStorageImpl {

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     */
    protected function truncateDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        if (isset($observers)) {
            // notifying observers that we are about to truncate dataset
            foreach ($observers as $observer) {
                $observer->truncateDatasetStorage($callcontext, $dataset);
            }
        }

        // truncating physical storage of the dataset
        $request = new DatasetStorageRequest($dataset->name);
        LogHelper::log_debug($request);
        $this->datasourceStructureHandler->truncateDatasetStorage($callcontext, $request);

        if (isset($observers)) {
            // truncating physical storage of the dataset columns
            foreach ($dataset->getColumns(FALSE) as $column) {
                if ($column->persistence == ColumnMetaData::PERSISTENCE__STORAGE_CREATED) {
                    foreach ($observers as $observer) {
                        $observer->truncateColumnStorage($callcontext, $this->datasourceStructureHandler, $dataset, $column->name);
                    }
                }
            }
        }
    }

    public function execute(DataControllerCallContext $callcontext, $datasetName, array $observers = NULL) {
        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        $this->initialize($callcontext, $dataset, $observers);

        $this->truncateDatasetStorage($callcontext, $dataset, $observers);

        $this->finalize($callcontext, $dataset, $observers);
    }
}
