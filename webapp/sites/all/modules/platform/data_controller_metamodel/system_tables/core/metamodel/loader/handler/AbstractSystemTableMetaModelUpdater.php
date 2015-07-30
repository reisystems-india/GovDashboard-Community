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


abstract class AbstractSystemTableMetaModelUpdater extends AbstractSystemTableMetaModelLoader {

    protected function initiateCallContext() {
        return new SystemTableMetaModelUpdaterCallContext();
    }

    abstract protected function prepareDatasets4Update(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource);

    protected function updateDatasets(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $updatedDatasets = NULL;

        // processing meta data for selected datasets
        $columnsProperties = $this->loadColumnsProperties($callcontext, $datasource);
        if (isset($columnsProperties)) {
            foreach ($columnsProperties as $columnProperties) {
                $tableAccessKey = $this->adjustTableName($columnProperties[self::CN_TABLE_NAME]);
                // checking if we need to work with the table
                if (!isset($callcontext->datasets[$tableAccessKey])) {
                    continue;
                }

                $datasets = $callcontext->datasets[$tableAccessKey];

                // invalidating column indexes
                if (!isset($updatedDatasets[$tableAccessKey])) {
                    foreach ($datasets as $dataset) {
                        $dataset->invalidateColumnIndexes();
                    }

                    $updatedDatasets[$tableAccessKey] = TRUE;
                }

                foreach ($datasets as $dataset) {
                    $column = new ColumnMetaData();
                    $column->name = $this->adjustColumnName($columnProperties[self::CN_COLUMN_NAME]);
                    $column->columnIndex = $columnProperties[self::CN_COLUMN_INDEX];
                    // preparing column type
                    $column->type->databaseType = $columnProperties[self::CN_COLUMN_TYPE];
                    $this->generateColumnApplicationType($callcontext, $datasource, $column);

                    // adjusting column properties
                    if (!isset($column->type->applicationType)) {
                        $column->visible = FALSE;
                    }
                    $this->adjustColumnVisibility($callcontext, $column);

                    $dataset->initializeColumnFrom($column);
                }
            }

            // marking all selected datasets as completed
            foreach ($callcontext->datasets as $tableAccessKey => $datasets) {
                if (!isset($updatedDatasets[$tableAccessKey])) {
                    continue;
                }

                foreach ($datasets as $dataset) {
                    $dataset->markAsComplete();
                }
            }
        }

        LogHelper::log_info(t(
            'Processed system meta data about @tableCount table(s) and @columnCount column(s)',
            array('@tableCount' => count($updatedDatasets), '@columnCount' => count($columnsProperties))));
    }

    protected function loadFromDataSource(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource, array $filters = NULL) {
        LogHelper::log_notice(t(
            "Updating Meta Model from '@datasourceName' data source (type: @datasourceType) system tables ...",
            array('@datasourceName' => $datasource->publicName, '@datasourceType' => $datasource->type)));

        $this->prepareDatasets4Update($callcontext, $metamodel, $datasource);
        $this->updateDatasets($callcontext, $datasource);

        $this->processTableComments($callcontext, $metamodel, $datasource);
        $this->processColumnComments($callcontext, $metamodel, $datasource);
    }
}

class SystemTableMetaModelUpdaterCallContext extends SystemTableMetaModelLoaderCallContext {

    public $datasets = array(); // table access key => dataset[]
}
