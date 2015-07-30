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

function gd_cache_admin_settings() {
    $form['gd_cache_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Page Auto-Caching'),
        '#description' => 'These settings affect performance of page load'
    );

    $form['gd_cache_settings']['resources'] = array(
        '#type' => 'textarea',
        '#title' => 'Resources',
        '#description' => 'Record each resource in separate line',
        '#default_value' => variable_get(VARIABLE_NAME__CACHE_URL),
        '#required' => false
    );

    $form['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save Settings')
    );

    $form['#validate'][] = 'gd_cache_admin_settings_validate';
    $form['#submit'][] = 'gd_cache_admin_settings_submit';

    return $form;
}

function gd_cache_admin_settings_validate($form, &$form_state) {
    $text = $form_state['values']['resources'];

    $resourceIds = isset($text) ? gd_cache_parse_resources($text): NULL;
    if (isset($resourceIds)) {
        foreach ($resourceIds as $resourceId) {
            $index = strpos($resourceId, 'http');
            if ($index === 0) {
                form_set_error('resources', t('%url should NOT contain schema, host name or port', array('%url' => $resourceId)));
                break;
            }
            if ($resourceId[0] != '/') {
                form_set_error('resources', t('%url should start with /', array('%url' => $resourceId)));
                break;
            }
        }
    }
}

function gd_cache_admin_settings_submit($form, &$form_state) {
    $text = StringHelper::trim($form_state['values']['resources']);

    $resourceIds = isset($text) ? gd_cache_parse_resources($text): NULL;

    $storageValue = isset($resourceIds) ? implode("\n", $resourceIds) : NULL;
    if (isset($storageValue)) {
        variable_set(VARIABLE_NAME__CACHE_URL, $storageValue);
        $message = t('@count URLs saved', array('@count' => count($resourceIds)));
    }
    else {
        variable_del(VARIABLE_NAME__CACHE_URL);
        $message = t('List of URLs is cleared');
    }
    drupal_set_message($message);
}
