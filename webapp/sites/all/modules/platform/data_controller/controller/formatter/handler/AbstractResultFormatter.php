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


abstract class AbstractResultFormatter extends AbstractObject implements ResultFormatter {

    /**
     * @var AbstractResultFormatter|null
     */
    protected $parent = NULL;

    protected $stateChanged = FALSE;
    
    private $formattedColumnNames = NULL;

    public function __construct(ResultFormatter $parent = NULL) {
        parent::__construct();
        $this->setParent($parent);
    }

    public function __clone() {
        parent::__clone();

        $this->formattedColumnNames = ArrayHelper::copy($this->formattedColumnNames);

        if (isset($this->parent)) {
            $this->parent = clone $this->parent;
        }
    }

    protected function printFormattingPathImpl() {
        return get_class($this);
    }

    public function printFormattingPath() {
        $path = $this->printFormattingPathImpl();
        if (isset($this->parent)) {
            $path .= '(' . $this->parent->printFormattingPath() . ')';
        }

        return $path;
    }

    protected function adjustDatasetQueryRequestImpl(DataControllerCallContext $callcontext, DatasetQueryRequest $request) {}

    final public function adjustDatasetQueryRequest(DataControllerCallContext $callcontext, DatasetQueryRequest $request) {
        $this->adjustDatasetQueryRequestImpl($callcontext, $request);
        if (isset($this->parent)) {
            $this->parent->adjustDatasetQueryRequest($callcontext, $request);
        }
    }

    protected function adjustDatasetCountRequestImpl(DataControllerCallContext $callcontext, DatasetCountRequest $request) {}

    final public function adjustDatasetCountRequest(DataControllerCallContext $callcontext, DatasetCountRequest $request) {
        $this->adjustDatasetCountRequestImpl($callcontext, $request);
        if (isset($this->parent)) {
            $this->parent->adjustDatasetCountRequest($callcontext, $request);
        }
    }

    protected function adjustCubeQueryRequestImpl(DataControllerCallContext $callcontext, CubeQueryRequest $request) {}

    final public function adjustCubeQueryRequest(DataControllerCallContext $callcontext, CubeQueryRequest $request) {
        $this->adjustCubeQueryRequestImpl($callcontext, $request);
        if (isset($this->parent)) {
            $this->parent->adjustCubeQueryRequest($callcontext, $request);
        }
    }

    protected function adjustCubeCountRequestImpl(DataControllerCallContext $callcontext, CubeCountRequest $request) {}

    final public function adjustCubeCountRequest(DataControllerCallContext $callcontext, CubeCountRequest $request) {
        $this->adjustCubeCountRequestImpl($callcontext, $request);
        if (isset($this->parent)) {
            $this->parent->adjustCubeCountRequest($callcontext, $request);
        }
    }

    protected function isClientSortingRequiredImpl() {
        return FALSE;
    }

    final public function isClientSortingRequired() {
        return $this->isClientSortingRequiredImpl()
            || (isset($this->parent) && $this->parent->isClientSortingRequired());
    }

    protected function isClientPaginationRequiredImpl() {
        return FALSE;
    }

    final public function isClientPaginationRequired() {
        return $this->isClientSortingRequiredImpl()
            || $this->isClientPaginationRequiredImpl()
            || (isset($this->parent) && $this->parent->isClientPaginationRequired());
    }

    public function getParent($lastInChain = FALSE) {
        $parent = isset($this->parent) ? $this->parent : NULL;
        if (isset($parent) && $lastInChain) {
            $lastInChainParent = $parent->getParent($lastInChain);
            if (isset($lastInChainParent)) {
                $parent = $lastInChainParent;
            }
        }

        return $parent;
    }

    public function setParent(ResultFormatter $parent = NULL) {
        $this->parent = $parent;

        $this->stateChanged = TRUE;
    }

    public function addParent(ResultFormatter $parent = NULL) {
        $root = $this->getParent(TRUE);
        if (!isset($root)) {
            $root = $this;
        }

        $root->setParent($parent);
    }

    protected function isStateChanged() {
        $changed = $this->stateChanged;
        if (!$changed && isset($this->parent)) {
            $changed = $this->parent->isStateChanged();
        }

        return $changed;
    }

    protected function cleanInternalState() {
        $this->stateChanged = FALSE;
        $this->formattedColumnNames = NULL;

        if (isset($this->parent)) {
            $this->parent->cleanInternalState();
        }
    }

    protected function formatColumnNameImpl($columnName) {
        return $columnName;
    }

    final public function formatColumnName($columnName, $matchRequired = FALSE) {
        if ($this->isStateChanged()) {
            $this->cleanInternalState();
        }

        if (isset($this->formattedColumnNames[$columnName])) {
            $formattedColumnName = $this->formattedColumnNames[$columnName];
            if ($formattedColumnName === FALSE) {
                $formattedColumnName = NULL;
            }
        }
        else {
            $formattedColumnName = $this->formatColumnNameImpl($columnName);
            if (isset($formattedColumnName) && isset($this->parent)) {
                $formattedColumnName = $this->parent->formatColumnName($formattedColumnName);
            }

            // checking if the same name is not mapped to two different columns
            if (isset($formattedColumnName) && isset($this->formattedColumnNames)) {
                $otherColumnName = array_search($formattedColumnName, $this->formattedColumnNames);
                if ($otherColumnName !== FALSE) {
                    throw new IllegalStateException(t(
                        'Name mapping is ambiguous for %columnName column: [%mappedColumnNameA, %mappedColumnNameB]',
                        array(
                            '%columnName' => $formattedColumnName,
                            '%mappedColumnNameA' => $otherColumnName,
                            '%mappedColumnNameB' => $columnName)));
                }
            }
            $this->formattedColumnNames[$columnName] = isset($formattedColumnName) ? $formattedColumnName : FALSE;
        }

        if (!isset($formattedColumnName) && $matchRequired) {
            $this->errorRequiredFormattedColumnName($columnName);
        }

        return $formattedColumnName;
    }

    public function formatColumnNames(array $columnNames = NULL, $matchRequired = FALSE) {
        $formattedColumnNames = NULL;

        if (isset($columnNames)) {
            foreach ($columnNames as $columnName) {
                $formattedColumnName = $this->formatColumnName($columnName, $matchRequired);
                if (isset($formattedColumnName)) {
                    $formattedColumnNames[] = $formattedColumnName;
                }
            }
        }

        return $formattedColumnNames;
    }

    protected function formatColumnValueImpl($formattedColumnName, $columnValue) {
        return $columnValue;
    }

    final protected function formatColumnValue($formattedColumnName, $columnValue) {
        $formattedColumnValue = $this->formatColumnValueImpl($formattedColumnName, $columnValue);
        if (isset($this->parent)) {
            $formattedColumnValue = $this->parent->formatColumnValue($formattedColumnName, $formattedColumnValue);
        }

        return $formattedColumnValue;
    }

    public function setRecordColumnValueImpl(array &$record = NULL, $formattedColumnName, $formattedColumnValue) {
        $record[$formattedColumnName] = $formattedColumnValue;
    }

    final public function setRecordColumnValue(array &$record = NULL, $columnName, $columnValue) {
        $formattedColumnName = $this->formatColumnName($columnName);

        // if formatted column name does not exist we do not need to store the column value
        if (isset($formattedColumnName)) {
            $formattedColumnValue = $this->formatColumnValue($formattedColumnName, $columnValue);

            $this->setRecordColumnValueImpl($record, $formattedColumnName, $formattedColumnValue);
        }
    }

    protected function registerRecordImpl(array &$records = NULL, $record) {
        return FALSE;
    }

    final public function registerRecord(array &$records = NULL, $record) {
        $result = $this->registerRecordImpl($records, $record);

        if (isset($this->parent)) {
            $parentResult = $this->parent->registerRecord($records, $record);

            // only one formatter should register the records
            // behaviour is unclear if two formatters register the same record
            if ($result && $parentResult) {
                $this->errorUnsupportedChainOfResultFormatters();
            }

            $result = $result || $parentResult;
        }

        return $result;
    }

    protected function startImpl() {}

    final public function start() {
        $this->startImpl();
        if (isset($this->parent)) {
            $this->parent->start();
        }
    }

    protected function finishImpl(array &$records = NULL) {}

    public function finish(array &$records = NULL) {
        if (isset($records)) {
            $this->finishImpl($records);
        }
        if (isset($records) && isset($this->parent)) {
            $this->parent->finish($records);
        }
    }

    final public function formatRecords(array $records = NULL) {
        $formattedRecords = NULL;

        if (isset($records)) {
            LogHelper::log_debug(t("Using '!formatterClassName' to reformat result", array('!formatterClassName' => get_class($this))));

            $this->start();
            foreach ($records as $record) {
                $formattedRecord = NULL;
                foreach ($record as $columnName => $columnValue) {
                    $this->setRecordColumnValue($formattedRecord, $columnName, $columnValue);
                }

                if (!$this->registerRecord($formattedRecords, $formattedRecord)) {
                    $formattedRecords[] = $formattedRecord;
                }
            }
            $this->finish($formattedRecords);
        }

        return $formattedRecords;
    }

    protected function errorRequiredFormattedColumnName($columnName) {
        throw new IllegalArgumentException(t('Could not format column name: %columnName', array('%columnName' => $columnName)));
    }

    protected function errorUnsupportedChainOfResultFormatters() {
        throw new IllegalStateException(t('Unsupported chain of result formatters'));
    }
}
