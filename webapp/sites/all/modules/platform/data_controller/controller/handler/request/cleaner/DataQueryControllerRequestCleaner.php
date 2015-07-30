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


class DataQueryControllerRequestCleaner extends AbstractObject {

    protected function adjustDatasetName(AbstractDataQueryControllerRequest $request) {
        $request->datasetName = StringHelper::trim($request->datasetName);
    }

    protected function adjustColumns(AbstractDataQueryControllerRequest $request) {
        $request->columns = ArrayHelper::trim($request->columns);
    }

    protected function adjustCompositeParameter($compositeParameter) {
        $adjustedCompositeParameter = NULL;

        $parameterIndex = 0;
        foreach ($compositeParameter as $key => $value) {
            $adjustedKey = is_string($key) ? StringHelper::trim($key) : $key;

            $adjustedValues = NULL;
            if ($value instanceof OperatorHandler) {
                // we do not need to change anything for the value
                $adjustedValues[] = $value;
            }
            else {
                // what if the value is a list of operators
                $operatorFound = FALSE;
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if ($v instanceof OperatorHandler) {
                            $operatorFound = TRUE;
                            break;
                        }
                    }
                }

                // we found at least one operator in the list
                if ($operatorFound) {
                    foreach ($value as $index => $v) {
                        $adjustedIndex = is_string($index) ? StringHelper::trim($index) : $index;

                        $adjustedValue = ($v instanceof OperatorHandler)
                            ? $v
                            : OperatorFactory::getInstance()->initiateHandler(EqualOperatorHandler::OPERATOR__NAME, array($v));
                        $adjustedValue->weight = $parameterIndex++;

                        $adjustedValues[$adjustedIndex] = $adjustedValue;
                    }
                }
                else {
                    $adjustedValue = OperatorFactory::getInstance()->initiateHandler(EqualOperatorHandler::OPERATOR__NAME, array($value));
                    $adjustedValue->weight = $parameterIndex++;

                    $adjustedValues[] = $adjustedValue;
                }
            }

            $adjustedCompositeParameter[$adjustedKey] = $adjustedValues;
        }

        return $adjustedCompositeParameter;
    }

    protected function adjustParameters(AbstractDataQueryControllerRequest $request) {
        if (isset($request->parameters)) {
            if (is_array($request->parameters) && ArrayHelper::isIndexed($request->parameters)) {
                foreach ($request->parameters as $index => $compositeParameter) {
                    $request->parameters[$index] = $this->adjustCompositeParameter($compositeParameter);
                }
            }
            else {
                $request->parameters = $this->adjustCompositeParameter($request->parameters);
            }
        }
    }

    protected function adjustOrderBy(AbstractDataQueryControllerRequest $request) {
        $request->orderBy = ArrayHelper::trim($request->orderBy);
    }

    protected function adjustPagination(AbstractDataQueryControllerRequest $request) {
        if (isset($request->startWith)) {
            if ($request->startWith < 0) {
                throw new IllegalArgumentException(t(
                    "Unsupported value for pagination 'START WITH' parameter: %startWith",
                    array('%startWith' => $request->startWith)));
            }
        }

        if (isset($request->limit)) {
            if ($request->limit < 0) {
                throw new IllegalArgumentException(t(
                    "Unsupported value for pagination 'LIMIT' parameter: %limit",
                    array('%limit' => $request->limit)));
            }
        }
    }

    protected function cleanRequest(AbstractDataQueryControllerRequest $request) {
        $this->adjustDatasetName($request);
        $this->adjustColumns($request);
        $this->adjustParameters($request);
        $this->adjustOrderBy($request);
        $this->adjustPagination($request);
    }

    protected function reformatRequest($request) {
        return $request;
    }

    protected function adjustBranchRequest(AbstractDataQueryControllerRequest $request) {
        $this->cleanRequest($request);

        return $this->reformatRequest($request);
    }

    public function adjustRequest($request) {
        if ($request instanceof AbstractDataQueryControllerRequest) {
            return $this->adjustBranchRequest($request);
        }
        elseif ($request instanceof AbstractDataQueryControllerRequestTreeBranch) {
            $request->request = $this->adjustBranchRequest($request->request);

            if (isset($request->branches)) {
                foreach ($request->branches as $index => $branch) {
                    $adjustedBranch = $this->adjustRequest($branch);
                    if (!isset($adjustedBranch)) {
                        unset($request->branches[$index]);
                    }
                }
            }

            return (isset($request->request) || (count($request->branches) > 0)) ? $request : NULL;
        }
    }
}
