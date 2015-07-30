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


function gd_user_admin_settings_form() {
    $config = gd_user_get_config();

    $form['gd_user_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Global User Configurations')
    );

    $form['gd_user_settings']['notify'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle global notifications.'),
        '#default_value' => isset($config['notify']) ? $config['notify'] : 1
    );

    $form['gd_user_settings']['enabled_cron'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle cron job (whether or not check for user inactivity).'),
        '#default_value' => isset($config['enabled_cron']) ? $config['enabled_cron'] : 1
    );

    $form['gd_user_settings']['user_expiration'] = array(
        '#type' => 'fieldset',
        '#states' => array(
            'invisible' => array(
                ':input[name="enabled_cron"]' => array('checked' => FALSE),
            ),
        ),
        '#title' => t('User Inactivity Configurations')
    );

    $form['gd_user_settings']['user_expiration']['duration'] = array(
        '#type' => 'select',
        '#title' => t('Number of days before user is declared inactive.'),
        '#required' => true,
        '#options' => range(1, 90),
        '#default_value' => isset($config['expiration']['duration']) ? ($config['expiration']['duration'] - 1) : 30
    );

    $form['gd_user_settings']['notifications'] = array(
        '#type' => 'fieldset',
        '#states' => array(
            'invisible' => array(
                ':input[name="enabled_cron"]' => array('checked' => FALSE),
            ),
        ),
        '#title' => t('Notification Configurations')
    );

    $form['gd_user_settings']['notifications']['user_reminder'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle user reminder notifications.'),
        '#default_value' => isset($config['notifications']['user_reminder']) ? $config['notifications']['user_reminder'] : 1
    );

    $form['gd_user_settings']['notifications']['user_reminder_countdown'] = array(
        '#type' => 'select',
        '#title' => t('Number of days before user inactivity to send reminder.'),
        '#options' => range(1, 30),
        '#states' => array(
            'invisible' => array(
                ':input[name="user_reminder"]' => array('checked' => FALSE),
            ),
        ),
        '#default_value' => isset($config['notifications']['user_reminder_countdown']) ? ($config['notifications']['user_reminder_countdown'] - 1) : 1
    );

    $form['gd_user_settings']['test'] = array(
        '#type' => 'fieldset',
        '#title' => t('Test user inactivity functionality')
    );

    $users = entity_load('user');
    $options = array();
    foreach ($users as $user) {
        if ($user->uid != 0) {
            $options[$user->uid] = $user->fullname;
        }
    }

    $form['gd_user_settings']['test']['test_user'] = array(
        '#type' => 'select',
        '#title' => t('The user to test against.'),
        '#options' => $options,
        '#default_value' => reset($options),
    );

    $form['gd_user_settings']['test']['test_warning'] = array(
        '#type' => 'submit',
        '#value' => t('Test user inactivity warning'),
        '#submit' => array('gd_user_test_warning_submit')
    );

    $form['gd_user_settings']['test']['test_expiration'] = array(
        '#type' => 'submit',
        '#value' => t('Test user inactivity  expiration'),
        '#submit' => array('gd_user_test_expiration_submit')
    );

    $form['gd_user_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_user_test_warning_submit($form, &$form_state) {
    gd_user_inactive_warn_user($form_state['values']['test_user']);
    drupal_set_message('User warned about inactivity.');
}

function gd_user_test_expiration_submit($form, &$form_state) {
    gd_user_inactive($form_state['values']['test_user']);
    drupal_set_message('User has been marked as inactive.');
}

function gd_user_admin_settings_form_submit($form, &$form_state) {
    $config = array(
        'notify' => $form_state['values']['notify'],
        'enabled_cron' => $form_state['values']['enabled_cron'],
        'expiration' => array(
            'duration' => ($form_state['values']['duration'] + 1)
        ),
        'notifications' => array(
            'user_reminder' => $form_state['values']['user_reminder']
        )
    );

    if (!empty($form_state['values']['user_reminder'])) {
        $config['notifications']['user_reminder_countdown'] = $form_state['values']['user_reminder_countdown'] + 1;
    }

    variable_set('gd_user_config',$config);

    drupal_set_message('Configurations saved successfully.');
}
