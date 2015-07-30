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

interface SelectStatement {

    /**
     * @param $name
     * @param $alias
     * @return SelectStatement_Table
     */
    function newTable($name, $alias = NULL);

    /**
     * @param $sql
     * @param null $alias
     * @return SelectStatement_SubQuery
     */
    function newSubquery($sql, $alias = NULL);
}

interface SelectStatement_TableReference {

    /**
     * @param $name
     * @return SelectStatement_Column
     */
    function newColumn($name);

    /**
     * @param $sql
     * @param null $alias
     * @return SelectStatement_CalculatedColumn
     */
    function newCalculatedColumn($sql, $alias = NULL);
}

interface SelectStatement_Table extends SelectStatement_TableReference {}

interface SelectStatement_SubQuery extends SelectStatement_TableReference {}

interface SelectStatement_ColumnReference {}

interface SelectStatement_Column extends SelectStatement_ColumnReference {}

interface SelectStatement_CalculatedColumn extends SelectStatement_ColumnReference {}
