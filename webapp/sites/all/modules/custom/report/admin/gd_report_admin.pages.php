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
 * @return array|int
 * @throws Exception
 */
function gd_report_admin_page_index () {
    $datasource = gd_datasource_find($_GET['ds']);
    if ( !$datasource ) {
        return MENU_NOT_FOUND;
    }

    gd_datasource_set_active($datasource->name);

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_library('gd_report_admin', 'GD_Admin_ReportSection_Index');
    return gd_admin_page_default();
}

/**
 * @param $reportId
 * @return array
 * @throws Exception
 * @throws IllegalArgumentException
 */
function gd_report_admin_page_edit ( $reportId ) {
    $reportNode = gd_report_load($reportId);
    if ( !$reportNode ) {
        return MENU_NOT_FOUND;
    }

    gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_library('gd_report_admin', 'GD_Admin_ReportSection_Builder');

    $options = array('fields' => array('datasource', 'config', 'filters', 'data', 'customview', 'tags'));
    $report = gd_report_create_api_object_from_node($reportNode,$options);

    $reportDataset = gd_data_controller_ui_metadata_get_dataset_ui_metadata($report->config->model->datasets[0],array_slice($report->config->model->datasets,1));

    drupal_add_http_header('Cache-Control','no-cache, max-age=0, must-revalidate, no-store');

    return gd_report_admin_page($report,$reportDataset);
}

/**
 * @return array|int
 * @throws Exception
 */
function gd_report_admin_page_new() {
    $datasource = gd_datasource_find($_GET['ds']);
    if ( !$datasource ) {
        return MENU_NOT_FOUND;
    }

    gd_datasource_set_active($datasource->name);

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_library('gd_report_admin', 'GD_Admin_ReportSection_Builder');

    if ( !empty($_GET['title']) ) {
        $report = new stdClass();
        $report->title = check_plain($_GET['title']);
    } else {
        $report = null;
    }

    if ( !empty($_GET['dataset']) ) {
        $reportDataset = gd_data_controller_ui_metadata_get_dataset_ui_metadata($_GET['dataset']);
    } else {
        $reportDataset = null;
    }

    return gd_report_admin_page($report,$reportDataset);
}

/**
 * @param null $report
 * @param null $reportDataset
 * @return array
 * @throws Exception
 */
function gd_report_admin_page ( $report = NULL, $reportDataset = NULL ) {
    return array(
        '#show_messages' => FALSE,
        '#type' => 'page',
        '#theme' => 'page__cp',
        'content' => array(
            'system_main' => array(
                '#markup' => theme(
                    'gd_report_admin_layout',
                    array(
                        'report' => $report,
                        'update' => !empty($report->id),
                        'settings' => gd_report_get_settings(),
                        'reportDataset' => $reportDataset
                    )
                )
            )
        )
    );
}
