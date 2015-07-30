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


abstract class AbstractWildcardOperatorHandler extends AbstractOperatorHandler implements ParameterBasedOperatorHandler {

    public $wildcard = NULL;
    public $anyCharactersOnLeft = FALSE;
    public $anyCharactersOnRight = FALSE;

    public function __construct($configuration, $wildcard, $anyCharactersOnLeft = FALSE, $anyCharactersOnRight = FALSE) {
        parent::__construct($configuration);

        $this->wildcard = StringHelper::trim($wildcard);
        $this->anyCharactersOnLeft = $anyCharactersOnLeft;
        $this->anyCharactersOnRight = $anyCharactersOnRight;
    }

    public function getParameterDataType() {
        return StringDataTypeHandler::DATA_TYPE;
    }
}


class WildcardOperatorMetaData extends AbstractOperatorMetaData {

    protected function initiateParameters() {
        return array(
            new OperatorParameter('wildcard', 'Wildcard'),
            new OperatorParameter('anyCharactersOnLeft', 'Any Characters on Left', FALSE, FALSE),
            new OperatorParameter('anyCharactersOnRight', 'Any Characters on Right', FALSE, FALSE));
    }
}
