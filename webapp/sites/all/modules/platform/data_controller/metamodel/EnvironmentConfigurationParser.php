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


class EnvironmentConfigurationParser extends AbstractConfigurationParser {

    protected function getStartDelimiter() {
        return array('$', '{');
    }

    protected function getEndDelimiter() {
        return '}';
    }

    public function executeStatement(ParserCallback $callback, &$callerSession) {
        $statement = $callback->marker;

        $statementFunctions = &drupal_static(__CLASS__ . '::statementFunctions');

        // Note. DO NOT store $statementFunctions into $callback because we want to execute those functions only once per thread
        $functionName = isset($statementFunctions[$statement]) ? $statementFunctions[$statement] : NULL;
        if (!isset($functionName)) {
            $functionName = create_function('', 'return ' . $statement . ';');
            if ($functionName === FALSE) {
                throw new IllegalArgumentException(t('Could not evaluate the statement: %statement', array('%statement' => $statement)));
            }

            $statementFunctions[$statement] = $functionName;
        }

        $callback->marker = $functionName();
        $callback->removeDelimiters = TRUE;
    }
}
