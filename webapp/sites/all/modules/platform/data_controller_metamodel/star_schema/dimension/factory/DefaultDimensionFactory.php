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


class DefaultDimensionFactory extends DimensionFactory {

    private $handlerConfigurations = NULL;
    private $handlers = array();

    private $datatypeHandlerMappings = NULL;

    public function __construct() {
        parent::__construct();

        $this->handlerConfigurations = module_invoke_all('dp_dimension');
    }

    protected function ensureHandler($handlerKey) {
        $handler = isset($this->handlers[$handlerKey]) ? $this->handlers[$handlerKey] : NULL;
        if (!isset($handler)) {
            $handlerConfiguration = $this->handlerConfigurations[$handlerKey];
            $classname = $handlerConfiguration['classname'];

            $handler = new $classname();
            $this->handlers[$handlerKey] = $handler;
        }
    }

    protected function findHandler($datatype) {
        if (!isset($this->datatypeHandlerMappings[$datatype])) {
            $selectedHandlerKey = NULL;
            // the type is supported 'natively'
            if (isset($this->handlerConfigurations[$datatype])) {
                $this->ensureHandler($datatype);
                $selectedHandlerKey = $datatype;
            }
            else {
                // trying to find a handler which supports the data type
                foreach ($this->handlerConfigurations as $handlerKey => $handlerConfiguration) {
                    $classname = $handlerConfiguration['classname'];
                    // when the key is equal to the handler class name it means that the handler supports 'custom' data types
                    if ($handlerKey == $classname) {
                        $this->ensureHandler($handlerKey);

                        $handler = $this->handlers[$handlerKey];
                        if ($handler->isDataTypeSupported($datatype)) {
                            if (isset($selectedHandlerKey)) {
                                $oldSelectedHandler = $this->handlers[$selectedHandlerKey];
                                throw new IllegalStateException(t(
                                    'Several handlers support %datatype data type: [%handlerClassNameA, %handlerClassNameB]',
                                    array(
                                        '%datatype' => $datatype,
                                        '%handlerClassNameA' => get_class($oldSelectedHandler),
                                        '%handlerClassNameB' => get_class($handler))));
                            }

                            $selectedHandlerKey = $handlerKey;
                        }
                    }
                }
            }
            if (!isset($selectedHandlerKey)) {
                if (isset($this->handlerConfigurations[self::DIMENSION_KEY__DEFAULT])) {
                    $this->ensureHandler(self::DIMENSION_KEY__DEFAULT);
                    $selectedHandlerKey = self::DIMENSION_KEY__DEFAULT;
                }
            }

            $this->datatypeHandlerMappings[$datatype] = isset($selectedHandlerKey) ? $selectedHandlerKey : FALSE;
        }

        $handlerKey = $this->datatypeHandlerMappings[$datatype];

        return ($handlerKey === FALSE) ? NULL : $this->handlers[$handlerKey];
    }

    public function getHandlers() {
        foreach ($this->handlerConfigurations as $handlerKey => $handlerConfiguration) {
            $this->ensureHandler($handlerKey);
        }

        return $this->handlers;
    }
}
