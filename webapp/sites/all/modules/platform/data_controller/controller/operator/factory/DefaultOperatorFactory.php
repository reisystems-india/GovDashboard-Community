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


class DefaultOperatorFactory extends OperatorFactory {

    private $handlerConfigurations = NULL;
    private $handlerMetaDataInstances = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_operator');
    }

    public function initiateHandler($operatorName) {
        $values = func_get_args();
        array_shift($values);

        $handlerConfiguration = $this->getHandlerConfiguration($operatorName);

        $classname = $handlerConfiguration['handler']['classname'];

        $handlerClass = new ReflectionClass($classname);

        $params = NULL;
        // first parameter is the operator configuration
        $operatorMetaData = $this->getOperatorMetaData($operatorName);
        $params[] = $operatorMetaData;
        // next are parameters which represent values
        if ((count($values) === 1) && is_array($values[0])) {
            $parameterCount = count($values[0]);

            $expectedMinimumParameterCount = $expectedTotalParameterCount = 0;
            if (isset($operatorMetaData)) {
                $operatorParameters = $operatorMetaData->getParameters();
                if (isset($operatorParameters)) {
                    $expectedTotalParameterCount = count($operatorParameters);
                    foreach ($operatorParameters as $operatorParameter) {
                        if ($operatorParameter->required) {
                            $expectedMinimumParameterCount++;
                        }
                    }
                }
            }

            if ($parameterCount == $expectedTotalParameterCount) {
                ArrayHelper::merge($params, $values[0]);
            }
            elseif ($expectedTotalParameterCount === 1) {
                $params[] = $values[0];
            }
            elseif (($parameterCount < $expectedTotalParameterCount) && ($parameterCount >= $expectedMinimumParameterCount)) {
                // we have some optional parameters which do not need to be provided
                ArrayHelper::merge($params, $values[0]);
            }
            else {
                throw new IllegalArgumentException(t('Inconsistent number of arguments for %name operator', array('%name' => $operatorName)));
            }
        }
        else {
            ArrayHelper::merge($params, $values);
        }

        return $handlerClass->newInstanceArgs($params);
    }

    protected function getHandlerConfiguration($operatorName) {
        if (!isset($this->handlerConfigurations[$operatorName])) {
            throw new IllegalArgumentException(t('Unsupported operator: %name', array('%name' => $operatorName)));
        }

        return $this->handlerConfigurations[$operatorName];
    }

    public function getSupportedOperators() {
        $supportedOperators = NULL;

        foreach ($this->handlerConfigurations as $operatorName => $handlerConfiguration) {
            $supportedOperators[$operatorName] = $handlerConfiguration['description'];
        }

        return $supportedOperators;
    }

    public function isSupported($operatorName) {
        $supportedOperators = $this->getSupportedOperators();

        return isset($supportedOperators[$operatorName]);
    }

    public function getOperatorMetaData($operatorName) {
        if (isset($this->handlerMetaDataInstances[$operatorName])) {
            $metadataInstance = $this->handlerMetaDataInstances[$operatorName];
        }
        else {
            $handlerConfiguration = $this->getHandlerConfiguration($operatorName);

            $classname = isset($handlerConfiguration['metadata']['classname'])
                ? $handlerConfiguration['metadata']['classname']
                : NULL;

            $metadataInstance = isset($classname) ? new $classname() : FALSE;

            $this->handlerMetaDataInstances[$operatorName] = $metadataInstance;
        }

        return ($metadataInstance === FALSE) ? NULL : $metadataInstance;
    }
}