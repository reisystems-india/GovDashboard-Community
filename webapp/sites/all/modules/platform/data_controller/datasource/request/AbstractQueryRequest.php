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


abstract class AbstractQueryRequest extends AbstractRequest {

    const OPTION__FORMULA_DEF = 'formula:definition';

    public $sourceName = NULL;
    public $queries = NULL;
    public $sortingConfigurations = NULL;
    public $limit = NULL;
    public $startWith = 0;
    public $options = NULL;

    public function __construct($sourceName) {
        parent::__construct();
        $this->sourceName = $sourceName;
    }

    public function __clone() {
        parent::__clone();
        $this->queries = ArrayHelper::copy($this->queries);
        $this->sortingConfigurations = ArrayHelper::copy($this->sortingConfigurations);
    }

    public function isSortingColumnPresent($columnName) {
        if (isset($this->sortingConfigurations)) {
            foreach ($this->sortingConfigurations as $sortingConfiguration) {
                if ($sortingConfiguration->getColumnName() == $columnName) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    public function initiateSortingConfiguration($columnName, $isSortAscending = TRUE) {
        $isFormula = $this->findFormula($columnName) != NULL;
        ReferencePathHelper::checkReference($columnName, TRUE, !$isFormula);

        return new ColumnBasedComparator_DefaultSortingConfiguration($columnName, $isSortAscending);
    }

    public function addSortingConfiguration(ColumnBasedComparator_AbstractSortingConfiguration $sortingConfiguration) {
        $this->sortingConfigurations[] = $sortingConfiguration;
    }

    public function addSortingConfigurations(array $sortingConfigurations = NULL) {
        if (isset($sortingConfigurations)) {
            foreach ($sortingConfigurations as $sortingConfiguration) {
                $this->addSortingConfiguration($sortingConfiguration);
            }
        }
    }

    public function addOrderByColumn($directionalColumnName) {
        list($columnName, $isSortAscending) = ColumnBasedComparator_AbstractSortingConfiguration::parseDirectionalColumnName($directionalColumnName);
        $this->addSortingConfiguration($this->initiateSortingConfiguration($columnName, $isSortAscending));
    }

    public function addOrderByColumns($directionalColumnNames) {
        if (isset($directionalColumnNames)) {
            if (is_array($directionalColumnNames)) {
                foreach ($directionalColumnNames as $directionalColumnName) {
                    $this->addOrderByColumn($directionalColumnName);
                }
            }
            else {
                $this->addOrderByColumn($directionalColumnNames);
            }
        }
    }

    public function setPagination($limit, $startWith = 0) {
        IntegerDataTypeHandler::checkNonNegativeInteger($limit);
        IntegerDataTypeHandler::checkNonNegativeInteger($startWith);

        $this->limit = $limit;
        $this->startWith = $startWith;
    }

    public function addOption($name, $value) {
        if (isset($this->options[$name])) {
            throw new IllegalStateException(t('The option has already been set: %optionName', array('%optionName' => $name)));
        }

        $this->options[$name] = $value;
    }

    public function addOptions($options) {
        if (isset($options)) {
            foreach ($options as $name => $value) {
                $this->addOption($name, $value);
            }
        }
    }

    public function findOption($name) {
        return isset($this->options[$name]) ? $this->options[$name] : NULL;
    }

    public function getOption($name) {
        $value = $this->findOption($name);
        if (!isset($value)) {
            throw new IllegalArgumentException(t('The option has not been defined: %optionName', array('%optionName' => $name)));
        }

        return $value;
    }

    public function getFormulas() {
        return $this->findOption(self::OPTION__FORMULA_DEF);
    }

    public function addFormula(FormulaMetaData $formula) {
        $existingFormula = $this->findFormula($formula->name);
        if (isset($existingFormula)) {
            throw new IllegalArgumentException(t(
                'The formula has already been defined: %formulaName',
                array('%formulaName' => $formula->name)));
        }

        $this->options[self::OPTION__FORMULA_DEF][] = $formula;
    }

    public function getFormulaNames() {
        $names = NULL;

        $formulas = $this->getFormulas();
        if (isset($formulas)) {
            foreach ($formulas as $formula) {
                $names[] = $formula->name;
            }
        }

        return $names;
    }

    public function findFormula($columnName) {
        $formulas = $this->getFormulas();

        if (isset($formulas)) {
            foreach ($formulas as $formula) {
                if ($formula->name == $columnName) {
                    return $formula;
                }
            }
        }

        return NULL;
    }

    public function getFormula($columnName) {
        $formula = $this->findFormula($columnName);
        if (!isset($formula)) {
            throw new IllegalArgumentException(t('The formula has not been defined: %formulaName', array('%formulaName' => $columnName)));
        }

        return $formula;
    }
}
