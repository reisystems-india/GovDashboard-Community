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

class TotalInFormulaExpressionCubeRequestProcessor extends AbstractTotalInFormulaExpressionRequestProcessor {

    protected function prepareRequestDataset(AbstractQueryRequest $request) {
        $metamodel = data_controller_get_metamodel();

        $cubeName = $request->getCubeName();
        $cube = $metamodel->getCube($cubeName);
        $datasetName = $cube->factsDatasetName;

        return $metamodel->getDataset($datasetName);
    }

    protected function collectReferencedFormulaNames(AbstractQueryRequest $request) {
        $usedFormulaNames = NULL;

        // checking dimensions
        if (isset($request->dimensions)) {
            foreach ($request->dimensions as $dimension) {
                $dimensionName = $dimension->name;
                if ($request->findFormula($dimensionName) != NULL) {
                    ArrayHelper::addUniqueValue($usedFormulaNames, $dimensionName);
                }
            }
        }
        // checking measures
        if (isset($request->measures)) {
            foreach ($request->measures as $measure) {
                $measureName = $measure->name;
                if ($request->findFormula($measureName) != NULL) {
                    ArrayHelper::addUniqueValue($usedFormulaNames, $measureName);
                }
            }
        }
        // checking conditions
        if (isset($request->queries)) {
            foreach ($request->queries as $query) {
                $name = $query->name;
                if ($request->findFormula($name) != NULL) {
                    ArrayHelper::addUniqueValue($usedFormulaNames, $name);
                }
            }
        }

        return $usedFormulaNames;
    }

    protected function initializeRequest4Total(AbstractQueryRequest $request) {
        return new CubeQueryRequest($request->getCubeName());
    }

    protected function finalizeRequest4Total(AbstractQueryRequest $request, AbstractQueryRequest $request4Total, array $columnNames4Total) {
        $totalExpressions = $request4Total->getOption(self::OPTION__TOTAL_FORMULA_EXPRESSION);

        // preparing returned columns
        $columnIndex = 0;
        // ... TODO existing dimensions (to support non-additive measures)
        /*
        if (isset($request->dimensions)) {
            foreach ($request->dimensions as $dimension) {
                if (isset($dimension->columns)) {
                    foreach ($dimension->columns as $column) {
                        $request4Total->addDimensionColumn($columnIndex++, $dimension->name, $column->name);
                    }
                }
                else {
                    $formula = $request->getFormula($dimension->name);
                    if (isset($formula)) {
                    // checking if the formula contains reference to TOTAL function
                        if (isset($totalExpressions[$formula->name])) {
                            // excluding the formula
                            continue;
                        }
                    }

                    $request4Total->addDimension($columnIndex++, $dimension->name);
                }
            }
        } */
        // ... measures to calculate total
        foreach ($columnNames4Total as $measureName) {
            $formula = $request4Total->getFormula($measureName);

            // TODO checking if the formula expression contains any aggregation functions (eliminate when non-additive measures are supported)
            $handler = FormulaExpressionLanguageFactory::getInstance()->getHandler($formula->expressionLanguage);
            $lexemes = $handler->lex($formula->source);
            $syntaxTree = $handler->parse($lexemes);
            $isMeasure = $handler->isMeasure($syntaxTree);
            if (!isset($isMeasure) || !$isMeasure) {
                // updating expression to summarize data for the expression
                $formula->source = 'SUM(' . $formula->source . ')';
            }

            $formula->isMeasure = TRUE;

            $request4Total->addMeasure($columnIndex++, $measureName);
        }

        // preparing data querying
        $dimensionQueries = $request->findDimensionQueries();
        if (isset($dimensionQueries)) {
            foreach ($dimensionQueries as $query) {
                foreach ($query->columns as $queryColumn) {
                    $request4Total->addDimensionColumnQueryValues($query->name, $queryColumn->name, $queryColumn->values);
                }
            }
        }

        $factsDatasetColumnQueries = $request->findFactsDatasetColumnQueries();
        if (isset($factsDatasetColumnQueries)) {
            foreach ($factsDatasetColumnQueries as $query) {
                $request4Total->addFactsDatasetColumnQueryValues($query->name, $query->values);
            }
        }

        $measureQueries = $request->findMeasureQueries();
        if (isset($measureQueries)) {
            foreach ($measureQueries as $query) {
                $formula = $request->getFormula($query->name);
                if (isset($formula)) {
                    // checking if the formula contains reference to TOTAL function
                    if (isset($totalExpressions[$formula->name])) {
                        // excluding the condition
                        continue;
                    }
                }

                $request4Total->addMeasureQueryValues($query->name, $query->values);
            }
        }
    }
}
