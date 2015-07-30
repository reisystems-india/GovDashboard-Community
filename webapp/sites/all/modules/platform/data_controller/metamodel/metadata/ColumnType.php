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


class ColumnType extends AbstractObject {

    public $applicationType = NULL;
    public $logicalApplicationType = NULL;

    public $databaseType = NULL;

    public $format = NULL;

    public $length = NULL;

    public $precision = NULL;
    public $scale = NULL;

    private $referencedApplicationType = NULL;
    private $referencedDatasetName = NULL;
    private $referencedColumnName = NULL;

    public function __construct($applicationType = NULL) {
        parent::__construct();
        $this->applicationType = $applicationType;
    }

    public function __sleep() {
        $names = array();
        foreach (array('applicationType', 'logicalApplicationType', 'databaseType', 'format', 'length', 'precision', 'scale') as $name) {
            if (isset($this->$name)) {
                $names[] = $name;
            }
        }

        return $names;
    }

    public function getLogicalApplicationType() {
        return isset($this->logicalApplicationType) ? $this->logicalApplicationType : $this->applicationType;
    }

    protected function prepareReferenceProperties() {
        if ($this->applicationType != $this->referencedApplicationType) {
            list($this->referencedDatasetName, $this->referencedColumnName) = ReferencePathHelper::splitReference($this->applicationType);
            $this->referencedApplicationType = $this->applicationType;
        }
    }

    public function getReferencedDatasetName() {
        $this->prepareReferenceProperties();

        return $this->referencedDatasetName;
    }

    public function getReferencedColumnName() {
        $this->prepareReferenceProperties();

        return $this->referencedColumnName;
    }
}
