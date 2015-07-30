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


class OracleApplyPaginationImpl extends AbstractApplyPaginationImpl {

    public function apply(DataSourceHandler $handler, &$sql, $startWith, $limit) {
        $firstRecordNumber = (isset($startWith) && ($startWith > 0)) ? ($startWith + 1) : NULL;

        $sql = 'SELECT n.*' . (isset($firstRecordNumber) ? ', rownum AS original_rownum' : '') . ' FROM ('
            . "\n" . StringHelper::indent($sql, SelectStatementPrint::INDENT__SECTION_ELEMENT, TRUE) . ') n';

        $lastRecordNumber = isset($limit) ? ((isset($firstRecordNumber) ? $firstRecordNumber : 1) + $limit - 1) : NULL;
        if (isset($lastRecordNumber)) {
            $sql .= "\n WHERE rownum <= " . $lastRecordNumber;
        }

        if (isset($firstRecordNumber)) {
            $sql = "SELECT * FROM (\n" . StringHelper::indent($sql, SelectStatementPrint::INDENT__SECTION_ELEMENT, TRUE) . ")\n WHERE original_rownum >= " . $firstRecordNumber;
        }
    }
}
