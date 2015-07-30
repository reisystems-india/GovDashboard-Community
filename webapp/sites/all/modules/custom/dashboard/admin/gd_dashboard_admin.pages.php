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
 * @return array
 */
function gd_dashboard_admin_page_index () {
    $datasource = gd_datasource_find($_GET['ds']);
    if ( !$datasource ) {
        return MENU_NOT_FOUND;
    }

    gd_datasource_set_active($datasource->name);

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_library('gd_dashboard_admin', 'GD_Admin_DashboardSection_Index');
    return gd_admin_page_default();
}

/**
 * @param $dashboardId
 * @return array
 */
function gd_dashboard_admin_page_edit ( $dashboardId ) {
    $dashboardNode = gd_dashboard_load($dashboardId);
    if ( !$dashboardNode ) {
        return MENU_NOT_FOUND;
    }

    gd_datasource_set_active(get_node_field_value($dashboardNode,'field_dashboard_datasource'));

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_library('gd_dashboard_admin', 'GD_Admin_DashboardSection_Builder');
    drupal_add_js(drupal_get_path('module','gd_dashboard_admin').'/js/builder/button/action/DashboardDeleteButton.js');

    drupal_add_library('gd','datatables');
    drupal_add_library('gd','highcharts');
    drupal_add_js('sites/all/libraries/sparkline/jquery.sparkline.min.js');

    $options = array('fields'=>array('filters','drilldowns','reports','css'));
    $dashboard = gd_dashboard_create_api_object_from_node($dashboardNode,$options);

    drupal_add_http_header('Cache-Control','no-cache, max-age=0, must-revalidate, no-store');

    return gd_dashboard_admin_page($dashboard);
}

/**
 * @return array
 */
function gd_dashboard_admin_page_new() {
    $datasource = gd_datasource_find($_GET['ds']);
    if ( !$datasource ) {
        return MENU_NOT_FOUND;
    }

    gd_datasource_set_active($datasource->name);

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_library('gd_dashboard_admin', 'GD_Admin_DashboardSection_Builder');

    drupal_add_library('gd','datatables');
    drupal_add_library('gd','highcharts');
    drupal_add_js('sites/all/libraries/sparkline/jquery.sparkline.min.js');

    return gd_dashboard_admin_page();
}

/**
 * @param null $dashboard
 * @return array
 */
function gd_dashboard_admin_page ( $dashboard = NULL ) {
    return array(
        '#show_messages' => FALSE,
        '#type' => 'page',
        '#theme' => 'page__cp',
        'content' => array(
            'system_main' => array(
                '#markup' => theme(
                    'gd_dashboard_admin_layout',
                    array(
                        'dashboard' => $dashboard,
                        'update' => !empty($dashboard->id),
                        'config' => gd_dashboard_get_settings()
                    )
                )
            )
        )
    );
}