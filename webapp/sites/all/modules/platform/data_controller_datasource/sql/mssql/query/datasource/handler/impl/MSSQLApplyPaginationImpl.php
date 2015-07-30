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


class MSSQLApplyPaginationImpl extends AbstractApplyPaginationImpl {

    public function apply(DataSourceHandler $handler, &$sql, $startWith, $limit) {
        $sql .= "\nOFFSET " . (isset($startWith) ? $startWith : 0) .  ' ROWS';
        if (isset($limit)) {
            if ($limit == 0) {
                throw new UnsupportedOperationException(t("Microsoft SQL Server does not support pagination 'LIMIT' parameter when it equals 0"));
            }

            $sql .= "\n FETCH NEXT $limit ROWS ONLY";
        }
    }
}
