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


/**
 * Terminology:
 *     'Reference Path': publicName@GrantTypes/grant_type_id@Applications/application_id@Grants
 *     'Reference' is 'Reference Path' or subset of it:
 *          - publicName@GrantTypes/grant_type_id@Applications/application_id@Grants
 *          - publicName@GrantTypes
 *     'Reference Parts': [GrantTypes, publicName, Applications, grant_type_id, Grants, application_id]
 */
class ReferencePathHelper {

    const SEPARATOR__REFERENCE = '@';
    const SEPARATOR__REFERENCE_PATH = '/';

    // *********************************************************************************************
    // * Reference Path
    // *********************************************************************************************
    public static function assembleReferencePath(array $references) {
        $referencePath = '';

        foreach ($references as $reference) {
            if (!isset($reference)) {
                continue;
            }

            $reference = trim($reference);
            if ($reference === '') {
                continue;
            }

            if ($referencePath != '') {
                $referencePath .= self::SEPARATOR__REFERENCE_PATH;
            }

            $referencePath .= $reference;
        }

        if ($referencePath === '') {
            throw new IllegalArgumentException(t('Assembled reference path is empty'));
        }

        return $referencePath;
    }

    public static function splitReferencePath($reference) {
        return explode(self::SEPARATOR__REFERENCE_PATH, $reference);
    }

    // *********************************************************************************************
    // * Reference
    // *********************************************************************************************
    public static function assembleReference($resource, $name) {
        if (!isset($resource) && !isset($name)) {
            throw new IllegalArgumentException(t('Undefined resource and name for reference assembling'));
        }

        return (isset($name) ? $name : '') . (isset($resource) ? self::SEPARATOR__REFERENCE . $resource : '');
    }

    public static function splitReference($reference) {
        $parts = NULL;

        $references = self::splitReferencePath($reference);
        foreach ($references as $reference) {
            $items = explode(self::SEPARATOR__REFERENCE, $reference);
            // adding resource name
            $parts[] = (count($items) > 1) ? $items[1] : NULL;
            // adding name
            $parts[] = $items[0];
        }

        return $parts;
    }

    public static function checkReference($reference, $checkResource = TRUE, $checkName = TRUE) {
        if (!isset($reference)) {
            return;
        }

        $parts = self::splitReference($reference);
        for ($i = 0, $count = count($parts); $i < $count; $i += 2) {
            if ($checkResource) {
                $resource = $parts[$i];
                if (isset($resource)) {
                    NameSpaceHelper::checkAlias($resource);
                }
            }

            if ($checkName) {
                $name = $parts[$i + 1];
                StringDataTypeHandler::checkValueAsWord($name);
            }
        }
    }

    // *********************************************************************************************
    // * Reference Parts
    // *********************************************************************************************
    public static function assembleReferenceParts(array $parts) {
        $references = NULL;

        while (TRUE) {
            $resource = array_shift($parts);
            $name = array_shift($parts);
            if (!isset($resource) && !isset($name)) {
                break;
            }

            $references[] = self::assembleReference($resource, $name);
        }

        return isset($references) ? self::assembleReferencePath($references) : NULL;
    }
}
