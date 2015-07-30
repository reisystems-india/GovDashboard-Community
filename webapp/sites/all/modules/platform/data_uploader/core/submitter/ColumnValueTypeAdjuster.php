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


class ColumnValueTypeAdjuster extends AbstractDataSubmitter {

    protected $exceptionPoolSize = 0;

    protected $exceptionPool = NULL;
    protected $exceptionCount = 0;

    public function __construct($exceptionPoolSize) {
        parent::__construct();
        $this->exceptionPoolSize = $exceptionPoolSize;
    }

    protected function cleanInternalState() {
        $this->exceptionPool = NULL;
        $this->exceptionCount = 0;
    }

    public function doBeforeProcessingRecords(RecordMetaData $recordMetaData, AbstractDataProvider $dataProvider) {
        $result = parent::doBeforeProcessingRecords($recordMetaData, $dataProvider);
        if ($result) {
            $this->cleanInternalState();
        }

        return $result;
    }

    public function doBeforeRecordSubmitted(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        $result = parent::doBeforeRecordSubmitted($recordMetaData, $recordNumber, $record);

        if ($result) {
            $datatypeFactory = DataTypeFactory::getInstance();

            foreach ($recordMetaData->getColumns() as $column) {
                if (!isset($record[$column->columnIndex])) {
                    continue;
                }

                // FIXME convert data to data type of corresponding lookup dataset column
                if ($column->type->getReferencedDatasetName() != NULL) {
                    continue;
                }

                $handler = $datatypeFactory->getHandler($column->type->applicationType);
                try {
                    $record[$column->columnIndex] = $handler->castValue($record[$column->columnIndex]);
                }
                catch (Exception $e) {
                    $this->exceptionPool[$recordNumber][$column->publicName] = array(
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => ExceptionHelper::getExceptionMessage($e));
                    $this->exceptionCount++;

                    if ($this->exceptionCount >= $this->exceptionPoolSize) {
                        $this->publishExceptions(TRUE);
                    }
                }
            }

            $result = !isset($this->exceptionPool);
        }

        return $result;
    }

    protected function publishExceptions($abort) {
        $originalExceptionCount = $this->exceptionCount;

        if (isset($this->exceptionPool)) {
            foreach ($this->exceptionPool as $recordNumber => $columnsException) {
                foreach ($columnsException as $columnPublicName => $exceptionInfo) {
                    $message = $exceptionInfo['message'] . t(
                        ' [column: @columnName] [line: @lineNumber]',
                        array('@columnName' => $columnPublicName, '@lineNumber' => $recordNumber));

                    LogHelper::log_warn(t(
                        '@message at @file:@line',
                        array('@message' => $message, '@file' => $exceptionInfo['file'], '@line' => $exceptionInfo['line'])));

                    drupal_set_message($message, 'error');
                }
            }
        }
        $this->cleanInternalState();

        if ($abort && ($originalExceptionCount > 0)) {
            throw new DataParserException(t(
                'Found %exceptionCount issue(s) while parsing data',
                array('%exceptionCount' => $originalExceptionCount)));
        }
    }

    public function doAfterProcessingRecords(RecordMetaData $recordMetaData, $fileProcessedCompletely) {
        parent::doAfterProcessingRecords($recordMetaData, $fileProcessedCompletely);

        $this->publishExceptions(TRUE);

    }

    public function abort() {
        parent::abort();

        $this->publishExceptions(FALSE);
    }
}
