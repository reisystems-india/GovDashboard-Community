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
 * @return array
 * @throws Exception
 * @throws IllegalArgumentException
 */
function gd_dashboard_index_variables () {
    $dashboards = array();
    $active_datasource_name = gd_datasource_find_active();
    $current_dashboard = gd_dashboard_get_current();
    global $user;

    if ( gd_account_user_is_admin() ) {

        // can see all datasources
        $datasources = gd_datasource_get_all();

        if ( $current_dashboard ) {
            gd_datasource_set_active(get_node_field_value($current_dashboard,'field_dashboard_datasource'));
        } else if ( !$active_datasource_name ) {
            if (isset($_GET['ds'])) {
                gd_datasource_set_active($_GET['ds']);
            } else {
                gd_datasource_set_active(key($datasources));
            }
        }

        // don't pick up any dashboards if there are no published datamarts - causes logic bomb further down
        if ( !empty($datasources) ) {
            $dashboards = gd_dashboard_findall_by_datasource(LOAD_ENTITY);
        }

    } else if ( $user->uid ) {

        // get view privileges for all dashboards
        $results = gd_account_user_get_dashboards();

        // pick up the datasources from the dashboards
        $datasources = gd_account_user_get_datasources();
        foreach ( $results as $dashboard ) {
            if ( !isset($datasources[get_node_field_value($dashboard,'field_dashboard_datasource')]) ) {
                $datasource = gd_datasource_get(get_node_field_value($dashboard,'field_dashboard_datasource'));
                $datasources[$datasource->name] = $datasource;
            }
        }

        // set current datasource
        if ( $current_dashboard ) {
            gd_datasource_set_active(get_node_field_value($current_dashboard,'field_dashboard_datasource'));
        } else {
            if ( !$active_datasource_name ) {
                if ( isset($_GET['ds']) && isset($datasources[$_GET['ds']]) ) {
                    gd_datasource_set_active($_GET['ds']);
                } else {
                    reset($results);
                    try {
                        if (!empty($results[key($results)])) {
                            $active_datasource_name = get_node_field_value($results[key($results)],'field_dashboard_datasource');
                            gd_datasource_set_active($active_datasource_name);
                        } else {
                            return 'You have not been granted permission to view any dashboards.';
                        }
                    } catch (Exception $e) {
                        drupal_set_message('No default datasource set.', 'error');
                        LogHelper::log_error($e->getMessage());
                    }
                }
            }
        }

        // remove dashboards that do not belong to datasource of current dashboard
        $active_datasource_name = gd_datasource_get_active();
        $dashboards = array();
        foreach ( $results as $key => $dashboard ) {
            if ( $active_datasource_name === get_node_field_value($dashboard,'field_dashboard_datasource') ) {
                $dashboards[$key] = $dashboard;
            }
        }

        // remove dashboards that were not created by the user
        if ( gd_datasource_is_property($active_datasource_name, 'personal') ) {
            global $user;
            $userCreatedDashboards = array();
            foreach ( $dashboards as $key => $dashboard ) {
                if ( $user->uid == $dashboard->uid ) {
                    $userCreatedDashboards[$key] = $dashboard;
                }
            }
            $dashboards = $userCreatedDashboards; // overwrite dashboard list
        }

    } else {

        // get datasources that belong to the dashboards
        // weed out dashboards with missing datasource
        $datasources = array();
        $publicDashboards = array();
        foreach ( gd_dashboard_get_dashboards_public(LOAD_ENTITY) as $dashboard ) {
            $datasourceName = get_node_field_value($dashboard,'field_dashboard_datasource');
            $datasource = gd_datasource_find($datasourceName);
            if ( !$datasource ) {
                continue;
            }
            $publicDashboards[$dashboard->nid] = $dashboard;
            if ( !isset($datasources[$datasourceName]) ) {
                $datasources[$datasource->name] = $datasource;
            }
        }

        // set current datamart
        if ( $current_dashboard ) {
            gd_datasource_set_active(get_node_field_value($current_dashboard,'field_dashboard_datasource'));
        } else {
            if ( isset($_GET['ds']) && isset($datasources[$_GET['ds']]) ) {
                gd_datasource_set_active($_GET['ds']);
            } else {
                gd_datasource_set_active(get_node_field_value($publicDashboards[key($publicDashboards)],'field_dashboard_datasource'));
            }
        }

        // remove dashboards that do not belong to datamart of current dashboard
        $active_datasource_name = gd_datasource_get_active();
        $dashboards = array();
        foreach ( $publicDashboards as $key => $dashboard ) {
            if ( $active_datasource_name === get_node_field_value($dashboard,'field_dashboard_datasource') ) {
                $dashboards[$key] = $dashboard;
            }
        }
    }

    reset($datasources);
    reset($dashboards);

    // sort the dashboard list by name
    usort($dashboards, function($a, $b) {
        if (strtolower($a->title) === strtolower($b->title)){
            return strnatcmp($a->title,$b->title);
        }
        return strnatcasecmp($a->title,$b->title);
    });

    // which dashboard to display
    if ( $current_dashboard ) {
        $dashboard = $current_dashboard;
    } else if (!empty($dashboards) ) {
        $dashboard = $dashboards[0];
    } else {
        $dashboard = null;
    }

    $display_dashboards = array();
    if ( !empty($dashboards) ) {
        $dashboard_ids = array(); // index of any parents from $dashboards
        $drilldown_dashboard_ids = array();
        foreach ( $dashboards as $d ) {
            $config = new GD_DashboardConfig($d);
            $dashboard_ids[] = (int)$d->nid;
            foreach( $config->drilldowns as $drilldown) {
                if ( is_object($drilldown->dashboard) ) {
                    $drilldown_dashboard_ids[] = (int)$drilldown->dashboard->id; // for backwards compatibility
                } else {
                    $drilldown_dashboard_ids[] = (int)$drilldown->dashboard;
                }
            }
        }
        $drilldown_dashboard_ids = array_unique($drilldown_dashboard_ids);
        $display_dashboard_ids = array_diff($dashboard_ids, $drilldown_dashboard_ids);
        $display_dashboards = array();
        foreach ( $dashboards as $d ) {
            if ( in_array($d->nid,$display_dashboard_ids) ) {
                $display_dashboards[] = $d;
            }
        }
        // if initial dashboard is a drilldown dashboard, load first non-drilldown dashboard instead
        if ( in_array($dashboard->nid, $drilldown_dashboard_ids) && empty($_GET['id']) ) {
            $dashboardKeys = array_keys($display_dashboard_ids);
            $dashboard = $dashboards[array_shift($dashboardKeys)];
        }
    }

    // force a dashboard id into the url for javascript libs
    // TODO doing a redirect is wasteful, find some other way
    if ( empty($_GET['id']) &&  isset($dashboard) ) {
        drupal_goto('dashboards',array('query'=>array('id'=>$dashboard->nid)));
    }

    foreach ( $datasources as $k => $ds ) {
        if ( $ds->name == gd_datasource_get_active() ) {
            $datasources[$k]->active = true;
        }
    }

    return array($datasources, $dashboard, $display_dashboards);
}

/**
 * @return array
 * @throws Exception
 * @throws IllegalArgumentException
 */
function gd_dashboard_public_index_variables () {
    $current_dashboard = gd_dashboard_get_current();

    // get datasources that belong to the dashboards
    // weed out dashboards with missing datasource
    $datasources = array();
    $publicDashboards = array();
    foreach ( gd_dashboard_get_dashboards_public(LOAD_ENTITY) as $dashboard ) {
        $datasourceName = get_node_field_value($dashboard,'field_dashboard_datasource');
        $datasource = gd_datasource_find($datasourceName);
        if ( !$datasource ) {
            continue;
        }
        $publicDashboards[$dashboard->nid] = $dashboard;
        if ( !isset($datasources[$datasourceName]) ) {
            $datasources[$datasource->name] = $datasource;
        }
    }

    // set current datamart
    if ( $current_dashboard ) {
        gd_datasource_set_active(get_node_field_value($current_dashboard,'field_dashboard_datasource'));
    } else {
        if ( isset($_GET['ds']) && isset($datasources[$_GET['ds']]) ) {
            gd_datasource_set_active($_GET['ds']);
        } else {
            gd_datasource_set_active(get_node_field_value($publicDashboards[key($publicDashboards)],'field_dashboard_datasource'));
        }
    }

    // remove dashboards that do not belong to datamart of current dashboard
    $active_datasource_name = gd_datasource_get_active();
    $dashboards = array();
    foreach ( $publicDashboards as $key => $dashboard ) {
        if ( $active_datasource_name === get_node_field_value($dashboard,'field_dashboard_datasource') ) {
            $dashboards[$key] = $dashboard;
        }
    }

    reset($datasources);
    reset($dashboards);

    // sort the dashboard list by name
    usort($dashboards, function($a, $b) {
        if (strtolower($a->title) === strtolower($b->title)){
            return strnatcmp($a->title,$b->title);
        }
        return strnatcasecmp($a->title,$b->title);
    });

    // which dashboard to display
    if ( $current_dashboard ) {
        $dashboard = $current_dashboard;
    } else if (!empty($dashboards) ) {
        $dashboard = $dashboards[0];
    } else {
        $dashboard = null;
    }

    $display_dashboards = array();
    if ( !empty($dashboards) ) {
        $dashboard_ids = array(); // index of any parents from $dashboards
        $drilldown_dashboard_ids = array();
        foreach ( $dashboards as $d ) {
            $config = new GD_DashboardConfig($d);
            $dashboard_ids[] = (int)$d->nid;
            foreach( $config->drilldowns as $drilldown) {
                if ( is_object($drilldown->dashboard) ) {
                    $drilldown_dashboard_ids[] = (int)$drilldown->dashboard->id; // for backwards compatibility
                } else {
                    $drilldown_dashboard_ids[] = (int)$drilldown->dashboard;
                }
            }
        }
        $drilldown_dashboard_ids = array_unique($drilldown_dashboard_ids);
        $display_dashboard_ids = array_diff($dashboard_ids, $drilldown_dashboard_ids);
        $display_dashboards = array();
        foreach ( $dashboards as $d ) {
            if ( in_array($d->nid,$display_dashboard_ids) ) {
                $display_dashboards[] = $d;
            }
        }
        // if initial dashboard is a drilldown dashboard, load first non-drilldown dashboard instead
        if ( in_array($dashboard->nid, $drilldown_dashboard_ids) && empty($_GET['id']) ) {
            $dashboardKeys = array_keys($display_dashboard_ids);
            $dashboard = $dashboards[array_shift($dashboardKeys)];
        }
    }

    // force a dashboard id into the url for javascript libs
    // TODO doing a redirect is wasteful, find some other way
    if ( empty($_GET['id']) &&  isset($dashboard) ) {
        drupal_goto('public/dashboards',array('query'=>array('id'=>$dashboard->nid)));
    }

    foreach ( $datasources as $k => $ds ) {
        if ( $ds->name == gd_datasource_get_active() ) {
            $datasources[$k]->active = true;
        }
    }

    return array($datasources, $dashboard, $display_dashboards);
}

/**
 * @return array
 */
function gd_dashboard_page_index () {
    global $user;
    $event = new DefaultEvent();

    if ( arg(0) == 'public' && !gd_dashboard_get_setting('public') ) {
        return MENU_NOT_FOUND;
    }

    if ( !empty($_GET['id']) ) {
        $dashboardNode = gd_dashboard_load($_GET['id']);
        if ( $dashboardNode ) {
            gd_datasource_set_active(get_node_field_value($dashboardNode,'field_dashboard_datasource'));
            if ( !gd_dashboard_access_view($dashboardNode) ) {
                return MENU_ACCESS_DENIED;
            }
        } else {
            return MENU_NOT_FOUND;
        }
    }

    if ( arg(0) == 'public' ) {
        list($datasources, $dashboardNode, $dashboards) = gd_dashboard_public_index_variables();
    } else {
        list($datasources, $dashboardNode, $dashboards) = gd_dashboard_index_variables();
    }

    ob_start();

    if ( arg(0) == 'public' ) {
        drupal_add_http_header('Cache-Control','no-transform,public,max-age=3600,s-maxage=3600');
        drupal_add_http_header('Expires',gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
    }

    if ( empty($datasources) ) {
        echo '<div id="dashboard-view" class="gd-container"><p>There are no topics. Please contact your site administrator to create a topic.</p></div>';
    } else if ( empty($dashboards) ) {
        echo '<div id="dashboard-view" class="gd-container"><p>There are no dashboards set up for this topic.</p></div>';
    } else {
        try {
            $DashboardConfig = new GD_DashboardConfig($dashboardNode, $_GET);

            drupal_add_library('gd_dashboard', 'GD_Dashboard_View');

            echo '<div id="dashboard-view" class="gd-container">';

            echo '<div class="row">';

            echo '  <div class="col-md-6">';
            echo '    <h2>'.$dashboardNode->title.'</h2>';
            if ( get_node_field_value($dashboardNode, 'field_dashboard_desc') ) {
                echo '<p>'.get_node_field_value($dashboardNode, 'field_dashboard_desc').'</p>';
            }
            echo '  </div>';
            echo '  <div class="col-md-6">';

            echo '<div class="pull-right">';
            if ( arg(0) != 'public' && (gd_account_user_is_admin() || gd_account_user_is_datasource_admin($user, gd_datasource_get_active())) ) {
                echo '<a role="button" type="button" id="editButton" tabindex="100" class="btn btn-default gd-dashboard-editbtn" href="/cp/dashboard/'.$dashboardNode->nid.'">Edit</a>';
            }

            if ( gd_dashboard_get_setting('export') && $DashboardConfig->isExportable() ) {
                echo ' <button role="button" type="button" id="exportButton" tabindex="100" class="btn btn-default btn-gd-dashboard-export" data-dashboard="'.$dashboardNode->nid.'">Export</button>';
            }
            if ( gd_dashboard_get_setting('print') && $DashboardConfig->isPrintable() ) {
                echo '<button role="button" type="button" style="margin-left:3px;" id="printButton" tabindex="100" class="btn btn-default btn-gd-dashboard-print" href='."javascript:void(0)".'>Print</button>';
            }

            echo '</div>';

            echo '  </div>';
            echo '</div>';

            $options = array();
            if ( $DashboardConfig->isPublic() && arg(0) == 'public' ) {
                $options['public'] = TRUE;
            }

            $configView = new GD_DashboardView($DashboardConfig);
            echo $configView->getView($options);
            echo '</div>';

            $DashboardConfig->attachRequiredLibs(); // must be called after building view, or libs won't be set yet

        } catch (Exception $e) {
            LogHelper::log_error($e);
            echo '<div id="dashboard-view" class="gd-container"><p class="messages error">Dashboard could not be rendered. '.$e->getMessage().'</p></div>';
        }
    }

    if ( arg(0) == 'public' ) {
        module_invoke_all('gd_dashboard_public_index_alter');
    } else {
        module_invoke_all('gd_dashboard_index_alter');
    }

    if (!empty($_REQUEST['export-view'])) {
        $page = array(
            '#show_messages' => false,
            '#theme' => 'page__dashboard__export__view',
            '#type' => 'page',
            'content' => array(
                'system_main' => array(
                    '#markup' => ob_get_clean()
                )
            ),
            'variables' => array(
                'datasources' => $datasources,
                'dashboard' => $dashboardNode,
                'dashboards' => $dashboards
            )
        );
    } else {
        $page = array(
            '#show_messages' => false,
            '#theme' => 'page__dashboard',
            '#type' => 'page',
            'content' => array(
                'system_main' => array(
                    '#markup' => ob_get_clean()
                )
            ),
            'variables' => array(
                'datasources' => $datasources,
                'dashboard' => $dashboardNode,
                'dashboards' => $dashboards
            )
        );
    }

    if (isset($dashboardNode->nid)) {
        $event->type = 1; // see gd_health_monitoring_database_install() for more details
        $event->owner = $dashboardNode->nid;

        EventRecorderFactory::getInstance()->record($event);
    }

    return $page;
}

/**
 * @param $dashboardNode
 * @return array
 */
function gd_dashboard_build_page ( $dashboardNode ) {
    $event = new DefaultEvent();
    gd_datasource_set_active(get_node_field_value($dashboardNode,'field_dashboard_datasource'));

    ob_start();

    /**
     * Build current dashboard config
     */
    $DashboardConfig = new GD_DashboardConfig($dashboardNode,$_GET);
    drupal_add_library('gd_dashboard', 'GD_Dashboard_View');

    print '<div id="dashboard-view" class="gd-container">';


    // dashboard view
    echo '<div class="row">';

    echo '  <div class="col-md-6">';
    echo '    <h2>'.$dashboardNode->title.'</h2>';
    if ( get_node_field_value($dashboardNode, 'field_dashboard_desc') ) {
        echo '<p>'.get_node_field_value($dashboardNode, 'field_dashboard_desc').'</p>';
    }
    echo '  </div>';
    echo '  <div class="col-md-6">';

    echo '<div class="pull-right">';
    // is not public
    if ( arg(0) != 'public' ) {
        $edit = false;
        if ( gd_account_user_is_admin() || gd_account_user_is_datasource_admin(null,$DashboardConfig->getDatasource()) ) {
            $edit = true;
        }

        if ($edit) {
            echo '<a role="button" type="button" id="editButton" tabindex="100" class="btn btn-default gd-dashboard-editbtn" href="/cp/dashboard/'.$dashboardNode->nid.'">Edit</a>';
        }
    }

    if ( gd_dashboard_get_setting('export') && $DashboardConfig->isExportable() ) {
        echo ' <button role="button" type="button" id="exportButton" tabindex="100" class="btn btn-default gd-dashboard-exportbtn" data-dashboard="'.$dashboardNode->nid.'">Export</button>';
    }
    echo '</div>';

    echo '  </div>';
    echo '</div>';

    $options = array();
    if ( $DashboardConfig->isPublic() && arg(0) == 'public' ) {
        $options['public'] = TRUE;
        drupal_add_http_header('Cache-Control','no-transform,public,max-age=3600,s-maxage=3600');
        drupal_add_http_header('Expires',gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
    }
    $configView = new GD_DashboardView($DashboardConfig);
    print $configView->getView($options);

    print '</div>';

    $DashboardConfig->attachRequiredLibs(); // must be called after building view, or libs won't be set yet

    $page = array(
        '#show_messages' => false,
        '#theme' => 'page',
        '#type' => 'page',
        'content' => array(
            'system_main' => array(
                '#markup' => ob_get_clean()
            )
        ),
        'post_header' => array(
            '#markup' => ''
        ),
        'pre_content' => array(
            '#markup' => ''
        )
    );

    if (isset($dashboardNode->nid)) {
        $event->type = 1; // see gd_health_monitoring_database_install() for more details
        $event->owner = $dashboardNode->nid;

        EventRecorderFactory::getInstance()->record($event);
    }

    return $page;
}

/**
 * @param $dashboardNode
 * @return array
 */
function gd_dashboard_page ( $dashboardNode ) {
    if ( !gd_dashboard_access_view($dashboardNode) ) {
        return MENU_ACCESS_DENIED;
    }
    return gd_dashboard_build_page($dashboardNode);
}

/**
 * @param $dashboardNode
 * @return array
 */
function gd_dashboard_page_public_framed ( $dashboardNode ) {
    if ( !gd_dashboard_access_view($dashboardNode) ) {
        return MENU_ACCESS_DENIED;
    }
    $page = gd_dashboard_build_page($dashboardNode);
    $page['#theme'] = 'page__framed';
    return $page;
}

/**
 * @param $dashboardNode
 * @return string
 */
function gd_dashboard_page_public_report_export ( $dashboardNode ) {
    try {
        if ( !gd_dashboard_access_view($dashboardNode) ) {
            return MENU_ACCESS_DENIED;
        }

        $config = variable_get('gd_report_config', array('export' => 0, 'print' => 0));
        $type = $_REQUEST['type'] . (isset($_REQUEST['raw']) ? '_raw' : '');
        $reportNid = $_REQUEST['report'];
        $viewFormat = null;

        if ($config['export'] == 0 || !isset($config[$type]) || $config[$type] == 0) {
            return MENU_ACCESS_DENIED;
        }

        $reportNode = gd_report_load($reportNid);
        if (!$reportNode) {
            return MENU_NOT_FOUND;
        }

        $ReportConfig = GD_ReportConfigFactory::getInstance()->getConfig($reportNode);

        // apply dashboard config, which is mainly filters
        $DashboardConfig = new GD_DashboardConfig($dashboardNode, $_REQUEST);
        $DashboardConfig->updateReportConfig($ReportConfig);

        switch ( $type ) {
            case 'xls':
            case 'xls_raw':
                $data = $ReportConfig->getExport(isset($_REQUEST['raw']));
                $viewFormat = new GD_ServicesViewFormat_Excel('public', $reportNid);
                break;

            case 'csv':
            case 'csv_raw':
                $data = $ReportConfig->getExport(isset($_REQUEST['raw']));
                $viewFormat = new GD_ServicesViewFormat_CSV('public', $reportNid);
                break;

            case 'pdf':
                $data = gd_report_get_export_table($ReportConfig, isset($_REQUEST['raw']));
                $viewFormat = new GD_ServicesViewFormat_PDF('public', $reportNid);
                break;

            default:
                $data = $ReportConfig->getExport(isset($_REQUEST['raw']));
                $viewFormat = new GD_ServicesViewFormat_CSV('public', $reportNid);
                break;
        }

        print $viewFormat->render($data);
        drupal_exit();
    } catch ( Exception $e ) {
        gd_exception_handler($e);
        drupal_set_message('An unexpected Error has occurred. Please contact your Site Administrator.', 'error');
        return ' ';
    }
}

/**
 * Report preview for admin
 * @return void
 */
function gd_dashboard_report_page_preview () {
    if ( empty($_POST['dashboard']) || empty($_POST['report']) && empty($_POST['ds']) ) {
        $message =  'Missing required params for preview';
        LogHelper::log_error($message);
        echo  $message;
    } else {
        /**
         *  Dashboard
         */
        $dashboard = json_decode($_POST['dashboard']);

        /**
         * Report
         */
        $report = json_decode($_POST['report']);
        gd_datasource_set_active($_POST['ds']);

        $DashboardConfig = new GD_DashboardConfig($dashboard);

        foreach ( $DashboardConfig->items as $item ) {
            if ( $report->id == $item->content ) {
                $options = array('admin' => true);
                $html = $DashboardConfig->getItemReportView($item, $options);

                print $DashboardConfig->getDashboardCustomView()
                    . $html->header
                    . $html->body
                    . $html->footer;
            }
        }
    }

    drupal_exit();
}

/**
 * @return array
 */
function gd_dashboard_report_print () {
    $config = variable_get('gd_report_config', array('export' => 0, 'print' => 0));
    if ($config['print'] == 0) {
        return MENU_ACCESS_DENIED;
    }

    drupal_add_http_header('Cache-Control','no-transform,public,max-age=3600,s-maxage=3600');
    drupal_add_http_header('Expires',gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
    drupal_add_library('gd_dashboard', 'GD_Dashboard_View');

    $page = array(
        '#show_messages' => false,
        '#theme' => 'page__framed',
        '#type' => 'page',
        'content' => array(
            'system_main' => array(
                '#markup' => ''
            )
        )
    );

    return $page;
}

/**
 * @param $dashboardNode
 * @param $reportNid
 * @return int|null
 */
function gd_dashboard_report_view ( $dashboardNode, $reportNid ) {
    ob_start();
    try {
        if ( !gd_dashboard_access_view($dashboardNode) ) {
            return MENU_ACCESS_DENIED;
        }

        $DashboardConfig = new GD_DashboardConfig($dashboardNode);
        gd_datasource_set_active($DashboardConfig->getDatasource());

        foreach ( $DashboardConfig->items as $item ) {
            if ( $reportNid == $item->content ) {
                $options = array();

                if (isset($_GET['dashboardBuilder']) && $_GET['dashboardBuilder'] == TRUE) {
                    $options['admin'] = TRUE;
                }

                if ( $DashboardConfig->isPublic() && arg(0) == 'public' ) {
                    $options['public'] = TRUE;
                }

                $html = $DashboardConfig->getItemReportView($item, $options);
                $output = array('header'=>$html->header, 'body'=>$html->body, 'footer'=>$html->footer);

                $response = new stdClass();
                $response->status = new stdClass();
                $response->status->code = 200;
                $response->status->message = 'OK';

                $response->data = $output;

                module_invoke_all('gd_ext_response_alter',$response);

                $messages = gd_get_session_messages();
                if ( !empty($messages['error']) ) {
                    $response->status->code = 500;
                    $response->status->message = $messages['error'];
                } else {
                    if ( !isset($_GET['callback']) && $DashboardConfig->isPublic() && arg(0) == 'public' ) {
                        drupal_add_http_header('Cache-Control','no-transform,public,max-age=3600,s-maxage=3600');
                        drupal_add_http_header('Expires',gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
                    }
                }

                echo \GD\Utility\Json::getPayload($response,$_GET['callback']);
            }
        }

        echo ob_get_clean();
        drupal_exit();
    } catch ( Exception $e ) {
        gd_exception_handler($e);
        $response = new stdClass();
        $response->status->code = 500;
        $response->status->message = $e->getMessage();
        echo \GD\Utility\Json::getPayload($response,$_GET['callback']);
        echo ob_get_clean();
        drupal_exit();
    }
}

function gd_dashboard_page_export ( $dashboardNode ) {

    // check to see if export is allowed
    if ( !gd_dashboard_get_setting('export') ) {
        LogHelper::log_notice('Exporting dashboards disabled globally.');
        return MENU_NOT_FOUND;
    }

    if ( !gd_dashboard_access_view($dashboardNode) ) {
        return MENU_ACCESS_DENIED;
    }

    $exporterPath = gd_dashboard_get_setting('export_tool_path');
    $arguments = array();

    $arguments[] = array (
        'name' => '--title',
        'value' => '\''.$dashboardNode->title.'\''
    );

    $callbackURL = GOVDASH_HOST;
    if ( user_is_logged_in() || isset($_GET['oauth_consumer_key']) ) {
        $callbackURL .= '/dashboards';
    } else if ( gd_dashboard_is_public($dashboardNode) ) {
        $callbackURL .= '/public/dashboards';
    } else {
        LogHelper::log_notice('Dashboard was requested anonymously but is not public. Requested: '.$dashboardNode->nid);
        return MENU_NOT_FOUND;
    }

    $params = $_GET;
    unset($params['q']);
    $params['export-view'] = true;
    $params['id'] = $dashboardNode->nid;
    $callbackURL .= '?'.http_build_query($params,null,'&');

    if ( !isset($_GET['oauth_consumer_key']) ) {
        foreach ($_COOKIE as $key => $value) {
            $arguments[] = array(
                'name' => '--cookie',
                'value' => '\'' . $key . '\' \'' . $value . '\''
            );
        }
    }

    $arguments[] = array (
        'name' => '--user-style-sheet',
        'value' => dirname(__FILE__) . '/css/export.css'
    );

    $arguments[] = array (
        'name' => '--javascript-delay',
        'value' => '5000'
    );

    $arguments[] = array (
        'name' => '--page-size',
        'value' => 'Letter'
    );

    $arguments[] = array (
        'name' => '--header-html',
        'value' => DRUPAL_ROOT.gd_dashboard_get_setting('export_header_path')
    );

    $arguments[] = array (
        'name' => '--footer-html',
        'value' => DRUPAL_ROOT.gd_dashboard_get_setting('export_footer_path')
    );

    $arguments[] = '--print-media-type';

    $command = $exporterPath;
    foreach ( $arguments as $arg ) {
        if ( is_array($arg) ) {
            $command .= ' ' . $arg['name'] . ' ' . escapeshellcmd($arg['value']);
        } else {
            $command .= ' '.escapeshellcmd($arg);
        }
    }

    // url input
    $command .= ' ' . escapeshellcmd($callbackURL);

    // pdf output
    $command .= ' -';

    // stderr getting logged or tossed to black hole by default
    $command .= ' 2>'.escapeshellcmd(gd_dashboard_get_setting('export_log_path'));

    // keep oauth token out of logs.
    if ( !isset($_GET['oauth_consumer_key']) ) {
        LogHelper::log_debug($command);
    }

    // generate filename title
    $filename = str_replace(' ','_',trim($dashboardNode->title));
    $filename .= '__'.date('Ymd');

    ob_start();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);
    header('Content-Description: File Transfer');
    if (strpos(php_sapi_name(), 'cgi') === false) {
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream', false);
        header('Content-Type: application/download', false);
        header('Content-Type: application/pdf', false);
    } else {
        header('Content-Type: application/pdf');
    }
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    header('Content-Transfer-Encoding: binary');
    passthru($command,$error);

    if ( !isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
        // the content length may vary if the server is using compression
        header('Content-Length: '.ob_get_length());
    }

    if ( $error ) {
        header_remove();
        ob_get_clean();
        gd_error_handler('Dashboard export failed to execute wkhtmltopdf successfully.');
        return MENU_NOT_FOUND;
    }

    ob_end_flush();
    exit();
}