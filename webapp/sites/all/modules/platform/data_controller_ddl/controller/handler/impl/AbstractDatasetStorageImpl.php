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


abstract class AbstractDatasetStorageImpl extends AbstractObject {

    protected $datasourceStructureHandler = NULL;

    public function __construct(DataSourceStructureHandler $datasourceStructureHandler) {
        parent::__construct();
        $this->datasourceStructureHandler = $datasourceStructureHandler;
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     */
    protected function initialize(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        if (isset($observers)) {
            foreach ($observers as $observer) {
                $observer->initialize($callcontext, $dataset);
            }
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     */
    protected function validate(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        if (isset($observers)) {
            foreach ($observers as $observer) {
                $observer->validate($callcontext, $dataset);
            }
        }
    }

    /**
     * @param DataControllerCallContext $callcontext
     * @param DatasetMetaData $dataset
     * @param DatasetStorageObserver[] $observers
     */
    protected function finalize(DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $observers = NULL) {
        if (isset($observers)) {
            foreach ($observers as $observer) {
                $observer->finalize($callcontext, $dataset);
            }
        }
    }
}
