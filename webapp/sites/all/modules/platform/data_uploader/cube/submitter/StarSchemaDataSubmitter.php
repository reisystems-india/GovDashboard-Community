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


class StarSchemaDataSubmitter extends AbstractControllerDataSubmitter {

    protected function submitRecordBatch(RecordMetaData $recordMetaData) {
        $dataManipulationController = data_controller_dml_get_instance();

        $identifierLoader = new StarSchemaLookupIdentifierLoader();
        $identifierLoader->load($this->datasetName, $recordMetaData, $this->recordsHolder);

        $factsDatasetName = StarSchemaNamingConvention::getFactsRelatedName($this->datasetName);

        if ($recordMetaData->findKeyColumns() == NULL) {
            $this->insertedRecordCount += $dataManipulationController->insertDatasetRecordBatch($factsDatasetName, $this->recordsHolder);
        }
        else {
            // even if we truncate the dataset we still need to support several references to the same record
            list($insertedRecordCount, $updatedRecordCount, $deletedRecordCount) =
                $dataManipulationController->insertOrUpdateOrDeleteDatasetRecordBatch($factsDatasetName, $this->recordsHolder);
            $this->insertedRecordCount += $insertedRecordCount;
            $this->updatedRecordCount += $updatedRecordCount;
            $this->deletedRecordCount += $deletedRecordCount;
        }
    }
}


class StarSchemaLookupIdentifierLoader extends AbstractLookupIdentifierLoader {

    public function selectLookupableColumns(RecordMetaData $recordMetaData) {
        $lookupableColumns = NULL;
        foreach ($recordMetaData->getColumns() as $column) {
            if ($column->isPhysical()) {
                $lookupableColumns[$column->columnIndex] = $column;
            }
        }

        return $lookupableColumns;
    }
}
