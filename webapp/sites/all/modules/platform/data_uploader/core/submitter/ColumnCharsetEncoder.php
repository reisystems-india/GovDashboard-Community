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


class ColumnCharsetEncoder extends AbstractDataSubmitter {

    public $oldEncoding = NULL;
    public $newEncoding = NULL;

    public function __construct($oldEncoding, $newEncoding) {
        parent::__construct();
        $this->oldEncoding = $oldEncoding;
        $this->newEncoding = $newEncoding;
    }

    public function doAfterLineParsed(array &$record) {
        parent::doAfterLineParsed($record);

        foreach ($record as &$columnValue) {
            $columnValue = mb_convert_encoding($columnValue, $this->newEncoding, $this->oldEncoding);
        }
        unset($columnValue);
    }
}
