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

class SQLFormulaExpressionHandler extends AbstractFormulaExpressionHandler {

    const LANGUAGE__SQL = 'SQL';

    protected $cleanHandlers = NULL;

    public function clean($expression) {
        $adjustedExpression = $expression;

        if (!isset($this->cleanHandlers)) {
            $handlerConfigurations = module_invoke_all('dp_formula_expression_sql_clean');
            if (isset($handlerConfigurations)) {
                foreach ($handlerConfigurations as $handlerConfiguration) {
                    $priority = $handlerConfiguration['priority'];
                    $classname = $handlerConfiguration['classname'];

                    if (isset($this->cleanHandlers[$priority])) {
                        throw new IllegalStateException(t(
                            'Several SQL expression cleaners have the same priority: [%classname1, %classname2]',
                            array('%classname1' => $this->cleanHandlers[$priority], '%classname2' => $classname)));
                    }
                    $this->cleanHandlers[$priority] = new $classname();
                }

                if (isset($this->cleanHandlers)) {
                    ksort($this->cleanHandlers, SORT_NUMERIC);
                }
            }
        }

        if (isset($this->cleanHandlers)) {
            foreach ($this->cleanHandlers as $handler) {
                $adjustedExpression = $handler->clean($adjustedExpression);
            }
        }

        return $adjustedExpression;
    }

    public function merge($expression, $index, $addon) {
        $addBrackets = TRUE;

        // checking place in the expression where we insert the addon
        if ($addBrackets && ($index > 0) && ($index < strlen($expression))) {
            if (($expression[$index - 1] == '(') && ($expression[$index] == ')')) {
                $addBrackets = FALSE;
            }
        }

        // checking if the addon starts and ends with bracket
        $addonLength = strlen($addon);
        if ($addBrackets && ($addon[0] == '(') && ($addon[$addonLength - 1] == ')')) {
            $counter = 1;
            $s = substr($addon, 1, $addonLength - 2);

            $i = 0;
            while (TRUE) {
                $open = strpos($s, '(', $i);
                $close = strpos($s, ')', $i);

                if ($open === FALSE) {
                    if ($close === FALSE) {
                        break;
                    }
                    else {
                        $counter--;
                        $i = $close + 1;
                    }
                }
                elseif ($close === FALSE) {
                    throw new IllegalArgumentException(t(
                        'Incorrect number of open and close brackets in the expression: %expression',
                        array('%expression' => $addon)));
                }
                elseif ($open < $close) {
                    $counter++;
                    $i = $open + 1;
                }
                else {
                    $counter--;
                    $i = $close + 1;
                }
                // there is no outermost brackets. Example '(...)...(...)' => '...)...(...'
                if ($counter == 0) {
                    break;
                }
            }

            if ($counter == 1) {
                $addBrackets = FALSE;
            }
        }

        $adjustedAddon = $addBrackets ? ('(' . $addon . ')') : $addon;

        return substr_replace($expression, $adjustedAddon, $index, 0);
    }

    public function lex($expression) {
        return $expression;
    }

    public function parse($lexemes) {
        return $lexemes;
    }

    function generate($syntaxTree) {
        return $syntaxTree;
    }

    protected function presentInSyntaxTree($syntaxTree, array $ruleNames) {
        foreach ($ruleNames as $ruleName) {
            $pattern = '/(?i)(^|\W)' . $ruleName . '\s*\((.|\s)+\)/';

            $result = preg_match($pattern, $syntaxTree);
            if ($result === 1) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function isMeasure($syntaxTree) {
        $aggregationRuleNames = array('COUNT', 'SUM', 'MIN', 'MAX', 'AVG');
        $expressionRuleNames = array(TotalInFormulaExpressionParser::FUNCTION_NAME);

        $isAggregation = $this->presentInSyntaxTree($syntaxTree, $aggregationRuleNames);
        $isExpression = $this->presentInSyntaxTree($syntaxTree, $expressionRuleNames);

        return $isAggregation ? TRUE : ($isExpression ? NULL : FALSE);
    }
}
