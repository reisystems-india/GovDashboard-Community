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


function gd_dashboard_resource_access ( $op = 'view', $args = array() ) {
    return user_is_logged_in();
}

/**
 * Dashboard service index operation
 *
 * @param null $fields
 * @param null $page
 * @param null $limit
 * @param null $sort
 * @param null $filter
 * @return array|services_error
 */
function gd_dashboard_resource_index (  $fields = null, $page = null, $limit = null, $sort = null, $filter = null ) {
    try {
        $options = array('fields'=>array(),'filters'=>array());

        if ( $filter !== null ) {
            if ( !empty($filter['groups']) ) {
                foreach ( $filter['groups'] as $g ) {
                    $options['filters']['groups'][] = $g;
                }
            }
            if ( !empty($filter['datasource']) ) {
                $options['filters']['datasource'] = $filter['datasource'];
                gd_datasource_set_active($filter['datasource']);
            }
        }

        if ( $fields !== null ) {
            $fields = explode(',',$fields);
            foreach ( $fields as $f ) {
                $options['fields'][] = $f;
            }
        }

        $dashboards = array();

        // FYI this group filter is some crazy junk the account management section demanded.
        if ( !empty($options['filters']['groups']) ) {
            $count = 0;
            foreach ( $options['filters']['groups'] as $groupId ) {
                if ( $count === 0 ) {
                    // this is for the group currently in view
                    $result = gd_account_group_get_dashboards($groupId);
                } else {
                    // these are for all the groups the admin user is a member of
                    $result = gd_account_group_get_dashboards($groupId,true);
                }
                foreach ( $result as $dashboard ) {
                    $dashboards[$dashboard->nid] = $dashboard;
                }
                $count++;
            }
        } else {
            if ( !empty($filter['datasource']) ) {
                $dashboards = gd_account_user_get_dashboards(null,gd_datasource_get_active());
            } else {
                $dashboards = gd_account_user_get_dashboards();
            }
        }

        $result = array();
        foreach ( $dashboards as $node ) {
            $result[] = gd_dashboard_create_api_object_from_node($node,$options);
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
function gd_dashboard_resource_retrieve ( $id, $fields ) {
    try {
        $dashboardNode = gd_dashboard_load($id);
        if ( !$dashboardNode ) {
            throw new ServicesException('Not Found', 404);
        }

        $datasourceName = get_node_field_value($dashboardNode,'field_dashboard_datasource');
        gd_datasource_set_active($datasourceName);

        if ( !gd_dashboard_access_view($dashboardNode) ) {
            throw new ServicesException('Forbidden', 403);
        }

        $options = array('fields'=>array('filters','drilldowns','css'));

        if ( $fields != '*' ) {
            $fields = explode(',',$fields);
            foreach ( $fields as $f ) {
                $options['fields'][] = $f;
            }
        }

        $apiObject = gd_dashboard_create_api_object_from_node($dashboardNode,$options);

        if ( !empty($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * Dashboard service create operation
 *
 * @param $dashboard
 * @return mixed|StdClass
 */
function gd_dashboard_resource_create ( $dashboard ) {
    try {
        $dashboard = (Object) $dashboard;
        gd_datasource_set_active($dashboard->datasource);

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        gd_dashboard_resource_validate_unique_name($dashboard);

        $dashboardNode = gd_dashboard_create_node_from_api_object($dashboard);
        gd_dashboard_save($dashboardNode);

        if ( isset($dashboardNode->nid) ) {
            $event = new DefaultEvent();
            $event->type = 100; /* @see gd_health_monitoring_database_install() for more details */
            $event->owner = $dashboardNode->nid;
            EventRecorderFactory::getInstance()->record($event);
        } else {
            throw new Exception('Dashboard create failed');
        }

        $options = array('fields'=>array('filters','drilldowns','reports'));

        $apiObject = gd_dashboard_create_api_object_from_node($dashboardNode,$options);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * Dashboard service update operation
 *
 * @param $id
 * @param $dashboard
 * @return mixed|StdClass
 */
function gd_dashboard_resource_update ( $id, $dashboard ) {
    try {
        $dashboard = (Object) $dashboard;
        $dashboardNode = gd_dashboard_load($id);
        if ( !$dashboardNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($dashboardNode,'field_dashboard_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        gd_dashboard_resource_validate_unique_name($dashboard,$dashboardNode);

        $dashboardNode = gd_dashboard_create_node_from_api_object($dashboard,$dashboardNode);
        gd_dashboard_save($dashboardNode);

        $event = new DefaultEvent();
        $event->type = 101; // see gd_health_monitoring_database_install() for more details
        $event->owner = $dashboardNode->nid;
        EventRecorderFactory::getInstance()->record($event);

        $options = array('fields'=>array('filters','drilldowns','reports','css'));

        $apiObject = gd_dashboard_create_api_object_from_node($dashboardNode,$options);

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * Dashboard service delete operation
 *
 * @param $id
 * @return bool
 */
function gd_dashboard_resource_delete ( $id ) {
    try {
        $dashboardNode = gd_dashboard_load($id);
        if ( !$dashboardNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($dashboardNode,'field_dashboard_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        // delete all links to the deleted dashboard
        $dashboards = gd_dashboard_findall_by_datasource(LOAD_ENTITY,get_node_field_value($dashboardNode,'field_dashboard_datasource'));
        foreach ( $dashboards as $dashboard ) {
            $DashboardConfig = new GD_DashboardConfig($dashboard);
            $keep = array();
            foreach ( $DashboardConfig->drilldowns as $drilldown ) {
                if ( $drilldown->dashboard != $dashboardNode->nid ) {
                    $keep[] = $drilldown;
                }
            }
            if ( count($keep) != count($DashboardConfig->drilldowns) ) {
                $DashboardConfig->drilldowns = $keep;
                $dashboard->field_dashboard_config[$dashboard->language][0]['value'] = $DashboardConfig->toJson();
                gd_dashboard_save($dashboard);
            }
        }

        // finally un-publish the dashboard itself
        node_unpublish_action($dashboardNode);
        gd_dashboard_save($dashboardNode);

        $event = new DefaultEvent();
        $event->type = 102; // see gd_health_monitoring_database_install() for more details
        $event->owner = $dashboardNode->nid;
        EventRecorderFactory::getInstance()->record($event);

        $apiObject = new StdClass();
        $apiObject->id = $dashboardNode->nid;

        if ( isset($_SESSION['messages']['error']) ) {
            return gd_admin_ui_service_session_error_handler();
        }

        return $apiObject;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $dashboard
 * @param $reportId
 * @param array $filters
 * @param null $orderBy
 * @param int $offset
 * @param null $limit
 * @return services_error|stdClass
 */
function gd_dashboard_resource_report_data_builder ( $dashboard, $reportId, $filters = array(), $orderBy = null, $offset = 0, $limit = null ) {
    try {
        $dashboard = json_decode($dashboard);

        $reportNode = gd_report_load($reportId);
        if ( !$reportNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($reportNode,'field_report_datasource'));

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        return gd_dashboard_get_paging_data(new GD_DashboardConfig($dashboard), $reportId, $filters, $orderBy, $offset, $limit);
    } catch (Exception $e) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $dashboardId
 * @param $reportId
 * @param array $filters
 * @param null $orderBy
 * @param int $offset
 * @param null $limit
 * @return services_error|stdClass
 */
function gd_dashboard_resource_report_data_viewer ( $dashboardId, $reportId, $filters = array(), $orderBy = null, $offset = 0, $limit = null ) {
    try {
        $dashboardNode = gd_dashboard_load($dashboardId);
        if ( !$dashboardNode ) {
            throw new ServicesException('Not Found', 404);
        }

        gd_datasource_set_active(get_node_field_value($dashboardNode,'field_dashboard_datasource'));

        if ( !gd_dashboard_access_view($dashboardNode) ) {
            throw new ServicesException('Forbidden', 403);
        }

        return gd_dashboard_get_paging_data(new GD_DashboardConfig($dashboardNode), $reportId, $filters, $orderBy, $offset, $limit);
    } catch (Exception $e) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $dashboard
 * @param null $dashboardNode
 * @throws ServicesException
 */
function gd_dashboard_resource_validate_unique_name ( $dashboard, $dashboardNode = null ) {
    $unique = true;
    if ( !$dashboardNode ) {
        $unique = gd_dashboard_name_isunique($dashboard->name);
    } else {
        if ( isset($dashboard->name) && strcasecmp($dashboardNode->title,$dashboard->name) !== 0 ) {
            $unique = gd_dashboard_name_isunique($dashboard->name);
        }
    }
    if ( !$unique ) {
        throw new ServicesException(t('A dashboard named "'.check_plain($dashboard->name).'" already exists. Please enter a unique dashboard name'), 406);
    }
 }

function gd_dashboard_resource_lookup($dashboard, $filter, $query = '*', $offset = 0, $limit = NULL) {
    try {
        $dashboard = json_decode($dashboard);
        gd_datasource_set_active($dashboard->datasource);
        $config = new GD_DashboardConfig($dashboard);
        $reports = $config->getReports();
        $results = array(
            'count' => 0,
            'data' => array()
        );

        foreach ($reports as $report) {
            $rConfig = $config->getReportConfig($report->nid);
            $column = $rConfig->getColumnByFilterName($filter);
            if (isset($column)) {
                $l = ($offset+$limit) - $results['count'];
                if ($l <= 0) break;

                $options = array(
                    'parameters' => array()
                );

                if (!empty($_REQUEST['appliedFilters'])) {
                    foreach ($_REQUEST['appliedFilters'] as $f) {
                        $c = $rConfig->getColumnByFilterName($f['name']);
                        if (isset($c) && $c->name != $column->name) {
                            $options['parameters'][$c->name] = OperatorFactory::getInstance()->initiateHandler($f['operator'], $f['value']);
                        }
                    }
                }

                $result = gd_report_lookup_column($rConfig, $column->name, $query, $offset, $l, $options);
                $results['data'] = array_unique(array_merge($results['data'], $result['data']));
                $results['count'] = count($results['data']);
            }
        }

        if ($offset) {
            //  Can't do the offset till we have the complete list of lookup values
            $results['data'] = array_slice($results['data'], $offset);
            $results['count'] = $results['count'] - $offset;
        }

        return $results;

    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

