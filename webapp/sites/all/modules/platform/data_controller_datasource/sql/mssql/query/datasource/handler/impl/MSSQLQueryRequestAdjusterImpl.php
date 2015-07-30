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


class MSSQLQueryRequestAdjusterImpl extends AbstractQueryRequestAdjusterImpl {

    public function adjustDatasetQueryRequest(DataSourceHandler $handler, AbstractQueryRequest &$request) {
        if (!isset($request->sortingConfigurations)) {
            // sorting configuration is required when pagination properties are present
            if ((isset($request->startWith) && ($request->startWith > 0)) || isset($request->limit)) {
                if (isset($request->columns)) {
                    foreach ($request->columns as $columnName) {
                        $request->addOrderByColumn($columnName);
                    }
                }
                if (!isset($request->sortingConfigurations)) {
                    throw new IllegalStateException(t('Pagination requires sorting configuration'));
                }
            }
        }
    }

    public function adjustDatasetCountRequest(DataSourceHandler $handler, AbstractQueryRequest &$request) {}

    public function adjustCubeQueryRequest(DataSourceHandler $handler, AbstractQueryRequest &$request) {
        if (!isset($request->sortingConfigurations)) {
            // sorting configuration is required when pagination properties are present
            if ((isset($request->startWith) && ($request->startWith > 0)) || isset($request->limit)) {
                $columnNames = NULL;
                // adding dimensions and their columns
                if (isset($request->dimensions)) {
                    foreach ($request->dimensions as $dimension) {
                        if (isset($dimension->requestColumnIndex)) {
                            $columnNames[$dimension->requestColumnIndex] = ParameterNameHelper::assemble($dimension->name);
                        }
                        if (isset($dimension->columns)) {
                            foreach ($dimension->columns as $column) {
                                if (isset($column->requestColumnIndex)) {
                                    $columnNames[$column->requestColumnIndex] = ParameterNameHelper::assemble($dimension->name, $column->name);
                                }
                            }
                        }
                    }
                }
                // adding measures
                if (isset($request->measures)) {
                    foreach ($request->measures as $measure) {
                        if (isset($measure->requestColumnIndex)) {
                            $columnNames[$measure->requestColumnIndex] = $measure->name;
                        }
                    }
                }
                if (!isset($columnNames)) {
                    throw new IllegalStateException(t('Pagination requires sorting configuration'));
                }

                if (isset($columnNames)) {
                    // sorting by column index
                    ksort($columnNames);

                    $request->addOrderByColumns($columnNames);
                }
            }
        }
    }

    public function adjustCubeCountRequest(DataSourceHandler $handler, AbstractQueryRequest &$request) {}
}
