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


class DrupalDatabaseEnvironmentMetaModelGenerator extends AbstractMetaModelLoader {

    public static $DATASOURCE_NAME__DEFAULT = 'default:default';

    public function load(AbstractMetaModel $environment_metamodel, array $filters = NULL) {
        LogHelper::log_notice(t('Generating Environment Meta Model for Drupal database connections ...'));

        global $databases;

        $datasourceCount = 0;
        foreach ($databases as $namespace => $connections) {
            foreach ($connections as $datasourceNameOnly => $connection) {
                $datasource = new DataSourceMetaData();
                $datasource->name = NameSpaceHelper::addNameSpace($namespace, $datasourceNameOnly);
                $datasource->markAsPrivate();
                $datasource->readonly = FALSE;
                // setting required properties
                $this->setDataSourceProperty($datasource, $connection, 'type', 'driver');
                // setting other provided properties
                $this->setDataSourceExtensionProperties($datasource, $connection);

                // registering the data source
                $environment_metamodel->registerDataSource($datasource);
                $datasourceCount++;
            }
        }

        // Default database connection is shared because we store common utilities and dimensions there
        $defaultDataSource = $environment_metamodel->getDataSource(self::$DATASOURCE_NAME__DEFAULT);
        $defaultDataSource->shared = TRUE;

        LogHelper::log_info(t('Generated @datasourceCount data sources', array('@datasourceCount' => $datasourceCount)));
    }

    protected function setDataSourceProperty(DataSourceMetaData $datasource, array &$connection, $datasourcePropertyName, $connectionPropertyName) {
        if (!isset($connection[$connectionPropertyName])) {
            return;
        }

        $datasource->$datasourcePropertyName = $connection[$connectionPropertyName];
        unset($connection[$connectionPropertyName]);
    }

    protected function setDataSourceExtensionProperties(DataSourceMetaData $datasource, array $connection) {
        foreach ($connection as $propertyName => $propertyValue) {
            $datasource->$propertyName = $propertyValue;
        }
    }
}
