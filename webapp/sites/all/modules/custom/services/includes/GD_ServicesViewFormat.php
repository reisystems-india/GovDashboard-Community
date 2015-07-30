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


abstract class GD_ServicesViewFormat {

    protected $node = null;
    protected $filename = null;

    public function __construct () {
        /**
         * check for node in url
         *
         * Current use is the following
         * /api/report/{id}/data
         *
         */
        $this->node = node_load(arg(2));

        if ( $this->node ) {

            // generate filename title
            $this->filename = str_replace(' ','_',trim($this->node->title));
            if ( !empty($_REQUEST['raw']) ) {
                $this->filename .= '__RAW';
            }
            $this->filename .= '__'.date('Ymd');

            // get author
            $this->node->author = user_load($this->node->uid);
        }
    }
}

class GD_ServicesViewFormat_Debug extends GD_ServicesViewFormat implements ServicesFormatterInterface{

    public function render ($data) {
        return print_r(array('service_info'=>services_server_info_object(),'filename'=>$this->filename,'node'=>$this->node,'model'=>$this->model,'arguments'=>$this->arguments),true);
    }

}
