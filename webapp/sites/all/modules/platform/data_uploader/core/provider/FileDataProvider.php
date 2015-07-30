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


class FileDataProvider extends AbstractDataProvider {

    private $filename = NULL;
    private $handle = FALSE;

    public function __construct($filename) {
        parent::__construct();
        $this->filename = $filename;
    }

    public function openResource() {
        LogHelper::log_notice(t('Parsing @filename ...', array('@filename' => $this->filename)));

        $result = parent::openResource();

        if ($result) {
            ini_set('auto_detect_line_endings', TRUE);
            $this->handle = fopen($this->filename, 'r');
            $result = $this->handle !== FALSE;
        }

        return $result;
    }

    protected function readLineFromResource() {
        $this->checkHandle();

        $this->incrementLineNumber();

        $line = fgets($this->handle);
        if ($line !== FALSE) {
            // removing line separators
            $line = trim($line, "\r\n");
        }

        return $line;
    }

    public function closeResource() {
        $this->checkHandle();

        fclose($this->handle);

        parent::closeResource();
    }

    protected function checkHandle() {
        if ($this->handle === FALSE) {
            throw new IllegalStateException(t('%fileName file has not been opened', array('%fileName' => $this->filename)));
        }
    }
}