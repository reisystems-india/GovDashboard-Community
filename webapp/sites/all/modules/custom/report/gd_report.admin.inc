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


function gd_report_admin_settings_form ( $form, &$form_state ) {
    $config = variable_get('gd_report_config', array());

    $form['gd_report_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Global Report Settings')
    );

    $form['gd_report_settings']['geocode_options'] = array(
        '#type' => 'fieldset',
        '#title' => 'Geocode Options'
    );

    $form['gd_report_settings']['geocode_options']['geocode_url'] = array(
        '#type' => 'textfield',
        '#title' => t('Geocode URL'),
        '#required' => TRUE,
        '#default_value' => isset($config['geocode_url']) ? $config['geocode_url'] : "54.164.132.179"
    );

    $form['gd_report_settings']['geocode_options']['geocode_key'] = array(
        '#type' => 'textfield',
        '#title' => t('Geocode API Key'),
        '#default_value' => isset($config['geocode_key']) ? $config['geocode_key'] : ''
    );

    $form['gd_report_settings']['export'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle export functionality for reports.'),
        '#default_value' => isset($config['export']) ? $config['export'] : 0
    );

    $form['gd_report_settings']['export_options'] = array(
        '#type' => 'fieldset',
        '#title' => 'Export Options'
    );

    $form['gd_report_settings']['export_options']['csv'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle export CSV functionality for reports.'),
        '#default_value' => isset($config['csv']) ? $config['csv'] : 0
    );

    $form['gd_report_settings']['export_options']['csv_raw'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle export CSV RAW functionality for reports.'),
        '#default_value' => isset($config['csv_raw']) ? $config['csv_raw'] : 0
    );

    $form['gd_report_settings']['export_options']['excel'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle export Excel functionality for reports.'),
        '#default_value' => isset($config['xls']) ? $config['xls'] : 0
    );

    $form['gd_report_settings']['export_options']['excel_raw'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle export Excel RAW functionality for reports.'),
        '#default_value' => isset($config['xls_raw']) ? $config['xls_raw'] : 0
    );

    $form['gd_report_settings']['export_options']['pdf'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle export PDF functionality for reports.'),
        '#default_value' => isset($config['pdf']) ? $config['pdf'] : 0
    );

    $form['gd_report_settings']['print'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle print functionality for reports.'),
        '#default_value' => isset($config['print']) ? $config['print'] : 0
    );

    $form['gd_report_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_report_admin_settings_form_submit ( $form, &$form_state ) {
    $config = array(
        'export' => $form_state['values']['export'],
        'csv' => $form_state['values']['csv'],
        'csv_raw' => $form_state['values']['csv_raw'],
        'xls' => $form_state['values']['excel'],
        'xls_raw' => $form_state['values']['excel_raw'],
        'pdf' => $form_state['values']['pdf'],
        'print' => $form_state['values']['print'],
        'geocode_url' => $form_state['values']['geocode_url'],
        'geocode_key' => $form_state['values']['geocode_key'],
    );

    variable_set('gd_report_config',$config);

    drupal_set_message('Configuration saved successfully.');
}
