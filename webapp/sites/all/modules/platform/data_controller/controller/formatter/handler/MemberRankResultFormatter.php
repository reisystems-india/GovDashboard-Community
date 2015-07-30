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


class MemberRankResultFormatter extends AbstractResultFormatter {

    private $valueColumnName = NULL;
    private $memberRankColumnName = NULL;

    public function __construct($valueColumnName, $memberRankColumnName, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        $this->valueColumnName = $valueColumnName;
        $this->memberRankColumnName = $memberRankColumnName;
    }

    protected function finishImpl(array &$records = NULL) {
        parent::finishImpl($records);

        if (!isset($records)) {
            return;
        }

        $valueMin = $valueMax = NULL;
        // preparing minimum and maximum values
        foreach ($records as $record) {
            if (!isset($record[$this->valueColumnName])) {
                continue;
            }

            $value = $record[$this->valueColumnName];

            $valueMin = MathHelper::min($valueMin, $value);
            $valueMax = MathHelper::max($value, $valueMax);
        }
        $valueRange = $valueMax - $valueMin;

        // generating weighted grade
        foreach ($records as &$record) {
            if (!isset($record[$this->valueColumnName])) {
                continue;
            }

            $value = $record[$this->valueColumnName];

            $rank = ($valueRange == 0) ? 1 : ($value - $valueMin) / $valueRange;
            $record[$this->memberRankColumnName] = $rank;
        }
        unset($record);
    }
}