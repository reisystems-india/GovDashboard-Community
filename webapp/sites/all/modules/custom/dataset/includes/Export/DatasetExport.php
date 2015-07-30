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

class DatasetExport extends Export\AbstractEntityExport {

    public function export(Export\ExportStream $stream, Export\ExportContext $context) {
        $datasource = gd_datasource_get_active();
        $readOnly = gd_datasource_is_property($datasource, 'readonly');
        if ($readOnly) {
            return;
        }

        $metamodel = data_controller_get_metamodel();

        // get datasets
        $datasets = array();
        foreach ($metamodel->datasets as $dataset) {
            if (!isset($dataset->nid)) {
                continue;
            }
            $datasets[] = $dataset;
        }

        $stream->set('datasets',$datasets);
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

        return node_load_multiple($datasetNids);
    }
}