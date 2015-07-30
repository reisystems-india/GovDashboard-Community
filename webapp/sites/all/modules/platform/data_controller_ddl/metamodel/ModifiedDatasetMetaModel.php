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


class ModifiedDatasetMetaModel extends MetaModel {

    protected $parentMetaModel = NULL;

    public function __construct(MetaModel $parentMetaModel) {
        parent::__construct();
        $this->parentMetaModel = $parentMetaModel;
    }

    public function findDataset($datasetName, $localOnly = FALSE) {
        $dataset = parent::findDataset($datasetName);
        if (!isset($dataset) && !$localOnly) {
            $dataset = $this->parentMetaModel->findDataset($datasetName);
        }

        return $dataset;
    }

    public function findCube($cubeName, $localOnly = FALSE) {
        $cube = parent::findCube($cubeName);
        if (!isset($cube) && !$localOnly) {
            $cube = $this->parentMetaModel->findCube($cubeName);
        }

        return $cube;
    }
}
