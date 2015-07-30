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


class GD_ModifyDatasetStorageObserver extends GD_CreateDatasetStorageObserver {

    protected $originalLogicalDataset = NULL;

    public function __construct(DatasetMetaData $newLogicalDataset) {
        parent::__construct($newLogicalDataset);

        $metamodel = data_controller_get_metamodel();

        $this->originalLogicalDataset = $metamodel->getDataset($newLogicalDataset->name);
    }

    protected function getDataset_NID(DatasetMetaData $dataset) {
        return $this->originalLogicalDataset->nid;
    }

    public function updateDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::updateDataset($callcontext, $dataset);

        $node = node_load($this->getDataset_NID($dataset));

        $node->title = $dataset->publicName;
        $node->field_dataset_desc[$node->language][0]['value'] = $dataset->description;
        // Note: storing source from logical dataset
        $node->field_dataset_source[$node->language][0]['value'] = $this->newLogicalDataset->source;
        // updating aliases
        unset($node->field_dataset_alias[$node->language]);
        if (isset($this->newLogicalDataset->aliases)) {
            foreach ($this->newLogicalDataset->aliases as $index => $alias) {
                $node->field_dataset_alias[$node->language][$index]['value'] = $alias;
            }
        }

        $node->status = $dataset->isUsed() ? NODE_PUBLISHED : NODE_NOT_PUBLISHED;

        node_save($node);
    }

    public function unregisterDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {
        if ($stage == DatasetStorageObserver::STAGE__BEFORE) {
            node_delete($this->getDataset_NID($dataset));
        }

        parent::unregisterDataset($callcontext, $dataset, $stage);
    }

    public function enableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::enableDataset($callcontext, $dataset);

        $node = node_load($this->getDataset_NID($dataset));

        $node->status = NODE_PUBLISHED;

        node_save($node);
    }

    public function disableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $node = node_load($this->getDataset_NID($dataset));

        $node->status = NODE_NOT_PUBLISHED;

        node_save($node);

        parent::disableDataset($callcontext, $dataset);
    }

    protected function getColumn_NID(DatasetMetaData $dataset, $columnName) {
        $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);

        return $originalLogicalColumn->nid;
    }

    public function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        parent::registerColumn($callcontext, $dataset, $columnName, $stage);

        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
            $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);

            $originalLogicalColumn->nid = $newLogicalColumn->nid;
        }
    }

    public function updateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::updateColumn($callcontext, $dataset, $columnName);

        $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
        $newColumn = $dataset->getColumn($columnName);

        $node = node_load($this->getColumn_NID($dataset, $columnName));

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
    }

    public function relocateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::relocateColumn($callcontext, $dataset, $columnName);

        $newColumn = $dataset->getColumn($columnName);

        $node = node_load($this->getColumn_NID($dataset, $columnName));

        $node->field_column_index[$node->language][0]['value'] = $newColumn->columnIndex;

        node_save($node);
    }

    public function disableColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        $node = node_load($this->getColumn_NID($dataset, $columnName));

        $node->status = NODE_NOT_PUBLISHED;

        node_save($node);

        parent::disableColumn($callcontext, $dataset, $columnName);
    }

    public function unregisterColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        if ($stage == DatasetStorageObserver::STAGE__BEFORE) {
            // Note: do not move this to 'after' stage. Other observer can delete the column form original dataset by that time
            node_delete($this->getColumn_NID($dataset, $columnName));
        }

        parent::unregisterColumn($callcontext, $dataset, $columnName, $stage);
    }

    public function dropColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $originalColumn = $dataset->getColumn($columnName);

            $node = node_load($this->getColumn_NID($dataset, $columnName));

            $node->field_column_persistence[$node->language][0]['value'] = $originalColumn->persistence;
            $node->status = $originalColumn->isUsed() ? NODE_PUBLISHED : NODE_NOT_PUBLISHED;

            node_save($node);
        }

        parent::dropColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);
    }
}
