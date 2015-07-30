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


class DefaultDimensionLookupFactory extends DimensionLookupFactory {

    private $handlerConfigurations = NULL;
    private $handlers = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_star_schema_lookup');
    }

    public function registerHandlerConfiguration($datatype, $classname) {
        $this->handlerConfigurations[$datatype] = $this->prepareHandlerConfiguration($datatype, $classname);
    }

    protected function prepareHandlerConfiguration($datatype, $classname) {
        return array('classname' => $classname);
    }

    public function findHandler($datatype) {
        // checking internal cache
        if (isset($this->handlers[$datatype])) {
            return $this->handlers[$datatype];
        }

        // looking for configuration for the type
        $handlerConfiguration = isset($this->handlerConfigurations[$datatype]) ? $this->handlerConfigurations[$datatype] : NULL;
        if (!isset($handlerConfiguration)) {
            // checking if data type is a reference to lookup dataset column
            list($datasetName) = ReferencePathHelper::splitReference($datatype);
            if (isset($datasetName)) {
                $handlerConfiguration = $this->prepareHandlerConfiguration($datatype, 'LookupDatasetColumnDimensionLookupHandler');
            }
        }

        if (isset($handlerConfiguration)) {
            // initializing handler based on configuration
            $classname = $handlerConfiguration['classname'];
            $handler = new $classname($datatype);
        }
        else {
            $handler = new SimpleDimensionLookupHandler($datatype);
        }

        $this->handlers[$datatype] = $handler;

        return $handler;
    }
}