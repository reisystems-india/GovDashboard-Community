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


class SettingsPHP_EnvironmentMetaModelLoader extends AbstractMetaModelLoader {

    public function load(AbstractMetaModel $environment_metamodel, array $filters = NULL) {
        LogHelper::log_notice(t('Loading Environment Meta Model from settings.php ...'));

        $datasourceCount = 0;

        $configurationDataSources = Environment::getInstance()->getConfigurationSection('Data Sources');
        if (isset($configurationDataSources)) {
            foreach ($configurationDataSources as $namespace => $sourceDataSources) {
                foreach ($sourceDataSources as $datasourceName => $sourceDataSource) {
                    $datasourceName = NameSpaceHelper::resolveNameSpace($namespace, $datasourceName);

                    $datasource = new DataSourceMetaData();
                    $datasource->name = $datasourceName;
                    $datasource->initializeFrom($sourceDataSource);
                    // it is possible that configuration contains 'readonly' property. We need to honor it
                    // ... and only when it is not set we mark the data source as read only
                    if (!isset($datasource->readonly)) {
                        $datasource->readonly = TRUE;
                    }

                    $environment_metamodel->registerDataSource($datasource);

                    $datasourceCount++;
                }
            }
        }

        LogHelper::log_info(t('Processed @datasourceCount data sources', array('@datasourceCount' => $datasourceCount)));
    }
}
