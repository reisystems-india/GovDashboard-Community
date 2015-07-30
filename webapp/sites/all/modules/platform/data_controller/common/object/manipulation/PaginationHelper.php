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


// FIXME move the function to join_controller or new module to support in-memory operations
function paginate_records(array &$records = NULL, $start_with = 0, $limit = NULL) {
    if (!isset($records)) {
        return $records;
    }

    if ((!isset($start_with) || ($start_with == 0)) && !isset($limit)) {
        return $records;
    }

    return array_slice($records, (isset($start_with) ? $start_with : 0), $limit);
}
