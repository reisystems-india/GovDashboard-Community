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


class UpdateDatasetStorageRequest extends DatasetStorageRequest {

    public $operations = NULL;

    public function addOperation(AbstractDatasetStorageOperation $operation) {
        $this->operations[] = $operation;
    }

    public function addOperations(array $operations) {
        ArrayHelper::merge($this->operations, $operations);
    }
}

abstract class AbstractDatasetStorageOperation extends AbstractObject {}

// *****************************************************************************
//   Column operations
// *****************************************************************************
abstract class AbstractColumnOperation extends AbstractDatasetStorageOperation {

    public $columnName = NULL;

    public function __construct($columnName) {
        parent::__construct();
        $this->columnName = $columnName;
    }
}

class CreateColumnOperation extends AbstractColumnOperation {}

class DropColumnOperation extends AbstractColumnOperation {}

abstract class AbstractColumnReferenceOperation extends AbstractColumnOperation {}

class CreateColumnReferenceOperation extends AbstractColumnReferenceOperation {

    public $referencedDatasetName = NULL;

    public function __construct($columnName, $referencedDatasetName) {
        parent::__construct($columnName);
        $this->referencedDatasetName = $referencedDatasetName;
    }
}

class DropColumnReferenceOperation extends AbstractColumnReferenceOperation {}


// *****************************************************************************
//   Dataset operations
// *****************************************************************************
class CreateDatasetKeyOperation extends AbstractDatasetStorageOperation {}

class DropDatasetKeyOperation extends AbstractDatasetStorageOperation {

    public $originalKeyColumnNames = NULL;

    public function __construct($originalKeyColumnNames) {
        parent::__construct();
        $this->originalKeyColumnNames = $originalKeyColumnNames;
    }
}
