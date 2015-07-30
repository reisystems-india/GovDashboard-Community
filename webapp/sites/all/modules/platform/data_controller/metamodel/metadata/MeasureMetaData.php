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


class MeasureMetaData extends AbstractMetaData {

    public $function = NULL;
    public $functionError = NULL;

    public $additivity = NULL;

    public $type = NULL;

    public function __construct() {
        parent::__construct();
        $this->type = $this->initiateType();
    }

    public function __clone() {
        parent::__clone();
        $this->type = clone $this->type;
    }

    protected function prepareUnserializablePropertyNames(&$names) {
        $names['functionError'] = TRUE;
    }

    public function finalize() {
        parent::finalize();

        $parser = new EnvironmentConfigurationParser();
        $this->function = $parser->parse($this->function, array($parser, 'executeStatement'));
    }

    public function isComplete() {
        return parent::isComplete() && isset($this->type->applicationType);
    }

    public function initializeFrom($sourceMeasure) {
        parent::initializeFrom($sourceMeasure);

        $sourceType = ObjectHelper::getPropertyValue($sourceMeasure, 'type');
        if (isset($sourceType)) {
            $this->initializeTypeFrom($sourceType);
        }
    }

    public function initializeTypeFrom($sourceType) {
        if (isset($sourceType)) {
            ObjectHelper::mergeWith($this->type, $sourceType, TRUE);
        }
    }

    public function getFunction() {
        if (isset($this->functionError)) {
            throw new IllegalStateException($this->functionError);
        }

        return $this->function;
    }

    public function getAdditivity() {
        return isset($this->additivity) ? $this->additivity : MeasureAdditivity::NON_ADDITIVE;
    }

    protected function initiateType() {
        return new ColumnType();
    }
}
