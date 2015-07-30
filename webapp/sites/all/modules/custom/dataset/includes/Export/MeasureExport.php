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


use \GD\Sync\Export;

class MeasureExport extends Export\AbstractEntityExport {

    public function export(Export\ExportStream $stream, Export\ExportContext $context) {

        $metamodel = data_controller_get_metamodel();
        $datasetUuidMappings = array();

        // get datasets
        $datasetNids = array();
        foreach ($metamodel->datasets as $dataset) {
            $datasetUuidMappings[$dataset->source] = DatasetExportHelper::getExportDatasetName($dataset->name, $metamodel);
            if (!isset($dataset->nid)) {
                continue;
            }
            $datasetNids[] = $dataset->nid;
        }

        // find all references with above reference points
        $measures = array();
        if (!empty($datasetNids)) {
            $measureNodes = gd_measure_get_measures_4_dataset($datasetNids, LOAD_ENTITY);
            if (!empty($measureNodes)) {
                foreach ( $measureNodes as $measure ) {
                    $export = new stdClass();

                    $export->id = (int) $measure->nid;
                    $export->title = $measure->title;

                    $export->sysname = get_node_field_value($measure,'field_measure_sysname');
                    $export->description = get_node_field_value($measure,'field_measure_desc');

                    $dataset = node_load(get_node_field_value($measure, 'field_measure_dataset', 0, 'nid'));
                    $export->dataset = get_node_field_value($dataset, 'field_dataset_uuid');

                    $function = get_node_field_value($measure,'field_measure_function');

                    foreach($datasetUuidMappings as $source => $uuid) {
                        $function = str_replace($source, $uuid, $function);
                    }

                    $export->function = $function;

                    $measures[] = $export;
                }
            }
        }

        $stream->set('measures',$measures);
    }

    public static function getExportables($datasourceName) {
        if ( $datasourceName != gd_datasource_get_active() ) {
            gd_datasource_set_active($datasourceName);
        }

        $metamodel = data_controller_get_metamodel();
        // get datasets
        $datasetNids = array();
        foreach ($metamodel->datasets as $dataset) {
            if (!isset($dataset->nid)) {
                continue;
            }
            $datasetNids[] = $dataset->nid;
        }

        return gd_measure_get_measures_4_dataset($datasetNids);
    }
}