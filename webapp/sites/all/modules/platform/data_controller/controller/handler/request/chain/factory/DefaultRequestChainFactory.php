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

class DefaultRequestChainFactory extends RequestChainFactory {

    private $handlerConfigurations = NULL;
    private $chain = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_request_chain');
    }

    public function initializeChain() {
        if (!isset($this->chain)) {
            if (isset($this->handlerConfigurations)) {
                $linkClassNames = NULL;
                foreach ($this->handlerConfigurations as $configuration) {
                    $classname = isset($configuration['classname']) ? $configuration['classname'] : NULL;
                    if (!isset($classname)) {
                        continue;
                    }
                    $priority = isset($configuration['priority']) ? $configuration['priority'] : 0;

                    $linkClassNames[$priority][] = $classname;
                }

                $orderedClassNames = array();
                if (isset($linkClassNames)) {
                    krsort($linkClassNames);

                    foreach ($linkClassNames as $priority => $classNames) {
                        $orderedClassNames = array_merge($orderedClassNames, $classNames);
                    }
                }

                $this->chain = new DefaultRequestLinkHandler();
                foreach ($orderedClassNames as $classname) {
                    $this->chain = new $classname($this->chain);
                }
            }
        }

        return $this->chain;
    }
}