<?php

class FormulaExpressionAssembler extends AbstractObject {

    protected $columnReferenceFactory = NULL;
    protected $columnAssemblingStack = NULL;

    public function __construct(ColumnReferenceFactory $columnReferenceFactory) {
        parent::__construct();
        $this->columnReferenceFactory = $columnReferenceFactory;
    }

    public function assemble(ColumnMetaData $column) {
        $expression = NULL;
        
        if ($column->persistence == FormulaMetaData::PERSISTENCE__CALCULATED) {
            $this->columnAssemblingStack = array();

            array_push($this->columnAssemblingStack, $column->name);

            try {
                if (!isset($column->source)) {
                    throw new IllegalStateException(t('Formula expression is not provided'));
                }

                $language = isset($column->expressionLanguage) ? $column->expressionLanguage : NULL;
                $parser = new FormulaExpressionParser($language);

                $expression = $parser->expressionLanguageHandler->clean($column->source);

                $expression = $parser->parse($expression, array($this, 'replaceColumnNames'));
                $expression = $parser->insertMarker('', 0, $expression, TRUE);

                $lexemes = $parser->expressionLanguageHandler->lex($expression);
                $syntaxTree = $parser->expressionLanguageHandler->parse($lexemes);
                $expression = $parser->expressionLanguageHandler->generate($syntaxTree);
            }
            catch (Exception $e) {
                LogHelper::log_error($e);
                throw new IllegalStateException(t(
                    "Cannot assemble expression for %columnName formula: %error",
                    array('%columnName' => $column->publicName, '%error' => $e->getMessage())));
            }

            array_pop($this->columnAssemblingStack);
        }
        else {
            $expression = $column->name;
        }

        return $expression;
    }

    protected function approveColumn4ParticipationInExpression(ColumnMetaData $column) {
        // checking if the column is available
        if (!$column->isUsed()) {
            throw new IllegalStateException(t(
                '%columnName column is unused and cannot participate in the expression',
                array('%columnName' => $column->publicName)));
        }
    }

    public function replaceColumnNames(ParserCallback $callback, &$callerSession) {
        $columnName = $callback->marker;

        $column = $this->columnReferenceFactory->getColumn($columnName);
        $this->approveColumn4ParticipationInExpression($column);

        if ($column->persistence == FormulaMetaData::PERSISTENCE__CALCULATED) {
            // checking if the column is already in the stack
            if (in_array($column->name, $this->columnAssemblingStack)) {
                $columnPublicNames = NULL;
                // adding columns from stack
                foreach ($this->columnAssemblingStack as $stackColumnName) {
                    $stackColumn = $this->dataset->getColumn($stackColumnName);
                    $columnPublicNames[] = $stackColumn->publicName;
                }
                // adding current processed column
                $columnPublicNames[] = $column->publicName;

                throw new IllegalStateException(t(
                    'Circular reference in formula expression is not allowed: %stack',
                    array('%stack' => ArrayHelper::serialize($columnPublicNames, ', ', TRUE, FALSE))));
            }

            array_push($this->columnAssemblingStack, $column->name);

            $language = isset($column->expressionLanguage) ? $column->expressionLanguage : NULL;
            $parser = new FormulaExpressionParser($language);

            $expression = $parser->expressionLanguageHandler->clean($column->source);

            $callback->marker = $parser->parse($expression, array($this, 'replaceColumnNames'));

            array_pop($this->columnAssemblingStack);

            $callback->removeDelimiters = TRUE;
        }
    }
}


class FormulaExpressionParser extends AbstractConfigurationParser {

    public $expressionLanguageHandler = NULL;

    public function __construct($expressionLanguage = NULL) {
        parent::__construct();
        $this->expressionLanguageHandler = FormulaExpressionLanguageFactory::getInstance()->getHandler($expressionLanguage);
    }

    protected function getStartDelimiter() {
        return array('$COLUMN', '{');
    }

    protected function getEndDelimiter() {
        return '}';
    }

    public function insertMarker($expression, $index, $marker, $parentRemoved) {
        return $parentRemoved
            ? $this->expressionLanguageHandler->merge($expression, $index, $marker)
            : parent::insertMarker($expression, $index, $marker, $parentRemoved);
    }
}


class FormulaExpressionParserEventNotification__ColumnNameCollector extends AbstractObject {

    public function collectColumnNames(ParserCallback $callback, &$columnNames) {
        ArrayHelper::addUniqueValue($columnNames, $callback->marker);
    }
}
