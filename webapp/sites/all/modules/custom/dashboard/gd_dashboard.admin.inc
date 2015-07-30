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


function gd_dashboard_admin_settings_form ( $form, &$form_state ) {
    $settings = gd_dashboard_get_settings();

    $form['gd_dashboard_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Global Dashboard Settings')
    );

    $form['gd_dashboard_settings']['public'] = array(
        '#type' => 'checkbox',
        '#title' => t('Allow public dashboards.'),
        '#description' => t('Allows individual dashboards to be viewable by anonymous users.'),
        '#default_value' => $settings['public']
    );
    $form['gd_dashboard_settings']['print_options'] = array(
        '#type' => 'fieldset',
        '#title' => 'Print Options'
    );

    $form['gd_dashboard_settings']['print_options']['print'] = array(
        '#type' => 'checkbox',
        '#title' => t('Allow dashboard printing.'),
        '#default_value' => $settings['print']
    );

    $form['gd_dashboard_settings']['export_options'] = array(
        '#type' => 'fieldset',
        '#title' => 'Export Options'
    );

    $form['gd_dashboard_settings']['export_options']['export'] = array(
        '#type' => 'checkbox',
        '#title' => t('Allow dashboard exporting.'),
        '#default_value' => $settings['export']
    );

    $form['gd_dashboard_settings']['export_options']['export_tool_path'] = array(
        '#type' => 'textfield',
        '#title' => t('Export tool path'),
        '#default_value' => $settings['export_tool_path']
    );

    $form['gd_dashboard_settings']['export_options']['export_log_path'] = array(
        '#type' => 'textfield',
        '#title' => t('Export error log path'),
        '#default_value' => $settings['export_log_path']
    );

    $form['gd_dashboard_settings']['export_options']['export_header_path'] = array(
        '#type' => 'textfield',
        '#title' => t('HTML header path'),
        '#default_value' => $settings['export_header_path'],
        '#description' => t('Path begins at drupal root')
    );

    $form['gd_dashboard_settings']['export_options']['export_footer_path'] = array(
        '#type' => 'textfield',
        '#title' => t('HTML footer path'),
        '#default_value' => $settings['export_footer_path'],
        '#description' => t('Path begins at drupal root')
    );

    $form['gd_dashboard_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_dashboard_admin_settings_form_submit ( $form, &$form_state ) {
    $config = array(
        'public' => $form_state['values']['public'],
        'export' => $form_state['values']['export'],
        'print' => $form_state['values']['print'],
        'export_tool_path' => $form_state['values']['export_tool_path'],
        'export_log_path' => $form_state['values']['export_log_path'],
        'export_header_path' => $form_state['values']['export_header_path'],
        'export_footer_path' => $form_state['values']['export_footer_path']
    );

    variable_set('gd_dashboard_config',$config);

    drupal_set_message('Configuration saved successfully.');
}
