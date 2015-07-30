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


module_load_include('inc','gd_datafile','resources/datafile');

/**
 * @param $op
 * @param $args
 * @return bool|mixed
 */
function gd_dataset_resource_access () {
    return user_is_logged_in();
}

/**
 * @param null $filter
 * @return array|DatasetMetaData[]|services_error
 * @throws Exception
 */
function gd_dataset_resource_index ( $filter = NULL ) {
    try {
        $datasource = NULL;
        if ( !empty($filter['datasource']) ) {
            $datasource = gd_datasource_find($filter['datasource']);
            if ( !$datasource ) {
                throw new ServicesException('Datasource not found', 404);
            }

            gd_datasource_set_active($datasource->name);

            if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
                throw new ServicesException('Forbidden', 403);
            }
        } else {
            if ( !gd_account_user_is_admin() ) {
                throw new ServicesException('Forbidden', 403);
            }
        }

        $metamodel = data_controller_get_metamodel();

        $datasets = array();
        foreach ( $metamodel->datasets as $dataset ) {
            if ( $dataset->isPublic() ) {
                // including public datasets
            } else if ( $dataset->isProtected() && isset($datasource) && ($dataset->datasourceName == $datasource->name) ) {
                // including protected datasets if they belong to active data source
            } else {
                continue;
            }

            $ds = NULL;
            // copying only required properties
            ObjectHelper::copySelectedProperties($ds, $dataset, array('name', 'publicName', 'description', 'nid'));

            $datasets[] = $ds;
        }

        // add node meta data
        gd_dataset_resource_apply_node_info($datasets);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $datasets;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @return DatasetMetaData|NULL|services_error
 */
function gd_dataset_resource_retrieve ( $datasetName ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException('Not found', 404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        // pick up additional info
        $dataQueryController = data_controller_get_instance();
        $dataset = $dataQueryController->getDatasetMetaData($datasetName);

        // add stats
        // TODO really only need record count, columns can be calculated in js
        $dataset->stats = new stdClass();
        $dataset->stats->records = $dataQueryController->countDatasetRecords($dataset->name);
        $dataset->stats->columns = new stdClass();
        $dataset->stats->columns->visible = (int) $dataset->getColumnCount();
        $dataset->stats->columns->hidden = ((int) $dataset->getColumnCount(false)) - $dataset->stats->columns->visible;

        // add node info
        gd_dataset_resource_apply_node_info(array($dataset));

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $dataset;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $data
 * @return DatasetMetaData|services_error
 * @throws ServicesException
 */
function gd_dataset_resource_create ( $dataset ) {
    try {
        $dataset = (Object) $dataset;

        $datasource = gd_datasource_find($dataset->datasourceName);
        if ( !$datasource ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($dataset->datasourceName);

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        gd_dataset_resource_validate_unique_name($dataset);

        $dataset = gd_dataset_create($dataset, $dataset->datasourceName);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        $response = new stdClass();
        $response->datasetName = $dataset->name;

        // warnings are unique to dataset create/update/truncate
        if ( isset($_SESSION['messages']['warning']) ) {
            $response->warnings = array();
            foreach ( drupal_get_messages('warning') as $warning) {
                $response->warnings[] = $warning;
                LogHelper::log_warn($warning);
            }
            unset($_SESSION['messages']);
        }

        return $response;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @param $data
 * @return DatasetMetaData|services_error
 * @throws ServicesException
 */
function gd_dataset_resource_update ( $datasetName, $dataset ) {
    try {
        $dataset = (object) $dataset;

        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $originalDataset = $metamodel->findDataset($datasetName);

        if ( !$originalDataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        if ( empty($dataset->nid) ) {
            throw new ServicesException(t('Dataset cannot be modified.'),406);
        }

        gd_dataset_resource_validate_unique_name($dataset,$originalDataset);

        // update dataset
        // prepares the metadata object for the next step.  metadata has to be of type RecordMetaData
        $datasetMetadata = new DatasetMetaData();
        $datasetMetadata->initializeFrom($dataset);
        gd_dataset_update($datasetMetadata);

        $dataQueryController = data_controller_get_instance();
        $dataset = $dataQueryController->getDatasetMetaData($datasetName);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        $response = new stdClass();
        $response->datasetName = $dataset->name;

        // warnings are unique to dataset create/update/truncate
        if ( isset($_SESSION['messages']['warning']) ) {
            $response->warnings = array();
            foreach ( drupal_get_messages('warning') as $warning) {
                $response->warnings[] = $warning;
                LogHelper::log_warn($warning);
            }
            unset($_SESSION['messages']);
        }

        return $response;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }

}

/**
 * @param $datasetName
 * @return services_error|stdClass
 */
function gd_dataset_resource_delete ( $datasetName ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        if ( empty($dataset->nid) ) {
            throw new ServicesException(t('Dataset cannot be modified.'),406);
        }

        gd_dataset_unpublish($datasetName);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return NULL;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @return services_error|stdClass
 */
function gd_dataset_resource_statistic ( $datasetName ) {
    try {
        $stats = new stdClass();

        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $dataQueryController = data_controller_get_instance();
        $dataset = $dataQueryController->getDatasetMetaData($datasetName);

        $stats->records = $dataQueryController->countDatasetRecords($dataset->name);

        $stats->columns = new stdClass();
        $stats->columns->visible = (int) $dataset->getColumnCount();
        $stats->columns->hidden = ((int) $dataset->getColumnCount(false)) - $stats->columns->visible;
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }

    return $stats;
}

/**
 * $view will be NULL or 'changelog'
 *
 * @param $datasetName
 * @param null $view
 * @param null $columns
 * @param null $order
 * @param null $filters
 * @param int $startRow
 * @param int $endRow
 * @return array|null|services_error
 */
function gd_dataset_resource_data ( $datasetName, $view = NULL, $columns = NULL, $order = NULL, $filters = NULL, $startRow = 0, $endRow = 5 ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $result = array();

        if ( $view === NULL ) {

            // TODO will come back to this when we implement "execution tree" functionality
            /*
            // filter parameter currently only allows for one condition and will override the first if a second one is provided.
            $query_filters = array();
            if ( is_array($filters) ) {
                foreach ( $filters as $key => $value ) {
                    if ( empty($query_filters[$key]) ) {
                        $query_filters[$key] =  array();
                    }
                    $query_filters[$key][] = OperatorFactory::getInstance()->initiateHandler($value['o'], $value['v']);
                }
            }
            
            $dataQueryController = data_controller_get_instance();
            $result = $dataQueryController->queryDataset($datasetName, $columns, $query_filters, $order, 0, 5);
            */
            
            $records = gd_data_controller_query_by_preview_metadata($datasetName,NULL,NULL,NULL,0,5);

            if ( isset($records) ) {
                foreach ( $records as $record ) {
                    $r = array();
                    foreach ( $record as $columnName => $columnValue ) {
                        $column = $dataset->getColumn($columnName);
                        $r[$columnName] = isset($columnValue) ? gd_dataset_data_formatter($columnValue, $column->type) : '&nbsp;';
                    }
                    $result[] = $r;
                }
            }
            
        } else if ( $view === 'changelog' ) {
            $datafileNodes = gd_dataset_get_datafiles($datasetName,LOAD_ENTITY);
            foreach ( $datafileNodes as $node ) {
                $result[] = gd_datafile_resource_get_object_from_node($node);
            }
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }
            
        return $result;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @return services_error|stdClass
 */
function gd_dataset_resource_data_truncate ( $datasetName ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        gd_data_controller_ddl_truncate_dataset($datasetName);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        $response = new stdClass();
        $response->datasetName = $dataset->name;

        // warnings are unique to dataset create/update/truncate
        if ( isset($_SESSION['messages']['warning']) ) {
            $response->warnings = array();
            foreach ( drupal_get_messages('warning') as $warning) {
                $response->warnings[] = $warning;
                LogHelper::log_warn($warning);
            }
            unset($_SESSION['messages']);
        }

        return $response;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @param null $view
 * @param null $columns
 * @param null $sort
 * @param null $filters
 * @param int $offset
 * @param int $limit
 * @return array|null|services_error
 */
function gd_dataset_resource_lookup ( $datasetName, $column = NULL, $query = '*', $generic = "true", $sort = NULL, $offset = 0, $limit = 100) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $params = new stdClass();
        if ( $generic == "true" ) {
            $params->generic = $generic;
        }
        $params->dataset = $dataset->name;
        $params->column = $column;
        $params->query = $query;
        $params->offset = isset($offset) ? $offset : 0;
        $params->limit = isset($limit) ? $limit : 100;

        if ( isset($sort) ) {
            $params->sort = $sort;
        }

        $result = gd_dataset_get_column_values($params, true);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $result;
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @param array $linked
 * @return DatasetUIMetaData|null|services_error
 */
function gd_dataset_resource_ui ( $datasetName, $linked = array() ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $result = gd_data_controller_ui_metadata_get_dataset_ui_metadata($datasetName,$linked);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $result;
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * There will be 3 types:
 * manual, lookup, and dimension
 * TODO: Data Controller needs to define these as constants
 *
 * @param $datasetName
 * @param string $type
 * @return array|services_error
 */
function gd_dataset_resource_references ( $datasetName, $type = 'lookup' ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $datasetReferences = array();
        if ( $type === 'lookup' || $type === NULL ) {
            $linkableDatasetNames = gd_data_controller_metamodel_get_linkable_datasets($dataset->name,array_keys($metamodel->datasets));
            if ( isset($linkableDatasetNames) ){
                foreach ( $linkableDatasetNames as $name ) {
                    $datasetReferences[] = $metamodel->getDataset($name);
                }
            }
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $datasetReferences;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @param null $group
 * @return array|services_error
 */
function gd_dataset_resource_referenced ( $datasetName, $group = NULL ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $result = gd_dataset_get_referenced($datasetName,$group);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $result;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasetName
 * @param null $depth
 * @return array|services_error
 */
function gd_dataset_resource_connections ( $datasetName, $depth = NULL ) {
    try {
        $datasourceName = gd_dataset_find_datasource_name($datasetName);
        if ( !isset($datasourceName) ) {
            throw new ServicesException('Datasource not found', 404);
        }

        gd_datasource_set_active($datasourceName);

        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->findDataset($datasetName);

        if ( !$dataset ) {
            throw new ServicesException(t('Not found'),404);
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(NULL,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $result = new stdClass();
        $result->directed = true;
        $result->graph = array();
        $result->multigraph = false;
        $result->nodes = array();
        $result->links = array();
        $result->depth = 0;

        $graph = gd_data_controller_metamodel_get_dataset_reference_graph($datasetName,$depth);

        $parseGraph = function( $link, $parent ) use ( &$parseGraph, &$result ) {

            if ( empty($link) ) {
                return;
            }

            if ( !isset($result->nodes[$link->linkId]) ) {
                $result->nodes[$link->linkId] = new stdClass();
                $result->nodes[$link->linkId]->linkId = $link->linkId;
                $result->nodes[$link->linkId]->size = count($link->dataset->columns);

                $result->nodes[$link->linkId]->dataset = new stdClass();
                $result->nodes[$link->linkId]->dataset->name = $link->dataset->name;
                $result->nodes[$link->linkId]->dataset->publicName = $link->dataset->publicName;
                $result->nodes[$link->linkId]->dataset->columns = array();
                foreach ( $link->dataset->columns as $column ) {
                    $c = new stdClass();
                    $c->name = $column->name;
                    $c->publicName = $column->publicName;
                    $result->nodes[$link->linkId]->dataset->columns[] = $c;
                }
            }

            if ( $parent ) {
                $l = new stdClass();
                $l->source = $parent->linkId;
                $l->target = $link->linkId;
                $result->links[] = $l;
            }

            if ( !empty($link->nestedLinks) ) {
                $result->depth++;
                foreach ( $link->nestedLinks as $nestedLink ) {
                    $parseGraph($nestedLink,$link);
                }
            }

        };

        $parseGraph($graph,NULL);

        // sort nodes by linkId
        usort($result->nodes, function($a, $b) {
            if ($a->linkId == $b->linkId) {
                return 0;
            }
            return ($a->linkId < $b->linkId) ? -1 : 1;
        });

        $result->nodes = array_values($result->nodes);
        foreach ( $result->links as $i => $link ) {
            foreach ( $result->nodes as $j => $node ) {
                if ( $node->linkId == $link->source ) {
                    $result->links[$i]->source = $j;
                }
                if ( $node->linkId == $link->target ) {
                    $result->links[$i]->target = $j;
                }
            }
        }

        $result->nodes[0]->color = "#da991c";

        // sort links by source
        usort($result->links, function($a, $b) {
            if ($a->source == $b->source) {
                return 0;
            }
            return ($a->source < $b->source) ? -1 : 1;
        });

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $result;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasets
 */
function gd_dataset_resource_apply_node_info ( $datasets ) {

    $nids = array();
    foreach ( $datasets as $d ) {
        if ( empty($d->nid) ) {
            continue;
        }

        $nids[$d->name] = $d->nid;
    }

    // do not continue if there are no node based datasets
    if ( empty($nids) ) {
        return;
    }

    // add some node info
    $sql  = 'SELECT nid, uid, created, changed ';
    $sql .= 'FROM {node} ';
    $sql .= 'WHERE nid IN (:nids) ';
    $result = db_query($sql,array(':nids'=>array_values($nids)));
    $uids = array();
    foreach ( $result as $row ) {
        foreach ( $datasets as $d ) {
            if ( empty($d->nid) || $d->nid != $row->nid ) {
                continue;
            }

            $d->created = date(GD_DATETIME_FORMAT_API, $row->created);
            $d->changed = $row->changed; // formatting after finding latest change
            $d->uid = $row->uid;

            $uids[$row->uid] = $row->uid;
        }
    }

    // add some user info
    $users = user_load_multiple($uids);
    foreach ( $datasets as $d ) {
        if ( empty($d->uid) ) {
            continue;
        }

        foreach ( $users as $u ){
            if ( $u->uid != $d->uid ) {
                continue;
            }

            $author = new stdClass();
            $author->id = $u->uid;
            $author->name = $u->fullname;
            $author->email = $u->mail;
            $d->author = $author;
        }
    }

    // check uploads for latest change
    $sql  = 'SELECT MAX(N.changed) changed, F.field_datafile_dataset_sysname_value name ';
    $sql .= 'FROM node N ';
    $sql .= 'INNER JOIN field_data_field_datafile_dataset_sysname F ON N.nid = F.entity_id ';
    $sql .= 'WHERE F.field_datafile_dataset_sysname_value IN (\'' . implode('\',\'', array_keys($nids)) . '\') ';
    $sql .= 'GROUP BY F.field_datafile_dataset_sysname_value ';
    $result = db_query($sql);

    foreach ( $datasets as $d ) {
        if ( empty($d->nid) ) {
            continue;
        }

        foreach ( $result as $row ) {
            if ( $row->name != $d->name ) {
                continue;
            }

            if ( $row->changed > $d->changed ) {
                $d->changed = $row->changed;
            }
        }
    }

    // check columns for latest change
    $sql  = 'SELECT MAX(N.changed) changed, CD.field_column_dataset_nid nid ';
    $sql .= 'FROM node N ';
    $sql .= 'INNER JOIN field_data_field_column_dataset CD ON N.nid = CD.entity_id ';
    $sql .= 'WHERE CD.field_column_dataset_nid IN (:nids) ';
    $sql .= 'GROUP BY CD.field_column_dataset_nid ';
    $result = db_query($sql,array(':nids'=>$nids));

    foreach ( $datasets as $d ) {
        if ( empty($d->nid) ) {
            continue;
        }

        foreach ( $result as $row ) {
            if ( $row->nid != $d->nid ) {
                continue;
            }

            if ( $row->changed > $d->changed ) {
                $d->changed = $row->changed;
            }
        }
    }

    // applying formatting for changed
    foreach ( $datasets as $d ) {
        if ( empty($d->nid) ) {
            continue;
        }

        $d->changed = date(GD_DATETIME_FORMAT_API, $d->changed);
    }
}

/**
 * @param $dataset
 * @param null $reportNode
 * @throws ServicesException
 */
function gd_dataset_resource_validate_unique_name ( $dataset, $originalDataset = NULL ) {
    $unique = true;
    if ( !$originalDataset ) {
        $unique = gd_dataset_name_isunique($dataset->publicName);
    } else {
        if ( isset($dataset->publicName) && strcasecmp($originalDataset->publicName,$dataset->publicName) !== 0 ) {
            $unique = gd_dataset_name_isunique($dataset->publicName);
        }
    }
    if ( !$unique ) {
        throw new ServicesException(t('A dataset named "'.check_plain($dataset->publicName).'" already exists. Please enter a unique dataset name'), 406);
    }
}