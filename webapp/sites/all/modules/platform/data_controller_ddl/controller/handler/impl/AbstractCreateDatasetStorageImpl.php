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


abstract class AbstractCreateDatasetStorageImpl extends AbstractDatasetStorageImpl {

    protected function revertIneligibleColumnPropertyValues(DatasetMetaData $dataset) {
        // excluded columns cannot be part of primary key
        foreach ($dataset->getColumns(FALSE) as $column) {
            if ($column->isUsed()) {
                continue;
            }

            if ($column->isKey()) {
                $column->key = FALSE;
            }
        }
    }

    public function executeDatasetUpdateOperations(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $operations) {
        $request = new UpdateDatasetStorageRequest($dataset->name);
        $request->addOperations($operations);

        LogHelper::log_debug($request);

        $this->datasourceStructureHandler->updateDatasetStorage($callcontext, $request);
    }
}
