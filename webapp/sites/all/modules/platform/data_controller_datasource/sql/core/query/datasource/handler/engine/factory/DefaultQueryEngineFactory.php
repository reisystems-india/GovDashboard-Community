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

class DefaultQueryEngineFactory extends QueryEngineFactory {

    private $handlerConfigurations = NULL;
    private $handler = NULL;

    public function __construct() {
        parent::__construct();

        $this->handlerConfigurations = module_invoke_all('dp_query_engine');
    }

    protected function getHandlerClassName() {
        $count = count($this->handlerConfigurations);
        if ($count == 0) {
            throw new IllegalStateException(t('Could not find active implementations of Query Engine. Enable one to proceed'));
        }
        else if ($count == 1) {
            $configuration = reset($this->handlerConfigurations);
        }
        else {
            throw new IllegalStateException(t(
                'Found %queryEngineCount active implementations of Query Engine. Disable all but one to proceed',
                array('%queryEngineCount' => $count)));
        }

        $classname = isset($configuration['classname']) ? $configuration['classname'] : NULL;
        if (!isset($classname)) {
            throw new IllegalStateException(t('Could not find Query Engine implementation'));
        }

        return $classname;
    }

    public function getHandler() {
        if (!isset($this->handler)) {
            $classname = $this->getHandlerClassName();

            $this->handler = new $classname();
        }

        return $this->handler;
    }
}
