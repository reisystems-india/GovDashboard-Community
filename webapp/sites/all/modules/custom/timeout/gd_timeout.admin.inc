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


function gd_timeout_admin_settings_form() {
    $config = variable_get('gd_timeout_config', array('session_limit' => 30, 'countdown_timer' => 30));

    $form['gd_timeout_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Global Session Timeout Settings')
    );

    $form['gd_timeout_settings']['session_limit'] = array(
        '#type' => 'textfield',
        '#title' => t('Session Timeout'),
        '#description' => t('The number of minutes between activities before session timeout.'),
        '#required' => true,
        '#default_value' => !empty($config['session_limit']) ? $config['session_limit'] : 30
    );

    $form['gd_timeout_settings']['countdown_timer'] = array(
        '#type' => 'textfield',
        '#title' => t('Countdown Timer'),
        '#description' => t('The number of seconds after idling before user is forcibly logged off.'),
        '#required' => true,
        '#default_value' => !empty($config['countdown_timer']) ? $config['countdown_timer'] : 30
    );

    $form['gd_timeout_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_timeout_admin_settings_form_validate($form, &$form_state) {
    if ($form_state['values']['countdown_timer'] == '') {
        form_set_error('name', t('You must enter a countdown timer.'));
    }

    if ($form_state['values']['countdown_timer'] < 5) {
        form_set_error('name', t('You must enter a countdown timer greater than 5 seconds.'));
    }
    if ($form_state['values']['countdown_timer'] > 60) {
        form_set_error('name', t('You must enter a countdown timer less than 60 seconds.'));
    }

    if ($form_state['values']['session_limit'] == '') {
        form_set_error('Session Limit', t('You must enter a session limit.'));
    }

    if ($form_state['values']['session_limit'] < .5) {
        form_set_error('Session Limit', t('You must enter a session limit greater than to half a minute.'));
    }

    if ($form_state['values']['session_limit'] > 60) {
        form_set_error('Session Limit', t('You must enter a session limit less than to 60 minutes.'));
    }
}

function gd_timeout_admin_settings_form_submit ( $form, &$form_state ) {
    $config = array(
        'countdown_timer' => $form_state['values']['countdown_timer'],
        'session_limit' => $form_state['values']['session_limit']
    );

    variable_set('gd_timeout_config',$config);

    drupal_set_message('Config saved successfully.');
}