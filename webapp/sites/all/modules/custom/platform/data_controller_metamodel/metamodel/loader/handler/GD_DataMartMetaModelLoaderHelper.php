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


class GD_DataMartMetaModelLoaderHelper {

    public static function prepareDataSourceName($datamartNode) {
        return get_node_field_value($datamartNode, 'field_datamart_sysname', 0, 'value', TRUE);
    }

    public static function prepareDataSource(EnvironmentMetaModel $environment_metamodel, $datamartNode) {
        $datasource = new DataSourceMetaData();
        $datasource->name = self::prepareDataSourceName($datamartNode);
        $datasource->publicName = $datamartNode->title;
        $datasource->description = get_node_field_value($datamartNode, 'field_datamart_desc');

        $datasource->parentName = get_node_field_value($datamartNode, 'field_datamart_parent_sysname');
        $datasource->type = get_node_field_value($datamartNode, 'field_datamart_type');
        $datasource->readonly = get_node_field_boolean_value($datamartNode, 'field_datamart_readonly');

        $datasource->initializeFrom(get_node_field_object_value($datamartNode, 'field_datamart_options'));

        // datasource system properties
        $datasource->nid = $datamartNode->nid;

        // marking as public to prevent the property from being populated from parent data source (which can be private)
        $datasource->markAsPublic();

        $environment_metamodel->registerDataSource($datasource);

        return $datasource;
    }

    public static function finalizeDataSourcePreparation(EnvironmentMetaModel $environment_metamodel, DataSourceMetaData $datasource) {
        if (!isset($datasource->parentName)) {
            return;
        }

        $parentDataSource = $environment_metamodel->getDataSource($datasource->parentName);

        ObjectHelper::mergeWith($datasource, $parentDataSource, TRUE, ObjectHelper::EXISTING_PROPERTY_RULE__SKIP_IF_PRESENT);
    }

    /**
     * @static
     * @param array|null $datasources
     * @param $datasource_nid
     * @return null|DataSourceMetaData
     */
    public static function findDataSourceByNodeId(array &$datasources = NULL, $datasource_nid) {
        if (isset($datasources)) {
            foreach ($datasources as $datasource) {
                if (isset($datasource->nid) && ($datasource->nid == $datasource_nid)) {
                    return $datasource;
                }
            }
        }

        return NULL;
    }

    /**
     * @static
     * @param array|null $datasources
     * @param $datasource_nid
     * @return DataSourceMetaData
     * @throws IllegalArgumentException
     */
    public static function getDataSourceByNodeId(array &$datasources = NULL, $datasource_nid) {
        $datasource = self::findDataSourceByNodeId($datasources, $datasource_nid);
        if (!isset($datasource)) {
            throw new IllegalArgumentException(t(
                'Could not find data source definition by the node identifier: %datasourceNodeId',
                array('%datasourceNodeId' => $datasource_nid)));
        }

        return $datasource;
    }
}