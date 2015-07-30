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

class MeasureImport extends Import\AbstractEntityImport {

    protected $datasets;

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
        $measures = $stream->get('measures');
        if (empty($measures)) {
            return;
        }

        $this->datasets = $stream->get('datasets');
        if (empty($this->datasets)) {
            throw new Exception('Missing datasets for references.');
        }

        foreach ( $measures as $i => $measure ) {
            $column = array(
                'publicName' => $measure->title,
                'description' => $measure->description,
                'name' => $measure->sysname,

            );

            $node = null;
            foreach ( $this->datasets as $dataset ) {
                if ( isset($dataset->uuid) && $dataset->uuid == $measure->dataset ) {
                    $column['dataset'] = $dataset->name;
                    $column['function'] = str_replace($dataset->uuid, $dataset->source, $measure->function);
                    $node = gd_measure_create($column, $dataset->nid);
                    break;
                }
            }

            if ( !empty($node->nid ) ) {
                $measures[$i] = $node;
            } else {
                throw new Exception('Measure node creation failed');
            }
        }

        $stream->set('measures', $measures);
    }

    protected function update(Import\ImportStream $stream, Import\ImportContext $context) {
        $measures = $stream->get('measures');
        if (empty($measures)) {
            return;
        }

        $metamodel = data_controller_get_metamodel();
        $this->datasets = $metamodel->datasets;

        foreach ( $measures as $k => $measure ) {
            foreach ( $this->datasets as $dataset ) {
                if ( DatasetExportHelper::getExportDatasetName($dataset->name, $metamodel) == $measure->dataset ) {
                    $measure->dataset = $dataset->name;
                    $measure->function = str_replace(DatasetExportHelper::getExportDatasetName($dataset->name, $metamodel), $dataset->source, $measure->function);

                    $existingMeasureNode = $this->findExistingMeasure($measure->sysname);
                    if ( !$existingMeasureNode ) {
                        // create
                        $node = gd_measure_create($measure, $dataset);
                        if ( !empty($node->nid ) ) {
                            $measures[$k] = $node;
                        } else {
                            throw new Exception('Measure node creation failed');
                        }
                    } else {

                        $existingMeasureNode->title = $measure->title;
                        $existingMeasureNode->field_measure_desc[$existingMeasureNode->language][] = array('value' => $measure->description);
                        $existingMeasureNode->field_measure_dataset[$existingMeasureNode->language][] = array('value' => $measure->dataset);
                        $existingMeasureNode->field_measure_function[$existingMeasureNode->language][] = array('value' => $measure->function);

                        node_save($existingMeasureNode);
                    }
                    break;
                }
            }
        }

        $stream->set('measures', $measures);
    }

    protected function findExistingMeasure ( $measureSysname ) {

        $metamodel = data_controller_get_metamodel();

        // get datasets
        $datasetNids = array();
        foreach ($metamodel->datasets as $dataset) {
            if (!isset($dataset->nid)) {
                continue;
            }
            $datasetNids[] = $dataset->nid;
        }

        $measureNodes = (array) gd_measure_get_measures_4_dataset($datasetNids, LOAD_ENTITY);

        $matchedMeasure = null;
        foreach ( $measureNodes as $measureNode ) {
            if (get_node_field_value($measureNode, 'field_measure_sysname') == $measureSysname) {
                $matchedMeasure = $measureNode;
                break;
            }
        }

        return $matchedMeasure;
    }
}