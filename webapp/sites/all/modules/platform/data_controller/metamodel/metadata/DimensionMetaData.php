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


class DimensionMetaData extends AbstractMetaData {

    // auto-generated cubes
    //   * equals to $name
    // manually defined cubes
    //   * developers might use different names to improve readability
    //   * developers might use different names to support backward compatibility
    public $attributeColumnName = NULL;

    public $datasetName = NULL;
    /**
     * @var DatasetMetaData|null
     */
    public $dataset = NULL; // populated automatically when corresponding cube is used for first time

    protected function prepareUnserializablePropertyNames(&$names) {
        $names['dataset'] = TRUE;
    }

    public function isComplete() {
        return parent::isComplete() && (!isset($this->datasetName) || (isset($this->dataset) && $this->dataset->isComplete()));
    }

    public function setDatasetName($datasetName) {
        if ($this->datasetName != $datasetName) {
            $this->datasetName = $datasetName;

            $this->dataset = NULL;
        }
    }
}
