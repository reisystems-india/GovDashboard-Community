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

/**
 * runtime config for dataset
 */
class GD_DatasetConfig {

    public $dataset;
    public $datasource;
    public $datafile;
    public $preview;
    public $metadata;
    public $recordCount;

    public function __construct ( $params = null ) {
        if ( is_array($params) ) {
            foreach ( $params as $key => $value ) {
                $this->$key = $value;
            }
        }
    }

    public function getApiObject () {
        $result = $this->metadata;
        $result->preview = $this->preview;
        return $result;
    }

    public function getDatasetSysname () {
        return $this->dataset->field_dataset_sysname[$this->dataset->language][0]['value'];
    }

    public function hasHeader () {
        if ( isset($this->datafile) ) {
            return (bool) $this->datafile->field_datafile_hasheader[$this->datafile->language][0]['value'];
        } else {
            throw new Exception('Expecting datafile to be loaded');
        }
    }
}
 
