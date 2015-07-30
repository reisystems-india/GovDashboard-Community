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


class DatabaseEventRecorder extends AbstractEventRecorder {

    protected $recordsHolder = NULL;

    public function record($requestId, AbstractEvent $event) {
        $duration = $event->getDuration();

        global $user;
        $referencedDataSourceName = gd_datasource_get_active();

        if (!isset($this->recordsHolder)) {
            $this->recordsHolder = new IndexedRecordsHolder();
        }

        $recordInstance = $this->recordsHolder->initiateRecordInstance();
        $recordInstance->initializeFrom(array(
            NULL, $requestId, $user->uid, $referencedDataSourceName, $event->type, $event->owner, $event->getStartDateTime(), $duration));
        $this->recordsHolder->registerRecordInstance($recordInstance);
    }

    public function flush() {
        if (!isset($this->recordsHolder)) {
            return;
        }

        $datasetName = NameSpaceHelper::addNameSpace(DATASOURCE_NAME__HEALTH_MONITORING, 'hm_trails');

        // generating and assigning primary key
        $identifiers = Sequence::getNextSequenceValues($datasetName, count($this->recordsHolder->records));
        foreach ($this->recordsHolder->records as $index => $recordInstance) {
            $recordInstance->setColumnValue(0, $identifiers[$index]);
        }

        // storing data in the database
        $dataManipulationController = data_controller_dml_get_instance();
        $dataManipulationController->insertDatasetRecordBatch($datasetName, $this->recordsHolder);

        $this->recordsHolder = NULL;
    }
}
