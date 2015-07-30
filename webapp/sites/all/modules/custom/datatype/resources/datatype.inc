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


function gd_datatype_resource_access () {
    return user_is_logged_in();
}

function gd_datatype_resource_index ( $datasourceName = null ) {
    try {
        if ( $datasourceName ) {
            gd_datasource_set_active($datasourceName);
        }
        return gd_data_controller_ui_metadata_get_datatype_ui_metadata();
    } catch ( Exception $e ) {
        return gd_admin_ui_service_exception_handler($e);
    }
}