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


abstract class AbstractLeftOuterJoinController extends AbstractColumnBasedJoinController {

    protected function preselectSourceConfiguration(JoinController_SourceConfiguration $sourceConfigurationA, JoinController_SourceConfiguration $sourceConfigurationB) {
        return isset($sourceConfigurationA->data)
            ? (isset($sourceConfigurationB->data)
                ? FALSE // we need to join the sources
                : $sourceConfigurationA)
            : new JoinController_SourceConfiguration(); // left dataset does not have any data -> result is empty list
    }

    public function joinHash(array &$result, array &$hashedSourceA, array &$hashedSourceB) {
        foreach ($hashedSourceA as $keyA => $recordsA) {
            if (isset($hashedSourceB[$keyA])) {
                $recordsB = $hashedSourceB[$keyA];
                foreach ($recordsA as $recordA) {
                    foreach ($recordsB as $recordB) {
                        $result[] = $this->mergeRecords($recordA, $recordB);
                    }
                }

                unset($hashedSourceB[$keyA]);
            }
            else {
                $result = array_merge($result, $recordsA);
            }

            unset($hashedSourceA[$keyA]);
        }
    }
}
