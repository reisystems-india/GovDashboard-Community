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

function template_preprocess_pivot_table_theme ( &$vars ) {
    if ( isset($vars['ReportConfig']->dashboard) ) {
        $reportSize = $vars['ReportConfig']->getDisplaySize();
        $vars['height'] = $reportSize->height;
        $vars['width'] = $reportSize->width;
    } else if ( isset($_GET['h']) ) {
        $vars['height'] = $_GET['h'];
        $vars['width'] = $_GET['w'];
    }

    if ( !isset($vars['ReportConfig']->options['visual']['displayChartTitle']) || is_null($vars['ReportConfig']->options['visual']['displayChartTitle']) || $vars['ReportConfig']->options['visual']['displayChartTitle']==true ) {
        $vars['height'] -= GD_REPORT_TITLE_HEIGHT;
    }

    $datasourceName = $vars['ReportConfig']->getDatasource();
    if (!isset($datasourceName)) {
        $datasourceName = gd_datasource_get_active();
    }

    $columns = array();
    $vars['measures'] = new stdClass();

    foreach ($vars['ReportConfig']->options['column_details'] as $column_detail) {
        list($elementNameSpace,$name) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($column_detail->name);
        $displayName = $column_detail->publicName;
        foreach ($vars['ReportConfig']->options['column_configs'] as $column_config) {
            if ($column_config['columnId'] === $column_detail->name) {
                $displayName = $column_config['displayName'];
                break;
            }
        }
        if ($elementNameSpace === AbstractMeasureUIMetaData::NAME_SPACE || ($elementNameSpace === FormulaUIMetaData::NAME_SPACE && pivot_table_is_formula_measure($column_detail))) {
            $fullName = $column_detail->name;
            $vars['measures']->$fullName = new stdClass();
            $vars['measures']->$fullName->name = $displayName;
        } else {
            $columns[$column_detail->name] = $displayName;
        }
    }

    if (!empty($vars['ReportConfig']->options['visual']['rows'])) {
        $vars['rows'] = $vars['ReportConfig']->options['visual']['rows'];
    }

    if (!empty($vars['ReportConfig']->options['visual']['cols'])) {
        $vars['cols'] = $vars['ReportConfig']->options['visual']['cols'];
    }

    $vars['pvtTableDisableDrag'] = isset($vars['ReportConfig']->options['visual']['pvtTableDisableDrag']) ? $vars['ReportConfig']->options['visual']['pvtTableDisableDrag'] : "true";

    if (isset($vars['options']['external'])) {
        $url = GOVDASH_HOST .'/gd/ext/dashboard/' . $vars['ReportConfig']->dashboard . '/data';
        $vars['external'] = TRUE;
    } else if (isset($vars['options']['public'])) {
        $url = '/public/dashboard/'.$vars['ReportConfig']->dashboard.'/data';
        $vars['public'] = TRUE;
    } else {
        if ( !isset($vars['ReportConfig']->dashboard) ) {
            $vars['report'] = TRUE;
            $vars['admin'] = TRUE;
            $url = '/api/report/data.json';
        } else {
            $vars['dashboard'] = TRUE;
            if (isset($vars['options']['admin'])) {
                $vars['admin'] = TRUE;
                $url = '/api/dashboard/data.json';
            } else {
                $url = '/api/dashboard/'.$vars['ReportConfig']->dashboard.'/data.json';
            }
        }
    }

    $vars['url'] = $url;
    $vars['columns'] = $columns;
    $vars['draft'] = gd_datasource_is_property($datasourceName, 'draft');
}

function pivot_table_is_formula_measure($column) {
    $isMeasure = false;

    $expression = $column->source;
    if (isset($expression)) {
        $isMeasure = strrpos(strtolower($expression), 'avg(') !== FALSE || strpos(strtolower($expression), 'count(') !== FALSE || strpos(strtolower($expression), 'sum(') !== FALSE || strpos(strtolower($expression), 'max(') !== FALSE || strpos(strtolower($expression), 'min(') !== FALSE;
    }

    return $isMeasure;
}