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


interface SQLDataSourceHandler {

    function getDataSourceOwner($datasourceName);

    function formatOperatorValue(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, OperatorHandler $value);
}

function assemble_database_entity_name(SQLDataSourceHandler $datasourceHandler, $datasourceName, $entityName) {
    $assembledEntityName = $entityName;

    $index = strpos($assembledEntityName, '.');
    if ($index === FALSE) {
        $owner = $datasourceHandler->getDataSourceOwner($datasourceName);
        if (isset($owner)) {
            $assembledEntityName = $owner . '.' . $assembledEntityName;
        }
    }

    return $assembledEntityName;
}
