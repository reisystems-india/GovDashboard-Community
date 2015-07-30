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


class DefaultJoinControllerFactory extends JoinControllerFactory {

    private $handlerConfigurations = NULL;
    private $handlers = NULL;

    protected function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_join_method');
    }

    protected function getHandlerClassName($method) {
        $classname = isset($this->handlerConfigurations[$method]['classname']) ? $this->handlerConfigurations[$method]['classname'] : NULL;
        if (!isset($classname)) {
            throw new IllegalArgumentException(t('Unsupported join method: %method', array('%method' => $method)));
        }

        return $classname;
    }

    public function getHandler($method) {
        if (isset($this->handlers[$method])) {
            return $this->handlers[$method];
        }

        $classname = $this->getHandlerClassName($method);

        $handler = new $classname();

        $this->handlers[$method] = $handler;

        return $handler;
    }

    public function getSupportedMethods() {
        return array_keys($this->handlerConfigurations);
    }
}
