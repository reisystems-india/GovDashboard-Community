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

class DashboardExport extends Export\AbstractEntityExport {

    protected $datasourceName;

    public function export(Export\ExportStream $stream, Export\ExportContext $context) {
        $this->datasourceName = $context->get('datasourceName');

        $dashboards = array();
        foreach (gd_dashboard_findall_by_datasource(LOAD_ENTITY,$this->datasourceName) as $dashboard) {
            $export = new stdClass();

            $export->id = (int) $dashboard->nid;
            $export->title = $dashboard->title;

            $export->uuid = get_node_field_value($dashboard,'field_dashboard_uuid',0,'value',true);
            $export->description = get_node_field_value($dashboard,'field_dashboard_desc');
            $export->public = (int) get_node_field_value($dashboard,'field_dashboard_public');
            $export->custom_view = get_node_field_value($dashboard,'field_dashboard_custom_view');
            $export->reports = (array) get_node_field_node_ref($dashboard,'field_dashboard_reports',null);
            $export->config = json_decode(get_node_field_value($dashboard,'field_dashboard_config',0,'value',true));

            $export->alias = array();
            if (!empty($dashboard->field_dashboard_alias)) {
                foreach($dashboard->field_dashboard_alias[$dashboard->language] as $alias) {
                    $export->alias[] = $alias['value'];
                }
            }

            $export->tags = array();
            if (!empty($dashboard->field_dashboard_tags)) {
                foreach($dashboard->field_dashboard_tags[$dashboard->language] as $tag) {
                    $export->tags[] = $tag['tid'];
                }
            }

            // replace report nid with report uuid
            $reportNodes = gd_report_load_multiple($export->reports);
            foreach ( $export->reports as $key => $value ) {
                foreach ( $reportNodes as $node ) {
                    if ( $node->nid == $value ) {
                        $export->reports[$key] = get_node_field_value($node,'field_report_uuid',0,'value',true);
                    }
                }
            }

            // replace references with uuid
            $this->processConfig($export);

            $dashboards[] = $export;
        }
        $stream->set('dashboards',$dashboards);
    }

    public static function getExportables($datasourceName) {
        return gd_dashboard_findall_by_datasource(LOAD_ENTITY,$datasourceName);
    }

    protected function processConfig ( $export ) {
        $metamodel = data_controller_get_metamodel();

        // replace report nid with report uuid
        foreach ( $export->config->items as $key => $item ) {
            if ( $item->type == 'report' ) {
                $reportNode = node_load($item->content);
                $export->config->items[$key]->content = get_node_field_value($reportNode,'field_report_uuid',0,'value',true);
            }
        }

        // array cast to deal with json decoder creating objects for arrays with missing keys
        $export->config->drilldowns = (array) $export->config->drilldowns;

        // replace report nid with report uuid
        // replace dashboard nid with dashboard uuid
        // replace dataset column reference with dataset uuid
        $updated_drilldowns = array();
        foreach ( $export->config->drilldowns as $drilldown ) {

            $reportNode = gd_report_get_by_nid($drilldown->report);
            $dashboardNode = gd_dashboard_get_by_nid($drilldown->dashboard);

            if ( !$reportNode || !$dashboardNode ) {
                $message = t('Skipping corrupt drilldown for node: @nodeId.',array('@nodeId' => $export->id));
                drupal_set_message($message, 'warning');
                LogHelper::log_warn($message);
            } else {
                $updated_drilldown = $drilldown;
                $updated_drilldown->report = get_node_field_value($reportNode,'field_report_uuid',0,'value',true);
                $updated_drilldown->column = DatasetExportHelper::getExportColumnName($drilldown->column,$metamodel);
                $updated_drilldown->dashboard = get_node_field_value($dashboardNode,'field_dashboard_uuid',0,'value',true);
                $updated_drilldown->id = count($updated_drilldowns);
                $updated_drilldowns[] = $updated_drilldown;
            }
        }

        $export->config->drilldowns = $updated_drilldowns;
    }
}