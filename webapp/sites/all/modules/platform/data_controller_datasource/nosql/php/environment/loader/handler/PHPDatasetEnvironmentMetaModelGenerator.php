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


class PHPDatasetEnvironmentMetaModelGenerator extends AbstractMetaModelLoader {

    public function load(AbstractMetaModelFactory $factory, AbstractMetaModel $environment_metamodel, array $filters = NULL) {
        LogHelper::log_notice(t('Generating Environment Meta Model for PHP dataset functionality ...'));

        $datasourceCount = 0;

        $datasource = new DataSourceMetaData();
        $datasource->name = PHPDataSourceHandler::$DATASOURCE_NAME__DEFAULT;
        $datasource->type = PHPDataSourceHandler::$DATASOURCE__TYPE;
        $environment_metamodel->registerDataSource($datasource);
        $datasourceCount++;

        LogHelper::log_info(t('Generated @datasourceCount data sources', array('@datasourceCount' => $datasourceCount)));
    }
}
