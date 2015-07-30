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

function template_preprocess_sparkline_theme ( &$vars ) {
    $rows = $vars['rows'];

    $columns = $vars['ReportConfig']->options['column_details'];
    $config = $vars['ReportConfig']->options['config'];
    $visual = $vars['ReportConfig']->options['visual'];

    $yaxis_columns = array();
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
            $series[$col->name][] = $row[$col->name];
        }
    }

    $sparklineType = empty($visual['sparklineType']) ? 'line' : $visual['sparklineType'];

    //Workaround for PIE, CASE:if only single value present.
    if($sparklineType == 'pie'){
        $series = sparkline_pie_single_value_workaround($series);
    }

    $vars['data'] = $series;
    $vars['config'] = $config;
    $sparklineOptions = array('type' => $sparklineType);

    $sparklineOptions['width'] = "auto";
    //  GOVDB-2044: For some reason sparkline auto height is 0
    $sparklineOptions['height'] = ($sparklineType == 'pie' ? (float) 18 : (float) 16);

    if(isset($visual['width']) && $visual['width']=='Fixed' && !empty($visual['widthValue'])) {
        $sparklineOptions['width'] = (float) $visual['widthValue'];
    }
    if(isset($visual['height']) && $visual['height']=='Fixed' && !empty($visual['heightValue'])) {
        $sparklineOptions['height'] = (float) $visual['heightValue'];
    }

    if($sparklineType == 'line') {
        if(!empty($visual['lineColorScheme'])) {
            $sparklineOptions['lineColor'] = $visual['lineColorScheme'];
        }
       if(isset($visual['lineWidth']) && $visual['lineWidth'] ==  'Fixed' && !empty($visual['lineWidthValue'])) {
            $sparklineOptions['lineWidth'] = (float) $visual['lineWidthValue'];
        }
       if(isset($visual['spotSize']) && $visual['spotSize'] ==  'Fixed' && !empty($visual['spotSizeValue'])) {
            $sparklineOptions['spotRadius'] = (float) $visual['spotSizeValue'];
        }
    } else if($sparklineType == 'bar') {
        if(!empty($visual['positiveColorScheme'])) {
            $sparklineOptions['barColor'] = $visual['positiveColorScheme'];
        }
        if(!empty($visual['negativeColorScheme'])) {
            $sparklineOptions['negBarColor'] = $visual['negativeColorScheme'];
        }
        if(isset($visual['barWidth']) && $visual['barWidth'] ==  'Fixed' && !empty($visual['barWidthValue'])) {
            $sparklineOptions['barWidth'] = (float) $visual['barWidthValue'];
        }
        if(isset($visual['barSpacing']) && $visual['barSpacing'] ==  'Fixed' && !empty($visual['barSpacingValue'])) {
            $sparklineOptions['barSpacing'] = (float) $visual['barSpacingValue'];
        }
    }

    $datasourceName = $vars['ReportConfig']->getDatasource();
    if (!isset($datasourceName)) {
        $datasourceName = gd_datasource_get_active();
    }
    $vars['draft'] = gd_datasource_is_property($datasourceName, 'draft');
    $vars['sparklineOptions'] = json_encode($sparklineOptions);
}

//Hack for sparkline PIE. If only one row present then add an empty row value.
function sparkline_pie_single_value_workaround($series)
{
    foreach($series as $key=>$value)
    {
        //check if only one item in series, if yes add one more item if value "0"
        if(sizeof($value)<=1)
        {
            array_push($value,0);
            $series[$key]=$value;
        }
    }
    return $series;
}