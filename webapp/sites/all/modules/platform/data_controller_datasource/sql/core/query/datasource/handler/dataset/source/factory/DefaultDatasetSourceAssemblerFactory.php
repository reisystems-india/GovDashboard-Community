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


class DefaultDatasetSourceAssemblerFactory extends DatasetSourceAssemblerFactory {

    private $handlerConfigurations = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_dataset_assembler');
    }

    protected function getHandlerClassName($assemblerName) {
        $classname = isset($this->handlerConfigurations[$assemblerName]['classname']) ? $this->handlerConfigurations[$assemblerName]['classname'] : NULL;
        if (!isset($classname)) {
            throw new IllegalArgumentException(t('Unsupported dataset assembler: %name', array('%name' => $assemblerName)));
        }

        return $classname;
    }

    public function getHandler($assemblerName, $assemblerConfiguration) {
        $classname = $this->getHandlerClassName($assemblerName);

        return new $classname($assemblerConfiguration);
    }
}
