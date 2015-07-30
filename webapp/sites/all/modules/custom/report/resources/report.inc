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


function gd_report_resource_access () {
    return user_is_logged_in();
}

/**
 * @param null $fields
 * @param null $page
 * @param null $limit
 * @param null $sort
 * @param null $filter
 * @return array|services_error
 */
function gd_report_resource_index ( $fields = null, $page = null, $limit = null, $sort = null, $filter = null ) {
    try {

        if ( !empty($filter['datasource']) ) {
            gd_datasource_set_active($filter['datasource']);

            if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
                throw new ServicesException('Forbidden', 403);
            }
        } else {
            if ( !gd_account_user_is_admin() ) {
                throw new ServicesException('Forbidden', 403);
            }
        }

        $result = array();
        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node');
        $query->propertyCondition('status', NODE_PUBLISHED);
        $query->propertyCondition('type', NODE_TYPE_REPORT);

        /**
         * Filters
         */
        if ( !empty($filter['datasource']) ) {
            $query->fieldCondition('field_report_datasource', 'value', $filter['datasource']);

            if ( !gd_account_user_is_admin() && gd_datasource_is_property($filter['datasource'], 'personal') ) {
                global $user;
                $query->propertyCondition('uid', $user->uid);
            }
        }

        if ( !empty($filter['id']) ) {
            $ids = explode(',', $filter['id']);
            $query->propertyCondition('nid', $ids);
        }

        /**
         * Sorting
         */
        if ( !empty($sort) ) {
            if ( in_array('datasource',$sort) ) {
                $query->fieldOrderBy('field_report_datasource','value');
            }
        }

        $query->propertyOrderBy('changed','DESC');
        $query->addTag('DANGEROUS_ACCESS_CHECK_OPT_OUT');

        $entities = $query->execute();

        if ( !empty($entities['node']) ) {
            $nodes = node_load_multiple(array_keys($entities['node']));

            $options = array('fields' => array('dataset', 'datasource','dashboard', 'tags'));

            if ( $fields != '*' ) {
                $fields = explode(',', $fields);
                foreach ( $fields as $f ) {
                    $options['fields'][] = $f;
                }
            }

            foreach ( $nodes as $node ) {
                if ( gd_datasource_is_published(get_node_field_value($node,'field_report_datasource')) ) {
                    $result[] = gd_report_create_api_object_from_node($node, $options);
                }
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
 * @param $id
 * @param $fields
 * @return services_error|StdClass
 */
function gd_report_resource_retrieve ( $id, $fields ) {
    try {
        $reportNode = gd_report_load($id);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $options = array('fields' => array('datasource', 'config', 'filters', 'data', 'customview', 'tags'));

        if ( $fields == '*' ) {
            return gd_report_create_api_object_from_node($reportNode, $options);
        } else {
            $fields = explode(',', $fields);
            $options['fields'] = array_merge($options['fields'], $fields);
        }

        //  TODO Why is this here and in gd_report_create_api_object_from_node
        if ( in_array('filters', $options['fields']) ) {
            $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($reportNode);
            $filters = array();
            foreach ( $ReportConfig->getFilters() as $key => $filter ) {
                $filters[$key] = (array) $filter;
                $filters[$key]['column'] = $ReportConfig->getColumnByFilterName($filter->name);
            }
            $apiObject = gd_report_create_api_object_from_node($reportNode, $options);
            $apiObject->columns = $ReportConfig->columns;
            $apiObject->filters = $filters;

        } else {
            $apiObject = gd_report_create_api_object_from_node($reportNode, $options);
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $report
 * @return services_error|StdClass
 */
function gd_report_resource_create ( $report ) {
    try {
        $report = (Object) $report;
        gd_datasource_set_active($report->datasource);

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        gd_report_resource_validate_unique_name($report);

        $reportNode = gd_report_create_node_from_api_object($report);
        gd_report_save($reportNode);

        if ( isset($reportNode->nid) ) {
            $event = new DefaultEvent();
            $event->type = 100; /* @see gd_health_monitoring_database_install() for more details */
            $event->owner = $reportNode->nid;
            EventRecorderFactory::getInstance()->record($event);
        } else {
            throw new Exception('Report create failed');
        }

        $options = array('fields' => array('dataset', 'datasource','config'));

        $apiObject = gd_report_create_api_object_from_node($reportNode, $options);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @param $report
 * @return services_error|StdClass
 */
function gd_report_resource_update ( $id, $report ) {
    try {
        $report = (Object) $report;
        $reportNode = gd_report_load($id);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        gd_report_resource_validate_unique_name($report,$reportNode);

        $reportNode = gd_report_create_node_from_api_object($report,$reportNode);
        gd_report_save($reportNode);

        $event = new DefaultEvent();
        $event->type = 101; // see gd_health_monitoring_database_install() for more details
        $event->owner = $reportNode->nid;
        EventRecorderFactory::getInstance()->record($event);

        $options = array('fields' => array('dataset', 'datasource','config'));
        $apiObject = gd_report_create_api_object_from_node($reportNode,$options);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @return services_error|StdClass
 */
function gd_report_resource_delete ( $id ) {
    try {
        $reportNode = gd_report_load($id);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        // check for dashboard usage and block
        $dashboardNodes = gd_report_get_dashboards($id);
        if ( !empty($dashboardNodes) ) {
            throw new ServicesException(t('Dashboards are using this report.'), 406);
        }

        if ( !gd_report_delete($id) ) {
            throw new ServicesException(t('Report not found'), 404);
        } else {
            $apiObject = new StdClass();
            $apiObject->id = $id;

            return $apiObject;
        }

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @param $report
 * @return services_error|StdClass
 */
function gd_report_resource_save_as ( $id, $report ) {
    try {
        $report = (Object) $report;
        $reportNode = gd_report_load($id);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        // setup the new name
        $report->title = $report->saveas;

        $apiObject = gd_report_resource_create($report);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @param null $name
 * @param null $datasource
 * @return services_error|StdClass
 */
function gd_report_resource_copy ( $id, $name = null, $datasource = null ) {
    try {
        $reportNode = gd_report_load($id);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $options = array('fields' => array('dataset', 'datasource','config'));
        $apiObject = gd_report_create_api_object_from_node($reportNode,$options);

        // setup the new name
        $apiObject->name = $name;
        $apiObject->datasource = $datasource;

        $apiObject = gd_report_resource_create($apiObject);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $reportId
 * @param $dashboardId
 * @return array|null|services_error
 */
function gd_report_resource_export ( $reportId, $dashboardId ) {
    try {
        $fileExtension = pathinfo($_GET['q'], PATHINFO_EXTENSION) . (isset($_REQUEST['raw']) ? '_raw' : '');

        if ( !gd_report_get_setting('export') || !gd_report_get_setting($fileExtension) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $reportNode = gd_report_load($reportId);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        $dashboardNode = gd_dashboard_load($dashboardId);
        if ( !$dashboardNode ) {
            throw new ServicesException('Not Found', 404);
        }

        if ( !gd_dashboard_access_view($dashboardNode) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($reportNode);

        // apply dashboard config, which is mainly filters
        $DashboardConfig = new GD_DashboardConfig($dashboardNode, $_REQUEST);
        $DashboardConfig->updateReportConfig($ReportConfig);

        $apiObject = $ReportConfig->getExport(isset($_REQUEST['raw']));

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * This is a special case
 *
 * @param $id
 * @param $dashboard
 * @return array|null
 */
function gd_report_resource_export_pdf ( $reportId, $dashboardId ) {
    try {
        if ( !gd_report_get_setting('export') || !gd_report_get_setting('pdf') ) {
            throw new ServicesException('Forbidden', 403);
        }

        $reportNode = gd_report_load($reportId);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($reportNode);

        $dashboardNode = gd_dashboard_load($dashboardId);

        if ($dashboardNode) {
            if ( !gd_dashboard_access_view($dashboardNode) ) {
                throw new ServicesException('Forbidden', 403);
            }

            // apply dashboard config, which is mainly filters
            $DashboardConfig = new GD_DashboardConfig($dashboardNode, $_REQUEST);
            $DashboardConfig->updateReportConfig($ReportConfig);
        }

        $apiObject = gd_report_get_export_table($ReportConfig, isset($_REQUEST['raw']));

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $report
 * @param array $filter
 * @param null $orderBy
 * @param int $offset
 * @param null $limit
 * @return services_error|stdClass
 */
function gd_report_resource_data ( $report, $filter = array(), $orderBy = NULL, $offset = 0, $limit = NULL ) {
    try {
        $report = (Object) $report;

        if ( isset($_POST['ds']) ) {
            gd_datasource_set_active($_POST['ds']); // from table
        } else {
            gd_datasource_set_active($report->datasource); // from custom view
        }

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $reportObj = new StdClass();
        $reportObj->id = isset($report->id) ? $report->id : NULL;
        $reportObj->title = isset($report->title) ? $report->title : '';
        $reportObj->config = isset($report->config) ? $report->config : null;

        if ( isset($report->scalar) ) {
            $reportObj->config = $report->scalar;
        }

        if ( !isset($reportObj->config) ) {
            throw new ServicesException('Missing config parameter', 406);
        }

        $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($reportObj);

        return _gd_report_get_paging_table_data($ReportConfig, $filter, $orderBy, $offset, $limit);
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $id
 * @param array $filter
 * @param null $orderBy
 * @param int $offset
 * @param null $limit
 * @param null $columns
 * @return array|null|services_error
 */
function gd_report_resource_get_data ( $id, $filter = array(), $orderBy = NULL, $offset = 0, $limit = NULL, $columns = NULL ) {
    try {
        $dashboardNode = gd_dashboard_load($_REQUEST['dashboard']);
        if ( !$dashboardNode ) {
            throw new ServicesException('Not Found', 404);
        }

        $reportNode = gd_report_load($id);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_dashboard_access_view($dashboardNode) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($reportNode);

        // flag to query in the raw
        if ( isset($_REQUEST['raw']) ) {
            $apiObject = $ReportConfig->getRawData();
        } else {
            $apiObject = $ReportConfig->getData();
        }

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $reportId
 * @param null $group
 * @return array|services_error
 */
function gd_report_resource_referenced ( $reportId, $group = NULL ) {
    try {
        $reportNode = gd_report_load($reportId);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $result = array('datasets' => array(), 'dashboards' => array());

        if ( $group == NULL || $group === "datasets" ) {
            $datasets = gd_report_get_datasets($reportId);
            foreach ( $datasets as $dataset ) {
                $result['datasets'][] = array('id' => $dataset->name, 'name' => $dataset->publicName);
            }
        }

        if ( $group == NULL || $group === "dashboards" ) {
            $result['dashboards'] = array();
            $dashboardNodes = gd_report_get_dashboards($reportId);
            foreach ( $dashboardNodes as $d ) {
                $item = new stdClass();
                $item->id = $d->nid;
                $item->name = $d->title;
                $result['dashboards'][] = $item;
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
 * @param $report
 * @param null $reportNode
 * @throws ServicesException
 */
function gd_report_resource_validate_unique_name ( $report, $reportNode = null ) {
    $unique = true;
    if ( !$reportNode ) {
        $unique = gd_report_name_isunique($report->title);
    } else {
        if ( isset($report->title) && strcasecmp($reportNode->title,$report->title) !== 0 ) {
            $unique = gd_report_name_isunique($report->title);
        }
    }
    if ( !$unique ) {
        throw new ServicesException(t('A report named "'.check_plain($report->title).'" already exists. Please enter a unique report name'), 406);
    }
}

function gd_report_resource_lookup($report, $column, $query = NULL, $offset = 0, $limit = NULL) {
    try {
        gd_datasource_set_active($report['datasource']);
        $config = new GD_ReportConfig($report['config']);

        $options = array(
            'parameters' => array()
        );
        if (!empty($_REQUEST['appliedFilters'])) {
            foreach ($_REQUEST['appliedFilters'] as $f) {
                $c = $config->getColumnByFilterName($f['name']);
                if (isset($c)) {
                    $options['parameters'][$c->name] = OperatorFactory::getInstance()->initiateHandler($f['operator'], $f['value']);
                }
            }
        }

        return gd_report_lookup_column($config, $column, $query, $offset, $limit, $options);
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}