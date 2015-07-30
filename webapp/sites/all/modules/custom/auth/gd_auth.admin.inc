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


function gd_auth_admin_settings () {

    $form['gd_auth_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Authentication Options'),
        '#description' => 'These settings only affect external dashboard operations as of now.  '
    );

    $form['gd_auth_settings']['authenticator'] = array(
        '#type' => 'select',
        '#title' => 'Authenticator',
        '#default_value' => variable_get('gd_auth_authenticator',GD_AUTH_DEFAULT_AUTHENTICATOR),
        '#options'=> drupal_map_assoc(array_merge(array(GD_AUTH_DEFAULT_AUTHENTICATOR),array_keys(gd_auth_get_registered_authenticators()))),
        '#required' => true
    );

    $form['gd_auth_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    $form['#validate'][] = 'gd_auth_admin_settings_validate';
    $form['#submit'][] = 'gd_auth_admin_settings_submit';

    return $form;
}

function gd_auth_admin_settings_validate ( $form, &$form_state ) {
    if ( $form_state['values']['authenticator'] == '' ) {
        form_set_error('authenticator', t('You must select an authenticator.'));
    }
}

function gd_auth_admin_settings_submit ( $form, &$form_state ) {
    variable_set('gd_auth_authenticator',$form_state['values']['authenticator']);
}