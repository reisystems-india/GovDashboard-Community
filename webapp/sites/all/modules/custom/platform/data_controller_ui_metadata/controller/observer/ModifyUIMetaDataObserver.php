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


class ModifyUIMetaDataObserver extends AbstractDatasetStorageObserver {

    protected $datasetStructureModified = FALSE;

    public function initialize(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::initialize($callcontext, $dataset);

        $this->datasetStructureModified = FALSE;
    }

    public function finalize(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        if ($this->datasetStructureModified) {
            $assembler = new DatasetUIMetaDataAssembler();
            // preparing possible combinations of cache names for the dataset
            $cacheEntryNames = array(
                $assembler->prepareCacheEntryName($dataset->name, FALSE),
                $assembler->prepareCacheEntryName($dataset->name, TRUE));
            // removing assembled UI Meta Data from cache
            $assembler->cache->expireCacheEntries($cacheEntryNames);
        }

        parent::finalize($callcontext, $dataset);
    }

    public function registerDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {
        parent::registerDataset($callcontext, $dataset, $stage);

        $this->datasetStructureModified = TRUE;
    }

    public function updateDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::updateDataset($callcontext, $dataset);

        $this->datasetStructureModified = TRUE;
    }

    public function unregisterDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {
        $this->datasetStructureModified = TRUE;

        parent::unregisterDataset($callcontext, $dataset, $stage);
    }

    public function enableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::enableDataset($callcontext, $dataset);

        $this->datasetStructureModified = TRUE;
    }

    public function disableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $this->datasetStructureModified = TRUE;

        parent::disableDataset($callcontext, $dataset);
    }

    public function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        parent::registerColumn($callcontext, $dataset, $columnName, $stage);

        $this->datasetStructureModified = TRUE;
    }

    public function updateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::updateColumn($callcontext, $dataset, $columnName);

        $this->datasetStructureModified = TRUE;
    }

    public function relocateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::relocateColumn($callcontext, $dataset, $columnName);

        $this->datasetStructureModified = TRUE;
    }

    public function disableColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        $this->datasetStructureModified = TRUE;

        parent::disableColumn($callcontext, $dataset, $columnName);
    }

    public function unregisterColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        $this->datasetStructureModified = TRUE;

        parent::unregisterColumn($callcontext, $dataset, $columnName, $stage);
    }

    public function updateColumnParticipationInDatasetKey(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::updateColumnParticipationInDatasetKey($callcontext, $dataset, $columnName);

        $this->datasetStructureModified = TRUE;
    }
}
