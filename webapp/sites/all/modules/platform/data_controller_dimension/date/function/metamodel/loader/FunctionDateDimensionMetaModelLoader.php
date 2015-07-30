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


class FunctionDateDimensionMetaModelLoader extends AbstractMetaModelLoader {

    public function finalize(AbstractMetaModel $metamodel) {
        parent::finalize($metamodel);

        $environment_metamodel = data_controller_get_environment_metamodel();

        $generators = NULL;

        foreach ($metamodel->datasets as $dataset) {
            $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

            // preparing generator for the data source type
            if (!isset($generators[$datasource->type])) {
                $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

                $generators[$datasource->type] = new FunctionDateDimensionMetaDataGenerator($datasourceQueryHandler);
            }
            $generator = $generators[$datasource->type];

            foreach ($dataset->columns as $column) {
                $generator->generate($column);
            }
        }
    }
}
