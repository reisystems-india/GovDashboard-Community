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


class GD_DashboardView {

    private $config;
    private $options = array();

    public function __construct ( GD_DashboardConfig $config ) {
        $this->config = $config;
    }

    public function getView ($options = array()) {
        $this->options = $options;

        $output = '<div id="breadcrumbContainer" class="int-container breadcrumb-container"></div><div id="filterContainer" class="int-container filter-container"><div id="intTopContainer" class="int-top-container"></div>';
        $output .= '<div id="int-ui-container">';
        $output .= '<span class="int-ui-filters-span">Filters: </span>';
        $this->ui_js($output);

        $output .= '</div>';
        $output .= '<div id="intBottomContainer" class="int-bottom-container"></div></div>';
        $this->renderDashboard($output);

        return $output;
    }

    private function renderDashboard ( &$output ) {
        if ( $this->config->id != 0 ) {
            $node = node_load($this->config->id);

            if ( !empty($node->field_dashboard_custom_view[$node->language]) ) {
                $output .= $node->field_dashboard_custom_view[$node->language][0]['value'];
            }
        }

        $output .= '<div id="dash_viewer" dashboard_id="'.$this->config->id.'" style="width:' . $this->config->width . '; height:' . $this->config->height . '; position: relative;">';

        foreach ($this->config->items as $item) {
            $item->style = $this->config->getItemStyle($item);

            if ($item->type == 'report') {
                $html = $this->config->getItemReportView($item, $this->options);
                $output .= '<div tabindex="'.GD_REPORT_TABINDEX.'" id="dashboard-report-container-'.$item->content.'" class="dashboard-report-container" style="'.$item->style.'">'
                  . $html->header
                  . $html->body
                  . $html->footer
                  . '</div>';

            } else if ($item->type == 'text') {
                $output .= '<div class="dashboard-report-container dashboard-text" style="' . $item->style . '">' . $item->content . '</div>';

            } else if ($item->type == 'image') {
                $output .= '<div class="dashboard-report-container dashboard-image" style="' . $item->style . '"><img alt="' . $item->content->alt . '" src="' . $item->content->src . '" width="100%" height="100%" /></div>';

            } else {
                $output .= '<div style="' . $item->style . '"></div>';
            }
        }

        $output .= '</div>';
    }

    private function ui_js ( &$output ) {
        $filters = array();
        $drilldown = array();

        if ( !empty($_REQUEST['t']) ) {
            foreach ($_REQUEST['t'] as $dashboard => $dashboardFilters) {
                if (!isset($_REQUEST['id'])) {
                    break;
                }

                foreach ($dashboardFilters as $filterName => $filter) {
                    if ($dashboard == $_REQUEST['id']) {
                        if (isset($filter['ddf']) && $filter['ddf'] == 1) {
                            $drilldown[] = $filterName;
                        }
                    }
                    else {
                        $drilldown[] = $filterName;
                    }
                }
            }
        }

        $f =  $this->config->getFilters();
        if (isset($f)) {
            foreach ( $f as $filter ) {
                if ( in_array($filter->name, $drilldown)) {
                    $filter->ddf = true;
                }
                $filters[] = $filter;
            }
        }

        $output .= '

            <script type="text/javascript">
            <!--//--><![CDATA[//><!--
                (function(global,$,GD) {
                    var options = {
                        "autodraw": true,
                        "filterContainer": "#int-ui-container",
                        "breadcrumbContainer": "#breadcrumbContainer",
                        "filters": ' . json_encode($filters) . ',
                        "breadcrumbs": ' . json_encode(BreadcrumbFactory::parseBreadcrumbs($this->config)) . ',
                        "dashboard": ' . $this->config->id . ',
                        "public": ' . (isset($this->options['public'])?'true':'false') . ',
                        "host": "' . GOVDASH_HOST . '",
                        "csrf": "' . drupal_get_token('services') . '"
                    };
                    var GD_Int = new GD.Int(options);
                    GD_Int.run();
                    if (GD_Int.filters.length <= 0) {
                        $("#filterContainer").hide();
                    }
                    if (GD_Int.breadcrumbs.length <= 1) {
                        $("#breadcrumbContainer").hide();
                    }
                })(typeof window === "undefined" ? this : window, jQuery,GD);
                //--><!]]>
            </script>

        ';
    }

}