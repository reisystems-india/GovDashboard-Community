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


function template_preprocess_highcharts_theme ( &$vars ) {
    $ReportConfig = $vars['ReportConfig'];
    $options = $vars['options'];
    $rows = $vars['rows'];

    $config = $ReportConfig->options['config'];
    $columns = $ReportConfig->options['column_details'];
    $visual = $ReportConfig->options['visual'];
    $visual['datalabels'] = array();

    $columnConfigs = array();

    foreach ( $columns as $key => $column ) {
        $c = $ReportConfig->getColumn($column->name);
        if ( isset($c) ) {
            $columnConfigs[$column->name] = array();
            $columnConfigs[$column->name]['id'] = $key;
            $columnConfigs[$column->name]['displayName'] = $column->publicName;
            $columnConfigs[$column->name]['columnId'] = $column->name;
            $columnConfigs[$column->name]['formatter'] = array();
            $columnConfigs[$column->name]['formatter']['format'] = $column->type->applicationType;
            $columnConfigs[$column->name]['formatter']['scale'] = $column->type->scale;
            $columnConfigs[$column->name]['columnType'] = $column->type->applicationType;
            $columnConfigs[$column->name]['datasetConfig'] = false;
        }
    }

    foreach ($ReportConfig->options['column_configs'] as $config) {
        foreach ($columns as $column) {
            if ( $column->name == $config['columnId'] ) {
                $columnConfigs[$config['columnId']] = $config;
                break;
            }
        }
    }

    $categories = array();
    $series = array();

    $fontSizeTitle = array('Small' => '12px', 'Medium' => '16px', 'Large' => '22px');
    $fontSizeAxisTitles = array('Small' => '12px', 'Medium' => '16px', 'Large' => '22px');
    $fontSizeAxisLabels = array('Small' => '10px', 'Medium' => '12px', 'Large' => '14px');
    $fontSizeLegend = array('Small' => '10px', 'Medium' => '12px', 'Large' => '14px');

    $columnLinks = array();

    if ( !empty($visual['displayXAxisLabel']) ) {
        $visual['displayXAxisLabel'] = 'true';
    } else {
        $visual['displayXAxisLabel'] = 'false';
    }

    if ( !isset($visual['displayGridLines']) ) {
        $visual['gridLineWidth'] = 1;
    } else {
        $visual['gridLineWidth'] = $visual['displayGridLines'];
    }

    if ( !isset($visual['displayBorder']) ) {
        $config['borderWidth'] = 0;
    } else {
        $config['borderWidth'] = $visual['displayBorder'];
    }

    if ( !isset($visual['displayLegend']) || !empty($visual['displayLegend']) ) {
        $visual['displayLegend'] = 'true';
    } else {
        $visual['displayLegend'] = 'false';
    }

    if ( !empty($visual['displaySeries']) ) {
        $visual['displaySeries'] = true;
    } else {
        $visual['displaySeries'] = false;
    }

    if ( !empty($visual['displayTooltip']) && $visual['displayTooltip'] == 'none' ) {
        $visual['showTooltip'] = 'false';
    } else {
        $visual['showTooltip'] = 'true';
    }

    if ( !isset($visual['stack']) ) {
        $visual['stack'] = false;
    }

    if ( !isset($visual['labelRotation']) ) {
        $visual['labelRotation'] = -45;
    }

    if ( $visual['labelRotation'] == 0 ) {
        $visual['xaxisAlign'] = 'center';
    } else if ( $visual['labelRotation'] > 0 ) {
        $visual['xaxisAlign'] = 'left';
    } else {
        $visual['xaxisAlign'] = 'right';
    }

    if ( empty($config['title']) ) {
        $config['title'] = '';
    }

    if ( empty($config['chartType']) ) {
        $config['chartType'] = $ReportConfig->options['config']['chartType'];
    }

    if ( $config['chartType'] == 'bar' ) {
        $visual['xaxisAlign'] = 'right'; //force this to align = 'right' for bar charts for proper label display.
        $visual['yaxisRotation'] = -45; //force this to -45 for bar charts for readable y-Axis display (this is default for column graphs for x-Axis).
        $visual['spacingRight'] = 80; //force this to 80 to allow for multi-axis display to stay in bounds mostly.
    }

    if ( empty($visual['fontSize']) ) {
        $visual['fontSize'] = 'Medium';
    }

    if ( empty($visual['minNumericSpan']) ) {
        $visual['minNumericSpan'] = 'Auto';
    }

    if ( empty($visual['maxNumericSpan']) ) {
        $visual['maxNumericSpan'] = 'Auto';
    }

    if ( empty($visual['tickInterval']) ) {
        $visual['tickInterval'] = 'Auto';
    }

    $visual['displayPercentStack'] = !empty($visual['displayPercentStack']);
    $postProcessforPercentages = $visual['displayPercentStack'] && !$visual['stack'];
    if ($postProcessforPercentages) {
        $percent_categories = array();
    }

    // chart title
    $config['chartTitle'] = " ";
    if ( !isset($visual['displayChartTitle']) || !empty($visual['displayChartTitle']) ) {
        $config['chartTitle'] = str_replace('"', '\\"', $config['title']);
    }

    // pie chart display options
    $config['pie'] = array();
    /*if ( !isset($visual['displayPieSliceLabels']) || !empty($visual['displayPieSliceLabels']) ) {
        $visual['displayPieSliceLabels'] = 'true';
    } else */if ( !isset($visual['displayPieSliceLabels']) || empty($visual['displayPieSliceLabels']) ) {
        $visual['displayPieSliceLabels'] = 'false';
    }

    $config['pie']['enableDataLabels'] = $visual['displayPieSliceLabels'];
    $config['pie']['dataLabelsOptions'] = (isset($visual['displayPieSliceLabelsOptions'])) ? $visual['displayPieSliceLabelsOptions'] : null;

    if ( !isset($visual['displayPieSliceBorders']) || !empty($visual['displayPieSliceBorders']) ) {
        $visual['displayPieSliceBorders'] = true;
    } else if ( empty($visual['displayPieSliceBorders']) ) {
        $visual['displayPieSliceBorders'] = false;
    }

    if ( !$visual['displayPieSliceBorders'] ) {
        $config['pie']['borderWidth'] = 0;
    }

    if ( !isset($visual['displayPieShadow']) || !empty($visual['displayPieShadow']) ) {
        $visual['displayPieShadow'] = 'true';
    } else if ( empty($visual['displayPieShadow']) ) {
        $visual['displayPieShadow'] = 'false';
    }

    $config['pie']['shadow'] = $visual['displayPieShadow'];

    if ( count($columns) > 1 ) {

        $config['pie']['dataLabelsFontSize'] = $fontSizeAxisLabels[$visual['fontSize']];

        // get x and y axis and column order

        list($xaxis_columns,$yaxis_columns) = highcharts_get_xy_axes($columns,$ReportConfig->getColumnOrder());

        /*
        $column_positions = highcharts_get_column_order($columns,$ReportConfig);
        $yaxis_columns = $column_positions['yaxis'];
        $xaxis_columns = $column_positions['xaxis'];
        */


        if ( count($xaxis_columns) == 1 && ($xaxis_columns[0]->type->applicationType == DateDataTypeHandler::DATA_TYPE || $xaxis_columns[0]->type->applicationType == DateTimeDataTypeHandler::DATA_TYPE) ) {

            if ( $config['chartType'] == 'pie' ) {
                $config['xaxis']['type'] = 'linear';
            } else {
                $config['xaxis']['type'] = 'datetime';
            }

        } else {
            $config['xaxis']['type'] = 'linear';
        }

        $xcolNames = array();
        if ($postProcessforPercentages) {
            $xcolNames[] = $xaxis_columns[0]->publicName;
        } else {
            foreach ( $xaxis_columns as $col ) {
                $xcolNames[] = $col->publicName;
            }
        }

        //---------------------------------------------------------------------
        // X-AXIS TOOLTIP DISPLAY
        //---------------------------------------------------------------------
        $colCount = count($xaxis_columns);
        $xAxisTooltip = '';

        for ( $i = 0; $i < $colCount; $i++ ) {

            $xKey = '"' . $xaxis_columns[$i]->publicName . '"';
            foreach ( $columnConfigs as $colConfig ) {
                if ( $xaxis_columns[$i]->name == $colConfig['columnId'] ) {
                    $xKey = '"' . $colConfig['displayName'] . '"';
                }
            }

            if ( $config['xaxis']['type'] == 'datetime' ) {
                $xValFormatted = highcharts_get_date_formatter($columnConfigs, $xaxis_columns[0], 'this.point.name');
                $xAxisTooltip = $xAxisTooltip . $xKey . '+": "+' . $xValFormatted . '+';
            } else {
                $xAxisTooltip = $xAxisTooltip . $xKey . '+": "+this.point["' . $xaxis_columns[$i]->publicName . '"]+';
            }

            if ( $i < $colCount - 1 ) {
                $xAxisTooltip = $xAxisTooltip . '",<br/>"+';
            }
        }

        $xAxisTooltip = 'if(!this.x){var xVal="null";}else{var xVal=this.x;} return ' . $xAxisTooltip;

        //---------------------------------------------------------------------
        // X & Y AXIS COLUMN NAME CONFIG - REFLECT IN HIGHCHART AXIS LABELS
        //---------------------------------------------------------------------
        foreach ( $columnConfigs as $colConfig ) {

            $dispNames = array();

            if ($postProcessforPercentages) {
                if ( $xaxis_columns[0]->name == $colConfig['columnId'] ) {
                    $dispNames[$xaxis_columns[0]->publicName] = $colConfig['displayName'];
                }
            } else {
                foreach ( $xaxis_columns as $col ) {
                    if ( $col->name == $colConfig['columnId'] ) {
                        $dispNames[$col->publicName] = $colConfig['displayName'];
                    }
                }
            }

            $replacementNames = array();
            foreach ( $dispNames as $colKey => $dName ) {
                $key = array_search($colKey, $xcolNames);
                $replacementNames[$key] = $dName;
            }

            $xcolNames = array_replace($xcolNames, $replacementNames);

            //---------------------------------------------------------------------
            // Y-AXIS TOOLTIP DISPLAY
            //---------------------------------------------------------------------
            $toolTipYAxisFormattedValue = array();

            foreach ( $yaxis_columns as $yaxis_column ) {
                if ( $yaxis_column->name == $colConfig['columnId'] ) {

                    $config['yaxis']['title'][$yaxis_column->name] = $colConfig['displayName'];

                    if ( isset($colConfig['formatter']) ) {
                        $yaxisValue = 'this.value';
                        $tooltipYAxisValue = 'this.y';

                        $separator = ',';
                        $scale = isset($colConfig['formatter']['scale']) ? $colConfig['formatter']['scale'] : 'null';

                        $config['yaxis']['chartType'][$yaxis_column->name] = isset($colConfig['formatter']['chartType']) ? $colConfig['formatter']['chartType'] : $config['chartType'];

                        $prefix = '';
                        if ( !empty($colConfig['formatter']['format']) && $colConfig['formatter']['format'] === 'currency' ) {
                            $scale = ($scale == 'null') ? 2 : $scale;
                            $prefix = '$';
                        }

                        $suffix = '';
                        if ( !empty($colConfig['formatter']['format']) && ($colConfig['formatter']['format'] === 'percentage' || $colConfig['formatter']['format'] === 'percent') ) {
                            $yaxisValue = "this.value * 100";
                            $tooltipYAxisValue = "this.y * 100";
                            $suffix = ' %';
                        }

                        $config['yaxis']['formatter'][$yaxis_column->name] = "return '$prefix'+GD.Utility.numberFormat($yaxisValue, $scale, '.','$separator')+'$suffix';";

                        $toolTipYAxisFormattedValue[$yaxis_column->name] = "'$prefix'+GD.Utility.numberFormat($tooltipYAxisValue, $scale, '.','$separator')+'$suffix'";
                        if ( !empty($visual['displayTooltip']) && $visual['displayTooltip'] == 'details' ) {
                            $yAxisTooltip[$yaxis_column->name] = 'this.series["name"] + ": " + ' . $toolTipYAxisFormattedValue[$yaxis_column->name];
                            $config['tooltip'][$colConfig['columnId']] = $xAxisTooltip . '"<br/><strong>"+' . $yAxisTooltip[$yaxis_column->name] . '+"</strong>";';
                        } else {
                            $config['tooltip'][$colConfig['columnId']] = 'return ' . $toolTipYAxisFormattedValue[$yaxis_column->name] . ';';
                        }
                    }
                }
            }
        }

        $config['titleFontSize'] = $fontSizeTitle[$visual['fontSize']];

        if ( !empty($visual['displayXAxisTitle']) ) {
            if ( $visual['displayXAxisTitle'] == 'yes' ) {
                $config['xaxis']['title'] = implode(', ', $xcolNames);
            } else if ( $visual['displayXAxisTitle'] == 'other' ) {
                $config['xaxis']['title'] = $visual['displayXAxisTitleValue'];
            } else {
                $config['xaxis']['title'] = '';
            }
        } else {
            $config['xaxis']['title'] = implode(', ', $xcolNames);
        }

        $config['xaxis']['titleFontSize'] = $fontSizeAxisTitles[$visual['fontSize']];
        $config['xaxis']['labelsFontSize'] = $fontSizeAxisLabels[$visual['fontSize']];

        $config['yaxis']['titleFontSize'] = $fontSizeAxisTitles[$visual['fontSize']];
        $config['yaxis']['labelsFontSize'] = $fontSizeAxisLabels[$visual['fontSize']];

        if ( $visual['minNumericSpan'] == 'Fixed' ) {
            $config['yaxis']['min'] = $visual['minNumericSpanValue'];
        } else {
            $config['yaxis']['min'] = "null";
        }
        if ( $visual['maxNumericSpan'] == 'Fixed' ) {
            $config['yaxis']['max'] = $visual['maxNumericSpanValue'];
        } else {
            $config['yaxis']['max'] = "null";
        }
        if ( $visual['tickInterval'] == 'Custom' ) {
            $config['yaxis']['tickInterval'] = $visual['tickIntervalValue'];
        } else {
            $config['yaxis']['tickInterval'] = "null";
        }

        $minValue = array();
        $maxValue = array();
        $totalValue = array();
        // Find max value for each series
        if ( !empty($yaxis_columns) && !empty($rows) ) {
            foreach ( $yaxis_columns as $col ) {
                foreach ( $rows as $row ) {
                    if ( isset($row[$col->name]) ) {
                        // initialize min/max/total values
                        if ( !isset($minValue[$col->name]) ) {
                            $minValue[$col->name] = $row[$col->name];
                        }
                        if ( !isset($maxValue[$col->name]) ) {
                            $maxValue[$col->name] = $row[$col->name];
                        }
                        if ( !isset($totalValue[$col->name]) ) {
                            $totalValue[$col->name] = 0;
                        }

                        // check values
                        if ( $row[$col->name] > $maxValue[$col->name] ) {
                            $maxValue[$col->name] = $row[$col->name];
                        }

                        if ( $row[$col->name] < $minValue[$col->name] ) {
                            $minValue[$col->name] = $row[$col->name];
                        }

                        // add them up
                        $totalValue[$col->name] = $totalValue[$col->name] + $row[$col->name];
                    }
                }
            }
        }

        $series_data = array();

        // x-axis color
        $xcol_colors = array();
        $criteria = 0;
        foreach ( $xaxis_columns as $col ) {
            if ( isset($visual['series'][$col->name]['criteria']) ) {
                $criteria = $visual['series'][$col->name]['criteria'];
            }
            if ( isset($visual['series'][$col->name]['colorScheme']) && $visual['series'][$col->name]['colorScheme'] == 'value' ) {
                for ( $i = 1; $i < ($criteria + 1); $i++ ) {
                    if ( isset($visual['series'][$col->name]['value' . $i]) && isset($visual['series'][$col->name]['color' . $i]) ) {
                        $col_value = $visual['series'][$col->name]['value' . $i];
                        $col_color = $visual['series'][$col->name]['color' . $i];
                        if ( $col->type->applicationType == 'date' ) {
                            $xcol_colors[date('Y-m-d', strtotime($col_value))] = $col_color;
                        } else {
                            $xcol_colors[strtolower($col_value)] = $col_color;
                        }
                    }
                }
            }
        }

        if ( !empty($rows) ) {

            if ( $config['xaxis']['type'] == 'datetime' ) {
                $ascending = function($a, $b) use ($xaxis_columns) {
                    return (strtotime($a[$xaxis_columns[0]->name]) * 1000) - (strtotime($b[$xaxis_columns[0]->name]) * 1000);
                };
                usort($rows, $ascending);
                reset($rows);
            }

            foreach ( $rows as $row ) {
                // This applies links to the chart labels
                if ( !empty($ReportConfig->drilldowns) ) {
                    foreach ( $ReportConfig->drilldowns as $d ) {
                        if ( array_key_exists($d->column, $row) ) {
                            $params = array();

                            // incorporate url filters
                            if ( !empty($_GET['t']) ) {
                                $params = $_GET['t'];
                            }

                            $link_column = $ReportConfig->getColumn($d->column);
                            $column = null;

                            foreach ( $d->filters as $f ) {
                                $column = $ReportConfig->getColumnByFilterName($f);
                                if ( $column && isset($row[$column->name]) ) {
                                    $column_type = $column->type->applicationType;
                                    if ( $column_type == DateDimensionQuarterDataTypeHandler::DATA_TYPE || $column_type == DateDimensionMonthDataTypeHandler::DATA_TYPE || $column_type == DateDataTypeHandler::DATA_TYPE || $column_type == DateTimeDataTypeHandler::DATA_TYPE ) {
                                        $params[$ReportConfig->dashboard][$f] = date('m/d/Y', strtotime($row[$column->name]));
                                    } else {
                                        $params[$ReportConfig->dashboard][$f] = $row[$column->name];
                                    }
                                }
                            }

                            $query = '';
                            foreach ( $params as $dashboard_id => $filters ) {
                                foreach ( $filters as $k => $v ) {
                                    if ( is_array($v) && isset($v['o']) ) {
                                        $query .= '&t['.$d->dashboard.']['.$k.'][o]='.$v['o'];
                                        if ( is_array($v['v']) ) {
                                            foreach ( $v['v'] as $idx => $val ) {
                                                $query .= '&t['.$d->dashboard.']['.$k.'][v]['.$idx.']='.rawurlencode($val);
                                            }
                                        } else {
                                            $query .= '&t['.$d->dashboard.']['.$k.'][v]='. rawurlencode($v['v']);
                                        }
                                        $query .= '&t['.$d->dashboard.']['.$k.'][ddf]=1';
                                    }
                                    else {
                                        $query .= '&t['.$d->dashboard.']['.$k.'][o]=equal';
                                        $query .= '&t['.$d->dashboard.']['.$k.'][v][0]='.rawurlencode($v);
                                        $query .= '&t['.$d->dashboard.']['.$k.'][ddf]=1';
                                    }
                                }
                            }

                            if ( $link_column && !empty($row[$d->column]) ) {
                                $get = $_GET;

                                if (isset($get['t'])) {
                                    $oldQuery = '';
                                    foreach ($get['t'] as $dId => $filter) {
                                        $q = '&t[' . $dId . ']';
                                        foreach ($filter as $name => $values) {
                                            $q1 = $q . '[' . $name . ']';
                                            foreach ($values as $k => $v) {
                                                if (is_array($v)) {
                                                    foreach ($v as $index => $i) {
                                                        $i = rawurlencode($i);
                                                        $oldQuery .= $q1 . '[' . $k . '][' . $index . ']=' . $i;
                                                    }
                                                }
                                                else {
                                                    $v = rawurlencode($v);
                                                    $oldQuery .= $q1 . '[' . $k . ']=' . $v;
                                                }
                                            }
                                        }
                                    }
                                    $query = $oldQuery . $query;
                                }

                                // sanitize the origin for dashboard builder
                                $origin = preg_replace('/\/api\/dashboard\/data.json/', 'dashboards', $ReportConfig->origin, 1);
                                // set the dashboard id in the origin url to the drilldown dashboard id
                                $drilldown_url = preg_replace('/\/dashboard\/(\d+)/', '/dashboard/'.$d->dashboard, $origin, 1);
                                $bc = null;

                                if (isset($get['bc'])) {
                                    $bc = $get['bc'] . ',' . $d->dashboard;
                                }
                                else {
                                    $bc = $ReportConfig->dashboard .','. $d->dashboard;
                                }

                                if ( $link_column->type->applicationType != DateDataTypeHandler::DATA_TYPE && $link_column->type->applicationType != DateTimeDataTypeHandler::DATA_TYPE ) {
                                    // combine multiple x columns, this is unique to highcharts
                                    $x_col_count = count($xaxis_columns);
                                    if ( $x_col_count > 1 ) {
                                        $tmp = array();
                                        foreach ( $xaxis_columns as $xcolumn ) {
                                            $tmp[] = $row[$xcolumn->name];
                                        }
                                        $link_id = implode(', ', $tmp);
                                    } else {
                                        $link_id = $row[$link_column->name];
                                    }
                                } else {
                                    $link_id = strtotime($row[$link_column->name]) * 1000;
                                }

                                unset($get['callback']);
                                unset($get['q']);
                                unset($get['_']);
                                unset($get['t']);
                                unset($get['bc']);
                                unset($get['id']);
                                unset($get['origin']);
                                unset($get['datamart']);
                                unset($get['w']);
                                unset($get['h']);

                                $link = str_replace('amp;', '', http_build_query($get));

                                $columnLinks[$link_id] = (!empty($options['admin']) ? '/' : '') . $drilldown_url . '?id=' . $d->dashboard . $query . '&bc='. $bc . (isset($link[0]) && $link[0] != '&' ? '&' : '') . $link;
                            }
                        }
                    }
                }

                ////////////////////
                // LINEAR/PIE DATA
                if ( $config['xaxis']['type'] == 'linear' ) {
                    $parseXAxis = function () use ($xaxis_columns, $columnConfigs, $config, $row) {
                        $xcol = array();
                        $combined_cats = array();
                        for ( $i = 0, $colCount = count($xaxis_columns); $i < $colCount; $i++ ) {
                            if ( !empty($row[$xaxis_columns[$i]->name]) ) {
                                if ( highcharts_x_axis_column_is_date($xaxis_columns[$i]->type->applicationType) ) {
                                    $combined_cats[] = highcharts_get_formatted_date($columnConfigs, $xaxis_columns[$i], $row[$xaxis_columns[$i]->name]);
                                    $xcol[$xaxis_columns[$i]->publicName] = highcharts_get_formatted_date($columnConfigs, $xaxis_columns[$i], $row[$xaxis_columns[$i]->name]);
                                    $xcol['name'] = highcharts_get_formatted_date($columnConfigs, $xaxis_columns[$i], $row[$xaxis_columns[$i]->name]);
                                    if ( !empty($columnLinks[$row[$xaxis_columns[$i]->name]]) ) {
                                        $columnLinks[$xcol['name']] = $columnLinks[$row[$xaxis_columns[$i]->name]];
                                        unset($columnLinks[$row[$xaxis_columns[$i]->name]]);
                                    }
                                } else {
                                    $combined_cats[] = $row[$xaxis_columns[$i]->name];
                                    $xcol[$xaxis_columns[$i]->publicName] = $row[$xaxis_columns[$i]->name];
                                    $xcol['name'] = $row[$xaxis_columns[$i]->name];
                                }
                            } else {
                                // need to keep each indices defined, for Pie dataLabel call below
                                $combined_cats[] = ''; // replace null with empty string to avoid "String" in Pie
                                $xcol[$xaxis_columns[$i]->publicName] = null;
                            }

                        }
                        if ( $config['chartType'] == 'pie' ) {
                            $xcol['name'] = $combined_cats[0]; //only display 1st column in Pie dataLabel/legend
                        }
                        return array($xcol, $combined_cats);
                    };
                    ////////////////////////
                    // X-AXIS LABEL BUILDER
                    list($xcol, $combined_cats) = $parseXAxis();
                    if (!$postProcessforPercentages) {
                        $categories[] = implode(', ', $combined_cats);
                    } else {
                        if (array_search($combined_cats[0], $categories) === false) {
                            $categories[] = $combined_cats[0];
                        }
                        unset($combined_cats[0]);
                        $implode = implode(', ', $combined_cats);
                        $xcol['name'] = $implode;
                        if (array_search($implode, $percent_categories) === false) {
                            $percent_categories[] = $implode;
                        }
                    }

                    $formatter = array();
                    foreach ( $columnConfigs as $conf ) {
                        $formatter[$conf['columnId']] = $conf['formatter'];
                    }

                    ////////////////////////
                    // Y-AXIS
                    foreach ( $yaxis_columns as $col ) {

                        if ( !isset($row[$col->name]) ) {
                            $y_row_value = null;
                        } else {
                            $y_row_value = $row[$col->name];
                        }

                        if ( empty($formatter[$col->name]) ) {
                            if ( $col->type->applicationType == NumberDataTypeHandler::DATA_TYPE ) {
                                $formatter[$col->name]['format'] = 'number';
                            } else if ( $col->type->applicationType == IntegerDataTypeHandler::DATA_TYPE ) {
                                $formatter[$col->name]['format'] = 'number';
                            } else if ( $col->type->applicationType == CurrencyDataTypeHandler::DATA_TYPE ) {
                                $formatter[$col->name]['format'] = 'currency';
                                $formatter[$col->name]['scale'] = 2;
                            } else if ( $col->type->applicationType == PercentDataTypeHandler::DATA_TYPE ) {
                                $formatter[$col->name]['format'] = 'percent';
                            }
                        }

                        $pointData = array_merge(
                            $xcol,
                            array(
                                'y' => $y_row_value,
                                'yFormatted' => gd_report_data_formatter($y_row_value, $formatter[$col->name])
                            )
                        );

                        if ( isset($xaxis_columns[0]) ) {
                            $pointData['reference'] = $xaxis_columns[0]->name;
                        }

                        // SERIES VISUALIZATION SETTINGS
                        if ( isset($visual['series'][$col->name]['options']) ) {
                            $pointData['displayOptions'] = $visual['series'][$col->name]['options'];
                        }

                        // Y-AXIS COLOR/LINKS : Apply Color Scheme, overwright with $xcol_colors in final step
                        $pointData = getGraphDetails($pointData, $row, $col, $vars, $totalValue, $xaxis_columns, $visual, $config, $xcol_colors, $columnLinks);

                        $series_data[$col->name][] = $pointData;
                    }

                } else {
                    ////////////////////
                    // DATETIME DATA  (not MON/QTR/YR )

                    ////////////////////////
                    // X-AXIS LABEL BUILDER
                    // using default highchart functionality
                    ////////////////////////
                    if ( in_array($xaxis_columns[0]->type->applicationType, array(DateDataTypeHandler::DATA_TYPE, DateTimeDataTypeHandler::DATA_TYPE)) ) {
                        $config['xaxis']['formatter'] = highcharts_get_date_formatter($columnConfigs, $xaxis_columns[0], 'this.value');
                    }
                    // Y-AXIS
                    foreach ( $yaxis_columns as $col ) {
                        if ( !empty($row[$xaxis_columns[0]->name]) ) {
                            $pointData = array(
                                'y' => $row[$col->name],
                                // epoch time * 1000:  eg 1331028000000
                                'x' => strtotime($row[$xaxis_columns[0]->name]) * 1000,
                                'name' => getNameRowValue($row, $columnConfigs, $xaxis_columns)
                            );
                            $series_data[$col->name][] = getGraphDetails($pointData, $row, $col, $vars, $totalValue, $xaxis_columns, $visual, $config, $xcol_colors, $columnLinks);
                        }
                    }
                }
            }
        }

        $visual['dataLabels'] = array();
        $title = array();
        $name = array();
        $formatter = array();
        $i = 0;
        foreach ( $yaxis_columns as $col ) {

            $c_title = '';

            if ( isset($config['yaxis']['title'][$col->name]) ) {
                $c_title = $config['yaxis']['title'][$col->name];
                $name[] = $col->name;
            }

            // column name override
            if ( isset($visual['series'][$col->name]) ) {
                if ( !empty($visual['series'][$col->name]['options']['displayTitle']) ) {
                    if ( $visual['series'][$col->name]['options']['displayTitle'] == 'other' ) {
                        if ( !empty($visual['series'][$col->name]['options']['title']) ) {
                            $c_title = $visual['series'][$col->name]['options']['title'];
                        } else {
                            $c_title = '';
                        }
                    } else if ( $visual['series'][$col->name]['options']['displayTitle'] == 'no' ) {
                        $c_title = '';
                    }
                }
            }
            $title[] = $c_title;

            if ( isset($config['yaxis']['formatter'][$col->name]) ) {
                $formatter[] = $config['yaxis']['formatter'][$col->name];
            }

            if ( !empty($series_data) ) {
                if ( $visual['displaySeries'] ) {
                    $series_obj = array(
                        'name' => highcharts_get_formatted_label($columnConfigs, $col),
                        'data' => $series_data[$col->name],
                        'yAxis' => $i,
                        'type' => $config['yaxis']['chartType'][$col->name]
                    );
                } else {
                    $series_obj = array(
                        'name' => highcharts_get_formatted_label($columnConfigs, $col),
                        'data' => $series_data[$col->name],
                        'type' => $config['yaxis']['chartType'][$col->name]
                    );
                }
            } else {
                $series_obj = array(
                    'name' => highcharts_get_formatted_label($columnConfigs, $col),
                    'data' => null
                );
            }

            if ( isset($visual['series'][$col->name]['colorScheme'])
              && $visual['series'][$col->name]['colorScheme'] == 'standard'
              && isset($visual['series'][$col->name]['color'])
            ) {
                $series_obj['color'] = $visual['series'][$col->name]['color'];
            }

            $markersEnabled = true;
            if ( isset($visual['series'][$col->name]['options']['displayMarkers']) ) {
                $markersEnabled = $visual['series'][$col->name]['options']['displayMarkers'];
            }

            $series_obj['marker'] = array('enabled' => $markersEnabled);

            $step = false;
            if ( isset($visual['series'][$col->name]['options']['step']) ) {
                $step = $visual['series'][$col->name]['options']['step'];
            }

            $series_obj['step'] = $step;

            if ( !isset($visual['series'][$col->name]['options']['displayDataLabels']) || $visual['series'][$col->name]['options']['displayDataLabels'] != false) {
                $dataLabel = new stdClass();
                $dataLabel->series = $series_obj['name'];
                $dataLabel->display = true;
                $dataLabel->formatter = end($formatter);
                $visual['datalabels'][] = $dataLabel;
            }

            $series[] = $series_obj;
            $i++;
        }

        //Title and formatter should in same order as series data
        $config['yaxis']['seriesTitle'] = $config['yaxis']['title'];
        $config['yaxis']['title'] = $title;
        $config['yaxis']['name'] = $name;
        $config['yaxis']['formatter'] = $formatter;
        $config['legend']['fontSize'] = $fontSizeLegend[$visual['fontSize']];

        //  Post processing for stacked percentages
        if ($postProcessforPercentages) {
            //  Create index mapping for categories
            $newSeries = array();
            //  Stacked percentages won't work with multiple values
            $sery = $series[0];
            $data = $sery['data'];
            $xaxis_name = $xaxis_columns[0]->publicName;
            $series_indices = array();
            foreach($data as $d) {
                if (!array_key_exists($d['name'], $series_indices)) {
                    $s = array();
                    //  Copy over series metadata over
                    foreach($sery as $k => $v) {
                        $s[$k] = $v;
                    }
                    if(!empty($d['name'])){
                        $s['name'] = $d['name'];

                    }

                    $s['data'] = array();
                    foreach($categories as $k => $cat) {
                        $s['data'][$k] = 0;
                    }
                    $series_indices[$d['name']] = count($newSeries);
                    $newSeries[] = $s;
                }

                $newSeries[$series_indices[$d['name']]]['data'][array_search($d[$xaxis_name], $categories)] = $d['y'];
            }
            $series = $newSeries;
        }
    } else {
        $config['xaxis']['title'] = '';
        $config['yaxis']['title'] = '';
    }

    if ( empty($config['xaxis']['formatter']) ) {
        $config['xaxis']['formatter'] = 'return GD_Report' . intval($ReportConfig->getId()) . '_Common.GetLink(this.value);';
    } else {
        $config['xaxis']['formatter'] = 'return GD_Report' . intval($ReportConfig->getId()) . '_Common.GetLink(this.value,' . $config['xaxis']['formatter'] . ');';
    }

    if ( isset($visual['targetLineValue']) ){
        $config['yaxis']['targetLineFormattedValue'][0] = targetline_value_formatter($visual['targetLineValue'],$columnConfigs,$yaxis_columns[0]);
    }

    $datasourceName = $vars['ReportConfig']->getDatasource();
    if (!isset($datasourceName)) {
        $datasourceName = gd_datasource_get_active();
    }

    $vars['draft'] = gd_datasource_is_property($datasourceName, 'draft');
    $vars['config'] = $config;
    $vars['visual'] = $visual;
    $vars['data'] = json_encode($series);
    $vars['categories'] = json_encode($categories);
    $vars['columnLinks'] = $columnLinks;
}

/**
 * return formatted target line value.
 *
 * @param $value
 * @param $displayOptions
 * @param $column
 * @return null|string
 */
function targetline_value_formatter ( $value, $displayOptions, $column ) {
    $formatter = array();
    if ( isset($displayOptions[$column->name]) && !empty($displayOptions[$column->name]['formatter']) ) {
        // if column is formatted in a report.
        $formatter = $displayOptions[$column->name]['formatter'];
    } else {
        // fall back to column type
        if ( $column->type->applicationType == NumberDataTypeHandler::DATA_TYPE ) {
            $formatter['format'] = 'number';
        } else if ( $column->type->applicationType == IntegerDataTypeHandler::DATA_TYPE ) {
            $formatter['format'] = 'integer';
        } else if ( $column->type->applicationType == CurrencyDataTypeHandler::DATA_TYPE ) {
            $formatter['format'] = 'currency';
            $formatter['scale'] = 2;
        } else if ( $column->type->applicationType == PercentDataTypeHandler::DATA_TYPE ) {
            $formatter['format'] = 'percent';
        }
    }
    return gd_report_data_formatter($value,$formatter);
}

function highcharts_get_xy_axes ( $columns, $columnOrder ) {

    $xAxis = array();
    $yAxis = array();

    // column groups
    $attributes = array();
    $measures = array();

    foreach ( $columns as $column ) {
        list($elementNameSpace,$name) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($column->name);
        switch ( $elementNameSpace ) {
            case AbstractAttributeUIMetaData::NAME_SPACE:
                $xAxis[$column->name] = $column;
                $attributes[$column->name] = $column;
                break;

            //  TODO Change in next several months
            case FormulaUIMetaData::NAME_SPACE:
                if ( in_array($column->type->applicationType,array(NumberDataTypeHandler::DATA_TYPE,IntegerDataTypeHandler::DATA_TYPE,CurrencyDataTypeHandler::DATA_TYPE,PercentDataTypeHandler::DATA_TYPE)) ) {
                    $yAxis[$column->name] = $column;
                } else {
                    $xAxis[$column->name] = $column;
                }
                break;
            case AbstractMeasureUIMetaData::NAME_SPACE:
                $yAxis[$column->name] = $column;
                $measures[$column->name] = $column;
                break;

            default:
                $message = t('Unsupported UI column name space: @columnName', array('@columnName' => $column->name));
                LogHelper::log_error($message);
                throw new UnsupportedOperationException($message);
        }
    }

    // if there are no measures, build the old way
    if ( empty($measures) ) {
        $xAxis = array();
        $yAxis = array();
        foreach ( $columns as $column ) {
            if ( in_array($column->type->applicationType,array(NumberDataTypeHandler::DATA_TYPE,IntegerDataTypeHandler::DATA_TYPE,CurrencyDataTypeHandler::DATA_TYPE,PercentDataTypeHandler::DATA_TYPE)) ) {
                $yAxis[$column->name] = $column;
            } else {
                $xAxis[$column->name] = $column;
            }
        }
    }

    // honor the column order setting, swap key with column name
    $columnOrder = array_flip($columnOrder);

    uksort($xAxis,function($a,$b) use($columnOrder) {
        if ($columnOrder[$a] == $columnOrder[$b]) {
            return 0;
        }
        return ($columnOrder[$a] < $columnOrder[$b]) ? -1 : 1;
    });

    uksort($yAxis,function($a,$b) use($columnOrder) {
        if ($columnOrder[$a] == $columnOrder[$b]) {
            return 0;
        }
        return ($columnOrder[$a] < $columnOrder[$b]) ? -1 : 1;
    });

    return array(array_values($xAxis),array_values($yAxis));
}

function highcharts_x_axis_column_is_date ( $columnType ) {
    if ( $columnType == DateDataTypeHandler::DATA_TYPE
      || $columnType == DateTimeDataTypeHandler::DATA_TYPE
      || $columnType == DateDimensionMonthDataTypeHandler::DATA_TYPE
      || $columnType == DateDimensionQuarterDataTypeHandler::DATA_TYPE
    ) {
        return true;
    }

    return false;
}

function highcharts_get_date_formatter ( $columnConfigs, $column, $xValue ) {

    $xFormatter = $xValue;

    if ( $column->type->applicationType != DateDimensionQuarterDataTypeHandler::DATA_TYPE
      && $column->type->applicationType != DateDimensionMonthDataTypeHandler::DATA_TYPE
    ) {

        if ($column->type->applicationType == DateTimeDataTypeHandler::DATA_TYPE) {
            $xFormatter = 'Highcharts.dateFormat("%Y-%m-%d %I:%M:%S %p", new Date(' . $xValue . '))';
        } else {
            $xFormatter = 'Highcharts.dateFormat("%Y-%m-%d", new Date(' . $xValue . '))'; // use as default to match pie when no format is set
        }

        // apply any date formatting from report config
        foreach ( $columnConfigs as $colConfig ) { // loop through column configs
            if ( $column->name == $colConfig['columnId'] ) { // config for this column
                if ( $colConfig['columnType'] == DateDataTypeHandler::DATA_TYPE || $colConfig['columnType'] == DateTimeDataTypeHandler::DATA_TYPE ) { // if date config, apply selected formatting
                    if ( isset($colConfig['formatter']) ) {
                        if ( $colConfig['formatter']['format'] == 'ymd' ) {
                            if ($column->type->applicationType == DateTimeDataTypeHandler::DATA_TYPE) {
                                $xFormatter = 'Highcharts.dateFormat("%Y-%m-%d %I:%M:%S %p", new Date(' . $xValue . '))';
                            } else {
                                $xFormatter = 'Highcharts.dateFormat("%Y-%m-%d", new Date(' . $xValue . '))';
                            }
                        } else if ( $colConfig['formatter']['format'] == 'mdy' ) {
                            if ($column->type->applicationType == DateTimeDataTypeHandler::DATA_TYPE) {
                                $xFormatter = 'Highcharts.dateFormat("%m/%d/%Y %I:%M:%S %p", new Date(' . $xValue . '))';
                            } else {
                                $xFormatter = 'Highcharts.dateFormat("%m/%d/%Y", new Date(' . $xValue . '))';
                            }
                        }
                    }
                }
            }
        }
    }

    return $xFormatter;
}

function highcharts_get_formatted_date ( $columnConfigs, $column, $labelValue ) {

    // MONTH
    if ( $column->type->applicationType == DateDimensionMonthDataTypeHandler::DATA_TYPE ) {
        $dateFormatter['format'] = 'month';
        $formattedValue = gd_report_data_formatter($labelValue, $dateFormatter);
    } // QUARTER
    else if ( $column->type->applicationType == DateDimensionQuarterDataTypeHandler::DATA_TYPE ) {
        $dateFormatter['format'] = 'quarter';
        $formattedValue = gd_report_data_formatter($labelValue, $dateFormatter);
    } // DATE: APPLY ANY DATE FORMAT CONFIG HERE
    else {
        $dateFormatter['format'] = 'mdy'; // default date format
        $formattedValue = gd_report_data_formatter($labelValue, $dateFormatter);

        // apply any date formatting from report config
        foreach ( $columnConfigs as $colConfig ) { // loop through column configs
            if ( $column->name == $colConfig['columnId'] ) { // config for this column
                if ( $colConfig['columnType'] == DateDataTypeHandler::DATA_TYPE || $colConfig['columnType'] == DateTimeDataTypeHandler::DATA_TYPE ) { // if date config, apply selected formatting
                    if ( isset($colConfig['formatter']['format']) ) {
                        $formattedValue = gd_report_data_formatter($labelValue, $colConfig['formatter']);
                    }
                }
            }
        }
    }

    // Change NULL name to empty string to avoid "Slice" set as label in pie graph, overide default highchart behavior
    if ( is_null($formattedValue) ) {
        $formattedValue = '';
    }

    return $formattedValue;
}

/**
 * @param $config
 * @param $column
 * @return mixed
 */
function highcharts_get_formatted_label ( $config, $column ) {
    $label = $column->publicName;
    foreach ( $config as $c ) {
        if ( $column->name == $c['columnId'] ) {
            $label = $c['displayName'];
        }
    }
    return $label;
}

function getGraphDetails ( $pointData, $row, $col, $vars, $totalValue, $xaxis_columns, $visual, $config, $xcol_colors, $columnLinks ) {
    $ReportConfig = $vars['ReportConfig'];
    if ( isset($col->name) ) { //avoid php notices

        if ( !isset($row[$col->name]) || empty($totalValue[$col->name]) ) {
            $percent = null;
        } else {
            $percent = ($row[$col->name] / $totalValue[$col->name]) * 100;
        }

        // Y-AXIS COLOR
        $intervals = 0;
        $criteria = 0;
        if ( isset($visual['series'][$col->name]['intervals']) ) {
            $intervals = $visual['series'][$col->name]['intervals'];
        }

        if ( isset($visual['series'][$col->name]['criteria']) ) {
            $criteria = $visual['series'][$col->name]['criteria'];
        }

        // STANDARD (COLOR ALL)
        if ( isset($visual['series'][$col->name]['colorScheme']) && $visual['series'][$col->name]['colorScheme'] == 'standard' ) {

            if ( isset($visual['series'][$col->name]['color']) ) {
                $pointData['color'] = $visual['series'][$col->name]['color'];
            }

            // COLOR BY RANGE
        } else if ( isset($visual['series'][$col->name]['colorScheme']) && in_array($visual['series'][$col->name]['colorScheme'], array('rangePercent', 'rangeNumeric')) ) {

            for ( $i = 1; $i < ($intervals + 1); $i++ ) {
                // PERCENT RANGE
                if ( isset($visual['series'][$col->name]['rangeIntervalFrom' . $i]) && isset($visual['series'][$col->name]['rangeIntervalTo' . $i]) &&
                  isset($visual['series'][$col->name]['rangeIntervalColor' . $i]) &&
                  $percent >= $visual['series'][$col->name]['rangeIntervalFrom' . $i] &&
                  $percent <= $visual['series'][$col->name]['rangeIntervalTo' . $i] &&
                  $visual['series'][$col->name]['colorScheme'] == 'rangePercent'
                ) {
                    $pointData['color'] = $visual['series'][$col->name]['rangeIntervalColor' . $i];
                } // NUMERIC RANGE
                else if ( isset($visual['series'][$col->name]['rangeIntervalFromNumeric' . $i]) && isset($visual['series'][$col->name]['rangeIntervalToNumeric' . $i]) &&
                  isset($visual['series'][$col->name]['rangeIntervalColor' . $i]) &&
                  $row[$col->name] >= $visual['series'][$col->name]['rangeIntervalFromNumeric' . $i] &&
                  $row[$col->name] <= $visual['series'][$col->name]['rangeIntervalToNumeric' . $i] &&
                  $visual['series'][$col->name]['colorScheme'] == 'rangeNumeric'
                ) {
                    $pointData['color'] = $visual['series'][$col->name]['rangeIntervalColor' . $i];
                }
            }

            // COLOR BY VALUE
        } else if ( isset($visual['series'][$col->name]['colorScheme']) && $visual['series'][$col->name]['colorScheme'] == 'value' ) {

            for ( $i = 1; $i < ($criteria + 1); $i++ ) {
                if ( isset($visual['series'][$col->name]['value' . $i]) && is_numeric($visual['series'][$col->name]['value' . $i]) ) {
                    if ( $row[$col->name] == $visual['series'][$col->name]['value' . $i] ) {
                        $pointData['color'] = $visual['series'][$col->name]['color' . $i];
                    }
                } else if ( isset($visual['series'][$col->name]['value' . $i]) ) {
                    if ( strcasecmp($row[$col->name], $visual['series'][$col->name]['value' . $i]) == 0 ) {
                        $pointData['color'] = $visual['series'][$col->name]['color' . $i];
                    }
                }
            }

        }
    }

    // override with any x-axis color setting
    if ( isset($xaxis_columns[0]->name) ) { //avoid php notices
        if ( isset($xcol_colors[strtolower($row[$xaxis_columns[0]->name])]) ) {
            $pointData['color'] = $xcol_colors[strtolower($row[$xaxis_columns[0]->name])];
        }
    }

    // slice, column, bar color from data
    if ( !empty($visual['useColumnDataForColor']) && isset($row[$visual['useColumnDataForColor']]) ) {
        if ( strpos($row[$visual['useColumnDataForColor']],'#') === false ) {
            $pointData['color'] = '#'.$row[$visual['useColumnDataForColor']];
        } else {
            $pointData['color'] = $row[$visual['useColumnDataForColor']];
        }
    }

    // POINT DATA DRILLDOWN LINKS
    if ( !empty($ReportConfig->drilldowns) ) {
        foreach ( $ReportConfig->drilldowns as $d ) {
            if ( $d->column == $xaxis_columns[0]->name ) {

                if ( $config['xaxis']['type'] == 'linear' ) {
                    if ( isset($pointData['name']) && isset($columnLinks[$pointData['name']]) ) {
                        $pointData['url'] = $columnLinks[$pointData['name']];
                    }
                } else {
                    if ( isset($columnLinks[$pointData['x']]) ) {
                        $pointData['url'] = $columnLinks[$pointData['x']];
                    }
                }

            }
        }
    }

    return $pointData;
}

function getNameRowValue ( $row, $columnConfigs, $xaxis_columns ) {

    global $name_row_value;

    if ( is_null($row[$xaxis_columns[0]->name]) ) {
        //replace null with empty string to avoid "Slice" in Pie charts legends/datalabels
        $name_row_value = '';
    } else {
        if ( highcharts_x_axis_column_is_date($xaxis_columns[0]->type->applicationType) ) {
            $name_row_value = highcharts_get_formatted_date($columnConfigs, $xaxis_columns[0], $row[$xaxis_columns[0]->name]);
        } else {
            $name_row_value = $row[$xaxis_columns[0]->name];
        }
    }

    return $name_row_value;
}