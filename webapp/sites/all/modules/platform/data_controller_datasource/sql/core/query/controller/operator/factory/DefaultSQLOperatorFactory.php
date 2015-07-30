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


class DefaultSQLOperatorFactory extends SQLOperatorFactory {

    protected function getHandlerClassName($operatorClassName) {
        $sqlOperatorClassName = 'SQL_' . $operatorClassName;

        if (!class_exists($sqlOperatorClassName)) {
            throw new IllegalArgumentException(t(
                'Unsupported SQL-driven extension for the operator: %className',
                array('%className' => $operatorClassName)));
        }

        return $sqlOperatorClassName;
    }

    public function getHandler(DataSourceQueryHandler $datasourceQueryHandler, OperatorHandler $operatorHandler) {
        $operatorClassName = get_class($operatorHandler);

        $sqlOperatorClassName = $this->getHandlerClassName($operatorClassName);

        return new $sqlOperatorClassName($datasourceQueryHandler, $operatorHandler);
    }
}
