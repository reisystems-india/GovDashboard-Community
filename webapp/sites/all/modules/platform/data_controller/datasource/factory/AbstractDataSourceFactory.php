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


abstract class AbstractDataSourceFactory extends AbstractFactory {

    private $handlerConfigurations = NULL;
    private $extensionConfigurations = NULL;
    private $handlers = NULL;

    protected function __construct() {
        parent::__construct();

        $this->extensionConfigurations = module_invoke_all('dp_datasource');
        $this->handlerConfigurations = module_invoke_all($this->getHookName());
    }

    abstract protected function getFactoryPublicNamePrefix();

    abstract protected function getHookName();

    protected function getExtensionConfiguration($type) {
        return isset($this->extensionConfigurations[$type]) ? $this->extensionConfigurations[$type] : NULL;
    }

    protected function getHandlerConfiguration($type) {
        if (!isset($this->handlerConfigurations[$type])) {
            $prefix = $this->getFactoryPublicNamePrefix();
            throw new IllegalArgumentException(t("Unsupported $prefix Data Source handler: %type", array('%type' => $type)));
        }

        return $this->handlerConfigurations[$type];
    }

    public function getHandler($type) {
        if (isset($this->handlers[$type])) {
            return $this->handlers[$type];
        }

        $extensionConfiguration = $this->getExtensionConfiguration($type);
        $handlerConfiguration = $this->getHandlerConfiguration($type);

        $combinedExtensionConfigurations = NULL;
        // adding generic configuration
        ArrayHelper::merge($combinedExtensionConfigurations, $extensionConfiguration['extensions']);
        // adding handler specific configurations
        if (isset($handlerConfiguration['extensions'])) {
            ArrayHelper::merge($combinedExtensionConfigurations, $handlerConfiguration['extensions']);
            unset($handlerConfiguration['extensions']);
        }

        $classname = $handlerConfiguration['classname'];

        $handler = new $classname($type, $combinedExtensionConfigurations);

        $this->handlers[$type] = $handler;

        return $handler;
    }
}
