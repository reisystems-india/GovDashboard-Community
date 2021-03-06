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


class ResourceDocumentGenerator extends AbstractDocumentGenerator {

    public static $CSS_CLASS__RESOURCE = 'dpc_resource';
    public static $CSS_CLASS__RESOURCE_NAME = 'dpc_resource_name';

    public $resourceName = NULL;

    public function __construct(AbstractDocumentGenerator $parent, $resourceName) {
        parent::__construct($parent);
        $this->resourceName = $resourceName;
    }

    protected function startGeneration(&$buffer) {
        $buffer .= self::startTag('div', self::$CSS_CLASS__RESOURCE);

        $buffer .= self::startTag('h3', self::$CSS_CLASS__RESOURCE_NAME);
        $buffer .= $this->resourceName;
        $buffer .= self::endTag('h3');
    }

    protected function finishGeneration(&$buffer) {
        $buffer .= self::endTag('div');
    }
}
