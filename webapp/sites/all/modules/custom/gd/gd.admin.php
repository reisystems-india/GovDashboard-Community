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


function gd_performance_admin_settings () {

    $form['gd_file_upload_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('File Upload'),
        '#description' => 'These settings affect performance of file upload'
    );

    $form['gd_file_upload_settings']['file_upload_batch_size'] = array(
        '#type' => 'textfield',
        '#title' => 'Record Batch Size',
        '#description' => 'Use positive number. Use 1 if you have issues with uploading a file which contains international characters. Use 500 for optimum performance',
        '#default_value' => variable_get('gd_file_upload_batch_size'),
        '#required' => false
    );

    $form['action'] = array(
        '#type' => 'submit',
        '#value' => t('Save Settings')
    );

    $form['#validate'][] = 'gd_performance_admin_settings_validate';
    $form['#submit'][] = 'gd_performance_admin_settings_submit';

    return $form;
}

function gd_performance_admin_settings_validate ( $form, &$form_state ) {
    $batchSize = StringHelper::trim($form_state['values']['file_upload_batch_size']);
    if (isset($batchSize)) {
        try {
            IntegerDataTypeHandler::checkPositiveInteger($batchSize);
        }
        catch (Exception $e) {
            form_set_error('file_upload_batch_size', $e->getMessage());
        }
    }
}

function gd_performance_admin_settings_submit ( $form, &$form_state ) {
    $originalBatchSize = variable_get('gd_file_upload_batch_size', NULL);
    $batchSize = StringHelper::trim($form_state['values']['file_upload_batch_size']);
    if ($originalBatchSize != $batchSize) {
        variable_set('gd_file_upload_batch_size', $batchSize);

        $message = isset($batchSize)
            ? t('File upload batch size is set to @batchSize records', array('@batchSize' => $batchSize))
            : t('File upload batch size is reset to default');
        drupal_set_message($message);
    }
}