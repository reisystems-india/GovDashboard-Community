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
 * @param string $op
 * @param array $args
 * @return bool|mixed
 */
function gd_datafile_resource_access ( $op = 'view', $args = array() ) {
    return user_is_logged_in();
}

/**
 * @param $datafileNid
 * @throws ServicesException
 * @return null|StdClass
 */
function gd_datafile_resource_retrieve ( $datafileNid ) {
    try {
        $datafileNode = gd_datafile_load($datafileNid);

        if ( !$datafileNode ) {
            throw new ServicesException('Not found', 404);
        }

        if ( !gd_account_user_is_admin() ) {
            // use datasource from dataset if exists
            $datasetName = get_node_field_value($datafileNode,'field_datafile_dataset_sysname');
            if ( $datasetName ) {
                $datasetNode = gd_dataset_load_by_sysname($datasetName);
                $datasourceName = get_node_field_value($datasetNode,'field_dataset_datasource');
                gd_datasource_set_active($datasourceName);
            } else {
                // no other way to get datasource
                gd_datasource_set_active($_GET['ds']);
            }

            if ( !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
                throw new ServicesException('Forbidden', 403);
            }
        }

        return gd_datafile_resource_get_object_from_node($datafileNode);
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datafileNid
 * @param $data
 * @throws ServicesException
 * @return null|StdClass
 */
function gd_datafile_resource_update ( $datafileNid, $data ) {
    try {
        $data = (Object) $data; // TODO make functionality work with an array
        $datafileNode = gd_datafile_load($datafileNid);

        if ( !$datafileNode ) {
            throw new ServicesException('Not found', 404);
        }

        if ( !gd_account_user_is_admin() ) {
            // use datasource from dataset if exists
            $datasetName = get_node_field_value($datafileNode,'field_datafile_dataset_sysname');
            if ( $datasetName ) {
                $datasetNode = gd_dataset_load_by_sysname($datasetName);
                $datasourceName = get_node_field_value($datasetNode,'field_dataset_datasource');
                gd_datasource_set_active($datasourceName);
            } else {
                // no other way to get datasource
                gd_datasource_set_active($_GET['ds']);
            }

            if ( !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
                throw new ServicesException('Forbidden', 403);
            }
        }

        if ( isset($data->hasheader) ) {
            $datafileNode->field_datafile_hasheader[$datafileNode->language][0]['value'] = (int) $data->hasheader;
        }

        node_save($datafileNode);
        return gd_datafile_resource_get_object_from_node($datafileNode);
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datafileNid
 * @param null $preview
 * @return array
 */
function gd_datafile_resource_structure ( $datafileNid, $datasourceName = null ) {
    try {
        $datafileNode = gd_datafile_load($datafileNid);

        if ( !$datafileNode ) {
            throw new ServicesException('Not found', 404);
        }

        // use datasource from dataset if exists
        $datasetName = get_node_field_value($datafileNode,'field_datafile_dataset_sysname');
        if ( $datasetName ) {
            $datasetNode = gd_dataset_load_by_sysname($datasetName);
            $datasourceName = get_node_field_value($datasetNode,'field_dataset_datasource');
            gd_datasource_set_active($datasourceName);
        } else {
            // no other way to get datasource
            gd_datasource_set_active($datasourceName);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        if ( $datasetName ) {
            $dataQueryController = data_controller_get_instance();
            return $dataQueryController->getDatasetMetaData($datasetName);
        } else {
            list($recordCount, $metadata, $detectedEncoding, $preview) = gd_dataset_uploader_get_source_metadata(
                get_node_field_value($datafileNode,'field_datafile_file',0,'fid'),
                get_node_field_value($datafileNode,'field_datafile_delimiter'),
                get_node_field_value($datafileNode,'field_datafile_hasheader'),
                null
            );

            // store the detected file encoding
            $datafileNode->field_datafile_encoding[$datafileNode->language][0]['value'] = $detectedEncoding;
            node_save($datafileNode);

            $message_object = gd_datafile_prepare_message_from_session();

            return array('structure'=>$metadata,'preview'=>$preview,'messages'=>$message_object);
        }
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $node
 * @return StdClass
 */
function gd_datafile_resource_get_object_from_node ( $node ) {
    $api_object = new StdClass();

    if ( isset($node->nid) ) {
        $api_object->id = $node->nid;
    }

    // name
    if ( isset($node->field_datafile_file[$node->language][0]['filename']) ) {
        $api_object->name = $node->field_datafile_file[$node->language][0]['filename'];
    }

    // file mime
    if ( isset($node->field_datafile_file[$node->language][0]['filemime']) ) {
        $api_object->mime = $node->field_datafile_file[$node->language][0]['filemime'];
    }

    // file size
    if ( isset($node->field_datafile_file[$node->language][0]['filesize']) ) {
        $api_object->size = $node->field_datafile_file[$node->language][0]['filesize'];
    }

    // file uri
    if ( isset($node->field_datafile_file[$node->language][0]['uri']) ) {
        $api_object->uri = file_create_url($node->field_datafile_file[$node->language][0]['uri']);
    }

    // description
    if ( isset($node->field_datafile_desc[$node->language][0]['value']) ) {
        $api_object->description = $node->field_datafile_desc[$node->language][0]['value'];
    }

    // author
    if ( isset($node->field_datafile_file[$node->language][0]['uid']) ) {
        $account = user_load($node->field_datafile_file[$node->language][0]['uid']);
        $api_object->author = new stdClass();
        if ( $account ) {
            $api_object->author->id = $account->uid;
            $api_object->author->name = $account->name;
            $api_object->author->email = $account->mail;
        }
    }

    // status
    if ( isset($node->field_datafile_status[$node->language][0]['value']) ) {
        $api_object->status = $node->field_datafile_status[$node->language][0]['value'];
    }

    // action
    if ( isset($node->field_datafile_action[$node->language][0]['value']) ) {
        $api_object->action = $node->field_datafile_action[$node->language][0]['value'];
    }

    // records
    if ( isset($node->field_datafile_records[$node->language][0]['value']) ) {
        $api_object->rows = $node->field_datafile_records[$node->language][0]['value'];
    }

    // dataset
    if ( isset($node->field_datafile_dataset_sysname[$node->language][0]['value']) ) {
        $api_object->dataset = $node->field_datafile_dataset_sysname[$node->language][0]['value'];
    }

    // delimiter
    if ( isset($node->field_datafile_delimiter[$node->language][0]['value']) ) {
        $api_object->delimiter = $node->field_datafile_delimiter[$node->language][0]['value'];
    }

    // has header
    if ( isset($node->field_datafile_hasheader[$node->language][0]['value']) ) {
        $api_object->hasheader = $node->field_datafile_hasheader[$node->language][0]['value'];
    }

    // encoding
    if ( isset($node->field_datafile_encoding[$node->language][0]['value']) ) {
        $api_object->encoding = $node->field_datafile_encoding[$node->language][0]['value'];
    }

    // timestamps
    $api_object->created = date(GD_DATETIME_FORMAT_API, $node->created);
    $api_object->changed = date(GD_DATETIME_FORMAT_API, $node->changed);

    // drupal messages
    $api_object->messages = gd_datafile_prepare_message_from_session();

    return $api_object;
}
