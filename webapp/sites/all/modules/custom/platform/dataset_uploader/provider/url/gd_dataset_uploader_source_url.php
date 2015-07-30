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


require_once drupal_get_path('module', 'filefield_sources') . '/sources/remote.inc';

function gd_dataset_uploader_source_url_save($url, $validators, $destination) {
    // added 'dummy' parent to prevent PHP warnings in 'FileField_Source' module
    $element['#parents'] = array('field_datafile_file');

    $element['#entity_type'] = 'node';
    $element['#field_name'] = 'field_datafile_file';
    $element['#bundle'] = NODE_TYPE_DATAFILE;
    $element['#delta'] = 0;

    $element['#upload_validators'] = $validators;

    // preparing destination for downloaded data
    if (empty($destination)) {
        $destination = DATASET_FILE_STORAGE_DESTINATION;
    }
    $element['#upload_location'] = $destination;

    $item['filefield_remote']['url'] = $url;

    filefield_source_remote_value($element, $item);

    // our code expects data as an object, not as an array as provided by 'FileField_Source' module
    return (object) $item;
}
