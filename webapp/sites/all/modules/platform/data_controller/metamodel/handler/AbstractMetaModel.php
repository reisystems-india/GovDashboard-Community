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


abstract class AbstractMetaModel extends AbstractObject {

    public $version = NULL;
    private $assembled = FALSE;

    public function __construct() {
        parent::__construct();
        $this->version = uniqid();
    }

    public function isAssembled() {
        return $this->assembled;
    }

    public function startAssembling() {
        if (!$this->assembled) {
            throw new IllegalStateException(t('Meta Model assembling has already been started'));
        }

        $this->assembled = FALSE;
    }

    public function markAsAssembled() {
        $this->finalize();
        $this->validate();
        $this->assembled = TRUE;
    }

    protected function finalize() {}

    protected function validate() {}

    protected function checkAssemblingStarted() {
        if ($this->assembled) {
            LogHelper::log_error(t('Meta Model assembling has not been started'));
        }
    }
}