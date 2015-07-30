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


class TableReferenceHelper {

    const TABLE_OWNER__SEPARATOR = '.';

    public static function assembleTableReference($tableOwner, $tableName) {
        return (isset($tableOwner) ? ($tableOwner . self::TABLE_OWNER__SEPARATOR) : '') . $tableName;
    }

    public static function splitTableReference($tableReference) {
        $index = strrpos($tableReference, self::TABLE_OWNER__SEPARATOR);

        return ($index === FALSE) ? array(NULL, $tableReference) : array(substr($tableReference, 0, $index), substr($tableReference, $index + 1));
    }

    public static function findTableOwner($tableReference) {
        list($tableOwner, $tableName) = self::splitTableReference($tableReference);

        return $tableOwner;
    }

    public static function getTableName($tableReference) {
        list($tableOwner, $tableName) = self::splitTableReference($tableReference);

        return $tableName;
    }
}
