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


class GD_DatasetMetaModelLoader extends AbstractMetaModelLoader implements ReferenceMetaModelLoader {

    protected function groupNodesByDataset(&$nodes, $fieldName) {
        $groupedNodes = NULL;

        if (isset($nodes)) {
            foreach ($nodes as $node) {
                $dataset_nid = get_node_field_node_ref($node, $fieldName);

                $groupedNodes[$dataset_nid][] = $node;
            }
        }

        return $groupedNodes;
    }

    public function load(AbstractMetaModel $metamodel, array $filters = NULL) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        LogHelper::log_notice(t('Loading Meta Model from GovDashboard Content Types ...'));

        $loaderName = $this->getName();

        $datasetQuery = new EntityFieldQuery();
        $datasetQuery->entityCondition('entity_type', 'node');
        $datasetQuery->propertyCondition('type', NODE_TYPE_DATASET);
        $datasetQuery->propertyCondition('status', NODE_PUBLISHED);
        $datasetQuery->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');
        // applying filters. Note that we have custom mapping for filter properties
        $datasetFilters = isset($filters['DatasetMetaData']) ? $filters['DatasetMetaData'] : NULL;
        if (isset($datasetFilters)) {
            foreach ($datasetFilters as $propertyName => $filterValues) {
                switch ($propertyName) {
                    case 'datasourceName':
                        $selectedDataSourceNames = FALSE;
                        // checking if any of the data sources are actually data marts
                        foreach ($filterValues as $datasourceName) {
                            $datasource = $environment_metamodel->findDataSource($datasourceName);
                            if (isset($datasource->nid)) {
                                $selectedDataSourceNames[] = $datasourceName;
                            }
                        }
                        if (isset($selectedDataSourceNames)) {
                            $datasetQuery->fieldCondition('field_dataset_datasource', 'value', $selectedDataSourceNames);
                        }
                        else {
                            // there is no selected datamarts for this request
                            return;
                        }
                        break;
                    default:
                        throw new UnsupportedOperationException(t(
                            'Unsupported mapping for the property for filtering during dataset loading: %propertyName',
                            array('%propertyName' => $propertyName)));
                }
            }
        }
        $datasetEntities = $datasetQuery->execute();
        $dataset_nids = isset($datasetEntities['node']) ? array_keys($datasetEntities['node']) : NULL;
        if (!isset($dataset_nids)) {
            return;
        }

        $datasetNodes = node_load_multiple($dataset_nids);

        // loading columns for selected datasets
        $columnNodes = gd_column_get_columns_4_dataset($dataset_nids, LOAD_ENTITY, INCLUDE_UNPUBLISHED);
        // grouping nodes in context of dataset
        $datasetsColumnNodes = $this->groupNodesByDataset($columnNodes, 'field_column_dataset');

        // preparing dataset & cubes
        $processedDatasetCount = 0;
        foreach ($datasetNodes as $datasetNode) {
            $dataset_nid = $datasetNode->nid;

            $datasourceName = get_node_field_value($datasetNode, 'field_dataset_datasource');
            $datasource = isset($datasourceName) ? $environment_metamodel->findDataSource($datasourceName) : NULL;
            if (!isset($datasource)) {
                // the data mart could be unpublished or ...
                continue;
            }

            $datasetColumnNodes = isset($datasetsColumnNodes[$dataset_nid]) ? $datasetsColumnNodes[$dataset_nid] : NULL;

            // preparing dataset
            $dataset = GD_DatasetMetaModelLoaderHelper::prepareDataset($metamodel, $datasetNode, $datasetColumnNodes, $datasource);
            // assigning a loader which created the dataset
            $dataset->loaderName = $loaderName;

            $processedDatasetCount++;
        }

        LogHelper::log_info(t('Processed @datasetCount dataset node(s)', array('@datasetCount' => $processedDatasetCount)));
    }

    public function adjustReferencePointColumn(AbstractMetaModel $metamodel, DatasetReferencePointColumn $referencePointColumn) {
        StarSchemaCubeMetaData::adjustReferencePointColumn($metamodel, $referencePointColumn);
    }

    public function finalize(AbstractMetaModel $metamodel) {
        parent::finalize($metamodel);

        $loaderName = $this->getName();

        // initializing all cubes. We separated registration from initialization to support inter-cube references
        $loadedDatasets = NULL;
        foreach ($metamodel->datasets as $dataset) {
            // working only with datasets which are created by this loader
            if ($dataset->loaderName != $loaderName) {
                continue;
            }

            StarSchemaCubeMetaData::registerFromDataset($metamodel, $dataset);

            $loadedDatasets[$dataset->nid] = $dataset;
        }

        if (isset($loadedDatasets)) {
            $dataset_nids = array_keys($loadedDatasets);

            // loading measures
            $measureNodes = gd_measure_get_measures_4_dataset($dataset_nids, LOAD_ENTITY);
            // grouping measures in context of dataset
            $datasetsMeasureNodes = $this->groupNodesByDataset($measureNodes, 'field_measure_dataset');

            // creating cubes for all datasets
            foreach ($loadedDatasets as $dataset) {
                // preparing corresponding cube
                $cube = StarSchemaCubeMetaData::initializeFromDataset($metamodel, $dataset);
                // assigning a loader which created the cube
                $cube->factsDataset->loaderName = $loaderName;
                $cube->loaderName = $loaderName;
                // preparing additional measures
                $datasetMeasureNodes = isset($datasetsMeasureNodes[$dataset->nid]) ? $datasetsMeasureNodes[$dataset->nid] : NULL;
                if (isset($datasetMeasureNodes)) {
                    foreach ($datasetMeasureNodes as $measureNode) {
                        $measure = $cube->registerMeasure(get_node_field_value($measureNode, 'field_measure_sysname', 0, 'value', TRUE));
                        $measure->publicName = $measureNode->title;
                        $measure->description = get_node_field_value($measureNode, 'field_measure_desc');
                        $measure->function = get_node_field_value($measureNode, 'field_measure_function', 0, 'value', TRUE);
                    }
                }
            }
        }

        // processing references
        $referenceNodes = gd_reference_get_references(LOAD_ENTITY);
        $referencePointNodes = isset($referenceNodes) ? gd_reference_get_reference_points(LOAD_ENTITY) : NULL;
        if (isset($referencePointNodes)) {
            foreach ($referenceNodes as $referenceNode) {
                GD_DatasetMetaModelLoaderHelper::prepareReference($metamodel, $referenceNode, $referencePointNodes);
            }
        }
    }
}
