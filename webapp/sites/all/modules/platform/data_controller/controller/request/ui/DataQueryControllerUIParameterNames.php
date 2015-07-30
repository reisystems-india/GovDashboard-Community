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


interface DataQueryControllerUIParameterNames {

    const DATASET = 'dn';
    const COLUMNS = 'c';

    const PARAMETERS = 'p';
    const PARAMETER__COLUMN_NAME = 'n';
    const PARAMETER__OPERATOR_NAME = 'o';
    const PARAMETER__OPERATOR_VALUE = 'v';

    const SORT = 's';
    const OFFSET = 'o';
    const LIMIT = 'l';
}
