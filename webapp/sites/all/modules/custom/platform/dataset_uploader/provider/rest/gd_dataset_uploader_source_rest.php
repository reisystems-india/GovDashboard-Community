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


define('PARAMETER_NAME_CONTENT', 'content');

class GD_ServicesParser_Text implements ServicesParserInterface {

    public function parse(ServicesContextInterface $context) {

        global $user;

        $destination = DATASET_FILE_STORAGE_DESTINATION;
        if (substr($destination, -1) != '/') {
            $destination .= '/';
        }

        $file = new stdClass();
        $file->uid = $user->uid;
        $file->status = 0;
        $file->filename = uniqid('push-api_');
        $file->uri = file_destination($destination . $file->filename, FILE_EXISTS_RENAME);
        $file->filemime = 'text/csv';

        if ( false === file_put_contents($file->uri,$context->getRequestBody()) ) {
            throw new IllegalArgumentException(t('Could not store received data on file system'));
        }
        drupal_chmod($file->uri);

        file_save($file);

        return array(PARAMETER_NAME_CONTENT => $file);
    }
}
