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


abstract class AbstractDatasetUIMetaDataGenerator extends AbstractObject {

    // *************************************************************************
    // *  Prepare element name
    // *************************************************************************
    public static function prepareElementUIMetaDataName($namespace, $name) {
        return NameSpaceHelper::addNameSpace($namespace, $name);
    }

    public static function splitElementUIMetaDataName($name) {
        // looking for FIRST occurrence of name space separator
        $index = strpos($name, NameSpaceHelper::NAME_SPACE_SEPARATOR);

        return ($index === FALSE) ? array(NULL, $name) : array(substr($name, 0, $index), substr($name, $index + 1));
    }

    protected static function prepareReferencedElementName($referencePath, $datasetName, $elementName) {
        $referencedElementName = $elementName;
        if (isset($referencePath)) {
            $nestedReference = ReferencePathHelper::assembleReference($datasetName, $elementName);
            $referencedElementName = ReferencePathHelper::assembleReferencePath(array($nestedReference, $referencePath));
        }

        return $referencedElementName;
    }

    public static function prepareColumnUIMetaDataName($referencePath, $datasetName, $columnName) {
        return self::prepareElementUIMetaDataName(
            AbstractAttributeUIMetaData::NAME_SPACE,
            self::prepareReferencedElementName($referencePath, $datasetName, $columnName));
    }
}
