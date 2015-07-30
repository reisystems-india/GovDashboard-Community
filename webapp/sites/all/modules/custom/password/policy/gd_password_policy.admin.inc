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


function gd_password_policy_admin_settings_form() {
    $config = gd_password_policy_get_config();

    $form['gd_password_policy_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Global Password Policy Configurations')
    );

    $form['gd_password_policy_settings']['enabled_cron'] = array(
        '#type' => 'checkbox',
        '#title' => t('Toggle cron job (whether or not check for password expiration).'),
        '#default_value' => isset($config['enabled_cron']) ? $config['enabled_cron'] : 1
    );

    $form['gd_password_policy_settings']['password_rules'] = array(
        '#type' => 'fieldset',
        '#title' => t('Password Rule Configurations')
    );

    $form['gd_password_policy_settings']['password_rules']['length'] = array(
        '#type' => 'select',
        '#title' => t('Minimum length for passwords.'),
        '#required' => true,
        '#options' => range(6, 128),
        '#default_value' => isset($config['rules']['length']) ? ($config['rules']['length'] - 6) : 6
    );

    $form['gd_password_policy_settings']['password_rules']['upper'] = array(
        '#type' => 'select',
        '#title' => t('Minimum amount of upper case characters for passwords.'),
        '#required' => true,
        '#options' => range(0, 128),
        '#default_value' => isset($config['rules']['upper']) ? ($config['rules']['upper']) : 1
    );

    $form['gd_password_policy_settings']['password_rules']['lower'] = array(
        '#type' => 'select',
        '#title' => t('Minimum amount of lower case characters for passwords.'),
        '#required' => true,
        '#options' => range(0, 128),
        '#default_value' => isset($config['rules']['lower']) ? ($config['rules']['lower']) : 1
    );

    $form['gd_password_policy_settings']['password_rules']['number'] = array(
        '#type' => 'select',
        '#title' => t('Minimum amount of numbers for passwords.'),
        '#required' => true,
        '#options' => range(0, 128),
        '#default_value' => isset($config['rules']['number']) ? ($config['rules']['number']) : 1
    );

    $form['gd_password_policy_settings']['password_rules']['symbols'] = array(
        '#type' => 'select',
        '#title' => t('Minimum amount of special characters for passwords.'),
        '#required' => true,
        '#options' => range(0, 128),
        '#default_value' => isset($config['rules']['symbols']) ? ($config['rules']['symbols']) : 1
    );

    $form['gd_password_policy_settings']['password_rules']['unique'] = array(
        '#type' => 'select',
        '#title' => t('Number of passwords to remember (password uniqueness).'),
        '#required' => true,
        '#options' => range(0, 100),
        '#default_value' => isset($config['rules']['unique']) ? ($config['rules']['unique']) : 5
    );

    $form['gd_password_policy_settings']['password_expiration'] = array(
        '#type' => 'fieldset',
        '#states' => array(
            'invisible' => array(
                ':input[name="enabled_cron"]' => array('checked' => FALSE),
            ),
        ),
        '#title' => t('Password Expiration Configurations')
    );

    $form['gd_password_policy_settings']['password_expiration']['duration'] = array(
        '#type' => 'select',
        '#title' => t('Number of days before password expiration.'),
        '#required' => true,
        '#options' => range(1, 365),
        '#default_value' => isset($config['expiration']['duration']) ? ($config['expiration']['duration'] - 1) : 45
    );

    $form['gd_password_policy_settings']['test'] = array(
        '#type' => 'fieldset',
        '#title' => t('Test password policy functionality')
    );

    $users = entity_load('user');
    $options = array();
    foreach ($users as $user) {
        if ($user->uid != 0) {
            $options[$user->uid] = $user->fullname;
        }
    }

    $form['gd_password_policy_settings']['test']['test_user'] = array(
        '#type' => 'select',
        '#title' => t('The user to test against.'),
        '#options' => $options,
        '#default_value' => reset($options),
    );

    $form['gd_password_policy_settings']['test']['test_warning'] = array(
        '#type' => 'submit',
        '#value' => t('Test Password Policy'),
        '#submit' => array('gd_password_policy_test_submit')
    );

    $form['gd_password_policy_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_password_policy_test_submit($form, &$form_state) {
    $config = gd_password_policy_get_config();
    if (!$config['enabled_cron']) return;

    $record = db_select('gd_password_record', 'pp')
        ->fields('pp')
        ->condition('uid', $form_state['values']['test_user'], '=')
        ->orderBy('created', 'DESC')
        ->execute()
        ->fetchAssoc();

    $currentTime = time();
    $expiration = $config['expiration']['duration'] * 86400;
    if ($record['warned'] != 1) {
        $warned = $record['warned'];
        $countdown = 1209600;
        if ($warned == 3) {
            //  7 days warning
            $countdown = 604800;
        } else if ($warned == 2) {
            //  1 day warning
            $countdown = 86400;
        }
        db_update('gd_password_record')
            ->fields(array('created' => $currentTime + $countdown - $expiration - 100))
            ->condition('pid', $record['pid'], '=')
            ->execute();

        drupal_set_message('User warned: ' . ($countdown == 1209600 ? 14 : ($countdown == 604800 ? 7 : 1)) . ' Day Warning');
    } else {
        db_update('gd_password_record')
            ->fields(array('created' => $currentTime - $expiration - 100, 'warned' => null))
            ->condition('pid', $record['pid'], '=')
            ->execute();

        drupal_set_message('User password expiration notified');
    }

    gd_password_policy_cron($form_state['values']['test_user']);
}

function gd_password_policy_admin_settings_form_submit ( $form, &$form_state ) {
    $config = array(
        'enabled_cron' => $form_state['values']['enabled_cron'],
        'expiration' => array(
            'duration' => ($form_state['values']['duration'] + 1)
        ),
        'rules' => array(
            'length' => ($form_state['values']['length'] + 6),
            'upper' => $form_state['values']['upper'],
            'lower' => $form_state['values']['lower'],
            'number' => $form_state['values']['number'],
            'symbols' => $form_state['values']['symbols'],
            'unique' => $form_state['values']['unique'],
        ),
    );

    variable_set('gd_password_policy_config',$config);

    drupal_set_message('Configurations saved successfully.');
}