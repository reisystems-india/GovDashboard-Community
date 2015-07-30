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


function gd_data_controller_metamodel_entity_insert($entity, $type) {
    if ($type == 'node') {
        if ($entity->type == NODE_TYPE_DATAMART) {
            if ($entity->status == NODE_PUBLISHED) {
                $environment_metamodel = data_controller_get_environment_metamodel();

                // the corresponding node already stored. If the meta model is loaded after the node storing operation it already contains the datasets
                $datasource = GD_DataMartMetaModelLoaderHelper::findDataSourceByNodeId($environment_metamodel->datasources, $entity->nid);
                if (!isset($datasource)) {
                    $datasource = GD_DataMartMetaModelLoaderHelper::prepareDataSource($environment_metamodel, $entity);
                    GD_DataMartMetaModelLoaderHelper::finalizeDataSourcePreparation($environment_metamodel, $datasource);
                }
            }
        }
    }
}

function gd_data_controller_metamodel_entity_update($entity, $type) {
    if ($type == 'node') {
        if ($entity->type == NODE_TYPE_DATAMART) {
            $environment_metamodel = data_controller_get_environment_metamodel();

            $datasource = GD_DataMartMetaModelLoaderHelper::findDataSourceByNodeId($environment_metamodel->datasources, $entity->nid);
            // unregistering OLD configuration
            if (isset($datasource)) {
                $environment_metamodel->unregisterDataSource($datasource->name);
            }
            // registering NEW configuration
            if ($entity->status == NODE_PUBLISHED) {
                $datasource = GD_DataMartMetaModelLoaderHelper::prepareDataSource($environment_metamodel, $entity);
                GD_DataMartMetaModelLoaderHelper::finalizeDataSourcePreparation($environment_metamodel, $datasource);
            }
        }
    }
}

function gd_data_controller_metamodel_entity_delete($entity, $type) {
    if ($type == 'node') {
        if ($entity->type == NODE_TYPE_DATAMART) {
            $environment_metamodel = data_controller_get_environment_metamodel();

            $datasource = GD_DataMartMetaModelLoaderHelper::findDataSourceByNodeId($environment_metamodel->datasources, $entity->nid);
            if (isset($datasource)) {
                $environment_metamodel->unregisterDataSource($datasource->name);
            }
        }
    }
}
