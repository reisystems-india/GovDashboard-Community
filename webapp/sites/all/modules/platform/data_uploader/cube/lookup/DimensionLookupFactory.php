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


abstract class DimensionLookupFactory extends AbstractFactory {

    /**
     * @static
     * @return DimensionLookupFactory
     */
    public static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new DefaultDimensionLookupFactory();
        }

        return $instance;
    }

    abstract function registerHandlerConfiguration($datatype, $classname);

    /**
     * @param $datatype
     * @return DimensionLookupHandler
     */
    abstract public function findHandler($datatype);

    /**
     * @param $datatype
     * @return DimensionLookupHandler
     * @throws IllegalArgumentException
     */
    public function getHandler($datatype) {
        $handler = $this->findHandler($datatype);
        if (!isset($handler)) {
            throw new IllegalArgumentException(t(
                'Lookup handler is not available for %datatype data type',
                array('%datatype' => $datatype)));
        }

        return $handler;
    }
}