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


function gd_datasource_page_admin_index () {

    $EnvironmentMetaModel = data_controller_get_environment_metamodel();

    $datasources = $EnvironmentMetaModel->getDataSources();

    $output = '<dl>';
    foreach ( $datasources as $ds ) {
        $output .= '<dt><a href="/admin/structure/govdashboard/datasource/ds/'.$ds->name.'">'.$ds->publicName.'</a></dt><dd><pre>'.print_r($ds,true).'</pre></dd>';
    }
    $output .= '</dl>';

    return array('datasource_list'=>array(
        '#markup' => $output
    ));
}

function gd_datasource_page_admin_retrieve ( $datasourceName ) {

    $EnvironmentMetaModel = data_controller_get_environment_metamodel();

    $DataSource = $EnvironmentMetaModel->getDataSource($datasourceName);

    $output  = '<h2>'.$DataSource->publicName.'</h2>';
    $output .= '<pre>'.print_r($DataSource,true).'</pre>';

    $output .= '<h3>Datasets</h3>';
    $output .= '<ul>';
    foreach ( gd_dataset_findall_by_datasource($datasourceName) as $dataset ) {
        $output .= '<li>'.$dataset->publicName.'</li>';
    }
    $output .= '</ul>';

    $output .= '<h3>Reports</h3>';
    $output .= '<ul>';
    foreach ( gd_report_findall_by_datasource(LOAD_ENTITY,$datasourceName) as $report ) {
        $output .= '<li>'.$report->title.'</li>';
    }
    $output .= '</ul>';

    $output .= '<h3>Dashboards</h3>';
    $output .= '<ul>';
    foreach ( gd_dashboard_findall_by_datasource(LOAD_ENTITY,$datasourceName) as $dashboard ) {
        $output .= '<li>'.$dashboard->title.'</li>';
    }
    $output .= '</ul>';


    return array('datasource_info'=>array(
        '#markup' => $output
    ));
}