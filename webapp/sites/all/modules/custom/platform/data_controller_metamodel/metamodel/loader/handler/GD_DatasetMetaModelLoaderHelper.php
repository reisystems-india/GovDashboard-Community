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


class GD_DatasetMetaModelLoaderHelper {

    public static function prepareDataset(MetaModel $metamodel, $datasetNode, array $datasetColumnNodes = NULL, DataSourceMetaData $datasource) {
        $dataset = new DatasetMetaData();
        $dataset->name = get_node_field_value($datasetNode, 'field_dataset_sysname', 0, 'value', TRUE);
        $dataset->publicName = $datasetNode->title;

        // preparing the dataset alternative names (aliases)
        $aliasIndex = 0;
        while (($alias = get_node_field_value($datasetNode, 'field_dataset_alias', $aliasIndex)) != NULL) {
            $dataset->aliases[] = $alias;

            $aliasIndex++;
        }

        $dataset->description = get_node_field_value($datasetNode, 'field_dataset_desc');

        $dataset->datasourceName = $datasource->name;

        // preparing source configuration properties
        $dataset->sourceType = get_node_field_value($datasetNode, 'field_dataset_source_type', 0, 'value', TRUE);
        $dataset->source = get_node_field_value($datasetNode, 'field_dataset_source', 0, 'value', TRUE);
        // preparing source properties as an associative array
        $dataset->configuration = get_node_field_object_value($datasetNode, 'field_dataset_source_properties');

        // dataset system properties
        $dataset->uuid = get_node_field_value($datasetNode, 'field_dataset_uuid', 0, 'value', TRUE);
        $dataset->nid = $datasetNode->nid;

        // preparing dataset columns
        if (isset($datasetColumnNodes)) {
            foreach ($datasetColumnNodes as $columnNode) {
                $column = $dataset->initiateColumn();
                $column->used = $columnNode->status == NODE_PUBLISHED;

                $column->name = get_node_field_value($columnNode, 'field_column_sysname', 0, 'value', TRUE);
                $column->publicName = $columnNode->title;
                $column->description = get_node_field_value($columnNode, 'field_column_desc');
                $column->key = get_node_field_boolean_value($columnNode, 'field_column_key');

                // column data type
                $column->type->applicationType = get_node_field_value($columnNode, 'field_column_datatype', 0, 'value', $column->used);
                if (isset($column->type->applicationType) && ($column->type->getReferencedDatasetName() == NULL)) {
                    $column->type->logicalApplicationType = NameSpaceHelper::addNameSpace(NAME_SPACE__STAR_SCHEMA, $column->type->applicationType);
                }
                $format = get_node_field_object_value($columnNode, 'field_column_format');
                if (isset($format->scale)) {
                    $column->type->scale = $format->scale;
                }

                $column->columnIndex = get_node_field_int_value($columnNode, 'field_column_index', 0, 'value', TRUE);
                $column->source = get_node_field_value($columnNode, 'field_column_source');
                $column->persistence = get_node_field_int_value($columnNode, 'field_column_persistence', 0, 'value', TRUE);

                // attribute system properties
                $column->nid = $columnNode->nid;

                $dataset->registerColumnInstance($column);
            }
        }

        // marking that the dataset object contains complete meta data
        $dataset->markAsComplete();

        $metamodel->registerDataset($dataset);

        return $dataset;
    }

    public static function prepareReference(MetaModel $metamodel, $referenceNode, array $referencePointNodes = NULL) {
        $referencePoints = $lookupReferencePoints = $interlookupReferences = NULL;

        // loading reference points
        $referencePointIndex = 0;
        while (TRUE) {
            $point_nid = get_node_field_int_value($referenceNode, 'field_reference_point', $referencePointIndex, 'nid');
            if (!isset($point_nid)) {
                break;
            }

            $referencePointNode = $referencePointNodes[$point_nid];

            $datasetName = get_node_field_value($referencePointNode, 'field_ref_point_dataset_sysname', 0, 'value', TRUE);
            $dataset = $metamodel->findDataset($datasetName);
            if (isset($dataset)) {
                $referencePoint = new DatasetReferencePoint();
                $lookupReferencePoint = new DatasetReferencePoint();

                $loader = MetaModelFactory::getInstance()->getLoader($dataset->loaderName);

                $cube = $metamodel->getCube($dataset->name);
                $referenceDatasetName = $cube->factsDatasetName;

                // preparing list of columns
                $referencePointColumnIndex = 0;
                while (TRUE) {
                    $columnName = get_node_field_value($referencePointNode, 'field_ref_point_column_sysname', $referencePointColumnIndex);
                    if (isset($columnName) || ($referencePointColumnIndex === 0)) {
                        $referencePointColumn = $referencePoint->initiateColumn();
                        $referencePointColumn->datasetName = $referenceDatasetName;
                        $referencePoint->registerColumnInstance($referencePointColumn);
                        if (isset($columnName)) {
                            $referencePointColumn->columnName = $columnName;
                        }

                        $interlookupLookupReferencePointColumn = $lookupReferencePoint->initiateColumn();
                        $interlookupLookupReferencePointColumn->datasetName = $referenceDatasetName;
                        $interlookupLookupReferencePointColumn->columnName = $referencePointColumn->columnName;
                        ReferenceMetaModelLoaderHelper::adjustReferencePointColumn($loader, $metamodel, $interlookupLookupReferencePointColumn);
                        if (!$interlookupLookupReferencePointColumn->isShared()) {
                            $interlookupReferences[$referencePointColumnIndex][$referencePointIndex] = $interlookupLookupReferencePointColumn;
                        }

                        $lookupReferencePointColumn = clone $interlookupLookupReferencePointColumn;
                        $lookupReferencePointColumn->columnName = NULL;
                        $lookupReferencePoint->registerColumnInstance($lookupReferencePointColumn);
                    }
                    if (!isset($columnName)) {
                        break;
                    }

                    $referencePointColumnIndex++;
                }

                $referencePoints[$referencePointIndex] = $referencePoint;
                $lookupReferencePoints[$referencePointIndex] = $lookupReferencePoint;
            }

            $referencePointIndex++;
        }

        $referencePointCount = count($referencePoints);
        if ($referencePointCount == 0) {
            return;
        }

        // checking if we need to add references between lookup reference point columns (second reference point in each reference)
        if (isset($interlookupReferences)) {
            // creating separate references for each reference point
            foreach ($referencePoints as $referencePointIndex => $referencePoint) {
                $lookupReferencePoint = $lookupReferencePoints[$referencePointIndex];

                $reference = new DatasetReference();
                $reference->name = get_node_field_value($referenceNode, 'field_reference_sysname', 0, 'value', TRUE) . '_rp' . $referencePointIndex;

                $reference->registerPointInstance($referencePoint);
                $reference->registerPointInstance($lookupReferencePoint);
                $metamodel->registerReference($reference);
            }

            // linking lookup reference point columns
            foreach ($interlookupReferences as $columnIndex => $interlookupReferencePointColumns) {
                if (count($interlookupReferencePointColumns) < $referencePointCount) {
                    throw new UnsupportedOperationException(t('All reference point columns with the same reference point column index have to be non-shared'));
                }

                $interLookupReferenceName = get_node_field_value($referenceNode, 'field_reference_sysname', 0, 'value', TRUE) . '_rpci' . $columnIndex;
                foreach ($interlookupReferencePointColumns as $interlookupReferencePointColumn) {
                    $metamodel->registerSimpleReferencePoint(
                        $interLookupReferenceName,
                        $interlookupReferencePointColumn->datasetName, $interlookupReferencePointColumn->columnName);
                }
            }
        }
        else {
            // checking that all lookup reference points are the same
            $masterLookupReferencePoint = NULL;
            foreach ($lookupReferencePoints as $lookupReferencePoint) {
                if (isset($masterLookupReferencePoint)) {
                    if (!$masterLookupReferencePoint->equals($lookupReferencePoint)) {
                        throw new UnsupportedOperationException(t('Unlinkable lookup reference points are not the same'));
                    }
                }
                else {
                    $masterLookupReferencePoint = $lookupReferencePoint;
                }
            }

            // combining all reference points into one reference
            $reference = new DatasetReference();
            $reference->name = get_node_field_value($referenceNode, 'field_reference_sysname', 0, 'value', TRUE);
            foreach ($referencePoints as $referencePointIndex => $referencePoint) {
                $lookupReferencePoint = $lookupReferencePoints[$referencePointIndex];

                $reference->registerPointInstance($referencePoint);
                $reference->registerPointInstance($lookupReferencePoint);
            }
            $metamodel->registerReference($reference);
        }
    }

    public static function findDatasetByUUID(array &$datasets = NULL, $uuid) {
        if (isset($datasets)) {
            foreach ($datasets as $dataset) {
                if (isset($dataset->uuid) && ($dataset->uuid == $uuid)) {
                    return $dataset;
                }
            }
        }

        return NULL;
    }

    public static function getDatasetByUUID(array &$datasets = NULL, $uuid) {
        $dataset = self::findDatasetByUUID($datasets, $uuid);
        if (!isset($dataset)) {
            throw new IllegalArgumentException(t(
                'Could not find dataset definition by the UUID: %UUID',
                array('%UUID' => $uuid)));
        }

        return $dataset;
    }

    public static function findDatasetByNodeId(array &$datasets = NULL, $dataset_nid) {
        if (isset($datasets)) {
            foreach ($datasets as $dataset) {
                if (isset($dataset->nid) && ($dataset->nid == $dataset_nid)) {
                    return $dataset;
                }
            }
        }

        return NULL;
    }

    public static function getDatasetByNodeId(array &$datasets = NULL, $dataset_nid) {
        $dataset = self::findDatasetByNodeId($datasets, $dataset_nid);
        if (!isset($dataset)) {
            throw new IllegalArgumentException(t(
                'Could not find dataset definition by the node identifier: %datasetNodeId',
                array('%datasetNodeId' => $dataset_nid)));
        }

        return $dataset;
    }

    public static function findColumnByNodeId(DatasetMetaData $dataset, $column_nid) {
        foreach ($dataset->columns as $column) {
            if (isset($column->nid) && ($column->nid == $column_nid)) {
                return $column;
            }
        }

        return NULL;
    }

    public static function getColumnByNodeId(DatasetMetaData $dataset, $column_nid) {
        $column = self::findColumnByNodeId($dataset, $column_nid);
        if (!isset($column)) {
            throw new IllegalStateException(t(
                'Could not find a column in %datasetName dataset by node identifier: %columnNodeId',
                array('%columnNodeId' => $column_nid, '%datasetName' => $dataset->publicName)));
        }

        return $column;
    }
}

