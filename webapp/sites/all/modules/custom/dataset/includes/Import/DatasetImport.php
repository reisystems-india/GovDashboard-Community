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


use \GD\Sync\Import;
use \GD\Sync\Import\Exception\UnsupportedImportOperationException;

class DatasetImport extends Import\AbstractEntityImport {

    public function import(Import\ImportStream $stream, Import\ImportContext $context) {
        $operation = $context->get('operation');
        switch ($operation) {
            case 'create' :
                $this->create($stream,$context);
                break;
            case 'update' :
                $this->update($stream,$context);
                break;
            default:
                throw new UnsupportedImportOperationException('Unsupported import operation "'.$operation.'" requested.');
                break;
        }
    }

    protected function create(Import\ImportStream $stream, Import\ImportContext $context) {
        $datasets = $stream->get('datasets');
        if (empty($datasets)) {
            return;
        }

        $environment_metamodel = data_controller_get_environment_metamodel();
        $datasource = $environment_metamodel->getDataSource($context->get('datasourceName'));

        // initialize dataset. must generate all new names before updating references.
        foreach ( $datasets as $dataset ) {
            $dataset->datasourceName = $datasource->name;

            // map for re-linking dependent objects
            $dataset->originalName = $dataset->name;

            // create new name
            $newDatasetName = GD_NamingConvention::generateDatasetName();
            $dataset->name = $newDatasetName;
            $dataset->source = $newDatasetName;
        }

        // prepare dataset columns for import
        $this->prepareColumns($datasets);

        // prepares the metadata object, has to be of type RecordMetaData
        foreach ( $datasets as $key => $dataset ) {
            $metadata = new DatasetMetaData();
            $metadata->initializeFrom($dataset);
            $datasets[$key] = $metadata;
        }

        // ensure datasets are created in order to satisfy dependencies
        usort($datasets, array(new ReferencedDatasetComparator(), 'compare'));

        foreach ( $datasets as $dataset ) {
            MetaModelFactory::getInstance()->startGlobalModification();
            try {
                $transaction = db_transaction();
                try {
                    gd_data_controller_ddl_create_dataset($dataset);
                } catch (Exception $e) {
                    $transaction->rollback();
                    throw $e;
                }
            } catch (Exception $e) {
                MetaModelFactory::getInstance()->finishGlobalModification(false);
                throw $e;
            }
            MetaModelFactory::getInstance()->finishGlobalModification(true);
        }

        $stream->set('datasets',$datasets);
    }

    protected function update(Import\ImportStream $stream, Import\ImportContext $context) {
        $datasets = $stream->get('datasets');
        if (empty($datasets)) {
            return;
        }

        gd_datasource_set_active($context->get('datasourceName'));
        $metamodel = data_controller_get_metamodel();

        $environment_metamodel = data_controller_get_environment_metamodel();
        $datasource = $environment_metamodel->getDataSource($context->get('datasourceName'));

        foreach ( $datasets as $dataset ) {
            $existingDataset = GD_DatasetMetaModelLoaderHelper::findDatasetByUUID($metamodel->datasets, $dataset->uuid);
            if ( !$existingDataset ) {

                // map for re-linking dependent objects
                $dataset->originalName = $dataset->name;

                // create new name
                $newDatasetName = GD_NamingConvention::generateDatasetName();
                $dataset->name = $newDatasetName;
                $dataset->source = $newDatasetName;
                $dataset->datasourceName = $datasource->name;
            } else {
                // map for re-linking dependent objects
                $dataset->originalName = $dataset->name;

                $dataset->name = $existingDataset->name;
                $dataset->source = $existingDataset->source;
                $dataset->datasourceName = $existingDataset->datasourceName;
            }
        }

        // prepare dataset columns for import
        $this->prepareColumns($datasets);

        // prepares the metadata object, has to be of type RecordMetaData
        foreach ( $datasets as $key => $dataset ) {
            $metadata = new DatasetMetaData();
            $metadata->initializeFrom($dataset);
            $datasets[$key] = $metadata;
        }

        // ensure datasets are created in order to satisfy dependencies
        usort($datasets, array(new ReferencedDatasetComparator(), 'compare'));

        foreach ( $datasets as $dataset ) {
            $existingDataset = GD_DatasetMetaModelLoaderHelper::findDatasetByUUID($metamodel->datasets, $dataset->uuid);
            if ($existingDataset) {
                gd_data_controller_ddl_modify_dataset($dataset);
            } else {
                MetaModelFactory::getInstance()->startGlobalModification();
                try {
                    $transaction = db_transaction();
                    try {
                        gd_data_controller_ddl_create_dataset($dataset);
                    } catch (Exception $e) {
                        $transaction->rollback();
                        throw $e;
                    }
                } catch (Exception $e) {
                    MetaModelFactory::getInstance()->finishGlobalModification(false);
                    throw $e;
                }
                MetaModelFactory::getInstance()->finishGlobalModification(true);
            }
        }

        $stream->set('datasets',$datasets);
    }

    protected function prepareColumns(&$datasets) {
        // prepare dataset columns for import
        $datasetCount = count($datasets);
        for ( $i = 0; $i < $datasetCount; $i++ ) {
            foreach ( $datasets[$i]->columns as &$column ) {
                // unset private properties. TODO export only public properties
                unset($column->type->referencedApplicationType);
                unset($column->type->referencedDatasetName);
                unset($column->type->referencedColumnName);

                // reset storage flags
                if ($column->persistence == ColumnMetaData::PERSISTENCE__STORAGE_CREATED) {
                    $column->persistence = ColumnMetaData::PERSISTENCE__NO_STORAGE;
                }

                // re-link column references
                list($datasetName, $columnName) = ReferencePathHelper::splitReference($column->type->applicationType);
                if ( !empty($datasetName) ) {
                    $reference_datatype = null;
                    for ( $j = 0; $j < $datasetCount; $j++ ) {
                        if ( empty($reference_datatype) && $datasetName == $datasets[$j]->originalName ) {
                            $reference_datatype = ReferencePathHelper::assembleReference($datasets[$j]->name,$columnName);
                        }
                    }
                    if ( empty($reference_datatype) ) {
                        throw new Exception('Dataset column reference lookup failed. Dataset: '.$datasets[$j]->name.' Column: '.$column->name.' Type: '.$column->type->applicationType);
                    } else {
                        $column->type->applicationType = $reference_datatype;
                    }
                }
            }
        }
    }

}