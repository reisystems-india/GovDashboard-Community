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

/*
 * ACCOUNT SECTION
 */
function gd_admin_ui_page_account () {

    if ( !gd_datasource_find_active() ) {
        if ( !empty($_GET['ds']) && gd_datasource_find($_GET['ds']) ) {
            gd_datasource_set_active($_GET['ds']);
        } else {
            global $user;
            $account = user_load($user->uid);
            $datasource = gd_account_user_find_default_datasource($account);
            if ( $datasource ) {
                drupal_goto('cp/account',array('query'=>array('ds'=>$datasource->name)));
            } else {
                return MENU_NOT_FOUND;
            }
        }
    }

    if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
        return MENU_ACCESS_DENIED;
    }

    $page = gd_admin_ui_page_defaults('/cp/account');
    drupal_add_library('gd_account_admin','GD_Admin_AccountSection_Builder');
    return $page;
}

function gd_admin_ui_page_defaults ( $path ) {
    drupal_add_library('gd_admin_ui', 'GD_Admin_UI');

    $config = gd_dashboard_get_settings();

    $main = '
        <div id="gd-admin-container">
            <div class="row" id="gd-admin-header"></div>
            <div class="row" id="gd-admin-messages"></div>
            <div class="row">
                <div class="col-md-12" id="gd-admin-body">
                <script type="text/javascript">
                <!--//--><![CDATA[//><!--
                (function(global){
                    var GovDashboard = GDApplication.create({
                        position: "relative",
                        account: Account.create('.gd_admin_ui_get_account().'),
                        user: CurrentUser.create('.gd_admin_ui_get_user().'),
                        datasources: '.gd_admin_ui_get_datasources().',
                        datasource: '.gd_admin_ui_get_datasource().',
                        stats: '.gd_admin_ui_get_stats().',
                        sections: ['.implode(',',gd_admin_ui_get_sections()).'],
                        token: "'.drupal_get_token('services').'",
                        environment: {
                        }
                    });
                    global.GovDashboard = GovDashboard;
                    GovDashboard.allowPublic = ' . $config['public'] .'
                    GovDashboard.run("'. $path . '");
                    GovDashboard.draw();
                })(window);
                //--><!]]>
                </script>
                </div>
            </div>
            <div class="row" id="gd-admin-footer"></div>
        </div>
        ';

    return array(
        '#show_messages' => false,
        '#theme' => 'page__cp',
        '#type' => 'page',
        'content' => array(
            'system_main' => array(
                '#markup' => $main
            )
        )
    );
}

