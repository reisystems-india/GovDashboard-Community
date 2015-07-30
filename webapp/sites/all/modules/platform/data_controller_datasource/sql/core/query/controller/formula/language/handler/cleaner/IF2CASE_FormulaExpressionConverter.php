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

class IF2CASE_FormulaExpressionConverter extends AbstractFormulaExpressionSQLCleaner {

    public function clean($expression) {
        $cleanExpression = $expression;

        $expressionTemplate = $this->eliminateStrings($cleanExpression);
        $expressionTemplate = strtoupper($expressionTemplate);

        $this->replaceSyntax($expressionTemplate, $cleanExpression);

        return $cleanExpression;
    }

    protected function eliminateStrings($expression) {
        $result = $expression;

        $offset = 0;
        while (TRUE) {
            $singleStart = strpos($result, '\'', $offset);
            $singleEnd = ($singleStart === FALSE) ? FALSE : strpos($result, '\'', $singleStart + 1);
            $doubleStart = strpos($result, '"', $offset);
            $doubleEnd = ($doubleStart === FALSE) ? FALSE : strpos($result, '"', $doubleStart + 1);

            $isSingle = ($singleStart !== FALSE) && ($singleEnd !== FALSE);
            $isDouble = ($doubleStart !== FALSE) && ($doubleEnd !== FALSE);

            $useSingle = $useDouble = FALSE;
            if (($singleStart !== FALSE) && ($doubleStart !== FALSE)) {
                if ($singleStart < $doubleStart) {
                    if (!$isSingle) {
                        throw new IllegalArgumentException('Uncompleted single-quoted string');
                    }
                    $useSingle = TRUE;
                }
                else {
                    if (!$isDouble) {
                        throw new IllegalArgumentException('Uncompleted double-quoted string');
                    }
                    $useDouble = TRUE;
                }
            }
            else {
                $useSingle = $isSingle;
                $useDouble = $isDouble;
            }

            if ($useSingle) {
                $length = $singleEnd - $singleStart + 1;
                $result = substr_replace($result, str_pad(' ', $length), $singleStart, $length);
                $offset = $singleEnd + 1;
            }
            elseif ($useDouble) {
                $length = $doubleEnd - $doubleStart + 1;
                $result = substr_replace($result, str_pad(' ', $length), $doubleStart, $length);
                $offset = $doubleEnd + 1;
            }
            else {
                break;
            }
        }

        return $result;
    }

    protected function replaceSyntax($expressionTemplate, &$cleanExpression) {
        $counterENDIF = $this->replaceSyntaxKeyword($expressionTemplate, $cleanExpression, 'END IF', 'END');
        $this->replaceSyntaxKeyword($expressionTemplate, $cleanExpression, 'ELSEIF', 'WHEN');
        $counterIF = $this->replaceSyntaxKeyword($expressionTemplate, $cleanExpression, 'IF', 'CASE WHEN');

        if ($counterIF != $counterENDIF) {
            throw new IllegalArgumentException(t(
                'Inconsistent number of IF (%counterIF occurrence(s)) and END IF (%counterENDIF occurrence(s)) keywords',
                array('%counterIF' => $counterIF, '%counterENDIF' => $counterENDIF)));
        }
    }

    protected function replaceSyntaxKeyword(&$expressionTemplate, &$cleanExpression, $keyword, $replacement) {
        $counter = 0;

        $keywordLength = strlen($keyword);

        while (($offset = $this->findToken($expressionTemplate, $keyword)) !== FALSE) {
            $expressionTemplate = $this->replaceToken($expressionTemplate, $replacement, $offset, $keywordLength);
            $cleanExpression = $this->replaceToken($cleanExpression, $replacement, $offset, $keywordLength);

            $counter++;
        }

        return $counter;
    }

    protected function findToken($expression, $token) {
        $expressionLength = strlen($expression);
        $tokenLength = strlen($token);

        $offset = 0;
        while (TRUE) {
            $index = strpos($expression, $token, $offset);
            if ($index === FALSE) {
                break;
            }

            $chars = NULL;
            if ($index > 0) {
                $chars[] = $expression[$index - 1];
            }
            if (($index + $tokenLength) < $expressionLength) {
                $chars[] = $expression[$index + $tokenLength];
            }

            // checking that none of the preceding or following characters are alphabetic characters
            if (isset($chars)) {
                foreach ($chars as $c) {
                    if (($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z')) {
                        $offset = $index + 1;
                        continue;
                    }
                }
            }

            return $index;
        }

        return FALSE;
    }

    protected function replaceToken($expression, $replacement, $start, $length) {
        return substr_replace($expression, $replacement, $start, $length);
    }
}
