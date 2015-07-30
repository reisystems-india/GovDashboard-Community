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

abstract class SQL_AbstractWildcardOperatorHandler extends SQL_AbstractOperatorHandler {

    public static $MATCH_PATTERN__SINGLE_CHARACTER = '_';
    public static $MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS = '%';

    public static $CUSTOM_MATCH_PATTERN__SINGLE_CHARACTER = '_';
    public static $CUSTOM_MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS = '%';

    protected static $ESCAPE_CHARACTER = '|';

    abstract protected function finalizeEqualExpression($formattedValue);
    abstract protected function finalizeWildcardExpression($formattedExpression);

    protected function prepareExpression(DataControllerCallContext $callcontext, AbstractRequest $request, $datasetName, $columnName, $columnDataType) {
        $wildcard = $this->getParameterValue('wildcard', TRUE);
        $anyCharactersOnLeft = $this->getParameterValue('anyCharactersOnLeft', FALSE);
        $anyCharactersOnRight = $this->getParameterValue('anyCharactersOnRight', FALSE);

        $useEqualOperator = FALSE;
        // checking if we can just compare to string instead of using wildcard expression
        if (!$anyCharactersOnLeft && !$anyCharactersOnRight) {
            $index = strpos($wildcard, self::$CUSTOM_MATCH_PATTERN__SINGLE_CHARACTER);
            if ($index === FALSE) {
                $index = strpos($wildcard, self::$CUSTOM_MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS);
                if ($index === FALSE) {
                    $useEqualOperator = TRUE;
                }
            }
        }

        $formattedValue = NULL;
        if ($useEqualOperator) {
            $formattedValue = $this->finalizeEqualExpression(
                $this->datasourceHandler->formatValue(StringDataTypeHandler::DATA_TYPE, $wildcard));
        }
        else {
            $searchCharacters = $replaceCharacters = array();
            // escape character
            $searchCharacters[] = self::$ESCAPE_CHARACTER;
            $replaceCharacters[] = self::$ESCAPE_CHARACTER . self::$ESCAPE_CHARACTER;
            // adding user defined match patterns for single character
            if (self::$CUSTOM_MATCH_PATTERN__SINGLE_CHARACTER != self::$MATCH_PATTERN__SINGLE_CHARACTER) {
                $searchCharacters[] = self::$MATCH_PATTERN__SINGLE_CHARACTER;
                $replaceCharacters[] = self::$ESCAPE_CHARACTER . self::$MATCH_PATTERN__SINGLE_CHARACTER;

                $searchCharacters[] = self::$CUSTOM_MATCH_PATTERN__SINGLE_CHARACTER;
                $replaceCharacters[] = self::$MATCH_PATTERN__SINGLE_CHARACTER;
            }
            // adding user defined match patterns for any number of characters
            if (self::$CUSTOM_MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS != self::$MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS) {
                $searchCharacters[] = self::$MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS;
                $replaceCharacters[] = self::$ESCAPE_CHARACTER . self::$MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS;

                $searchCharacters[] = self::$CUSTOM_MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS;
                $replaceCharacters[] = self::$MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS;
            }

            $adjustedWildcard = str_replace($searchCharacters, $replaceCharacters, $wildcard);
            if ($anyCharactersOnLeft) {
                $adjustedWildcard = self::$MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS . $adjustedWildcard;
            }
            if ($anyCharactersOnRight) {
                $adjustedWildcard .= self::$MATCH_PATTERN__ANY_NUMBER_OF_CHARACTERS;
            }

            $formattedWildcard = $this->datasourceHandler->formatValue(StringDataTypeHandler::DATA_TYPE, $adjustedWildcard);

            $formattedValue = $this->finalizeWildcardExpression(
                $this->datasourceHandler->getExtension('formatWildcardValue')->format(
                    $this->datasourceHandler, $formattedWildcard, self::$ESCAPE_CHARACTER));
        }

        return $formattedValue;
    }
}