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


class PostgreSQLCreateTableStatementImpl extends AbstractCreateTableStatementImpl {

    protected function maxLength4VariableLengthString() {
        return 1024 * 1024 * 1024; // 1 GB
    }

    protected function assembleLongString(ColumnMetaData $column) {
        $column->type->databaseType = 'TEXT';
    }

    protected function assembleBigInteger(ColumnMetaData $column) {
        $column->type->databaseType = 'BIGINT';
    }
}
