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


class FlatSchemaDataSubmitter extends AbstractControllerDataSubmitter {

    protected function submitRecordBatch(RecordMetaData $recordMetaData) {
        $dataManipulationController = data_controller_dml_get_instance();

        if ($recordMetaData->findKeyColumns() == NULL) {
            $this->insertedRecordCount += $dataManipulationController->insertDatasetRecordBatch($this->datasetName, $this->recordsHolder);
        }
        else {
            // even if we truncate the dataset we still need to support several instances of the same record
            list($insertedRecordCount, $updatedRecordCount, $deletedRecordCount) =
                $dataManipulationController->insertOrUpdateOrDeleteDatasetRecordBatch($this->datasetName, $this->recordsHolder);
            $this->insertedRecordCount += $insertedRecordCount;
            $this->updatedRecordCount += $updatedRecordCount;
            $this->deletedRecordCount += $deletedRecordCount;
        }
    }
}
