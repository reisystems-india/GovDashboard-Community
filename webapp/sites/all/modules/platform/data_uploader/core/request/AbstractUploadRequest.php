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


abstract class AbstractUploadRequest extends AbstractMetaDataRequest {

    // are column names present in header of a stream of data
    public $isHeaderPresent = TRUE;

    // 'parser' will skip the following number of records before start processing
    public $skipRecordCount = 0;
    // 'parser' will process only the following number of records. <NULL> means ALL
    public $limitRecordCount = NULL;

    abstract protected function initiateDataParser();

    public function prepareDataParser() {
        $parser = $this->initiateDataParser();
        $parser->metadata = $this->metadata;
        $parser->isHeaderPresent = $this->isHeaderPresent;
        $parser->skipRecordCount = $this->skipRecordCount;
        $parser->limitRecordCount = $this->limitRecordCount;

        return $parser;
    }

    abstract protected function initiateDataProvider();

    public function prepareDataProvider() {
        $dataProvider = $this->initiateDataProvider();

        return $dataProvider;
    }
}
