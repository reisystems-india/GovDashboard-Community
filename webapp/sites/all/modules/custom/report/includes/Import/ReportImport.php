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

class ReportImport extends Import\AbstractEntityImport {

    protected $datasourceName;
    protected $datasets;

    protected function create(Import\ImportStream $stream, Import\ImportContext $context) {
        $reports = $stream->get('reports');
        if (empty($reports)) {
            return;
        }

        $this->datasourceName = $context->get('datasourceName');
        $readOnly = gd_datasource_is_property($this->datasourceName, 'readonly');
        if ($readOnly) {
            $metamodel = data_controller_get_metamodel();
            $this->datasets = $metamodel->datasets;
        } else {
            $this->datasets = $stream->get('datasets');
        }

        if (empty($this->datasets)) {
            throw new Exception('Must have datasets to create reports.');
        }


        foreach ( $reports as $reportKey => $report ) {
            $node = $this->createReport($report);
            if ( !empty($node->nid ) ) {
                $reports[$reportKey] = $node;
            } else {
                throw new Exception('Report node creation failed');
            }
        }

        $stream->set('reports',$reports);
    }

    protected function update(Import\ImportStream $stream, Import\ImportContext $context) {

        $this->datasourceName = $context->get('datasourceName');

        $reports = $stream->get('reports');
        if (empty($reports)) {
            return;
        }

        $metamodel = data_controller_get_metamodel();
        $this->datasets = $metamodel->datasets;

        foreach ( $reports as $reportKey => $report ) {
            $report = (object) $report;
            $existingReport = gd_report_get_by_uuid($report->uuid,$this->datasourceName);
            if ( !$existingReport ) {
                // create
                $node = $this->createReport($report);
                if ( !empty($node->nid ) ) {
                    $reports[$reportKey] = $node;
                } else {
                    throw new Exception('Report node creation failed');
                }
            } else {
                $existingReport->title = $report->title;
                $existingReport->field_report_desc[$existingReport->language][0]['value'] = $report->description;
                $existingReport->field_report_datasource[$existingReport->language][0]['value'] = $this->datasourceName;
                $existingReport->field_report_custom_view[$existingReport->language][0]['value'] = $report->custom_view;
                $existingReport->field_report_tags[$existingReport->language] = array();
                if (!empty($report->tags)) {
                    foreach ($report->tags as $tid) {
                        $existingReport->field_report_tags[$existingReport->language][] = array('tid' => $tid);
                    }
                }

                // update dataset references
                $existingReport->field_report_dataset_sysnames[$existingReport->language] = array();
                foreach ( $report->datasets as $datasetIdentifier ) {
                    $dataset = GD_DatasetMetaModelLoaderHelper::findDatasetByUUID($this->datasets,$datasetIdentifier);
                    if (!isset($dataset)) {
                        $datasetName = NameSpaceHelper::addNameSpace(gd_datasource_get_active(), $datasetIdentifier);
                        $dataset = $metamodel->getDataset($datasetName);
                    }
                    $existingReport->field_report_dataset_sysnames[$existingReport->language][] = array('value'=>$dataset->name);
                }

                $this->processConfig($report->config);
                $existingReport->field_report_conf[$existingReport->language][0]['value'] = json_encode($report->config);

                gd_report_save($existingReport);

                $event = new DefaultEvent();
                $event->type = 101; // see gd_health_monitoring_database_install() for more details
                $event->owner = $existingReport->nid;
                EventRecorderFactory::getInstance()->record($event);

                $reports[$reportKey] = $existingReport;
            }
        }

        $stream->set('reports',$reports);
    }

    protected function createReport ( $report ) {
        $node = new stdClass();
        $node->type = NODE_TYPE_REPORT;
        $node->language = LANGUAGE_NONE;
        $node->status = NODE_PUBLISHED;
        node_object_prepare($node);

        $node->originalNid = $report->id;
        $node->title = $report->title;

        $node->field_report_uuid[$node->language][0]['value'] = $report->uuid;
        $node->field_report_datasource[$node->language][0]['value'] = $this->datasourceName;

        if ( !empty($report->description) ) {
            $node->field_report_desc[$node->language][0]['value'] = $report->description;
        }

        if ( !empty($report->custom_view) ) {
            $node->field_report_custom_view[$node->language][0]['value'] = $report->custom_view;
        }

        $node->field_report_tags[$node->language] = array();
        if (!empty($report->tags)) {
            foreach ($report->tags as $tid) {
                $node->field_report_tags[$node->language][] = array('tid' => $tid);
            }
        }

        // update dataset references
        $node->field_report_dataset_sysnames[$node->language] = array();
        $metamodel = data_controller_get_metamodel();
        foreach ( $report->datasets as $datasetIdentifier ) {
            $dataset = GD_DatasetMetaModelLoaderHelper::findDatasetByUUID($this->datasets,$datasetIdentifier);
            if (!isset($dataset)) {
                $datasetName = NameSpaceHelper::addNameSpace(gd_datasource_get_active(), $datasetIdentifier);
                $dataset = $metamodel->getDataset($datasetName);
            }
            $node->field_report_dataset_sysnames[$node->language][] = array('value'=>$dataset->name);
        }

        // update dataset references
        $this->processConfig($report->config);
        $node->field_report_conf[$node->language][0]['value'] = json_encode($report->config);

        gd_report_save($node);

        if (isset($node->nid)) {
            $event = new DefaultEvent();
            $event->type = 100; // see gd_health_monitoring_database_install() for more details
            $event->owner = $node->nid;
            EventRecorderFactory::getInstance()->record($event);
        }

        return $node;
    }

    protected function processConfig ( &$config ) {
        //  TODO Services casts everything to array not objects
        $config = (object) $config;
        $config->model = (object) $config->model;
        $metamodel = data_controller_get_metamodel();
        if ( !empty($config->model->datasets) ) {
            foreach ( $config->model->datasets as $key => $datasetIdentifier ) {
                $dataset = GD_DatasetMetaModelLoaderHelper::findDatasetByUUID($this->datasets,$datasetIdentifier);
                if (!isset($dataset)) {
                    $datasetName = NameSpaceHelper::addNameSpace(gd_datasource_get_active(), $datasetIdentifier);
                    $dataset = $metamodel->getDataset($datasetName);
                }
                $config->model->datasets[$key] = $dataset->name;
            }
        }

        // update columns
        if ( !empty($config->model->columns) ) {
            foreach ( $config->model->columns as $key => $value ) {
                $config->model->columns[$key] = DatasetImportHelper::getNewColumnName($value,$this->datasets);
            }
        }

        // update column configs
        if ( !empty($config->columnConfigs) ) {
            foreach ( $config->columnConfigs as $key => $value ) {
                $config->columnConfigs[$key]->columnId = DatasetImportHelper::getNewColumnName($value->columnId,$this->datasets);
            }
        }

        // update column orders
        if ( !empty($config->model->columnOrder) ) {
            foreach ( $config->model->columnOrder as $key => $value ) {
                $config->model->columnOrder[$key] = DatasetImportHelper::getNewColumnName($value,$this->datasets);
            }
        }

        // update column sorts
        if ( !empty($config->model->orderBy) ) {
            foreach ( $config->model->orderBy as $key => $value ) {
                $config->model->orderBy[$key]->column = DatasetImportHelper::getNewColumnName($value->column,$this->datasets);
            }
        }

        // update filters
        if ( !empty($config->model->filters) ) {
            foreach ( $config->model->filters as $key => $value ) {
                $config->model->filters[$key]->column = DatasetImportHelper::getNewColumnName($value->column,$this->datasets);
            }
        }

        // update visual series
        if ( !empty($config->visual->series) ) {
            $newSeries = array();
            foreach ( $config->visual->series as $key => $value ) {
                $newSeries[DatasetImportHelper::getNewColumnName($key,$this->datasets)] = $value;
            }
            $config->visual->series = $newSeries;
        }

        // update traffic column
        if ( !empty($config->visual->trafficColumn) ) {
            $config->visual->trafficColumn = DatasetImportHelper::getNewColumnName($config->visual->trafficColumn,$this->datasets);
        }

        // update color column
        if ( !empty($config->visual->useColumnDataForColor) ) {
            $config->visual->useColumnDataForColor = DatasetImportHelper::getNewColumnName($config->visual->useColumnDataForColor,$this->datasets);
        }

        // update traffic columns
        if ( !empty($config->visual->traffic) ) {
            $newTraffic = array();
            foreach ( $config->visual->traffic as $key => $value ) {
                $newName = DatasetImportHelper::getNewColumnName($key,$this->datasets);
                $value->trafficColumn = $newName;
                $newTraffic[$newName] = $value;
            }
            $config->visual->traffic = $newTraffic;
        }
    }
}