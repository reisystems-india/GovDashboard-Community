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


class GD_WidgetCustomView extends GD_Widget {

    public function getView( array $options = array() ) {
        $scrollHeight = 400;
        $heightAdj = 0;
        if ( $this->ReportConfig->showTitle() || $this->ReportConfig->showMenu() ) {
            $heightAdj = (GD_REPORT_TITLE_HEIGHT);
        }

        $reportSize = $this->ReportConfig->getDisplaySize();
        if ( !empty($reportSize) ) {
            $scrollHeight = $reportSize->height;
        }

        if ( isset($_REQUEST['h']) && isset($_REQUEST['w']) ) {
            $scrollHeight = $_REQUEST['h'];
        }

        $scrollHeight -= $heightAdj;
        $width = isset($_REQUEST['w']) ? $_REQUEST['w'] . 'px' : '100%';

        $datasourceName = $this->ReportConfig->getNode() != null ? get_node_field_value($this->ReportConfig->getNode(), 'field_report_datasource') : null;
        if (!isset($datasourceName)) {
            $datasourceName = gd_datasource_get_active();
        }
        $viewStart = gd_datasource_is_property($datasourceName, 'draft') ?
          '<img id="report-'.intval($this->ReportConfig->getId()).'-overlay" class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>
          <script type="text/javascript">
            jQuery("#report-'.intval($this->ReportConfig->getId()).'-overlay").bind("mouseenter mouseleave click dblclick foucsout hover mousedown mousemove mouseout mouseover mouseup", function (e) {
                if (navigator.appName == "Microsoft Internet Explorer") {
                    jQuery("#report-'.intval($this->ReportConfig->getId()).'-overlay").hide();
                    var target = document.elementFromPoint(e.clientX, e.clientY);
                    jQuery(target).trigger(e.type);
                    jQuery("#report-'.intval($this->ReportConfig->getId()).'-overlay").show();
                }
            });
          </script>' : '';
        $viewStart .= '<div class="report report-custom" id="report-'.intval($this->ReportConfig->getId()).'" style="width:' . $width . ';height:' . $scrollHeight . 'px;clear:both; overflow:hidden;position:relative;">';
        $viewEnd = '</div>';
        $viewBody = $this->getBody($options);

        return
          $viewStart.
            $viewBody.
          $viewEnd;
    }

    protected function getConstants(array $options) {
        $dashboards = gd_dashboard_findall_by_datasource(LOAD_ENTITY);
        $dashboardMetadata = array();
        foreach($dashboards as $dashboard) {
            $metadata = new stdClass();
            $metadata->name = $dashboard->title;
            $metadata->id = $dashboard->nid;
            $metadata->alias = get_node_field_value($dashboard,'field_dashboard_alias',0);
            $dashboardMetadata[] = $metadata;
        }

        $admin = !empty($options['admin']) ? 'true' : 'false';
        $public = !empty($options['public']) ? 'true' : 'false';

        return 'var ReportConstants = {};
                            ReportConstants.reportTitle = "' . $this->ReportConfig->title . '";
                            ReportConstants.reportId = ' . intval($this->ReportConfig->getId()) .';
                            ReportConstants.dashboards = ' . json_encode($dashboardMetadata) .';
                            ReportConstants.host = "' . GOVDASH_HOST .'";
                            ReportConstants.adminView = ' . $admin . ';
                            ReportConstants.publicView = ' . $public . ';';
    }

    protected function getBody(array $options) {
        if (!empty($this->ReportConfig->title)) {
            $constants = $this->getConstants($options);
            list($fields,$tableData,$errors) = array_values(gd_report_format_data($this->ReportConfig,$this->ReportConfig->getData()));
            $data =
                'var ReportData = {};
                    ReportData.data='.json_encode($tableData).';
                    ReportData.drilldowns = '. json_encode($this->ReportConfig->drilldowns) .';';

            $viewCode = $this->ReportConfig->getCustomView();

            if ( !isset($viewCode) ) {
                $node = $this->ReportConfig->getNode();
                if ( !isset($node) && $this->ReportConfig->getId() ) {
                    $node = node_load($this->ReportConfig->getId());
                }

                if ( isset($node) ) {
                    $viewCode = $node->field_report_custom_view[$node->language][0]['value'];
                }
            }

            if ( isset($viewCode) ) {
                $viewCode = $this->scopeScripts($viewCode, $constants, $data);
            }

            return $viewCode;
        }

        return '';
    }

    protected function scopeScripts($viewCode, $constants, $data) {
        $pattern = "/<script(\b[^>]*)>([\s\S]*?)<\/script>/i";
        $matches = array();
        preg_match_all($pattern, $viewCode, $matches);
        $srcs = array();
        foreach ($matches[1] as $attribute) {
            if (strpos($attribute, 'src')) {
                $srcs[] = '<script ' . $attribute . '></script>';
            }
        }
        $script = '';
        foreach ($matches[2] as $group) {
            $script .= $group;
        }
        $script = '<script type="text/javascript">(function(global){ !function($,undefined) {$("#report-'.intval($this->ReportConfig->getId()).'").on("ready.report.render", function() {' . $constants . ' ' . $data . ' ' . $script . '}); }(global.GD_jQuery ? global.GD_jQuery : jQuery); })(!window ? this : window);</script>';
        $viewCode = preg_replace($pattern, '', $viewCode);
        foreach ($srcs as $src) {
            $viewCode .= $src;
        }
        return $viewCode . $script;
    }
}