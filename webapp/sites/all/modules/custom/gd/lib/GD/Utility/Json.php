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


namespace GD\Utility;

class Json {

    /**
     * jsonp payload wrapper
     *
     * prepend jsonp callbacks with a comment to prevent the rosetta-flash vulnerability
     */
    public static function getPayload ( $data, $callback = null ) {
        if ( !isset($callback) ) {
            drupal_add_http_header('Content-Type', 'application/json');
            return json_encode($data);
        } else {
            drupal_add_http_header('Content-Type', 'text/javascript');
            drupal_add_http_header('X-Content-Type-Options', 'nosniff');
            return '/**/'.$callback.'('.json_encode($data).');';
        }
    }

}