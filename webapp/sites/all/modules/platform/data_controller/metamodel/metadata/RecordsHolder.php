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


abstract class AbstractRecordsHolder extends AbstractObject {

    // In most cases we will not use this property.
    // It is required only when provided record structure does not match to corresponding dataset
    /**
     * @var RecordMetaData
     */
    public $recordMetaData = NULL;

    /**
     * @var AbstractRecord[]
     */
    public $records = NULL;

    public $version = NULL;

    /**
     * @return AbstractRecord
     */
    abstract public function initiateRecordInstance();

    public function registerRecordInstance(AbstractRecord $recordInstance) {
        $this->records[] = $recordInstance;
    }
}

class AssociativeRecordsHolder extends AbstractRecordsHolder {

    public function initiateRecordInstance() {
        return new AssociativeRecord();
    }
}

class IndexedRecordsHolder extends AbstractRecordsHolder {

    public function initiateRecordInstance() {
        return new IndexedRecord();
    }
}

abstract class AbstractRecord extends AbstractObject {

    public $recordIdentifier = NULL;

    abstract public function getColumnValue($identifier, $required = FALSE);
    abstract public function setColumnValue($identifier, $value);

    public function initializeFrom($columnValues) {
        if (isset($columnValues)) {
            foreach ($columnValues as $identifier => $value) {
                $this->setColumnValue($identifier, $value);
            }
        }
    }
}

class AssociativeRecord extends AbstractRecord {

    public function getColumnValue($columnName, $required = FALSE) {
        $value = isset($this->$columnName) ? $this->$columnName : NULL;
        if (!isset($value) && $required) {
            $message = t('Value is not provided [');
            if (isset($this->recordIdentifier)) {
                $message .= t('record: %recordIdentifier; ', array('%recordIdentifier' => $this->recordIdentifier));
            }
            $message .= t('%columnName column]', array('%columnName' => $columnName));

            throw new IllegalArgumentException($message);
        }

        return $value;
    }

    public function setColumnValue($columnName, $value) {
        if (isset($value)) {
            $this->$columnName = $value;
        }
        else {
            unset($this->$columnName);
        }
    }
}

class IndexedRecord extends AbstractRecord {

    public $columnValues = NULL;

    public function getColumnValue($columnIndex, $required = FALSE) {
        $value = isset($this->columnValues[$columnIndex]) ? $this->columnValues[$columnIndex] : NULL;
        if (!isset($value) && $required) {
            $message = t('Value is not provided [');
            if (isset($this->recordIdentifier)) {
                $message .= t('record: %recordIdentifier; ', array('%recordIdentifier' => $this->recordIdentifier));
            }
            $message .= t('column index: %columnIndex]', array('%columnIndex' => $columnIndex));

            throw new IllegalArgumentException($message);
        }

        return $value;
    }

    public function setColumnValue($columnIndex, $value) {
        $this->columnValues[$columnIndex] = $value;
    }
}
