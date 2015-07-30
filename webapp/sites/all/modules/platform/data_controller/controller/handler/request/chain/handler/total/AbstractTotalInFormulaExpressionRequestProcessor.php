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

abstract class AbstractTotalInFormulaExpressionRequestProcessor extends AbstractObject {

    const OPTION__TOTAL_FORMULA_EXPRESSION = 'total:formula:expression';
    const OPTION__TOTAL_EXPRESSION_COLUMN_MAPPING = 'total:column:mapping';

    abstract protected function prepareRequestDataset(AbstractQueryRequest $request);

    /*
     * Returns list of formula names which are used directly in the request
     */
    abstract protected function collectReferencedFormulaNames(AbstractQueryRequest $request);

    /*
     * Returns list of formula names which are used directly or indirectly (through other formulas) in the request
     */
    protected function assembleUsedFormulaExpressions(AbstractQueryRequest $request) {
        $usedFormulaExpressions = NULL;

        // assembling all used formulas to find all referenced formulas
        $usedFormulaNames = $this->collectReferencedFormulaNames($request);
        if (isset($usedFormulaNames)) {
            $columnReferenceFactory = new CompositeColumnReferenceFactory(array(
                $this->prepareRequestDataset($request),
                new FormulaReferenceFactory($request->getFormulas())));

            $index = 0;
            while (count($usedFormulaNames) > $index) {
                $formulaName = $usedFormulaNames[$index];

                $formula = $columnReferenceFactory->getColumn($formulaName);

                $expressionAssembler = new __TotalInFormulaExpressionRequestPreparer__FormulaExpressionAssembler($columnReferenceFactory);
                $usedFormulaExpressions[$formulaName] = $expressionAssembler->assemble($formula);
                ArrayHelper::addUniqueValues($usedFormulaNames, $expressionAssembler->usedFormulaNames);

                $index++;
            }
        }

        return $usedFormulaExpressions;
    }

    protected function collectTotalExpressions(AbstractQueryRequest $request) {
        // checking if the request has any formulas
        $formulas = $request->getFormulas();
        if (!isset($formulas)) {
            return NULL;
        }

        // collecting list of used formula names
        $usedFormulaExpressions = $this->assembleUsedFormulaExpressions($request);
        if (!isset($usedFormulaExpressions)) {
            return NULL;
        }

        $parser = new TotalInFormulaExpressionParser();
        $expressionCollector = new __TotalInFormulaExpressionRequestPreparer__ExpressionCollector();

        // collecting column names for which we need to calculate total
        $expressions = NULL;
        foreach ($usedFormulaExpressions as $formulaName => $formulaExpressions) {
            $totalExpressions = NULL;
            $parser->parse($formulaExpressions, array($expressionCollector, 'collectExpressionInTotalFunctionCall'), $totalExpressions);
            if (isset($totalExpressions)) {
                $expressions[$formulaName] = $totalExpressions;
            }
        }

        return $expressions;
    }

    abstract protected function initializeRequest4Total(AbstractQueryRequest $request);
    abstract protected function finalizeRequest4Total(AbstractQueryRequest $request, AbstractQueryRequest $request4Total, array $columnNames4Total);

    public function prepareRequest4Total(AbstractQueryRequest $request) {
        $expressions = $this->collectTotalExpressions($request);
        if (!isset($expressions)) {
            return NULL;
        }

        $uniqueExpressions = NULL;
        foreach ($expressions as $formulaName => $totalExpressions) {
            ArrayHelper::addUniqueValues($uniqueExpressions, $totalExpressions);
        }

        // initializing request object
        $request4Total = $this->initializeRequest4Total($request);

        // preparing options
        $request4Total->addOptions(ArrayHelper::copy($request->options));
        // adding total expressions
        $totalExpressionColumnMapping = NULL;
        foreach ($uniqueExpressions as $index => $expression) {
            $expressionColumnName = 'sm_total_' . $index;

            $formula = new FormulaMetaData();
            $formula->name = $expressionColumnName;
            $formula->type->applicationType = NumberDataTypeHandler::DATA_TYPE;
            $formula->source = $expression;

            $request4Total->addFormula($formula);

            $totalExpressionColumnMapping[$expressionColumnName] = $expression;
        }
        $request4Total->addOption(self::OPTION__TOTAL_FORMULA_EXPRESSION, $expressions);
        $request4Total->addOption(self::OPTION__TOTAL_EXPRESSION_COLUMN_MAPPING, $totalExpressionColumnMapping);

        // finalizing the request
        $this->finalizeRequest4Total($request, $request4Total, array_keys($totalExpressionColumnMapping));

        return $request4Total;
    }

    public function updateTotals(AbstractQueryRequest $request, AbstractQueryRequest $request4Total, array $totals) {
        $totalExpressionColumnMapping = $request4Total->getOption(self::OPTION__TOTAL_EXPRESSION_COLUMN_MAPPING);

        $adjustedTotals = NULL;
        foreach ($totals as $columnName => $value) {
            $totalExpression = $totalExpressionColumnMapping[$columnName];
            $adjustedTotals[$totalExpression] = $value;
        }

        $columnReferenceFactory = new CompositeColumnReferenceFactory(array(
            $this->prepareRequestDataset($request),
            new FormulaReferenceFactory($request->getFormulas())));
        $expressionAssembler = new FormulaExpressionAssembler($columnReferenceFactory);
        $parser = new TotalInFormulaExpressionParser();
        // preparing new formula expressions in separate loop to prevent interference
        $newExpressions = NULL;
        foreach ($request->getFormulas() as $formula) {
            $oldExpression = $expressionAssembler->assemble($formula);

            $columnNameCollector = new __TotalInFormulaExpressionRequestPreparer__TotalUpdater();
            $newExpression = $parser->parse($oldExpression, array($columnNameCollector, 'updateTotal'), $adjustedTotals);
            if ($newExpression != $oldExpression) {
                $newExpressions[$formula->name] = $newExpression;
            }
        }
        // assigning new expressions
        if (isset($newExpressions)) {
            foreach ($newExpressions as $formulaName => $newExpression) {
                $formula = $request->getFormula($formulaName);
                $formula->source = $newExpression;
            }
        }
    }
}


class __TotalInFormulaExpressionRequestPreparer__FormulaExpressionAssembler extends FormulaExpressionAssembler {

    public $usedFormulaNames = NULL;

    protected function approveColumn4ParticipationInExpression(ColumnMetaData $column) {
        parent::approveColumn4ParticipationInExpression($column);

        if ($column->persistence == FormulaMetaData::PERSISTENCE__CALCULATED) {
            ArrayHelper::addUniqueValue($this->usedFormulaNames, $column->name);
        }
    }
}


class TotalInFormulaExpressionParser extends AbstractConfigurationParser {

    const FUNCTION_NAME = 'TOTAL';

    protected function getStartDelimiter() {
        return array(self::FUNCTION_NAME, '(');
    }

    protected function getEndDelimiter() {
        return ')';
    }

    protected function acceptMarker($marker) {
        $acceptance = parent::acceptMarker($marker);

        if ($acceptance === FALSE) {
            // correct marker
        }
        elseif ($acceptance === TRUE) {
            // incorrect marker
        }
        else {
            throw new IllegalArgumentException(t(
                '%functionName function does not support nested calls',
                array('%functionName' => self::FUNCTION_NAME)));
        }

        return $acceptance;
    }
}


class __TotalInFormulaExpressionRequestPreparer__ExpressionCollector extends AbstractObject {

    public function collectExpressionInTotalFunctionCall(ParserCallback $callback, &$expressions) {
        ArrayHelper::addUniqueValue($expressions, $callback->marker);
    }
}


class __TotalInFormulaExpressionRequestPreparer__TotalUpdater extends AbstractObject {

    public function updateTotal(ParserCallback $callback, &$totals) {
        $expression = $callback->marker;

        if (isset($totals[$expression])) {
            $total = $totals[$expression];

            $callback->marker = $total;
            $callback->removeDelimiters = TRUE;
        }
    }
}
