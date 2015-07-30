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


abstract class DimensionFactory extends AbstractFactory {

    const DIMENSION_KEY__DEFAULT = '__default';

    /**
     * @static
     * @return DimensionFactory
     */
    public static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new DefaultDimensionFactory();
        }

        return $instance;
    }

    protected abstract function findHandler($datatype);

    /**
     * @param $datatype
     * @return DimensionHandler
     * @throws IllegalArgumentException
     */
    public function getHandler($datatype) {
        $handler = $this->findHandler($datatype);
        if (!isset($handler)) {
            throw new IllegalArgumentException(t(
                'Dimension handler is not available for %datatype data type',
                array('%datatype' => $datatype)));
        }

        return $handler;
    }

    public abstract function getHandlers();
}
