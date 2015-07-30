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

class ReferenceExport extends Export\AbstractEntityExport {

    public function export(Export\ExportStream $stream, Export\ExportContext $context) {
        $datasource = gd_datasource_get_active();
        $readOnly = gd_datasource_is_property($datasource, 'readonly');
        if ($readOnly) {
            return;
        }

        $metamodel = data_controller_get_metamodel();

        // get reference points that have ref to the dataset in this datamart
        $datasetNames = array();
        foreach ( $stream->get('datasets') as $d ) {
            $datasetNames[] = $d->name;
        }

        $referencePoints = array();
        $referencePointNodes = gd_reference_get_reference_points_by_dataset($datasetNames, LOAD_ENTITY);
        $referencePointNids = array();
        foreach ( $referencePointNodes as $ref_point ) {
            $export = new stdClass();

            $export->id = (int) $ref_point->nid;
            $export->title = $ref_point->title;

            $dataset = $metamodel->getDataset(get_node_field_value($ref_point,'field_ref_point_dataset_sysname',0,'value',true));
            $export->dataset = $dataset->uuid;

            $export->columns = array();
            foreach ( (array) get_node_field_value($ref_point,'field_ref_point_column_sysname',null) as $column ) {
                $export->columns[] = $column;
            }

            $referencePoints[] = $export;
            $referencePointNids[] = (int) $ref_point->nid;
        }

        // find all references with above reference points
        $references = array();
        $referenceNodes = gd_reference_get_references_by_reference_points($referencePointNids, LOAD_ENTITY);
        foreach ( $referenceNodes as $reference ) {
            $export = new stdClass();

            $export->id = (int) $reference->nid;
            $export->title = $reference->title;

            $export->sysname = get_node_field_value($reference,'field_reference_sysname');

            foreach ( (array) get_node_field_node_ref($reference,'field_reference_point',null) as $referencePointNid ) {
                foreach ( $referencePoints as $referencePoint ){
                    if ( $referencePointNid == $referencePoint->id ) {
                        $export->referencePoints[] = $referencePoint;
                    }
                }
            }

            if ( empty($export->referencePoints) ) {
                throw new Exception('Reference missing reference points.');
            }

            $references[] = $export;
        }

        $stream->set('references',$references);
    }

    public static function getExportables($datasourceName) {
        if ( $datasourceName != gd_datasource_get_active() ) {
            gd_datasource_set_active($datasourceName);
        }

        $metamodel = data_controller_get_metamodel();
        // get datasets
        $datasetNames = array();
        foreach ($metamodel->datasets as $dataset) {
            if (!isset($dataset->nid)) {
                continue;
            }
            $datasetNames[] = $dataset->name;
        }

        $referencePointNids = gd_reference_get_reference_points_by_dataset($datasetNames);

        return gd_reference_get_references_by_reference_points($referencePointNids,LOAD_ENTITY);
    }

}