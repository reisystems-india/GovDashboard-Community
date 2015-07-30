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


// FIXME move the file to controller/observer
class ModifyStarSchemaStorageObserver extends CreateStarSchemaStorageObserver {

    protected $originalLogicalDataset = NULL;
    protected $originalCube = NULL;

    public function __construct(DatasetMetaData $newLogicalDataset) {
        parent::__construct($newLogicalDataset);

        $metamodel = data_controller_get_metamodel();

        $this->originalLogicalDataset = $metamodel->getDataset($newLogicalDataset->name);

        $cubeName = $newLogicalDataset->name;
        $this->originalCube = $metamodel->getCube($cubeName);
    }

    public function validate(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::validate($callcontext, $dataset);

        // checking if all dimensions approve changes to dataset
        foreach (DimensionFactory::getInstance()->getHandlers() as $handler) {
            $handler->permitDatasetStorageChanges($callcontext, $this->originalLogicalDataset, $this->newLogicalDataset);
        }
    }

    public function updateDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::updateDataset($callcontext, $dataset);

        $this->originalLogicalDataset->publicName = $dataset->publicName;
        $this->originalLogicalDataset->description = $dataset->description;
        $this->originalLogicalDataset->initializeSourceFrom($this->newLogicalDataset->source, TRUE);
        $this->originalLogicalDataset->initializeAliasesFrom($this->newLogicalDataset->aliases, TRUE);

        $this->originalCube->publicName = $this->originalLogicalDataset->publicName;
        $this->originalCube->description = $this->originalLogicalDataset->description;
    }

    public function unregisterDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $stage) {
        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $metamodel = data_controller_get_metamodel();

            StarSchemaCubeMetaData::deinitializeByDataset($metamodel, $this->originalLogicalDataset);
            $metamodel->unregisterDataset($this->originalLogicalDataset->name);
        }

        parent::unregisterDataset($callcontext, $dataset, $stage);
    }

    public function enableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::enableDataset($callcontext, $dataset);

        $metamodel = data_controller_get_metamodel();

        $this->originalLogicalDataset->used = TRUE;

        StarSchemaCubeMetaData::registerFromDataset($metamodel, $this->originalLogicalDataset);
        StarSchemaCubeMetaData::initializeFromDataset($metamodel, $this->originalLogicalDataset);
    }

    public function disableDataset(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $metamodel = data_controller_get_metamodel();

        StarSchemaCubeMetaData::deinitializeByDataset($metamodel, $this->originalLogicalDataset);
        $metamodel->unregisterDataset($this->originalLogicalDataset->name);

        $this->originalLogicalDataset->used = FALSE;

        parent::disableDataset($callcontext, $dataset);
    }

    public function registerColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        parent::registerColumn($callcontext, $dataset, $columnName, $stage);

        if ($stage == DatasetStorageObserver::STAGE__BEFORE) {
            $metamodel = data_controller_get_metamodel();

            $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
            $newColumn = $dataset->getColumn($columnName);

            $originalLogicalColumn = $this->originalLogicalDataset->initiateColumn();
            $originalLogicalColumn->name = $newColumn->name;
            $originalLogicalColumn->publicName = $newColumn->publicName;
            $originalLogicalColumn->description = $newColumn->description;

            $originalLogicalColumn->columnIndex = $newColumn->columnIndex;

            // Note: storing logical data type for the column
            $originalLogicalColumn->initializeTypeFrom($newLogicalColumn->type);
            $originalLogicalColumn->persistence = $newColumn->persistence;

            $originalLogicalColumn->key = $newColumn->key;
            $originalLogicalColumn->source = $newColumn->source;

            $originalLogicalColumn->used = $newColumn->used;

            $this->originalLogicalDataset->registerColumnInstance($originalLogicalColumn);

            StarSchemaCubeMetaData::initializeFromColumn($metamodel, $this->originalCube, $this->originalLogicalDataset, $columnName);
        }
    }

    public function updateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::updateColumn($callcontext, $dataset, $columnName);

        $metamodel = data_controller_get_metamodel();

        $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
        $newColumn = $dataset->getColumn($columnName);

        StarSchemaCubeMetaData::deinitializeByColumn($this->originalCube, $this->originalLogicalDataset, $columnName);

        $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);

        $originalLogicalColumn->publicName = $newColumn->publicName;
        $originalLogicalColumn->description = $newColumn->description;

        // Note: storing logical data type for the column
        $originalLogicalColumn->initializeTypeFrom($newLogicalColumn->type, TRUE);
        $originalLogicalColumn->persistence = $newColumn->persistence;

        $originalLogicalColumn->key = $newColumn->key;
        $originalLogicalColumn->source = $newColumn->source;

        $originalLogicalColumn->used = $newColumn->used;

        StarSchemaCubeMetaData::initializeFromColumn($metamodel, $this->originalCube, $this->originalLogicalDataset, $columnName);
    }

    public function relocateColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::relocateColumn($callcontext, $dataset, $columnName);

        $newColumn = $dataset->getColumn($columnName);

        $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);
        $originalLogicalColumn->columnIndex = $newColumn->columnIndex;
    }

    public function updateColumnParticipationInDatasetKey(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        parent::updateColumnParticipationInDatasetKey($callcontext, $dataset, $columnName);

        $metamodel = data_controller_get_metamodel();

        $newColumn = $dataset->getColumn($columnName);

        StarSchemaCubeMetaData::deinitializeByColumn($this->originalCube, $this->originalLogicalDataset, $columnName);

        $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);
        $originalLogicalColumn->key = $newColumn->key;

        StarSchemaCubeMetaData::initializeFromColumn($metamodel, $this->originalCube, $this->originalLogicalDataset, $columnName);
    }

    public function disableColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName) {
        StarSchemaCubeMetaData::disableByColumn($this->originalCube, $this->originalLogicalDataset, $columnName);

        $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);
        $originalLogicalColumn->used = FALSE;

        parent::disableColumn($callcontext, $dataset, $columnName);
    }

    public function unregisterColumn(DataControllerCallContext $callcontext, DatasetMetaData $dataset, $columnName, $stage) {
        switch ($stage) {
            case DatasetStorageObserver::STAGE__BEFORE:
                StarSchemaCubeMetaData::deinitializeByColumn($this->originalCube, $this->originalLogicalDataset, $columnName);
                break;
            case DatasetStorageObserver::STAGE__AFTER:
                $this->originalLogicalDataset->unregisterColumn($columnName);
                break;
        }

        parent::unregisterColumn($callcontext, $dataset, $columnName, $stage);
    }

    public function createColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        switch ($stage) {
            case DatasetStorageObserver::STAGE__BEFORE:
                StarSchemaCubeMetaData::deinitializeByColumn($this->originalCube, $this->originalLogicalDataset, $columnName);
                break;
            case DatasetStorageObserver::STAGE__AFTER:
                $metamodel = data_controller_get_metamodel();

                $newLogicalColumn = $this->newLogicalDataset->getColumn($columnName);
                $newColumn = $dataset->getColumn($columnName);

                $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);

                // Note: storing logical data type for the column
                $originalLogicalColumn->initializeTypeFrom($newLogicalColumn->type, TRUE);
                $originalLogicalColumn->persistence = $newColumn->persistence;

                StarSchemaCubeMetaData::initializeFromColumn($metamodel, $this->originalCube, $this->originalLogicalDataset, $columnName);
                break;
        }

        parent::createColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);
    }

    public function dropColumnStorage(DataControllerCallContext $callcontext, DataSourceStructureHandler $datasourceStructureHandler, DatasetMetaData $dataset, $columnName, $stage) {
        if ($stage == DatasetStorageObserver::STAGE__AFTER) {
            $originalLogicalColumn = $this->originalLogicalDataset->getColumn($columnName);
            $originalColumn = $dataset->getColumn($columnName);

            $originalLogicalColumn->persistence = $originalColumn->persistence;
            $originalLogicalColumn->used = $originalColumn->used;

            $handler = DimensionFactory::getInstance()->getHandler($originalLogicalColumn->type->getLogicalApplicationType());
            $handler->dropDimensionStorage($callcontext, $datasourceStructureHandler, $this->originalLogicalDataset, $columnName);
        }

        parent::dropColumnStorage($callcontext, $datasourceStructureHandler, $dataset, $columnName, $stage);
    }
}
