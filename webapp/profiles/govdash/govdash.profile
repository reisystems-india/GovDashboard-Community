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
 * Implements hook_form_FORM_ID_alter().
 *
 * Allows the profile to alter the site configuration form.
 *
 * Not supposed to use system here..  but no other way currently.
 */
function system_form_install_settings_form_alter(&$form, $form_state) {

    $form['settings']['mysql']['advanced_options']['collation'] = array(
        '#type' => 'textfield',
        '#title' => st('Default Collation'),
        '#default_value' => empty($database['collation']) ? 'utf8_unicode_ci' : $database['collation'],
        '#size' => 45,
        // The maximum port number is 65536, 5 digits.
        '#maxlength' => 45,
        '#description' => st('Enter default collation.'),
    );

    $form['#validate'][] = 'govdash_install_form_validate_database';
}

function govdash_install_form_validate_database ( $form, &$form_state ) {

    $database = $form_state['storage']['database'];
    $errors = array();

    // Verify the database name prefix.
    if (!empty($database['prefix']) && is_string($database['prefix']) && !preg_match('/^[A-Za-z0-9_]+$/', $database['prefix'])) {
      $errors[$database['driver'] . '][advanced_options][db_prefix'] = st('The database table prefix you have entered, %prefix, is invalid. The table prefix can only contain alphanumeric characters or underscores.', array('%prefix' => $database['prefix']));
    }

    // Verify the database name.
    if (!empty($database['database']) && is_string($database['database']) && !preg_match('/^[A-Za-z0-9_]+$/', $database['database'])) {
      $errors[$database['driver'] . '][database'] = st('The database name you have entered, %name, is invalid. The database name can only contain alphanumeric characters or underscores.', array('%name' => $database['database']));
    }

    foreach ( $errors as $name => $message ) {
      form_set_error($name, $message);
    }
}


/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Allows the profile to alter the site configuration form.
 */
function govdash_form_install_configure_form_alter(&$form, $form_state) {
  // Pre-populate the site name with the server name.
  $form['site_information']['site_name']['#default_value'] = 'GovDashboard';
  $form['server_settings']['site_default_country']['#default_value'] = 'US';
  $form['server_settings']['date_default_timezone']['#default_value'] = 'America/New_York';
}


/**
 * Implements hook_install_tasks_alter().
 */
function govdash_install_tasks_alter ( &$tasks, $install_state ) {
    global $databases;

    // TODO probably worst place to put this, please find alternative.
    if ( !empty($databases) && isset($databases['default']) && isset($databases['default']['default']) &&  isset($databases['default']['default']['database']) ) {
        $database_name = $databases['default']['default']['database'];
        if ( !empty($database_name) && is_string($database_name) && !preg_match('/^[A-Za-z0-9_]+$/', $database_name) ) {
            throw new Exception('Database name can only consist of alphanumeric and underscore characters.');
        }
    }

    // Preselect the English language, so users can skip the language selection
    // form. This is required for data controller.
    if (empty($_GET['locale'])) {
        $_POST['locale'] = 'en';
    }
}
