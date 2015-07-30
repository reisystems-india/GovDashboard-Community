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


class FullJoinController extends AbstractColumnBasedJoinController {

    const METHOD_NAME = 'Full';

    private $outerJoinController = NULL;

    public function __construct() {
        parent::__construct();
        $this->outerJoinController = new LeftOuterJoinController();
    }

    protected function preselectSourceConfiguration(JoinController_SourceConfiguration $sourceConfigurationA, JoinController_SourceConfiguration $sourceConfigurationB) {
        return isset($sourceConfigurationA->data)
            ? (isset($sourceConfigurationB->data)
                ? FALSE // we need to join the sources
                : $sourceConfigurationA)
            : (isset($sourceConfigurationB->data)
                ? $sourceConfigurationB
                : new JoinController_SourceConfiguration());
    }

    protected function joinHash(array &$result, array &$hashedSourceA, array &$hashedSourceB) {
        $this->outerJoinController->joinHash($result, $hashedSourceA, $hashedSourceB);
        $this->outerJoinController->joinHash($result, $hashedSourceB, $hashedSourceA);
    }
}
