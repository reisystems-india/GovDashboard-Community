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

// $Id$

function template_preprocess_gauge_theme ( &$vars ) {
    $rows = $vars['ReportConfig']->getData();

    if ( empty($rows) ) {
        throw new ServicesException('No Data');
    }
    else {
        $config = $vars['ReportConfig']->options['config'];
        $columns = $vars['ReportConfig']->options['column_details'];

        $yaxis_columns = array();
        $series = array();

        foreach ( $columns as $col ) {
            if ( $col->type->applicationType == NumberDataTypeHandler::DATA_TYPE
                || $col->type->applicationType == IntegerDataTypeHandler::DATA_TYPE
                || $col->type->applicationType == CurrencyDataTypeHandler::DATA_TYPE
                || $col->type->applicationType == PercentDataTypeHandler::DATA_TYPE
               ) {
                $yaxis_columns[] = $col;
            }
        }

        foreach ( $rows as $row ) {
            foreach ( $yaxis_columns as $col ) {
                $value = $row[$col->name];

                //  Apply default formatting
                $formatted_value = gd_report_data_formatter($value, array('format' => $col->type->applicationType,'scale'=>(isset($col->scale) ? $col->scale : null)));

                // get any formatting for the column
                if ( !empty($vars['ReportConfig']->options['column_configs']) ) {
                    foreach ( $vars['ReportConfig']->options['column_configs'] as $colConfig ) {
                        if ( $colConfig['columnId'] == $col->name) {
                            $formatted_value = gd_report_data_formatter($value,$colConfig['formatter']);
                        }
                    }
                }

                if ( $col->type->applicationType == PercentDataTypeHandler::DATA_TYPE ) {
                    $value = $value*100;
                }

                $series[$col->name][] = $value;
                $series['formatted_value'][$col->name] = $formatted_value;
            }
        }

        $datasourceName = $vars['ReportConfig']->getDatasource();
        if (!isset($datasourceName)) {
            $datasourceName = gd_datasource_get_active();
        }
        $vars['draft'] = gd_datasource_is_property($datasourceName, 'draft');
        $vars['config'] = $config;
        $vars['data'] = $series;
    }
}