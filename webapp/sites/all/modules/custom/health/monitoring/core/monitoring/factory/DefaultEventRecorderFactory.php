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


class DefaultEventRecorderFactory extends EventRecorderFactory {

    private $handlerConfigurations = NULL;
    private $handlers = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('gd_health_monitoring');
    }

    public function __destruct() {
        // because it is executed in destructor we should not allow exceptions to reach PHP script execution engine
        // otherwise execution of the script will halt
        try {
            $this->flush();
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        parent::__destruct();
    }

    protected function initializeHandlers() {
        $this->handlers = array();

        foreach ($this->handlerConfigurations as $handlerConfiguration) {
            $classname = $handlerConfiguration['classname'];
            $handler = new $classname();

            $this->handlers[] = $handler;
        }
    }

    protected function getRequestId() {
        $requestId = &drupal_static(__CLASS__ . '::requestId');
        if (!isset($requestId)) {
            $sequenceName = NameSpaceHelper::addNameSpace(get_class($this), 'requestId');
            $requestId = Sequence::getNextSequenceValue($sequenceName);
        }

        return $requestId;
    }

    public function record(AbstractEvent $event) {
        if (!isset($this->handlers)) {
            $this->initializeHandlers();
        }
        // if we do not have any registered handler we should not continue
        if (count($this->handlers) == 0) {
            return;
        }

        $requestId = $this->getRequestId();

        foreach ($this->handlers as $handler) {
            $handler->record($requestId, $event);
        }
    }

    protected function flush() {
        if (isset($this->handlers)) {
            foreach ($this->handlers as $handler) {
                $handler->flush();
            }
        }
    }
}
