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


class EnableDatasetImpl extends AbstractDatasetStorageImpl {

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     * @throws Exception
     */
    protected function enableDatasetStorage(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        MetaModelFactory::getInstance()->startGlobalModification();
        try {
            $transaction = db_transaction();
            try {
                $dataset->used = TRUE;

                if (isset($observers)) {
                    foreach ($observers as $observer) {
                        $observer->enableDataset($callcontext, $dataset);
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

        $this->enableDatasetStorage($callcontext, $dataset, $observers);

        $this->finalize($callcontext, $dataset, $observers);
    }
}
