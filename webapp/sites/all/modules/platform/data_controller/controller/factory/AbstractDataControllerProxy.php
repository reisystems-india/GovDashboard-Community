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


abstract class AbstractDataControllerProxy extends AbstractFactory {

    protected $controllerInstance = NULL;

    protected function __construct() {
        parent::__construct();

        $this->controllerInstance = $this->prepareProxiedInstance();
    }

    abstract protected function prepareProxiedInstance();

    public function __call($methodName, $args) {
        $timeStart = microtime(TRUE);
        $result = call_user_func_array(array($this->controllerInstance, $methodName), $args);
        LogHelper::log_notice(t(
            'Data Controller execution time for @methodName(): !executionTime',
            array('@methodName' => $methodName, '!executionTime' => LogHelper::formatExecutionTime($timeStart))));

        return $result;
    }
}
