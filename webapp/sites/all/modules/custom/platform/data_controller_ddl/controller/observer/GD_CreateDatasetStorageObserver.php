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


class GD_CreateDatasetStorageObserver extends AbstractDatasetStorageObserver {

    protected $newLogicalDataset = NULL;

    public function __construct(DatasetMetaData $newLogicalDataset) {
        parent::__construct();
        $this->newLogicalDataset = $newLogicalDataset;
    }

    protected function getDataset_NID(DatasetMetaData $dataset) {
        return $this->newLogicalDataset->nid;
    }

    public function registerDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {
        parent::registerDataset($callcontext, $dataset, $stage);

        if ($stage == DatasetStorageObserver::STAGE__BEFORE) {
            // 'fixing' logical dataset
            if (!isset($this->newLogicalDataset->uuid)) {
                $this->newLogicalDataset->uuid = \GD\Utility\Uuid::generate();
            }

            // 'fixing' physical dataset
            $dataset->loaderName = 'GD_DatasetMetaModelLoader';

            global $user;

            $environment_metamodel = data_controller_get_environment_metamodel();
            $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

            $node = new stdClass();
            $node->type = NODE_TYPE_DATASET;
            $node->language = LANGUAGE_NONE;
            $node->uid = $user->uid;
            node_object_prepare($node);

            $node->title = $dataset->publicName;
            $node->field_dataset_uuid[$node->language][0]['value'] = $this->newLogicalDataset->uuid;
            $node->field_dataset_sysname[$node->language][0]['value'] = $this->newLogicalDataset->name;
            $node->field_dataset_datasource[$node->language][0]['value'] = $datasource->name;
            $node->field_dataset_desc[$node->language][0]['value'] = $dataset->description;
            $node->field_dataset_source_type[$node->language][0]['value'] = $this->newLogicalDataset->sourceType;
            $node->field_dataset_source[$node->language][0]['value'] = $this->newLogicalDataset->source;
            $node->field_dataset_records[$node->language][0]['value'] = 0;
            // updating aliases
            if (isset($this->newLogicalDataset->aliases)) {
                foreach ($this->newLogicalDataset->aliases as $index => $alias) {
                    $node->field_dataset_alias[$node->language][$index]['value'] = $alias;
                }
            }

            $node->status = $dataset->isUsed() ? NODE_PUBLISHED : NODE_NOT_PUBLISHED;

            node_save($node);

            $this->newLogicalDataset->nid = $node->nid;
            $this->newLogicalDataset->loaderName = $dataset->loaderName;
        }
    }

    protected function getColumn_NID(DatasetMetaData $dataset, $columnName) {
        $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);

        return $newLogicalColumn->nid;
    }

    public function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        parent::registerColumn($callcontext, $dataset, $columnName, $stage);

        if ($stage == DatasetStorageObserver::STAGE__BEFORE) {
            $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
            $newColumn = $dataset->getColumn($columnName);

            $node = new stdClass();
            $node->type = NODE_TYPE_COLUMN;
            $node->language = LANGUAGE_NONE;
            node_object_prepare($node);

            $node->field_column_sysname[$node->language][0]['value'] = $newColumn->name;
            $node->field_column_dataset[$node->language][0]['nid'] = $this->getDataset_NID($dataset);
            $node->field_column_index[$node->language][0]['value'] = $newColumn->columnIndex;

            $node->title = $newColumn->publicName;
            $node->field_column_desc[$node->language][0]['value'] = $newColumn->description;

            // Note: storing logical data type for the column
            $node->field_column_datatype[$node->language][0]['value'] = $newLogicalColumn->type->applicationType;
            $node->field_column_format[$node->language][0]['value'] = gd_column_prepare_column_format($newLogicalColumn->type);
            $node->field_column_persistence[$node->language][0]['value'] = $newColumn->persistence;

            $node->field_column_key[$node->language][0]['value'] = $newColumn->isKey() ? 1 : 0;
            $node->field_column_source[$node->language][0]['value'] = $newColumn->source;
            $node->status = $newColumn->isUsed() ? NODE_PUBLISHED : NODE_NOT_PUBLISHED;

            node_save($node);

            $newLogicalColumn->nid = $node->nid;
        }
    }

    public function updateColumnParticipationInDatasetKey(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::updateColumnParticipationInDatasetKey($callcontext, $dataset, $columnName);

        $newColumn = $dataset->getColumn($columnName);

        $node = node_load($this->getColumn_NID($dataset, $columnName));

        $node->field_column_key[$node->language][0]['value'] = $newColumn->isKey() ? 1 : 0;

        node_save($node);
    }

    public function createColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        parent::createColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);

        $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);

        $node = node_load($this->getColumn_NID($dataset, $columnName));
        switch ($stage) {
            case DatasetStorageObserver::STAGE__BEFORE:
                // Note: storing logical data type for the column
                $node->field_column_datatype[$node->language][0]['value'] = $newLogicalColumn->type->applicationType;
                $node->field_column_format[$node->language][0]['value'] = gd_column_prepare_column_format($newLogicalColumn->type);
                break;
            case DatasetStorageObserver::STAGE__AFTER:
                $newColumn = $dataset->getColumn($columnName);

                $node->field_column_persistence[$node->language][0]['value'] = $newColumn->persistence;
                $node->status = $newColumn->isUsed() ? NODE_PUBLISHED : NODE_NOT_PUBLISHED;
                break;
        }
        node_save($node);
    }
}
