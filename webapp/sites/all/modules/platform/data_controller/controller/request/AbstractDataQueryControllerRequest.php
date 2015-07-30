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


abstract class AbstractDataQueryControllerRequest extends AbstractObject {

    public $datasetName = NULL;
    public $columns = NULL;
    public $parameters = NULL;
    public $orderBy = NULL;
    public $startWith = 0;
    public $limit = NULL;
    public $options = NULL;
    /**
     * @var ResultFormatter
     */
    public $resultFormatter = NULL;

    public function initializeFrom($datasetName, $columns = NULL, $parameters = NULL, $orderBy = NULL, $startWith = 0, $limit = NULL, array $options = NULL, ResultFormatter $resultFormatter = NULL) {
        $this->datasetName = $datasetName;
        $this->columns = $columns;
        $this->parameters = $parameters;
        $this->orderBy = $orderBy;
        $this->startWith = $startWith;
        $this->limit = $limit;
        $this->options = $options;
        $this->resultFormatter = $resultFormatter;
    }
}
