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


abstract class AbstractSystemTableMetaModelGenerator extends AbstractSystemTableMetaModelLoader {

    const CONSTRAINT_TYPE__PRIMARY_KEY = 'PK';
    const CONSTRAINT_TYPE__REFERENCE = 'FK';

    const FLAG_IGNORE = 'sf__ignore';

    protected $nameGenerator = NULL;

    public function __construct() {
        parent::__construct();
        $this->nameGenerator = new ColumnNameGenerator();
    }

    protected function isTableSupported(SystemTableMetaModelLoaderCallContext $callcontext, $tableName) {
        $default = TRUE;

        return isset($callcontext->environment->options->table->pattern)
            ? $this->applyPattern($callcontext, $callcontext->environment->options->table->pattern, $tableName, $default)
            : $default;
    }

    protected function generateDatasets(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $columnsProperties = $this->loadColumnsProperties($callcontext, $datasource);
        if (isset($columnsProperties)) {
            foreach ($columnsProperties as $columnProperties) {
                $tableOwner = $this->adjustOwnerName($columnProperties[self::CN_TABLE_OWNER]);

                $originalTableName = $columnProperties[self::CN_TABLE_NAME];
                $tableName = $this->adjustTableName($originalTableName);
                if (!$this->isTableSupported($callcontext, $tableName)) {
                    continue;
                }

                $tableAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $tableName));

                // checking if we can process the column name
                $originalColumnName = $columnProperties[self::CN_COLUMN_NAME];
                $columnName = $this->adjustColumnName($originalColumnName);
                $generatedColumnName = $this->nameGenerator->generate($columnName);
                // this column cannot be supported by the system
                if ($columnName != $generatedColumnName) {
                    LogHelper::log_warn(t(
                        "Unsupported column name from '@tableName' table: @columnName",
                        array('@tableName' => $originalTableName, '@columnName' => $originalColumnName)));
                    continue;
                }

                // preparing new dataset
                if (!isset($callcontext->datasets[$tableAccessKey])) {
                    $source =  TableReferenceHelper::assembleTableReference($tableOwner, $tableName);

                    $dataset = new DatasetMetaData();
                    $dataset->name = NameSpaceHelper::addNameSpace($datasource->name, $this->nameGenerator->generate($tableName));
                    $dataset->publicName = $this->generateTablePublicName($originalTableName);
                    $dataset->datasourceName = $datasource->name;
                    $dataset->source = $source;
                    $dataset->markAsPrivate();

                    $callcontext->datasets[$tableAccessKey] = $dataset;
                }
                $dataset = $callcontext->datasets[$tableAccessKey];

                // adding new column to the dataset
                $column = $dataset->initiateColumn();
                $column->name = $columnName;
                $column->publicName = $this->generateColumnPublicName($originalColumnName);
                $column->persistence = ColumnMetaData::PERSISTENCE__STORAGE_CREATED;
                $column->columnIndex = $columnProperties[self::CN_COLUMN_INDEX];
                $column->type->databaseType = $columnProperties[self::CN_COLUMN_TYPE];
                if (isset($columnProperties[self::CN_COLUMN_TYPE_LENGTH])) {
                    $column->type->length = $columnProperties[self::CN_COLUMN_TYPE_LENGTH];
                }
                if (isset($columnProperties[self::CN_COLUMN_TYPE_PRECISION])) {
                    $column->type->precision = $columnProperties[self::CN_COLUMN_TYPE_PRECISION];
                }
                if (isset($columnProperties[self::CN_COLUMN_TYPE_SCALE])) {
                    $column->type->scale = $columnProperties[self::CN_COLUMN_TYPE_SCALE];
                }
                $this->generateColumnApplicationType($callcontext, $datasource, $column);

                // adjusting column properties
                if (!isset($column->type->applicationType)) {
                    $column->visible = FALSE;
                    LogHelper::log_warn(t(
                        "Data type is not supported for '@columnName' column from '@tableName' table: @databaseDataType",
                        array(
                            '@tableName' => $originalTableName,
                            '@columnName' => $originalColumnName,
                            '@databaseDataType' => $column->type->databaseType)));
                }
                $this->adjustColumnVisibility($callcontext, $column);

                $dataset->registerColumnInstance($column);
            }
        }

        LogHelper::log_info(t(
            'Processed system meta data about @tableCount table(s) and @columnCount column(s)',
            array('@tableCount' => count($callcontext->datasets), '@columnCount' => count($columnsProperties))));
    }

    protected function isDatasetEligible(SystemTableMetaModelLoaderCallContext $callcontext, array $filters = NULL, DatasetMetaData $dataset) {
        if ($this->isMetaDataAcceptable($dataset, $filters) === FALSE) {
            return FALSE;
        }

        // it is possible that the dataset does not have any visible columns
        $isVisibleColumnFound = FALSE;
        foreach ($dataset->getColumns() as $column) {
            if ($column->isVisible()) {
                $isVisibleColumnFound = TRUE;
                break;
            }
        }
        if (!$isVisibleColumnFound) {
            return FALSE;
        }

        return TRUE;
    }

    protected function eliminateIneligibleDatasets(SystemTableMetaModelLoaderCallContext $callcontext, array $filters = NULL) {
        foreach ($callcontext->datasets as $index => $dataset) {
            if (!$this->isDatasetEligible($callcontext, $filters, $dataset)) {
                unset($callcontext->datasets[$index]);
            }
        }
    }

    protected function loadPrimaryKeyConstraintsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        return NULL;
    }

    protected function loadConstraintColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, array $constraintTypes) {
        return NULL;
    }

    protected function processPrimaryKeyConstraints(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $primaryKeyConstraintCount = 0;

        // Note: loading columns from Primary Key constraints only
        $constraintColumnsProperties = $this->loadConstraintColumnsProperties($callcontext, $datasource, array(self::CONSTRAINT_TYPE__PRIMARY_KEY));
        foreach ($constraintColumnsProperties as $constraintColumnProperties) {
            $tableOwner = $this->adjustOwnerName($constraintColumnProperties[self::CN_TABLE_OWNER]);

            $tableName = $this->adjustTableName($constraintColumnProperties[self::CN_TABLE_NAME]);

            $tableAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $tableName));
            if (!isset($callcontext->datasets[$tableAccessKey])) {
                continue;
            }

            $dataset = $callcontext->datasets[$tableAccessKey];
            $columnName = $this->adjustColumnName($constraintColumnProperties[self::CN_COLUMN_NAME]);
            $column = $dataset->findColumn($columnName);
            if (!isset($column)) {
                continue;
            }

            $column->key = TRUE;
            $primaryKeyConstraintCount++;
        }

        LogHelper::log_info(t(
            'Processed system meta data about @primaryKeyConstraintCount primary key constraint(s)',
            array('@primaryKeyConstraintCount' => $primaryKeyConstraintCount)));
    }

    protected function loadReferenceConstraintsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        return NULL;
    }

    protected function processReferences(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $foreignKeyConstraintCount = 0;

        $constraintsProperties = $this->loadReferenceConstraintsProperties($callcontext, $datasource);
        if (isset($constraintsProperties)) {
            $constraintsColumnsPropertyFormatter = new QueryKeyResultFormatter(array(self::CN_TABLE_OWNER, self::CN_OBJECT_NAME), FALSE);
            $constraintsColumnsProperties = $constraintsColumnsPropertyFormatter->formatRecords(
                $this->loadConstraintColumnsProperties(
                    $callcontext, $datasource,
                    array(self::CONSTRAINT_TYPE__PRIMARY_KEY, self::CONSTRAINT_TYPE__REFERENCE)));

            foreach ($constraintsProperties as $constraintProperties) {
                $tableOwner = $this->adjustOwnerName($constraintProperties[self::CN_TABLE_OWNER]);

                // ---------- preparing referring constraint properties
                $constraintName = $constraintProperties[self::CN_OBJECT_NAME];
                $constraintAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $constraintName));
                // for some reason we do not have access to column configuration for referring constraint
                if (!isset($constraintsColumnsProperties[$constraintAccessKey])) {
                    continue;
                }
                $constraintColumnsProperties = $constraintsColumnsProperties[$constraintAccessKey];
                // we do not support composite references yet
                if (count($constraintColumnsProperties) > 1) {
                    continue;
                }

                // ----------- preparing referenced constraint properties
                $refConstraintName = $constraintProperties[self::CN_REFERENCED_OBJECT_NAME];
                $refConstraintAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $refConstraintName));
                // for some reason we do not have access to column configuration for referenced constraint
                if (!isset($constraintsColumnsProperties[$refConstraintAccessKey])) {
                    continue;
                }

                $constraintColumnProperties = $constraintColumnsProperties[0];
                $tableName = $this->adjustTableName($constraintColumnProperties[self::CN_TABLE_NAME]);
                $tableAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $tableName));
                if (!isset($callcontext->datasets[$tableAccessKey])) {
                    continue;
                }

                $refConstraintColumnProperties = $constraintsColumnsProperties[$refConstraintAccessKey][0];
                $refTableName = $this->adjustTableName($refConstraintColumnProperties[self::CN_TABLE_NAME]);
                $refTableAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $refTableName));
                if (!isset($callcontext->datasets[$refTableAccessKey])) {
                    continue;
                }

                $columnName = $this->adjustColumnName($constraintColumnProperties[self::CN_COLUMN_NAME]);
                $refColumnName = $this->adjustColumnName($refConstraintColumnProperties[self::CN_COLUMN_NAME]);

                if ($tableName == $refTableName) {
                    LogHelper::log_warn(t(
                        "Self-reference is not supported yet: @tableName(@columnName)",
                        array('@tableName' => $tableName, '@columnName' => $columnName)));
                    continue;
                }

                $dataset = $callcontext->datasets[$tableAccessKey];
                $column = $dataset->findColumn($columnName);
                if (!isset($column)) {
                    continue;
                }

                $refDataset = $callcontext->datasets[$refTableAccessKey];
                $refColumn = $refDataset->findColumn($refColumnName);
                if (!isset($refColumn)) {
                    continue;
                }

                $logicalApplicationType = ReferencePathHelper::assembleReference($refDataset->name, $refColumnName);
                if (isset($column->type->logicalApplicationType)) {
                    LogHelper::log_warn(t(
                        "Multi-reference is not supported yet for '@columnName' column from '@tableName' table: [@referenceExisting, @referenceNew]",
                        array(
                            '@tableName' => $tableName,
                            '@columnName' => $columnName,
                            '@referenceExisting' => $column->type->logicalApplicationType,
                            '@referenceNew' => $logicalApplicationType)));
                    continue;
                }

                $column->type->logicalApplicationType = $logicalApplicationType;
                $foreignKeyConstraintCount++;
            }
        }

        LogHelper::log_info(t(
            'Processed system meta data about @constraintCount foreign key constraint(s)',
            array('@constraintCount' => $foreignKeyConstraintCount)));
    }

    protected function fixColumnApplicationType(SystemTableMetaModelLoaderCallContext $callcontext) {
        foreach ($callcontext->datasets as $dataset) {
            foreach ($dataset->columns as $column) {
                // number -> integer for PK and reference
                if ($column->type->applicationType == NumberDataTypeHandler::DATA_TYPE) {
                    if ($column->isKey() || isset($column->type->logicalApplicationType)) {
                        $column->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
                    }
                }
            }
        }
    }

    protected function findEligibleDatasets4TableComment(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, $tableOwner, $tableName) {
        $datasets = NULL;

        $tableAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $tableName));
        if (isset($callcontext->datasets[$tableAccessKey])) {
            $datasets[] = $callcontext->datasets[$tableAccessKey];
        }

        return $datasets;
    }

    protected function findEligibleColumns4ColumnComment(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, $tableOwner, $tableName, $columnName) {
        $columns = NULL;

        $tableAccessKey = ArrayHelper::prepareCompositeKey(array($tableOwner, $tableName));
        if (isset($callcontext->datasets[$tableAccessKey])) {
            $column = $callcontext->datasets[$tableAccessKey]->findColumn($columnName);
            if (isset($column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    protected function generateMissingDatasetPublicNames(SystemTableMetaModelLoaderCallContext $callcontext) {
        foreach ($callcontext->datasets as $dataset) {
            if (!isset($dataset->publicName)) {
                $dataset->publicName = $dataset->source;
            }
        }
    }

    protected function generateMissingColumnPublicNames(SystemTableMetaModelLoaderCallContext $callcontext) {}

    protected function registerDatasets(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel) {
        $datasetCount = 0;

        $loaderName = $this->getName();

        // registering eligible datasets
        foreach ($callcontext->datasets as $tableAccessKey => $dataset) {
            $dataset->loaderName = $loaderName;
            $dataset->markAsComplete();

            $existingDataset = $metamodel->findDataset($dataset->name, TRUE);
            if (isset($existingDataset)) {
                throw new IllegalStateException(t(
                    'Found several references to the same table: %existingTableReference, %tableReference',
                    array(
                        '%existingTableReference' => $existingDataset->source,
                        '%tableReference' => $dataset->source)));
            }

            $metamodel->registerDataset($dataset);
            $datasetCount++;
        }

        LogHelper::log_info(t('Registered @datasetCount datasets', array('@datasetCount' => $datasetCount)));
    }

    protected function generateLogicalDatasetName($datasetName) {
        return $datasetName . '_logical';
    }

    protected function generateLogicalDatasets(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel) {
        $datasetCount = 0;

        foreach ($callcontext->datasets as $tableAccessKey => $dataset) {
            $logicalDataset = new DatasetMetaData();
            $logicalDataset->name = $this->generateLogicalDatasetName($dataset->name);
            $logicalDataset->publicName = $dataset->publicName;
            $logicalDataset->description = $dataset->description;
            $logicalDataset->datasourceName = $dataset->datasourceName;
            $logicalDataset->sourceType = StarSchemaDatasetSourceTypeHandler::SOURCE_TYPE;

            $callcontext->logicalDatasetNameMappings[$logicalDataset->name] = $tableAccessKey;

            foreach ($dataset->columns as $column) {
                $logicalColumn = $logicalDataset->initiateColumn();
                $logicalColumn->name = $column->name;
                $logicalColumn->publicName = $column->publicName;
                $logicalColumn->description = $column->description;
                $logicalColumn->persistence = $column->persistence;
                $logicalColumn->columnIndex = $column->columnIndex;
                $logicalColumn->key = $column->key;
                $logicalColumn->visible = $column->visible;
                $logicalColumn->used = $column->used;

                if (isset($column->type->logicalApplicationType)) {
                    list($refDatasetName, $refColumnName) = ReferencePathHelper::splitReference($column->type->logicalApplicationType);

                    $refDataset = $metamodel->getDataset($refDatasetName);
                    $refColumn = $refDataset->getColumn($refColumnName);

                    $logicalColumn->type->databaseType = $refColumn->type->databaseType;
                    $logicalColumn->type->length = $refColumn->type->length;
                    $logicalColumn->type->precision = $refColumn->type->precision;
                    $logicalColumn->type->scale = $refColumn->type->scale;

                    $logicalColumn->type->applicationType = ReferencePathHelper::assembleReference(
                        $this->generateLogicalDatasetName($refDataset->name), $refColumnName);
                }
                else {
                    $logicalColumn->initializeTypeFrom($column->type);
                }

                $logicalDataset->registerColumnInstance($logicalColumn);
            }

            $callcontext->logicalDatasets[$tableAccessKey] = $logicalDataset;
            $datasetCount++;
        }

        LogHelper::log_info(t('Generated @datasetCount logical datasets', array('@datasetCount' => $datasetCount)));
    }

    protected function registerLogicalDatasets(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel) {
        $datasetCount = 0;

        $loaderName = $this->getName();

        // registering logical datasets
        foreach ($callcontext->logicalDatasets as $logicalDataset) {
            $logicalDataset->loaderName = $loaderName;
            $logicalDataset->markAsComplete();

            $metamodel->registerDataset($logicalDataset);
            $datasetCount++;
        }

        LogHelper::log_info(t('Registered @datasetCount logical datasets', array('@datasetCount' => $datasetCount)));
    }

    protected function generateCubes(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel) {
        $cubeCount = 0;

        $loaderName = $this->getName();

        foreach ($callcontext->logicalDatasets as $tableAccessKey => $logicalDataset) {
            $dataset = $callcontext->datasets[$tableAccessKey];

            $cubeName = $logicalDataset->name;
            $cube = new CubeMetaData();
            $cube->name = $cubeName;
            $cube->publicName = $logicalDataset->publicName;
            $cube->description = $logicalDataset->description;
            $cube->factsDatasetName = $dataset->name;
            $cube->factsDataset = $dataset;

            foreach ($logicalDataset->columns as $logicalColumn) {
                StarSchemaCubeMetaData::initializeDimensionFromColumn($metamodel, $cube, $logicalDataset, $logicalColumn->name);

                // FIXME fixing dimensions and pointing to physical datasets instead. Remove the following block once we remove support for logical datasets in cubes
                // FIXME remove support for $callcontext->logicalDatasetNameMappings property as well
                $dimension = $cube->findDimension($logicalColumn->name);
                if (isset($dimension) && isset($dimension->datasetName)) {
                    $dimensionLogicalDatasetName = $dimension->datasetName;
                    $dimensionTableAccessKey = $callcontext->logicalDatasetNameMappings[$dimensionLogicalDatasetName];
                    $dimensionDataset = $callcontext->datasets[$dimensionTableAccessKey];
                    $dimension->datasetName = $dimensionDataset->name;
                    $dimension->dataset = $dimensionDataset;
                }
            }

            foreach ($logicalDataset->columns as $logicalColumn) {
                StarSchemaCubeMetaData::initializeMeasuresFromColumn($cube, $logicalDataset, $logicalColumn->name);
            }

            StarSchemaCubeMetaData::registerCubeMeasures($cube);

            $cube->loaderName = $loaderName;
            $cube->markAsComplete();

            $metamodel->registerCube($cube);
            $cubeCount++;
        }

        LogHelper::log_info(t('Generated and registered @cubeCount cubes', array('@cubeCount' => $cubeCount)));
    }

    protected function initiateCallContext() {
        return new SystemTableMetaModelGeneratorCallContext();
    }

    protected function loadFromDataSource(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource, array $filters = NULL) {
        LogHelper::log_notice(t(
            "Loading Meta Model from '@datasourceName' data source (type: @datasourceType) system tables ...",
            array('@datasourceName' => $datasource->publicName, '@datasourceType' => $datasource->type)));

        $this->generateDatasets($callcontext, $datasource);
        $this->eliminateIneligibleDatasets($callcontext, $filters);
        $this->processPrimaryKeyConstraints($callcontext, $datasource);
        $this->processReferences($callcontext, $datasource);
        $this->fixColumnApplicationType($callcontext);

        $this->processTableComments($callcontext, $metamodel, $datasource);
        $this->processColumnComments($callcontext, $metamodel, $datasource);
        $this->generateMissingDatasetPublicNames($callcontext);
        $this->generateMissingColumnPublicNames($callcontext);

        $this->registerDatasets($callcontext, $metamodel);

        $this->generateLogicalDatasets($callcontext, $metamodel);
        $this->registerLogicalDatasets($callcontext, $metamodel);
        $this->generateCubes($callcontext, $metamodel);
    }
}

class SystemTableMetaModelGeneratorCallContext extends SystemTableMetaModelLoaderCallContext {

    /**
     * @var DatasetMetaData[]
     */
    public $datasets = array(); // table access key => dataset
    public $logicalDatasets = array(); // table access key => dataset
    public $logicalDatasetNameMappings = array(); // logical dataset name => table access key
}
