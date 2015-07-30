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


class EmptyRecordSkipper extends AbstractDataSubmitter {

    protected function checkIfRecordEmpty(RecordMetaData $recordMetaData, array &$record) {
        // checking if the record has values for at least one column
        foreach ($recordMetaData->getColumns() as $column) {
            if (isset($record[$column->columnIndex])) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function doBeforeRecordSubmitted(RecordMetaData $recordMetaData, $recordNumber, array &$record) {
        $result = parent::doBeforeRecordSubmitted($recordMetaData, $recordNumber, $record);

        if ($result) {
            $isEmpty = $this->checkIfRecordEmpty($recordMetaData, $record);
            if ($isEmpty) {
                drupal_set_message(t('Empty record in line @lineNumber was ignored', array('@lineNumber' => $recordNumber)), 'warning');

                $result = FALSE;
            }
        }

        return $result;
    }
}
