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
 * Reference service access control
 *
 * @param string $op
 * @param array $args
 * @return bool|mixed
 */
function gd_reference_resource_access ( $op, $args ) {
    //if Instance Admin return true ... no additional processing required.
    global $user;
    //if annonymous user return error
    if ( $user->uid == 0 ) {
        return services_error('Authorization Required/ Session Expired', 401);
    }

    if ( gd_account_user_is_admin($user) || gd_account_user_is_datasource_admin($user,gd_datasource_get_active()) ) {
        return true;
    }

    return services_error('You are not authorized!', 403);
}

/**
 * Reference service index operation
 *
 * @param $filter
 * @return mixed|StdClass
 */
function gd_reference_resource_index ( $filter = null ) {

    /**
    $metamodel = data_controller_get_metamodel();
    return $metamodel->references;
    **/

    try {
        $referenceNodes = array();
        $result = new StdClass();

        // primary dataset filter
        if ( isset($filter['dataset']) ) {
            $datasetName = $filter['dataset'];

            $reference_point_nids = gd_reference_get_reference_points_by_dataset($datasetName);
            if ( !empty($reference_point_nids) ) {
                $referenceNodes = gd_reference_get_references_by_reference_points($reference_point_nids, LOAD_ENTITY);
            }
        }

        if ( !empty($referenceNodes) ) {
            $result = gd_reference_resource_create_api_object_from_node($referenceNodes, $filter);
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $result;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}


function gd_reference_resource_retrieve ( $id ) {

    $referenceNode = node_load($id);
    gd_reference_resource_assert_valid_reference($referenceNode);

    $result = null;
    try {
        $api_object = gd_reference_resource_create_api_object_from_node(array($referenceNode));

        if ( !empty($api_object->references[0]) ) {
            $result = $api_object->references[0];
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
    return $result;
}

/**
 * @param $reference
 * @return services_error|StdClass
 * @throws Exception
 */
function gd_reference_resource_create ( $reference ) {
    $reference = (Object) $reference; // TODO make functionality work with an array
    $result = null;

    $transaction = db_transaction('reference_create');
    try {
        // create reference
        $reference_node = new StdClass();
        $reference_node->type = NODE_TYPE_REFERENCE;
        $reference_node->language = LANGUAGE_NONE;
        node_object_prepare($reference_node);
        $reference_node->title = 'Generated for Report';
        $reference_node->status = !NODE_PUBLISHED;
        $reference_node->field_reference_sysname[$reference_node->language][0]['value'] = GD_NamingConvention::generateReferenceName();
        node_save($reference_node);

        // create reference points
        $reference_point_nodes = gd_reference_resource_create_reference_points($reference);
        // add reference points to the reference
        foreach ( $reference_point_nodes as $reference_point_nid => $reference_point_node ) {
            $reference_node->field_reference_point[$reference_node->language][] = array('nid' => $reference_point_nid);
        }
        node_save($reference_node);

        // register with metamodel
        $metamodel = data_controller_get_metamodel();
        MetaModelFactory::getInstance()->startGlobalModification();
        try {
            GD_DatasetMetaModelLoaderHelper::prepareReference($metamodel, $reference_node, $reference_point_nodes);
        } catch ( Exception $e ) {
            MetaModelFactory::getInstance()->finishGlobalModification(false);
            throw $e;
        }
        MetaModelFactory::getInstance()->finishGlobalModification(true);

        // publish
        $reference_node->status = NODE_PUBLISHED;
        node_save($reference_node);
        foreach ( $reference_point_nodes as $node ) {
            $node->status = NODE_PUBLISHED;
            node_save($node);
        }

        $result = gd_reference_resource_create_api_object_from_node(array($reference_node));

        if ( isset($_SESSION['messages']['error']) ) {
            gd_admin_ui_service_session_error_handler();
        }

    } catch ( Exception $e ) {
        $transaction->rollback();
        gd_admin_ui_service_exception_handler($e);
    }

    return $result;
}

/**
 * @param $reference
 * @return array
 */
function gd_reference_resource_create_reference_points ( $reference ) {
    $reference_points = array();

    // check for columns
    $has_columns = true;
    foreach ( $reference->datasets as $dataset ) {
        if ( empty($dataset) ) {
            $has_columns = false;
            break;
        }
    }

    if ( $has_columns ) {
        foreach ( $reference->datasets as $dataset_sysname => $column_sysnames ) {
            $ref_point_node = new StdClass();
            $ref_point_node->type = NODE_TYPE_REFERENCE_POINT;
            $ref_point_node->status = !NODE_PUBLISHED;
            $ref_point_node->language = LANGUAGE_NONE;
            node_object_prepare($ref_point_node);

            $ref_point_node->title = 'Generated for Report';
            $ref_point_node->field_ref_point_dataset_sysname[$ref_point_node->language][0]['value'] = $dataset_sysname;
            foreach ( $column_sysnames as $column_sysname ) {
                $ref_point_node->field_ref_point_column_sysname[$ref_point_node->language][] = array('value' => $column_sysname);
            }
            node_save($ref_point_node);
            $reference_points[$ref_point_node->nid] = $ref_point_node;
        }
    }

    return $reference_points;
}

/**
 * @param $id
 * @param $reference
 * @return services_error|StdClass
 * @throws Exception
 */
function gd_reference_resource_update ( $id, $reference ) {
    $reference = (Object) $reference; // TODO make functionality work with an array
    $api_obj = null;
    $transaction = db_transaction('reference_update');
    try {
        $reference_node = node_load($id);
        $referenceName = get_node_field_value($reference_node, 'field_reference_sysname', 0, 'value', true);
        $metamodel = data_controller_get_metamodel();

        MetaModelFactory::getInstance()->startGlobalModification();
        try {
            // delete existing reference points
            $old_ref_point_nids = array();
            foreach ( $reference_node->field_reference_point[$reference_node->language] as $ref_point ) {
                $old_ref_point_nids[] = $ref_point['nid'];
            }
            if ( !empty($old_ref_point_nids) ) {
                node_delete_multiple($old_ref_point_nids);
            }
            // ... and unregistering old definition of the reference
            $metamodel->unregisterReference($referenceName);

            // create reference points
            $reference_point_nodes = gd_reference_resource_create_reference_points($reference);
            if ( !empty($reference_point_nodes) ) {
                // add reference points to the reference
                $reference_node->field_reference_point[$reference_node->language] = array();
                foreach ( $reference_point_nodes as $reference_point_nid => $reference_point_node ) {
                    $reference_node->field_reference_point[$reference_node->language][] = array('nid' => $reference_point_nid);
                }
                node_save($reference_node);

                GD_DatasetMetaModelLoaderHelper::prepareReference($metamodel, $reference_node, $reference_point_nodes);

                $api_obj = gd_reference_resource_create_api_object_from_node(array($reference_node));
            } else {
                // if no reference points, delete the reference
                node_delete($id);
                $api_obj = new StdClass();
            }
        } catch ( Exception $e ) {
            MetaModelFactory::getInstance()->finishGlobalModification(false);
            throw $e;
        }
        MetaModelFactory::getInstance()->finishGlobalModification(true);

        if ( isset($_SESSION['messages']['error']) ) {
            gd_admin_ui_service_session_error_handler();
        }

    } catch ( Exception $e ) {
        $transaction->rollback();
        gd_admin_ui_service_exception_handler($e);
    }

    return $api_obj;
}

/**
 * Reference service delete operation
 *
 * @param $id
 * @return bool|services_error
 * @throws Exception
 */
function gd_reference_resource_delete ( $id ) {
    try {
        $reference_node = node_load($id);
        $referenceName = get_node_field_value($reference_node, 'field_reference_sysname', 0, 'value', true);
        $metamodel = data_controller_get_metamodel();

        MetaModelFactory::getInstance()->startGlobalModification();
        try {
            // unregister definition of the reference
            $metamodel->unregisterReference($referenceName);
        } catch ( Exception $e ) {
            MetaModelFactory::getInstance()->finishGlobalModification(false);
            throw $e;
        }
        MetaModelFactory::getInstance()->finishGlobalModification(true);

        // delete existing reference points
        $transaction = db_transaction('reference_delete');
        $ref_point_nids = array();
        foreach ( $reference_node->field_reference_point[$reference_node->language] as $ref_point ) {
            $ref_point_nids[] = $ref_point['nid'];
        }
        if ( !empty($ref_point_nids) ) {
            node_delete_multiple($ref_point_nids);
        }
        node_delete($id);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        } else {
            return true;
        }
    } catch ( Exception $e ) {
        if ( isset($transaction) ) {
            $transaction->rollback();
        }
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * Validates that the requested node is available
 *
 * @param $node
 * @return mixed
 */
function gd_reference_resource_assert_valid_reference ( $node ) {
    // node MUST exist
    if ( !isset($node) ) {
        return services_error(t('Reference not found'), 404);
    }

    // node MUST be of type dashboard
    if ( $node->type != NODE_TYPE_REFERENCE ) {
        return services_error(t('Reference not found'), 404);
    }

    // node MUST be published
    if ( $node->status != NODE_PUBLISHED ) {
        return services_error(t('Reference not found'), 404);
    }

    return true;
}

function gd_reference_resource_create_api_object_from_node ( $references, $filter = null ) {
    $api_object = new StdClass();
    $api_references = array();

    $all_dataset_sysnames = array();

    $metadata = data_controller_get_metamodel();

    foreach ( $references as $reference ) {
        $ref_point_ids = array();
        foreach ( $reference->field_reference_point[$reference->language] as $rp ) {
            $ref_point_ids[] = $rp['nid'];
        }

        $api_ref_points = array();
        if ( !empty($ref_point_ids) ) {
            $reference_points = node_load_multiple($ref_point_ids);

            // if the first reference point is not from the primary dataset, reverse the array
            // this correctly orders the list for the UI
            if ( isset($filter['dataset']) ) {
                $first_ref_point = reset($reference_points);
                if ( $first_ref_point->field_ref_point_dataset_sysname[$first_ref_point->language][0]['value'] != $filter['dataset'] ) {
                    $reference_points = array_reverse($reference_points);
                }
            }

            $dataset_sysnames = array();
            foreach ( $reference_points as $reference_point ) {
                $column_sysnames = array();

                $dataset_sysnames[] = $reference_point->field_ref_point_dataset_sysname[$reference_point->language][0]['value'];
                foreach ( $reference_point->field_ref_point_column_sysname[$reference_point->language] as $item ) {
                    $column_sysnames[] = $item['value'];
                }

                $api_dataset_obj = new StdClass();
                if ( !empty($dataset_sysnames) ) {
                    foreach ( $dataset_sysnames as $dataset_sysname ) {
                        $dataset = $metadata->findDataset($dataset_sysname);

                        if ( !$dataset ) {
                            throw new Exception('Cannot find dataset "'.$dataset_sysname.'" for reference point "'.$reference_point->nid.'".');
                        }

                        $all_dataset_sysnames[] = $dataset_sysname;

                        $api_dataset_obj->name = $dataset->name;
                        $api_dataset_obj->publicName = $dataset->publicName;

                        $api_columns = array();
                        if ( !empty($column_sysnames) ) {
                            foreach ( $column_sysnames as $column_sysname ) {
                                $column = $dataset->findColumn($column_sysname);
                                if ( $column ) {
                                    $api_column_obj = new StdClass();
                                    $api_column_obj->name = $column->name;
                                    $api_column_obj->publicName = $column->publicName;
                                    $api_columns[] = $api_column_obj;
                                }
                            }
                        }
                    }
                }

                $api_ref_point_obj = new StdClass();
                $api_ref_point_obj->dataset = $api_dataset_obj;
                $api_ref_point_obj->columns = $api_columns;
                $api_ref_points[] = $api_ref_point_obj;
            }
        }

        // secondary dataset filter: if a secondary dataset specified,
        // only return reference between primary dataset and this dataset
        if ( !isset($filter['secondaryDataset']) || (isset($filter['secondaryDataset']) && !array_diff($all_dataset_sysnames, array($filter['dataset'], $filter['secondaryDataset']))) ) {
            $api_reference_obj = new StdClass();
            $api_reference_obj->id = $reference->nid;
            $api_reference_obj->reference_points = $api_ref_points;
            $api_references[] = $api_reference_obj;
        }
    }

    $api_object->references = $api_references;

    return $api_object;
}