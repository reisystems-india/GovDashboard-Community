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


class NameSpaceHelper {

    const NAME_SPACE_SEPARATOR = ':';

    public static function addNameSpace($namespace, $name) {
        return $namespace . self::NAME_SPACE_SEPARATOR . $name;
    }

    public static function resolveNameSpace($namespace, $alias) {
        list($originalNameSpace, $name) = self::splitAlias($alias);

        return isset($originalNameSpace) ? $alias : self::addNameSpace($namespace, $alias);
    }

    public static function splitAlias($alias) {
        // looking for last occurrence of name space separator
        $index = strrpos($alias, self::NAME_SPACE_SEPARATOR);

        return ($index === FALSE) ? array(NULL, $alias) : array(substr($alias, 0, $index), substr($alias, $index + 1));
    }

    public static function findNameSpace($alias) {
        list($namespace, $name) = self::splitAlias($alias);

        return $namespace;
    }

    public static function removeNameSpace($alias) {
        list($namespace, $name) = self::splitAlias($alias);

        return $name;
    }

    public static function getNameSpace($alias) {
        $namespace = self::findNameSpace($alias);
        if (!isset($namespace)) {
            throw new IllegalArgumentException(t('Name space is not defined for the name: %alias', array('%alias' => $alias)));
        }

        return $namespace;
    }

    public static function checkAlias($alias) {
        $v = $alias;
        do {
            list($v, $name) = NameSpaceHelper::splitAlias($v);
            StringDataTypeHandler::checkValueAsWord($name);
        }
        while ($v != NULL);
    }
}
