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


abstract class AbstractConfigurationParser extends AbstractObject implements ConfigurationParser {

    abstract protected function getStartDelimiter();

    protected function assembleStartDelimiter() {
        $delimiter = $this->getStartDelimiter();

        return is_array($delimiter) ? ($delimiter[0] . $delimiter[1]) : $delimiter;
    }

    abstract protected function getEndDelimiter();

    public function insertMarker($expression, $index, $marker, $parentRemoved) {
        return substr_replace($expression, $marker, $index, 0);
    }

    /*
     * FALSE - unacceptable
     * TRUE - acceptable
     * int - number of nested expressions
     */
    protected function acceptMarker($marker) {
        $startDelimiter = $this->getStartDelimiter();

        $fullStartDelimiter = $alternativeStartDelimiter = NULL;
        if (is_array($startDelimiter)) {
            $fullStartDelimiter = $startDelimiter[0] . $startDelimiter[1];
            $alternativeStartDelimiter = $startDelimiter[1];
        }
        else {
            $fullStartDelimiter = $startDelimiter;
        }
        $fullStartDelimiterLength = strlen($fullStartDelimiter);
        $alternativeStartDelimiterLength = isset($alternativeStartDelimiter) ? strlen($alternativeStartDelimiter) : 0;

        $endDelimiter = $this->getEndDelimiter();
        $endDelimiterLength = strlen($endDelimiter);

        $nestedExpressionCounter = $nestedFullExpressionCounter = 0;

        $i = 0;
        while (TRUE) {
            $openFull = strpos($marker, $fullStartDelimiter, $i);
            $openAlt = isset($alternativeStartDelimiter) ? strpos($marker, $alternativeStartDelimiter, $i) : FALSE;

            $fullMatch = (($openAlt === FALSE) || (($openFull !== FALSE) && ($openFull < $openAlt)));
            list($open, $openDelimiterLength) = $fullMatch
                ? array($openFull, $fullStartDelimiterLength)
                : array($openAlt, $alternativeStartDelimiterLength);

            $close = strpos($marker, $endDelimiter, $i);

            if ($open === FALSE) {
                if ($close === FALSE) {
                    break;
                }
                else {
                    $nestedExpressionCounter--;
                    $i = $close + $endDelimiterLength;
                }
            }
            elseif ($close === FALSE) {
                return FALSE;
            }
            elseif ($open < $close) {
                $nestedExpressionCounter++;
                if ($fullMatch) {
                    $nestedFullExpressionCounter++;
                }
                $i = $open + $openDelimiterLength;
            }
            else {
                $nestedExpressionCounter--;
                $i = $close + $endDelimiterLength;
            }
        }

        $acceptance = ($nestedExpressionCounter == 0);
        if ($acceptance && ($nestedFullExpressionCounter > 0)) {
            $acceptance = $nestedFullExpressionCounter;
        }

        return $acceptance;
    }

    protected function updateExpression($expression, $start, $length, $marker, $parentRemoved) {
        $updatedExpression = $expression;

        // removing replaceable data
        $updatedExpression = substr_replace($updatedExpression, '', $start, $length);

        // inserting new marker
        $updatedExpression = $this->insertMarker($updatedExpression, $start, $marker, $parentRemoved);

        return $updatedExpression;
    }

    public function parse($expression, $eventFunction = NULL, &$callerSession = NULL) {
        $callback = new ParserCallback();

        $startDelimiter = $this->getStartDelimiter();
        $startDelimiterPrefix = is_array($startDelimiter) ? $startDelimiter[0] : $startDelimiter;
        $startDelimiterSuffix = is_array($startDelimiter) ? $startDelimiter[1] : NULL;
        $startDelimiterPrefixLength = strlen($startDelimiterPrefix);
        $startDelimiterSuffixLength = isset($startDelimiterSuffix) ? strlen($startDelimiterSuffix) : 0;

        $endDelimiter = $this->getEndDelimiter();
        $endDelimiterLength = strlen($endDelimiter);

        $offset = 0;
        while (($startDelimiterPrefixIndex = strpos($expression, $startDelimiterPrefix, $offset)) !== FALSE) {
            $startDelimiterLength = $startDelimiterPrefixLength;
            if (isset($startDelimiterSuffix)) {
                $startDelimiterSuffixIndex = strpos($expression, $startDelimiterSuffix, $startDelimiterPrefixIndex + $startDelimiterPrefixLength);
                $found = FALSE;
                if ($startDelimiterSuffixIndex !== FALSE) {
                    // checking if there is space between the prefix and the suffix
                    $found = TRUE;
                    $i = $startDelimiterPrefixIndex + $startDelimiterPrefixLength;
                    while ($found && ($i < $startDelimiterSuffixIndex)) {
                        if ($expression[$i] == ' ') {
                            $i++;
                        }
                        else {
                            $found = FALSE;
                        }
                    }
                    if ($found) {
                        $startDelimiterLength = $startDelimiterSuffixIndex - $startDelimiterPrefixIndex + $startDelimiterSuffixLength;
                    }
                }

                if (!$found) {
                    $offset = $startDelimiterPrefixIndex + $startDelimiterPrefixLength;
                    continue;
                }
            }

            $endDelimiterIndex = $startDelimiterPrefixIndex + $startDelimiterLength - 1;

            $originalMarker = NULL;
            while (TRUE) {
                $endDelimiterIndex = strpos($expression, $endDelimiter, $endDelimiterIndex + 1);
                if ($endDelimiterIndex === FALSE) {
                    throw new UnsupportedOperationException(t('Expression should contain equal number of starting and ending delimiters'));
                }

                $originalMarker = substr($expression, $startDelimiterPrefixIndex + $startDelimiterLength, $endDelimiterIndex - $startDelimiterPrefixIndex - $startDelimiterLength);

                $acceptance = $this->acceptMarker($originalMarker);
                if ($acceptance === TRUE) {
                    break;
                }
                elseif ($acceptance === FALSE) {
                    continue;
                }
                else {
                    // processing nested expressions
                    $nestedMarker = $originalMarker;
                    $originalMarker = $this->parse($nestedMarker, $eventFunction, $callerSession);
                    break;
                }
            }

            $assembledMarker = $originalMarker;
            $assembledMarker = trim($assembledMarker);

            $callback->marker = $assembledMarker;
            $callback->removeDelimiters = FALSE;

            if (isset($eventFunction)) {
                call_user_func_array($eventFunction, array($callback, &$callerSession));
            }

            $offset = $endDelimiterIndex + $endDelimiterLength;
            if (($callback->marker != $assembledMarker) || $callback->removeDelimiters) {
                $index = $startDelimiterPrefixIndex + ($callback->removeDelimiters ? 0 : $startDelimiterLength);

                $expression = $this->updateExpression(
                    $expression,
                    $index,
                    $endDelimiterIndex - $startDelimiterPrefixIndex - ($callback->removeDelimiters ? -$endDelimiterLength : $startDelimiterLength),
                    $callback->marker,
                    $callback->removeDelimiters);

                $offset += strlen($callback->marker) - strlen($originalMarker);
                if ($callback->removeDelimiters) {
                    $offset -= $startDelimiterLength + $endDelimiterLength;
                }
            }
        }

        return $expression;
    }

    public function assemble($marker) {
        return $this->assembleStartDelimiter() . $marker . $this->getEndDelimiter();
    }
}
