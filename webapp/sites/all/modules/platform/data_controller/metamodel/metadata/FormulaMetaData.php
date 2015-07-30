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

class FormulaMetaData extends ColumnMetaData {

    const PERSISTENCE__CALCULATED = 1000;

    public $expressionLanguage = NULL;

    public $isMeasure = FALSE;

    public function __construct() {
        parent::__construct();
        $this->persistence = self::PERSISTENCE__CALCULATED;
    }
}


class FormulaReferenceFactory extends AbstractObject implements ColumnReferenceFactory {

    protected $formulas = NULL;

    public function __construct(array $formulas = NULL) {
        parent::__construct();
        $this->formulas = $formulas;
    }

    public function findColumn($columnName) {
        if (isset($this->formulas)) {
            foreach ($this->formulas as $formula) {
                if ($formula->name == $columnName) {
                    return $formula;
                }
            }
        }

        return NULL;
    }

    public function getColumn($columnName) {
        $column = $this->findColumn($columnName);
        if (!isset($column)) {
            $this->errorFormulaNotFound($columnName);
        }

        return $column;
    }

    protected function errorFormulaNotFound($formulaName) {
        throw new IllegalArgumentException(t('Formula %columnName is not registered', array('%columnName' => $formulaName)));
    }
}
