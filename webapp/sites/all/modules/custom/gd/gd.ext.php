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


function gd_ext () {

    ob_get_clean();

    if ( !empty($_REQUEST['datasource']) ) {
        gd_datasource_set_active($_REQUEST['datasource']);
    }

    $ext = array();
    $dashboards = gd_dashboard_findall_by_datasource(LOAD_ENTITY);
    foreach ( $dashboards as $dashboard ) {
        $ext[] = array('id'=>$dashboard->nid,'title'=>$dashboard->title);
    }

    $response = new stdClass();
    $response->status = new stdClass();
    $response->status->code = 200;
    $response->status->message = 'OK';

    if ( isset($_REQUEST['id']) ) {
        $response->data = array('id'=>$_REQUEST['id'], 'title'=>$dashboards[$_REQUEST['id']]->title);
    }
    else {
        $response->data = $ext;
    }

    module_invoke_all('gd_ext_response_alter',$response);

    echo \GD\Utility\Json::getPayload($response,$_GET['callback']);

    gd_get_session_messages();

    drupal_exit();
}

function gd_css_ext () {
    ob_start();
    header('Content-Type: text/css; charset=UTF-8');

    $cssFiles = array(
        '/sites/all/modules/custom/webui/external/css/gd-jquery-ui.css',
        '/sites/all/modules/custom/webui/external/css/global.css',
        '/sites/all/libraries/jquery-ui/css/ui-lightness/jquery.ui.selectmenu.css',
        '/sites/all/libraries/datatables/media/css/jquery.dataTables.min.css',
        '/sites/all/libraries/bootstrap/css/bootstrap.min.css',
        '/sites/all/modules/custom/gd/css/Component.css'
    );

    if (isset($_REQUEST['theme'])) {
        $pathToTheme = '/' . path_to_theme() . '/css/viewer/';
        $cssFiles[] = $pathToTheme . 'table.css';
        $cssFiles[] = $pathToTheme . 'filter.css';
        $cssFiles[] = $pathToTheme . 'highcharts.css';
        $cssFiles[] = $pathToTheme . 'reportMenu.css';
        $cssFiles[] = $pathToTheme . 'report.css';
    }

    foreach ( $cssFiles as $file ) {
        $assetFilter = new \GD\Utility\CssAssetFilter($file);
        echo $assetFilter->embedImages()->fixFontPath()->getCss()."\n";
    }

    gd_get_session_messages();

    echo ob_get_clean();
    drupal_exit();
}

function gd_js_ext () {
    ob_start();
    header('Content-Type: text/javascript; charset=UTF-8');

    echo '(function(global){ '."\n\n";

    foreach ( \GD\Js\Registry::getInstance()->getVendorFiles() as $file ) {
        echo file_get_contents($file)."\n\n";
    }

    foreach ( \GD\Js\Registry::getInstance()->getFiles() as $file ) {
        echo file_get_contents($file)."\n\n";
    }

    echo 'GD.options.host = "'.GOVDASH_HOST.'";'."\n\n";
    echo 'GD.options.themeList = ["table.css", "filter.css", "highcharts.css", "reportMenu.css", "report.css"];'."\n\n";
    echo 'GD.options.themePath = "' . path_to_theme() . '/css/viewer/";'."\n\n";
    echo 'GD.options.csrf = "' . drupal_get_token('services') . '";'."\n";

    echo "\n\n";
    echo file_get_contents(dirname(__FILE__) . '/js/apps/Ext.js')."\n\n";

    echo 'global.GD_Highcharts = Highcharts;'."\n";
    echo 'global.GD_jQuery = jQuery;'."\n";

    echo "\n";
    echo '})(typeof window === "undefined" ? this : window);'."\n";


    gd_get_session_messages();

    echo ob_get_clean();
    drupal_exit();
}