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


class SimpleDimensionLookupHandler extends AbstractDimensionLookupHandler {

    function prepareLookupValue($value) {
        $lookupValue = new DimensionLookupHandler__LookupValue();
        // for this dimension lookup handler we do not need to support any lookups. $value is the identifier
        $lookupValue->identifier = $value;

        return $lookupValue;
    }

    public function prepareDatasetColumnLookupIds($datasetName, ColumnMetaData $column, array &$lookupValues) {}

    public function prepareDimension(MetaModel $metamodel, DatasetMetaData $dataset, $columnName, CubeMetaData $cube) {
        $column = $dataset->getColumn($columnName);
        $dimension = $cube->getDimension($columnName);

        $dimension->attributeColumnName = $column->name;
    }
}
