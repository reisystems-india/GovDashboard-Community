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

class TotalInFormulaExpressionDatasetRequestProcessor extends AbstractTotalInFormulaExpressionRequestProcessor {

    protected function prepareRequestDataset(AbstractQueryRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $datasetName = $request->getDatasetName();

        return $metamodel->getDataset($datasetName);
    }

    protected function collectReferencedFormulaNames(AbstractQueryRequest $request) {
        $usedFormulaNames = NULL;

        // checking conditions
        if (isset($request->queries)) {
            foreach ($request->queries as $index => $query) {
                foreach ($query as $columnName => $values) {
                    if ($request->findFormula($columnName) != NULL) {
                        ArrayHelper::addUniqueValue($usedFormulaNames, $columnName);
                    }
                }
            }
        }
        // checking columns
        if ($request instanceof DatasetQueryRequest) {
            if (isset($request->columns)) {
                foreach ($request->columns as $columnName) {
                    if ($request->findFormula($columnName) != NULL) {
                        ArrayHelper::addUniqueValue($usedFormulaNames, $columnName);
                    }
                }
            }
        }

        return $usedFormulaNames;
    }

    protected function initializeRequest4Total(AbstractQueryRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $datasetName = $request->getDatasetName();

        $cube = $metamodel->getCubeByDatasetName($datasetName);

        return new CubeQueryRequest($cube->name);
    }

    protected function finalizeRequest4Total(AbstractQueryRequest $request, AbstractQueryRequest $request4Total, array $columnNames4Total) {
        $totalExpressions = $request4Total->getOption(self::OPTION__TOTAL_FORMULA_EXPRESSION);

        // preparing returned columns
        $columnIndex = 0;
        foreach ($columnNames4Total as $columnName) {
            $formula = $request4Total->getFormula($columnName);
            // updating expression to summarize data for the expression
            $formula->source = 'SUM(' . $formula->source . ')';
            $formula->isMeasure = TRUE;

            $request4Total->addMeasure($columnIndex++, $columnName);
        }

        // preparing data querying
        if (isset($request->queries)) {
            if (count($request->queries) > 1) {
                throw new UnsupportedOperationException('Composite requests are not supported');
            }

            foreach ($request->queries as $index => $queries) {
                foreach ($queries as $name => $values) {
                    $formula = $request->getFormula($name);
                    if (isset($formula)) {
                        // checking if the formula contains reference to TOTAL function
                        if (isset($totalExpressions[$formula->name])) {
                            // excluding the condition
                            continue;
                        }
                    }
                    $request4Total->addFactsDatasetColumnQueryValues($name, $values);
                }
            }
        }
    }
}