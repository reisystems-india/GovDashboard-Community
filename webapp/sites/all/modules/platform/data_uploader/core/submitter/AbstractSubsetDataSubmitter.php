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


abstract class AbstractSubsetDataSubmitter extends AbstractDataSubmitter {

    protected $skipRecordCount = NULL;
    protected $limitRecordCount = NULL;

    protected $skippedRecordCount = 0;
    protected $processedRecordCount = 0;

    public function __construct($skipRecordCount = 0, $limitRecordCount = NULL) {
        parent::__construct();
        $this->skipRecordCount = $skipRecordCount;
        $this->limitRecordCount = $limitRecordCount;
    }
}
