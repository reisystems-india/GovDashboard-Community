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


/**
 * GD_DashboardConfig
 */
class GD_DashboardConfig {

    public $id;
    public $title;
    public $items = array();
    public $filters = array();
    public $drilldowns = array();
    public $request = array();
    public $customView = null;

    public $reports = array();

    public $public = false;
    public $exportable = false;
    public $printable = false;
    public $datasource = null;

    // TODO just a hack for maps, but reports should register the libs they need
    private $requiredLibs = array();

    public function __construct ( $dashboard, $params = null ) {

        if ( empty($dashboard->language) ) {
            $dashboard->language = LANGUAGE_NONE;
        }

        $config = null;
        if (!empty($dashboard->nid) && !empty($dashboard->field_dashboard_config[$dashboard->language])) {
            $config = json_decode(trim($dashboard->field_dashboard_config[$dashboard->language][0]['value']));
            if ( !empty($dashboard->field_dashboard_public) && $dashboard->field_dashboard_public[$dashboard->language][0]['value'] == 1 ) {
                $this->public = true;
            }
            $this->datasource = $dashboard->field_dashboard_datasource[$dashboard->language][0]['value'];
        } else if (!empty($dashboard->config)) {

            if ( is_string($dashboard->config) ) {
                // this is for dashboard builder, table views
                $config = json_decode(trim($dashboard->config));
            } else {
                // this is for dashboard builder, non table views
                $config = $dashboard->config;
            }
        }

        if (empty($config)) {
            $config = array();
        }

        if (!empty($dashboard->nid)) {
            $this->id = $dashboard->nid;
        }

        if (!empty($dashboard->title)) {
            $this->title = $dashboard->title;
        }

        if (!empty($dashboard->customView)) {
            $this->customView = $dashboard->customView;
        }

        // Dimensions
        if (!empty($config->width)) {
            $this->width = $config->width . 'px';
        } else {
            $this->width = '1000px';
        }

        if (!empty($config->height)) {
            $this->height = $config->height . 'px';
        } else {
            $this->height = '600px';
        }

        // items
        if ( !empty($config->items) ) {
            $this->items = $config->items;
        }

        // drilldowns
        if (!empty($config->drilldowns)) {
            $this->drilldowns = array_values((array) $config->drilldowns);
        }

        if (isset($config->exportable)) {
            $this->exportable = $config->exportable;
        }
        if (isset($config->printable)) {
            $this->printable = $config->printable;
        }
        if (!empty($params)) {
            $this->setParams($params);
        }

        $this->filters = null;
        $this->config = $config;
    }

    public function isPublic () {
        return $this->public;
    }

    public function isExportable () {
        return $this->exportable;
    }
    public function isPrintable () {
        return $this->printable;
    }
    public function setParams ( $params )  {

        $this->request['dashboard'] = $this->id;

        $url_info = parse_url($_SERVER['REQUEST_URI']);
        $this->request['origin'] = $url_info['path'];

        if (!empty($params['t'])) {
            $this->request['tags'] = $params['t'];
        }
    }

    public function getReport ( $reportNodeId ) {
        $reports = $this->getReports();
        if ( isset($reports[$reportNodeId]) ) {
            return $reports[$reportNodeId];
        }
        return null;
    }

    public function getReports () {
        if ( empty($this->reports) ) {
            $reportNodeIds = array();
            foreach ( $this->items as $item ) {
                if ( $item->type == 'report' ) {
                    $reportNodeIds[$item->content] = $item->content;
                }
            }
            if ( !empty($reportNodeIds) ) {
                $this->reports = gd_report_load_multiple($reportNodeIds);
            }
        }

        return $this->reports;
    }

    public function getReportConfig ( $reportNodeId ) {
        return GD_ReportConfigFactory::getInstance()->getConfig($this->getReport($reportNodeId));
    }

    public function getFilters () {
        if ( empty($this->filters) ) {
            // filters
            if ( !empty($this->config->filters) ) {
                $this->prepareFilters($this->config->filters);
            }
        }
        return $this->filters;
    }

    public function getFilterByName ( $name ) {
        foreach ( $this->getFilters() as $filter ) {
            if ( $filter->name == $name ){
                return $filter;
            }
        }
        return null;
    }

    public function updateReportConfig ( GD_ReportConfig $ReportConfig ) {

        // signal dashboard
        $ReportConfig->setDashboard(isset($this->id) ? $this->id : true);

        // apply drilldowns
        foreach ( $this->drilldowns as $d ) {
            if ( $d->report == $ReportConfig->getId() ) {
                foreach ($d->filters as $k => $f) {
                    $add = false;
                    foreach ($ReportConfig->getFilters() as $f2) {
                        if ( $f == $f2->name && $f2->exposed ) {
                            $add = true;
                            break;
                        }
                    }

                    if (!$add) {
                        unset($d->filters[$k]);
                    }
                }
                $ReportConfig->addDrilldown($d);
            }
        }

        // apply filters
        $f = $this->getFilters();
        if ( !empty($f) && count($ReportConfig->getFilters()) ) {
            // this has the filter list
            foreach ($f as $dashboard_filter) {
                // find corresponding report filter
                foreach ( $ReportConfig->getFilters() as $report_filter ) {
                    if ( $dashboard_filter->name == $report_filter->name && $report_filter->exposed) {
                        $ReportConfig->updateFilterValue($report_filter->name, $report_filter->exposed, $dashboard_filter);
                    }
                }
            }
        }
    }

    public function getWarningItemView( $node, $item, $options = array() ) {
        $report = new stdClass();
        $report->title = $node->title;
        $report->nid = $node->nid;
        $report->language = $node->language;
        $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($report);
        $ReportConfig->setDisplaySize($item->size->width,$item->size->height);
        $ReportView = new GD_ReportView($ReportConfig);
        return $ReportView->getWarningView($options);
    }

    public function getDashboardCustomView () {
        if (isset($this->customView)) {
            return $this->customView;
        }

        return null;
    }

    /**
     * @param $item
     * @param array $options
     * @return stdClass
     */
    public function getItemReportView ( $item, $options = array() ) {
        try {
            $ReportConfig = $this->getReportConfig($item->content);
            $ReportConfig->setDisplaySize($item->size->width,$item->size->height);
            $this->requiredLibs[$ReportConfig->getDisplayType()] = $ReportConfig->getDisplayType();
            $ReportView = new GD_ReportView($ReportConfig);
            if ( $ReportConfig->validate() ) {
                $this->updateReportConfig($ReportConfig);
                return $ReportView->getView($options);
            } else {
                return $ReportView->getWarningView($options);
            }
        } catch (Exception $e) {
            $options['error'] = $e->getMessage();
            return $this->getWarningItemView($this->getReport($item->content), $item, $options);
        }
    }

    public function getItemTableView ( $item, $options = array() ) {
        try {
            $ReportConfig = $this->getReportConfig($item->content);
            $ReportConfig->setDisplaySize($item->size->width,$item->size->height);
            $displayType = $ReportConfig->getDisplayType();
            $ReportConfig->setDisplayType('table');
            $ReportView = new GD_ReportView($ReportConfig);
            if ( $ReportConfig->validate() ) {
                $this->updateReportConfig($ReportConfig);
                $view = $ReportView->getView($options);
            } else {
                $view = $ReportView->getWarningView($options);
            }
            $ReportConfig->setDisplayType($displayType);
            return $view;
        } catch (Exception $e) {
            $options['error'] = $e->getMessage();
            return $this->getWarningItemView($this->getReport($item->content), $item, $options);
        }
    }

    public function getItemTextView ( $item ) {
        $style = array();
        $style[] = 'position: absolute;';
        $style[] = 'left: ' . ($item->position->left) . 'px;';
        $style[] = 'top: ' . ($item->position->top) . 'px;';
        $style[] = 'width: ' . ($item->size->width) . 'px;';
        $style[] = 'height: ' . ($item->size->height) . 'px;';

        return '<div style="'.implode(' ', $style).'">'.$item->content.'</div>';
    }

    public function getItemImageView ( $item ) {
        $style = array();
        $style[] = 'position: absolute;';
        $style[] = 'left: ' . ($item->position->left) . 'px;';
        $style[] = 'top: ' . ($item->position->top) . 'px;';
        $style[] = 'width: ' . ($item->size->width) . 'px;';
        $style[] = 'height: ' . ($item->size->height) . 'px;';

        return '<img style="'.implode(' ', $style).'" src="'.$item->content->src.'" alt="'.$item->content->alt.'" />';
    }

    public function getDatasource() {
        return $this->datasource;
    }

    public function getItemStyle ( $item ) {
        $style = array();

        // If dashboard builder -- not report builder, not dashboard viewer /* TODO: how else can we check for this??? */
        if ( $_GET['q'] == 'dashboard/report/preview' ) {
            $style[] = 'width: ' . ($item->size->width) . 'px;';
            $style[] = 'height: ' . ($item->size->height) . 'px;';
        } else {
            $style[] = 'overflow: hidden;';
            $style[] = 'position: absolute;';
            $style[] = 'left: ' . ($item->position->left) . 'px;';
            $style[] = 'top: ' . ($item->position->top) . 'px;';
            $style[] = 'width: ' . ($item->size->width) . 'px;';
            $style[] = 'height: ' . ($item->size->height) . 'px;';
        }

        // backwards compatibility check, can be removed eventually.
        if ( !isset($item->bgcolor) ) {
            $item->bgcolor = 'inherit';
        }

        return implode(' ', $style);
    }

    private function prepareFilters ( $filters ) {
        $this->filters = $filters;

        $i = 0;
        foreach ( $this->getFilters() as $filter ) {
            $column = null;
            foreach ( $this->getReports() as $report ) {
                $ReportConfig = $this->getReportConfig($report->nid);
                $column = $ReportConfig->getColumnByFilterName($filter->name);
                if ( isset($column) ) {
                    break;
                }
            }

            if ( $column ) {
                $this->filters[$i]->id = $i;
                $this->filters[$i]->type = $column->type->applicationType;
            } else {
                $this->filters[$i]->id = $i;
                $this->filters[$i]->type = null;
                $this->filters[$i]->invalid = true;
            }
            $i++;
        }
    }

    public function attachRequiredLibs() {

        drupal_add_library('gd','datatables');
        drupal_add_library('gd','highcharts');

        foreach ( $this->requiredLibs as $name ) {

            if ( $name == 'sparkline' ) {
                drupal_add_js('sites/all/libraries/sparkline/jquery.sparkline.min.js');
            }

        }
    }

    //  Convert DashboardConfig back to node json format
    public function toJson() {
        $config = new stdClass();

        // Dimensions
        if ($this->width != '1000px') {
            $config->width = intval(substr($this->width, 0, -2));
        }

        if ($this->height != '1000px') {
            $config->height = intval(substr($this->height, 0, -2));
        }

        // items
        if ( !empty($this->items) ) {
            $config->items = $this->items;
        } else {
            $config->items = array();
        }

        // filters
        $filters = $this->getFilters();
        if ( !empty($filters) ) {
            $config->filters = array();
            //  prepare filters adds extra data to filters that we don't need
            foreach ( $filters as $filter) {
                $f = new stdClass();
                $f->name = $filter->name;
                $f->operator = $filter->operator;
                $f->value = $filter->value;
                $f->exposed = $filter->exposed;
                $f->exposedType = $filter->exposedType;
                $f->view = $filter->view;
                $config->filters[] = $f;
            }
        } else {
            $config->filters = array();
        }

        // drilldowns
        if (!empty($this->drilldowns)) {
            $config->drilldowns = $this->drilldowns;
        } else {
            $config->drilldowns = array();
        }

        return json_encode($config);
    }
}
