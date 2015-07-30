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


class DataSourceColumnNameHelper {

    // support for auto incrementation as column name suffix
    public static $AUTOMATIC_SUFFIX_DELIMITER = '_';
    protected static $automaticSuffixSequence = 0;

    // internal cache
    protected static $truncatedNames = NULL; // [$maximumLength][$originalColumnName]

    public static function generateFromParameterElements($maximumLength, $rootName, $leafName = NULL) {
        $columnName = ParameterNameHelper::assemble(
            self::generateFromReference($rootName),
            self::generateFromReference($leafName),
            '_');

        return self::generateFromColumnName($maximumLength, $columnName);
    }

    public static function generateFromColumnName($maximumLength, $columnName) {
        $truncatedColumnName = $columnName;

        if (isset(self::$truncatedNames[$maximumLength][$columnName])) {
            $truncatedColumnName = self::$truncatedNames[$maximumLength][$columnName];
        }
        elseif (strlen($truncatedColumnName) > $maximumLength) {
            $suffix = self::$automaticSuffixSequence++;

            $maximumColumnPrefixLength = $maximumLength;
            $maximumColumnPrefixLength -= strlen(self::$AUTOMATIC_SUFFIX_DELIMITER);
            $maximumColumnPrefixLength -= strlen($suffix);

            $shreddableCharacterCount = strlen($truncatedColumnName) - $maximumColumnPrefixLength;
            $truncatedColumnName = ColumnNameTruncator::shortenName($truncatedColumnName, $shreddableCharacterCount);

            if (strlen($truncatedColumnName) > $maximumColumnPrefixLength) {
                $truncatedColumnName = substr($truncatedColumnName, 0, $maximumColumnPrefixLength);
            }
            $truncatedColumnName .= self::$AUTOMATIC_SUFFIX_DELIMITER . $suffix;

            self::$truncatedNames[$maximumLength][$columnName] = $truncatedColumnName;
        }

        return $truncatedColumnName;
    }
    
    protected static function generateFromReference($reference) {
        if (!isset($reference)) {
            return NULL;
        }

        $parts = ReferencePathHelper::splitReference($reference);

        $databaseColumnName = NULL;
        for ($i = 0, $count = count($parts); $i < $count; $i += 2) {
            $resource = $parts[$i];
            $name = $parts[$i + 1];

            $columnNameSegment = $name;
            if (isset($resource)) {
                list($namespace, $resourceName) = NameSpaceHelper::splitAlias($resource);

                $columnNameSegment .= '_';
                if (isset($namespace)) {
                    $columnNameSegment .= $namespace . '_';
                }
                $columnNameSegment .= $resourceName;
            }

            if (isset($databaseColumnName)) {
                $databaseColumnName .= '_';
            }
            $databaseColumnName .= $columnNameSegment;
        }

        // replacing non-word characters with '_'
        $databaseColumnName = preg_replace('#\W+#', '_', $databaseColumnName);
        // removing several subsequent instances of '_'
        $databaseColumnName = preg_replace('#_{2,}#', '_', $databaseColumnName);
        // fixing possibility for leading digits
        if ((strlen($databaseColumnName) > 0) && is_numeric(substr($databaseColumnName, 0, 1))) {
            $databaseColumnName = 'fix_' . $databaseColumnName;
        }

        $databaseColumnName = strtolower($databaseColumnName);

        return $databaseColumnName;
    }
}
