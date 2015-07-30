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


abstract class AbstractDatasetQueryRequest extends AbstractQueryRequest {

    public function getDatasetName() {
        return $this->sourceName;
    }

    public function addQueryValue($index, $name, $value) {
        $isFormula = $this->findFormula($name) != NULL;
        ReferencePathHelper::checkReference($name, TRUE, !$isFormula);

        $this->queries[$index][$name][] = $value;
    }

    public function addQueryValues($index, $name, $value) {
        foreach ($value as $v) {
            $this->addQueryValue($index, $name, $v);
        }
    }

    public function addCompositeQueryValues($compositeQuery) {
        if (!isset($compositeQuery)) {
            return;
        }

        $isIndexedArray = ArrayHelper::isIndexed($compositeQuery);
        if ($isIndexedArray && (count($compositeQuery) === 1)) {
            $compositeQuery = $compositeQuery[0];
            $isIndexedArray = FALSE;
        }

        if ($isIndexedArray) {
            foreach ($compositeQuery as $query) {
                $this->addCompositeQueryValues($query);
            }
        }
        else {
            $index = count($this->queries);

            foreach ($compositeQuery as $name => $value) {
                $this->addQueryValues($index, $name, $value);
            }
        }
    }
}