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


namespace GD\Sync\Import;

class ImportController {

    protected $handlers = array();

    public function __construct () {
        $this->initHandlers();
    }

    public function import ( ImportStream $stream, ImportContext $context ) {
        $datasourceName = $context->get('datasourceName');
        if (!$datasourceName) {
            throw new \Exception('Missing required datasource name.');
        }
        gd_datasource_set_active($datasourceName);

        foreach ( $this->handlers as $h ) {
            $handler = new $h['class']();
            $handler->import($stream,$context);
        }
    }

    protected function initHandlers() {
        $handlers = module_invoke_all('gd_sync_entities');
        $importHandlers = array();
        foreach ( $handlers as $h ) {
            $importHandlers[] = $h['import'];
        }

        // sort by operation weight
        usort($importHandlers,function($a,$b){
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] < $b['weight']) ? -1 : 1;
        });

        $this->handlers = $importHandlers;
    }

}