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


class ColumnExpressionObserver extends AbstractDatasetStorageObserver {

    protected function initializeColumnExpressionAssembler(DatasetMetaData $dataset) {
        return new ColumnExpressionAssembler($dataset);
    }

    public function validate(DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        parent::validate($callcontext, $dataset);

        // validating if we can assemble expression for all included columns
        $columnExpressionAssembler = $this->initializeColumnExpressionAssembler($dataset);
        foreach ($dataset->getColumns() as $column) {
            $columnExpressionAssembler->assemble($column);
        }
    }
}
