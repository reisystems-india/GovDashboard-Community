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


abstract class OperatorFactory extends AbstractFactory {

    /**
     * @static
     * @return OperatorFactory
     */
    public static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new DefaultOperatorFactory();
        }

        return $instance;
    }

    /**
     * @param $operatorName
     *     A string containing name of an operator.
     * @param ...
     *     A variable number of arguments which are passed to corresponding operator handler instance.
     *     Instead of a variable number of arguments, you may also pass a single array containing the arguments.
     */
    public abstract function initiateHandler($operatorName);

    public abstract function isSupported($operatorName);
    public abstract function getSupportedOperators();

    /**
     * @param $operatorName
     * @return OperatorMetaData|null
     */
    public abstract function getOperatorMetaData($operatorName);
}