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


function gd_notification_admin_settings_form() {
    $config = gd_notification_get_config();

    $form['gd_notification_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Global Notification Configurations')
    );

    $form['gd_notification_settings']['toggle'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle notifications.'),
        '#default_value' => isset($config['notifications']) ? $config['notifications'] : 1
    );

    $form['gd_notification_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_notification_admin_settings_form_submit ( $form, &$form_state ) {
    $config = array(
        'notifications' => $form_state['values']['toggle']
    );

    variable_set('gd_notification_config',$config);

    drupal_set_message('Configurations saved successfully.');
}
