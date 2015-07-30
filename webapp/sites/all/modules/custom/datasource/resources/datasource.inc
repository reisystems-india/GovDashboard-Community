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
 * @param $op
 * @param $args
 * @return bool
 */
function gd_datasource_resource_access ( $op, $args ) {
    return user_is_logged_in();
}

/**
 * @return array|services_error
 */
function gd_datasource_resource_index () {
    try {
        return array_values(gd_account_user_get_datasources());
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasourceName
 * @return null|services_error|stdClass
 */
function gd_datasource_resource_retrieve ( $datasourceName ) {
    try {
        $datasource = gd_datasource_find($datasourceName);
        if ( !$datasource ) {
            throw new ServicesException('Not found', 404);
        }

        gd_datasource_set_active($datasource->name);

        if ( !gd_account_user_is_admin() && !gd_account_user_is_datasource_admin(null,gd_datasource_get_active()) ) {
            throw new ServicesException('Forbidden', 403);
        }

        return $datasource;
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}

/**
 * @param $datasourceName
 * @return bool|services_error
 */
function gd_datasource_resource_setactive ( $datasourceName ) {
    try {
        $datasource = gd_datasource_find($datasourceName);
        if ( !$datasource ) {
            throw new ServicesException('Not found', 404);
        }
        gd_datasource_set_active($datasource->name);
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }

    return true;
}

