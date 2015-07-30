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

class ReportExport extends Export\AbstractEntityExport {

    public function export(Export\ExportStream $stream, Export\ExportContext $context) {
        $metamodel = data_controller_get_metamodel();

        $reports = array();
        foreach (gd_report_findall_by_datasource(LOAD_ENTITY,$context->get('datasourceName')) as $report) {
            $export = new stdClass();

            $export->id = (int) $report->nid;
            $export->title = $report->title;

            $export->description = get_node_field_value($report,'field_report_desc',0,'value');
            $export->uuid = get_node_field_value($report,'field_report_uuid',0,'value',true);
            $export->config = json_decode(get_node_field_value($report,'field_report_conf',0,'value',true));
            $export->custom_view = get_node_field_value($report,'field_report_custom_view');
            $export->datasets = (array) get_node_field_value($report,'field_report_dataset_sysnames',null,'value',true);
            $export->tags = array();
            if (!empty($report->field_report_tags)) {
                foreach($report->field_report_tags[$report->language] as $tag) {
                    $export->tags[] = $tag['tid'];
                }
            }

            // replace dataset name with dataset uuid
            foreach ( $export->datasets as $key => $value ) {
                $dataset = $metamodel->getDataset($value);
                if (isset($dataset->uuid)) {
                    $export->datasets[$key] = $dataset->uuid;
                } else {
                    $export->datasets[$key] = NameSpaceHelper::removeNameSpace($dataset->name);
                }
            }

            // replace dataset name with dataset uuid
            $this->processConfig($export->config);

            $reports[] = $export;
        }
        $stream->set('reports',$reports);
    }

    public static function getExportables($datasourceName) {
        return gd_report_findall_by_datasource(LOAD_ENTITY,$datasourceName);
    }

    protected function processConfig ( &$config ) {
        $metamodel = data_controller_get_metamodel();

        if ( !empty($config->model->datasets) ) {
            foreach ( $config->model->datasets as $key => $datasetName ) {
                $dataset = $metamodel->getDataset($datasetName);
                if (isset($dataset->uuid)) {
                    $config->model->datasets[$key] = $dataset->uuid;
                } else {
                    $config->model->datasets[$key] = NameSpaceHelper::removeNameSpace($dataset->name);
                }
            }
        }

        // update columns
        if ( !empty($config->model->columns) ) {
            foreach ( $config->model->columns as $key => $value ) {
                $config->model->columns[$key] = DatasetExportHelper::getExportColumnName($value,$metamodel);
            }
        }

        // update column configs
        if ( !empty($config->columnConfigs) ) {
            foreach ( $config->columnConfigs as $key => $value ) {
                $config->columnConfigs[$key]->columnId = DatasetExportHelper::getExportColumnName($value->columnId,$metamodel);
            }
        }

        // update column orders
        if ( !empty($config->model->columnOrder) ) {
            foreach ( $config->model->columnOrder as $key => $value ) {
                $config->model->columnOrder[$key] = DatasetExportHelper::getExportColumnName($value,$metamodel);
            }
        }

        // update column sorts
        if ( !empty($config->model->orderBy) ) {
            foreach ( $config->model->orderBy as $key => $value ) {
                $config->model->orderBy[$key]->column = DatasetExportHelper::getExportColumnName($value->column,$metamodel);
            }
        }

        // update visual series
        if ( !empty($config->visual->series) ) {
            $newSeries = array();
            foreach ( $config->visual->series as $key => $value ) {
                $newSeries[DatasetExportHelper::getExportColumnName($key,$metamodel)] = $value;
            }
            $config->visual->series = $newSeries;
        }

        // update traffic column
        if ( !empty($config->visual->trafficColumn) ) {
            $config->visual->trafficColumn = DatasetExportHelper::getExportColumnName($config->visual->trafficColumn,$metamodel);
        }

        // update color column
        if ( !empty($config->visual->useColumnDataForColor) ) {
            $config->visual->useColumnDataForColor = DatasetExportHelper::getExportColumnName($config->visual->useColumnDataForColor,$metamodel);
        }

        // update traffic columns
        if ( !empty($config->visual->traffic) ) {
            $newTraffic = array();
            foreach ( $config->visual->traffic as $key => $value ) {
                $value->trafficColumn = DatasetExportHelper::getExportColumnName($key,$metamodel);
                $newTraffic[DatasetExportHelper::getExportColumnName($key,$metamodel)] = $value;
            }
            $config->visual->traffic = $newTraffic;
        }

        // update filters
        if ( !empty($config->model->filters) ) {
            foreach ( $config->model->filters as $key => $value ) {
                $config->model->filters[$key]->column = DatasetExportHelper::getExportColumnName($value->column,$metamodel);
            }
        }
    }

}