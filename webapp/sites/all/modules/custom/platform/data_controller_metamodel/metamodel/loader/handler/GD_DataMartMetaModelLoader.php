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


class GD_DataMartMetaModelLoader extends AbstractMetaModelLoader {

    public function load(AbstractMetaModel $environment_metamodel, array $filters = NULL) {
        LogHelper::log_notice(t('Loading Environment Meta Model from GovDashboard Content Types ...'));

        // Note we do not apply filters because we do not have any
        // if we want to use the filters we would need to prepare list of data source names
        // but to prepare those name we need to load meta model.
        // but that is what we are trying to do in this code
        // Catch 22
        if (isset($filters)) {
            throw new UnsupportedOperationException(t('Filters are not supported during data source loading'));
        }

        $datamartNodes = gd_datamart_get_datamarts(LOAD_ENTITY);

        // preparing data sources
        foreach ($datamartNodes as $datamartNode) {
            GD_DataMartMetaModelLoaderHelper::prepareDataSource($environment_metamodel, $datamartNode);
        }

        // finalizing the preparation
        foreach($datamartNodes as $datamartNode) {
            $datasource = GD_DataMartMetaModelLoaderHelper::getDataSourceByNodeId($environment_metamodel->datasources, $datamartNode->nid);

            GD_DataMartMetaModelLoaderHelper::finalizeDataSourcePreparation($environment_metamodel, $datasource);
        }

        LogHelper::log_info(t('Processed @datamartCount data mart node(s)', array('@datamartCount' => count($datamartNodes))));
    }
}
