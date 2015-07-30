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

//TODO: "highcharts_get_column_order" function is defined in highcharts.them.inc.
//TODO: Including this file adds indirect dependency on highcharts.
include_once(drupal_get_path('module', 'gd_report')."/theme/highcharts.theme.inc");

class GD_ReportView {

    private $ReportConfig = null;

    public function __construct ( GD_ReportConfig $ReportConfig ) {
        $this->ReportConfig = $ReportConfig;
    }

    public function getView ( array $options = array() ) {
        $view = new stdClass();
        $view->header = $this->getHeader();
        $view->body = $this->getBody($options);
        $view->body .= $this->getFilterOverlay();
        $view->footer = $this->getFooter();
        return $view;
    }

    public function getWarningView (array $options = array()) {
        $view = new stdClass();
        $view->header = $this->getHeader();
        $view->body = $this->getWarningBody($options) . $this->getFilterOverlay();
        $view->footer = $this->getFooter();
        return $view;
    }

    public function getWarningBody(array $options = array()) {

        $body = '<div class="report-container" id="reportId-' . intval($this->ReportConfig->getId()) .'" class="report">';

        $messages = '<li>The report is configured incorrectly</li><li>The report is using a column that was removed from the system</li>';

        if (isset($options['error'])) {
            $messages .= '<li>' . $options['error'] . '</li>';
            if (isset($_SESSION['messages'])) {
                if (isset($_SESSION['messages']['error'])) {
                    foreach ($_SESSION['messages']['error'] as $error) {
                        $messages .= '<li>' . $error . '</li>';
                        LogHelper::log_error($error);
                    }
                }
                unset($_SESSION['messages']);
            }
        }

        $body .= '<div class="alert alert-warning alert-dismissible" role="alert" style="text-align: left;"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4>The report could not be rendered!</h4>Possible causes may include any of the following:<ol>'.$messages.'</ol></div>';


        return $body.'</div>';
    }

    public function getBody ( array $options = array() ) {

        $emptyResultMessage = '<div class="alert alert-warning alert-dismissible" role="alert" style="text-align: left;"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4>There are no records to display!</h4>Possible causes may include any of the following:<ol><li>The dataset used for this report is empty</li><li>There are no records that support the report settings</li><li>There are no records that match an applied filter</li></ol></div>';
        $invalidConfigMessage = '<div class="alert alert-warning alert-dismissible" role="alert" style="text-align: left;"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4>The report could not be rendered!</h4>Possible causes may include any of the following:<ol><li>The report is configured incorrectly</li><li>The report is using a column that was removed from the system</li></ol></div>';
        $tooManyRecordsMessage = '<div class="alert alert-warning alert-dismissible" role="alert" style="text-align: left;"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button><h4>The report could not be rendered!</h4>More than one record returned. Please add filters to reduce the number of records to one.</div>';

        switch ( $this->ReportConfig->getDisplayType() ) {

            case 'table' :
                //  Table already handles no data by itself
                return GD_WidgetFactory::getView($this->ReportConfig, $options);
                break;

            case 'dynamic_text' :
                $rows = $this->ReportConfig->getData();
                if ( count($rows) < 1 ) {
                    return _gd_report_get_formatted_error_text($emptyResultMessage);
                } else {
                    list($fields,$formattedData,$errors) = array_values(gd_report_format_data($this->ReportConfig, $rows));
                    return gd_report_render_dynamic_text($this->ReportConfig, $formattedData, $fields);
                }
                break;

            case 'customview' :
                //  Report designer is in charge of handling empty reports
                return GD_WidgetFactory::getView($this->ReportConfig, $options);
                break;

            case 'sparkline' :
                $rows = $this->ReportConfig->getData();
                if ( count($rows) < 1 ) {
                    return _gd_report_get_formatted_error_text($emptyResultMessage);
                } else {
                    return theme('sparkline_theme', array('ReportConfig' => $this->ReportConfig, 'rows' => $rows, 'options' => $options));
                }
                break;

            case 'gauge' :
                if ( ($this->ReportConfig->getNumericColumnCount() != 1 && $this->ReportConfig->getMeasureCount() != 1) || $this->ReportConfig->getNonNumericColumnCount() ) {
                    return _gd_report_get_formatted_error_text($invalidConfigMessage);
                }
                $rows = $this->ReportConfig->getData();
                if ( count($rows) < 1 ) {
                    return _gd_report_get_formatted_error_text($emptyResultMessage);
                } else {
                    return gd_report_render_gauge($this->ReportConfig, $rows);
                }
                break;

            case 'pie' :
                if ( ($this->ReportConfig->getNumericColumnCount() != 1 && $this->ReportConfig->getMeasureCount() != 1) || !$this->ReportConfig->getNonNumericColumnCount() ) {
                    return _gd_report_get_formatted_error_text($invalidConfigMessage);
                }
                $rows = $this->ReportConfig->getData();
                if ( count($rows) < 1 ) {
                    return _gd_report_get_formatted_error_text($emptyResultMessage);
                } else {
                    return theme('highcharts_theme', array('ReportConfig' => $this->ReportConfig, 'rows' => $rows, 'options' => $options));
                }
                break;

            case 'map' :
                return gd_report_render_map($this->ReportConfig);
                break;

            case 'pivot_table':
                if ( !$this->ReportConfig->getMeasureCount() ) {
                    return _gd_report_get_formatted_error_text($invalidConfigMessage);
                }
                return theme('pivot_table_theme', array('ReportConfig' => $this->ReportConfig, 'options' => $options));
                break;

            default:
                if ( (!$this->ReportConfig->getNumericColumnCount() && !$this->ReportConfig->getMeasureCount()) || !$this->ReportConfig->getNonNumericColumnCount() ) {
                    return _gd_report_get_formatted_error_text($invalidConfigMessage);
                }
                $rows = $this->ReportConfig->getData();
                if ( count($rows) < 1 ) {
                    return _gd_report_get_formatted_error_text($emptyResultMessage);
                } else {
                    return theme('highcharts_theme', array('ReportConfig' => $this->ReportConfig, 'rows' => $rows, 'options' => $options));
                }
                break;
        }
    }

    public function getHeader () {
        $header = '<div id="gd-report-header-'.intval($this->ReportConfig->getId()).'"';

        if ( $this->ReportConfig->showMenu() ) {
            $header .= ' style="display:inline-block; width:100%;">';
            $header .= $this->getMenu();
        } else {
            $header .= '>';
        }

        if ( $this->ReportConfig->showTitle() ) {
            $size = $this->ReportConfig->getDisplaySize();
            $header .= '<h3 class="gd_report_title"';
            //GOVDB-1934 - Update table header with target line value
            if (isset($this->ReportConfig->options['config']['chartType']) &&
                isset($this->ReportConfig->options['visual']['targetLine']) &&
                $this->ReportConfig->options['config']['chartType'] == "table" &&
                $this->ReportConfig->options['visual']['targetLine'] == true){

                $header .= '>'.$this->ReportConfig->title.'</h3>';
                //get correct column positions. To determine primary column
                $column_positions = highcharts_get_xy_axes($this->ReportConfig->options['column_details'],$this->ReportConfig->getColumnOrder());
                $columnConfigs = array();
                //create column config array in a format required by targetline_value_formatter method
                $yaxis_columns = (isset($column_positions[1])) ? $column_positions[1] : array();
                foreach($this->ReportConfig->options['column_configs'] as $col){
                    $columnConfigs[$col['columnId']] = $col;
                }

                //get formatted target line value
                //GOVDB-2023 - First check column value from Configure option then check visualize
                if(isset($this->ReportConfig->options['visual']['targetLineValue'])){
                    $target_line_display_value = targetline_value_formatter($this->ReportConfig->options['visual']['targetLineValue'], $columnConfigs, $yaxis_columns[0]);
                    if(!empty($columnConfigs[$yaxis_columns[0]->name]['displayName'])){
                        $header .= '<div class="gd_report_sub_title">(Target value for '.$columnConfigs[$yaxis_columns[0]->name]['displayName'].
                            ' is '.$target_line_display_value.")</div>";
                    }else if (!empty($yaxis_columns[0]->publicName)){
                        $header .= '<div class="gd_report_sub_title">(Target value for '.$yaxis_columns[0]->publicName.
                            ' is '.$target_line_display_value.")</div>";
                    } else{
                        $header .= '<div class="gd_report_sub_title">(Target value for is '.$target_line_display_value.")</div>";
                    }
                }
            } else {
                if (isset($size)) {
                    $header .= ' style="width:' . ($size->width - 75) .'px;">';
                } else {
                    $header .= '>';
                }

                $header .= $this->ReportConfig->title.'</h3>';
            }
        }
        $header .= '</div>';
        return $header;
    }

    public function getFooter () {
        $data = new stdClass();
        $data->config = $this->ReportConfig;
        $data->footer_contents = '';
        $reportId = intval($this->ReportConfig->getId());

        if(!empty($this->ReportConfig->options['visual']['footer'])) {
            foreach($this->ReportConfig->options['visual']['footer'] as $footer) {
                $cols = $this->ReportConfig->columns;
                $offset = $this->ReportConfig->getOffset();
                $sort = $this->ReportConfig->sort;
                $colorColumn = !empty($this->ReportConfig->options['visual']['useColumnDataForColor']) ? $this->ReportConfig->options['visual']['useColumnDataForColor'] : NULL;
                $this->ReportConfig->options['visual']['useColumnDataForColor'] = NULL;
                $this->ReportConfig->setColumns(array($footer['measure']));
                $this->ReportConfig->sort = NULL;
                $this->ReportConfig->setOffset(0);
                $format = array(
                    array(
                        'formatter' => array(
                            'format' => isset($footer['format']) ? $footer['format'] : 'number',
                            'scale' => isset($footer['scale']) ? $footer['scale'] : 0
                        ),
                        'displayName' => $footer['text'],
                        'columnId' => $footer['measure'],
                    )
                );
                $this->ReportConfig->setColumnConfigs($format);
                $value = $this->ReportConfig->getData();
                list($fields, $formatted, $errors) = array_values(gd_report_format_data($this->ReportConfig, $value));
                $fontSize = intval(!empty($footer['size']) ? $footer['size'] : '14');
                $styles = 'text-align:' . (!empty($footer['alignment']) ? $footer['alignment'] : 'right') . ';color: ' . (!empty($footer['color']) ? $footer['color'] . ' !important' : '#000080 !important') . ';font-size: ' . $fontSize . 'px;font-weight: bold;';
                $data->footer_contents .= '<span class="gd-footer-measure" style="' . $styles . '">' . $footer['text'] . ': ' . $formatted[0]->record[$footer['measure']] . '</span>';
                $this->ReportConfig->options['visual']['useColumnDataForColor'] = $colorColumn;
                $this->ReportConfig->setColumns($cols);
                $this->ReportConfig->sort = $sort;
                $this->ReportConfig->setOffset($offset);
            }
        }

        drupal_alter('gd_report_view_footer', $data);

        //  TODO Move to new report rendering JavaScript
        //  Set timeout is necessary for Dashboard Builder.
        //  Not entirely certain but pretty sure .html() of the DashboardCanvas caused main UI thread of browser to stall and not calculated heights until after .html() call is over
        //  Which means when the script below is ran by jQuery inside .html() call, elements without specified heights like footer are 0
        $footerScript = '<script type="text/javascript">!function(global) { !function($,undefined) {
            setTimeout(function() {
                var height = $("#report-' . $reportId . '").height();
                var footerHeight = $("#gd-report-footer-' . $reportId . '").height();
                $("#report-' . $reportId . '").height((height - footerHeight));
                $("#report-' . $reportId . '").trigger("ready.report.render");
            }, 1);
            }(global.GD_jQuery ? global.GD_jQuery : jQuery);
        }(!window ? this : window);</script>';

        return '<div id="gd-report-footer-'.$reportId.'" report_id="'.$reportId.'" class="gd-report-footer">' . $data->footer_contents . '</div>'.$footerScript;
    }

    public function getFilterOverlay() {
        $showOverlay = ($this->ReportConfig->showFilterOverlay() ? '' : ' hide');
        $script = '<script type="text/javascript">!function(global) { !function($,undefined) {';
        $script .= 'setTimeout(function() {';
        $script .= 'var reportID = "#gd-report-header-' . intval($this->ReportConfig->getId()) . '";';
        $script .= 'if (!$(reportID).find("div.filter-overlay").length) {';
        $script .= '$(reportID).append(\'<div class="filter-overlay' . ($showOverlay) .'"><span data-container="body" data-toggle="popover" class="glyphicon glyphicon-filter"></span></div>\');';
        $script .= 'var filters = [];';
        $filters = $this->ReportConfig->getUsedFilters();
        foreach($filters as $k => $filter) {
            if (isset($filter->operator)) {
                $value = isset($filter->value) ? json_encode(array($filter->value)) : "null";
                $script .= 'filters.push({name: "' . $filter->name . '", operator: "' . $filter->operator . '", value: ' . $value . ', type: "' . $filter->type . '"});';
            }
        }

        $title = isset($this->ReportConfig->title) ? ($this->ReportConfig->title . ': ') : ("Report " . intval($this->ReportConfig->getId()) . ": ");
        $script .= 'global.GD.Filter.renderOverlay(reportID, filters, "' . $title .'");}},100) }(global.GD_jQuery ? global.GD_jQuery : jQuery); }(!window ? this : window);</script>';
        return $script;
    }

    public function getMenu () {
        $config = variable_get('gd_report_config', array('export' => 0, 'print' => 0));

        $menuAttributes = array(
            'csv' => ((!isset($config['csv']))?0:$config['csv']),
            'csv_raw' => ((!isset($config['csv_raw']))?0:$config['csv_raw']),
            'excel' => ((!isset($config['xls']))?0:$config['xls']),
            'excel_raw' => ((!isset($config['xls_raw']))?0:$config['xls_raw']),
            'pdf' => ((!isset($config['pdf']))?0:$config['pdf']),
            'export' => ((!isset($config['export']))?0:$config['export']),
            'print' => ((!isset($config['print']))?0:$config['print']),
            'id' => 'gd-report-top-menu-'.intval($this->ReportConfig->getId()),
            'class' => 'gd-report-top-menu'
        );

        $attributesMarkup = '';
        foreach ( $menuAttributes as $k => $v ) {
            $attributesMarkup .= $k.'="'.$v.'" ';
        }

        return  '<div class="gd-report-menu-container" id="gd-report-menu-container-'.intval($this->ReportConfig->getId()).'">
                    <ul '.$attributesMarkup.'>
                        <li class="gd-report-top-menu-root"
                            id="gd-report-top-menu-root-'.intval($this->ReportConfig->getId()).'"
                            report_id="'.intval($this->ReportConfig->getId()).'"
                            edit="'.$this->ReportConfig->canEdit().'"
                            chartType="'.$this->ReportConfig->getDisplayType().'">
                            <a tabindex="'.GD_REPORT_TABINDEX.'" class="gd-report-top-menu-link clear" id="gd-report-top-menu-link-'.intval($this->ReportConfig->getId()).'">
                                <img alt="Menu Options" class="gd-report-menu-root-icon" src="'. GOVDASH_HOST . '/sites/all/modules/custom/gd/images/report_menu/cog.png"/>
                                <span class="gd-report-menu-root-arrow">&nbsp;</span>
                            </a>
                        </li>
                    </ul>
                </div>';
    }
}
