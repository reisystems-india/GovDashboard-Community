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

class DashboardImport extends Import\AbstractEntityImport {

    protected $datasourceName;

    protected $datasets;
    protected $reports;

    protected function create(Import\ImportStream $stream, Import\ImportContext $context) {
        $dashboards = (array) $stream->get('dashboards');
        if (empty($dashboards)) {
            return;
        }

        $this->datasourceName = $context->get('datasourceName');

        $this->reports = (array) $stream->get('reports');

        $readOnly = gd_datasource_is_property($this->datasourceName, 'readonly');
        if ($readOnly) {
            $metamodel = data_controller_get_metamodel();
            $this->datasets = $metamodel->datasets;
        } else {
            $this->datasets = (array) $stream->get('datasets');
        }

        if ( !empty($reports) && empty($datasets) ) {
            throw new Exception('Missing datasets for dashboard reports.');
        }

        foreach ( $dashboards as $dashboardKey => $dashboard ) {
            $node = $this->createDashboard($dashboard);
            if ( !empty($node->nid ) ) {
                $dashboards[$dashboardKey] = $node;
            } else {
                throw new Exception('Dashboard node creation failed');
            }
        }

        // update report/dashboard ids in config, for linking
        foreach ( $dashboards as $dashboardKey => $dashboard ) {
            $config = json_decode($dashboard->field_dashboard_config[$dashboard->language][0]['value']);

            $this->processConfigDashboards($config);

            $dashboards[$dashboardKey]->field_dashboard_config[$dashboards[$dashboardKey]->language][0]['value'] = json_encode($config);
            gd_dashboard_save($dashboards[$dashboardKey]);
        }

        $stream->set('dashboards',$dashboards);
    }

    protected function update(Import\ImportStream $stream, Import\ImportContext $context) {
        $dashboards = $stream->get('dashboards');
        if (empty($dashboards)) {
            return;
        }

        $this->datasourceName = $context->get('datasourceName');

        $this->reports = gd_report_findall_by_datasource(LOAD_ENTITY,$this->datasourceName);

        $metamodel = data_controller_get_metamodel();
        $this->datasets = $metamodel->datasets;

        foreach ( $dashboards as $dashboardKey => $dashboard ) {
            $dashboard = (object) $dashboard;
            $existingDashboard = gd_dashboard_get_by_uuid($dashboard->uuid,$this->datasourceName);
            if ( !$existingDashboard ) {
                // create
                $node = $this->createDashboard($dashboard);
                if ( !empty($node->nid ) ) {
                    $dashboards[$dashboardKey] = $node;
                } else {
                    throw new Exception('Dashboard node creation failed');
                }
            } else {
                $existingDashboard->title = $dashboard->title;
                $existingDashboard->field_dashboard_desc[$existingDashboard->language][0]['value'] = $dashboard->description;
                $existingDashboard->field_dashboard_datasource[$existingDashboard->language][0]['value'] = $this->datasourceName;
                $existingDashboard->field_dashboard_custom_view[$existingDashboard->language][0]['value'] = $dashboard->custom_view;
                $existingDashboard->field_dashboard_public[$existingDashboard->language][0]['value'] = (int) $dashboard->public;

                // update report references
                $existingDashboard->field_dashboard_reports[$existingDashboard->language] = array();
                foreach ( $dashboard->reports as $reportUuid ) {
                    $reportNode = gd_report_get_by_uuid($reportUuid,$this->datasourceName);
                    $existingDashboard->field_dashboard_reports[$existingDashboard->language][] = array('nid'=>$reportNode->nid);
                }

                $this->processConfigReports($dashboard->config);
                $existingDashboard->field_dashboard_config[$existingDashboard->language][0]['value'] = json_encode($dashboard->config);

                gd_dashboard_save($existingDashboard);

                $event = new DefaultEvent();
                $event->type = 101; // see gd_health_monitoring_database_install() for more details
                $event->owner = $existingDashboard->nid;
                EventRecorderFactory::getInstance()->record($event);

                $dashboards[$dashboardKey] = $existingDashboard;
            }
        }

        // update report/dashboard ids in config, for linking
        foreach ( $dashboards as $dashboardKey => $dashboard ) {
            $config = json_decode($dashboard->field_dashboard_config[$dashboard->language][0]['value']);

            $this->processConfigDashboards($config);

            $dashboards[$dashboardKey]->field_dashboard_config[$dashboards[$dashboardKey]->language][0]['value'] = json_encode($config);
            gd_dashboard_save($dashboards[$dashboardKey]);
        }
    }

    protected function createDashboard ( $dashboard ) {
        $node = new stdClass();
        $node->type = NODE_TYPE_DASHBOARD;
        $node->language = LANGUAGE_NONE;
        $node->status = NODE_PUBLISHED;
        node_object_prepare($node);

        $node->originalNid = $dashboard->id;
        $node->title = $dashboard->title;
        $node->field_dashboard_datasource[$node->language][0]['value'] = $this->datasourceName;

        if ( !empty($dashboard->uuid) ) {
            $node->field_dashboard_uuid[$node->language][0]['value'] = $dashboard->uuid;
        }

        if ( !empty($dashboard->alias) ) {
            for($i = 0; $i < count($dashboard->alias); $i++) {
                $node->field_dashboard_alias[$node->language][] = array('value'=>$dashboard->alias[$i]);
            }
        }

        if ( !empty($dashboard->tags) ) {
            for($i = 0; $i < count($dashboard->tags); $i++) {
                $node->field_dashboard_tags[$node->language][] = array('tid'=>$dashboard->tags[$i]);
            }
        }

        if ( !empty($dashboard->description) ) {
            $node->field_dashboard_desc[$node->language][0]['value'] = $dashboard->description;
        }

        if ( !empty($dashboard->custom_view) ) {
            $node->field_dashboard_custom_view[$node->language][0]['value'] = $dashboard->custom_view;
        }

        // public dashboard flag
        if ( isset($dashboard->public) ) {
            $node->field_dashboard_public[$node->language][0]['value'] = $dashboard->public;
        }

        // update report references
        $node->field_dashboard_reports[$node->language] = array();
        foreach ( $dashboard->reports as $reportUuid ) {
            $reportNode = gd_report_get_by_uuid($reportUuid,$this->datasourceName);
            if (isset($reportNode)) {
                $node->field_dashboard_reports[$node->language][] = array('nid'=>$reportNode->nid);
            }
        }

        // update dataset references
        $this->processConfigReports($dashboard->config);
        $node->field_dashboard_config[$node->language][0]['value'] = json_encode($dashboard->config);

        gd_dashboard_save($node);

        if (isset($node->nid)) {
            $event = new DefaultEvent();
            $event->type = 100; // see gd_health_monitoring_database_install() for more details
            $event->owner = $node->nid;
            EventRecorderFactory::getInstance()->record($event);
        }

        return $node;
    }

    protected function processConfigReports (&$config) {
        //  TODO Services casts everything to array not objects
        $config = (object) $config;
        // dashboard reports
        foreach ( $config->items as $key => $item ) {
            //  TODO Services casts everything to array not objects
            $item = (object) $item;
            if ( $item->type == 'report' ) {
                $reportNode = gd_report_get_by_uuid($item->content,$this->datasourceName);
                //  TODO Services casts everything to array not objects
                $config->items[$key] = (object) $config->items[$key];
                $config->items[$key]->content = $reportNode->nid;
            }
        }

        // drill down reports
        foreach ( $config->drilldowns as $key => $drilldown ) {
            //  TODO Services casts everything to array not objects
            $config->drilldowns[$key] = (object) $config->drilldowns[$key];
            // report nid update
            $reportNode = gd_report_get_by_uuid($drilldown->report,$this->datasourceName);
            $config->drilldowns[$key]->report = (int) $reportNode->nid;

            // column name update
            $config->drilldowns[$key]->column = DatasetImportHelper::getNewColumnName($drilldown->column,$this->datasets);
        }
    }

    protected function processConfigDashboards (&$config) {
        // drill down dashboards
        foreach ( $config->drilldowns as $key => $drilldown ) {
            //  TODO Services casts everything to array not objects
            $config->drilldowns[$key] = (object) $config->drilldowns[$key];
            $dashboardNode = gd_dashboard_get_by_uuid($drilldown->dashboard,$this->datasourceName);
            $config->drilldowns[$key]->dashboard = $dashboardNode->nid;
        }
    }

}