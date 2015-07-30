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


function gd_adfs_admin_settings_form ( $form, &$form_state ) {

    $config = variable_get('gd_adfs_config', array('authSource', 'requiredGroup', 'dsMappings', 'roleMappings', 'returnUrl', 'autoCreate'));

    $form['gd_adfs_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('ADFS Integration Config')
    );

    $form['gd_adfs_settings']['autoCreate'] = array(
        '#type' => 'checkbox',
        '#title' => t('Automatically create new user'),
        '#default_value' => isset($config['autoCreate']) ? $config['autoCreate'] : 1
    );

    $form['gd_adfs_settings']['authSource'] = array(
        '#type' => 'textfield',
        '#title' => t('Service Provider'),
        '#description' => t('The name of the authentication source to use from authsources.php'),
        '#required' => true,
        '#default_value' => isset($config['authSource']) ? $config['authSource'] : ''
    );

    $form['gd_adfs_settings']['requiredGroups'] = array(
        '#type' => 'textfield',
        '#title' => t('Required Groups'),
        '#description' => t('The required ADFS groups needed to grant access.'),
        '#default_value' => isset($config['requiredGroups']) ? implode(',', $config['requiredGroups']) : ''
    );

    $form['gd_adfs_settings']['mappings'] = array(
        '#type' => 'fieldset',
        '#multiple' => true,
        '#title' => t('Mappings'),
        '#description' => 'Map ADFS groups to GovDB roles'
    );

    $form['gd_adfs_settings']['mappings']['datasource'] = array(
        '#type' => 'textfield',
        '#title' => t('Datasource Mappings'),
        '#maxlength' => 1028,
        '#size'=> 200,
        '#description' => t('The mapping configuration for default datasources. Comma separated values using format "ADFS Group:Datasource"'),
        '#required' => true,
        '#default_value' => isset($config['dsMappings']) ? gd_adfs_implode_mappings($config['dsMappings']) : ''
    );

    $form['gd_adfs_settings']['mappings']['roles'] = array(
        '#type' => 'textfield',
        '#title' => t('Role Mappings'),
        '#description' => t('The mapping configuration for ADFS groups to GovDB roles. Comma separated values using format "ADFS Group:GovDB Role"'),
        '#maxlength' => 1028,
        '#size'=> 200,
        '#required' => true,
        '#default_value' => isset($config['roleMappings']) ? gd_adfs_implode_mappings($config['roleMappings']) : ''
    );

    $form['gd_adfs_settings']['returnUrl'] = array(
        '#type' => 'textfield',
        '#title' => t('Return URL'),
        '#description' => t('The URL to return the user to after logout process is finished.'),
        '#size'=> 200,
        '#required' => true,
        '#default_value' => isset($config['returnUrl']) ? $config['returnUrl'] : ''
    );

    $form['gd_adfs_settings']['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save')
    );

    return $form;
}

function gd_adfs_admin_settings_form_submit ( $form, &$form_state ) {
    $config = array(
        'autoCreate' => $form_state['values']['autoCreate'],
        'authSource' => $form_state['values']['authSource'],
        'requiredGroups' => !empty($form_state['values']['requiredGroups']) ? explode(',', $form_state['values']['requiredGroups']) : array(),
        'dsMappings' => gd_adfs_explode_mappings($form_state['values']['datasource']),
        'roleMappings' => gd_adfs_explode_mappings($form_state['values']['roles']),
        'returnUrl' => $form_state['values']['returnUrl'],

    );

    variable_set('gd_adfs_config',$config);

    drupal_set_message('Config saved successfully.');
}

function gd_adfs_implode_mappings($mappings) {
    $map = array();
    foreach ($mappings as $k => $v) {
        if (strpos($k,':') !== FALSE) {
            $k = '"' . $k . '"';
        }
        foreach ($v as $w) {
            if (strpos($w,':') !== FALSE) {
                $w = '"' . $w . '"';
            }
            $map[] = $k . ':' . $w;
        }
    }

    return implode(',', $map);
}

function gd_adfs_explode_mappings($value) {
    $mappings = array();
    $exploded = explode(',', $value);
    foreach ($exploded as $mapping) {
        $mapping = explode(':', $mapping);
        for($i = 0; $i < count($mapping); $i++) {
            if ($mapping[$i][0] == '"') {
                for($j = $i + 1; $j < count($mapping); $j++) {
                    $mapping[$i] .= ':' . $mapping[$j];
                    if ($mapping[$i][strlen($mapping[$i]) - 1] == '"') {
                        break;
                    }
                }
                array_splice($mapping, $i + 1, $j - $i);
            }
        }
        $mapping[0] = trim($mapping[0], '"');
        $mapping[1] = trim($mapping[1], '"');

        if (!isset($mappings[$mapping[0]])) {
            $mappings[$mapping[0]] = array($mapping[1]);
        } else {
            $mappings[$mapping[0]][] = $mapping[1];
        }
    }

    return $mappings;
}