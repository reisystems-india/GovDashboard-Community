<?php

class FormulaExpressionObserver extends AbstractDatasetStorageObserver {

    public function validate(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::validate($callcontext, $dataset);

        // validating if we can assemble expression for all included columns
        $expressionAssembler = new FormulaExpressionAssembler($dataset);
        foreach ($dataset->getColumns() as $column) {
            $expressionAssembler->assemble($column);
        }
    }
}
