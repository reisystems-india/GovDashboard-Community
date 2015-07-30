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


class RowFlattenerResultFormatter extends AbstractResultFormatter {

    protected $groupByColumnNames = NULL;
    protected $enumerationColumnName = NULL;
    protected $subjectColumnNames = NULL;

    protected $formattedGroupByColumnNames = NULL;
    protected $formattedEnumerationColumnName = NULL;
    protected $formattedSubjectColumnNames = NULL;

    public function __construct($groupByColumnNames, $enumerationColumnName, $subjectColumnNames, ResultFormatter $parent = NULL) {
        parent::__construct($parent);

        $this->groupByColumnNames = is_array($groupByColumnNames) ? $groupByColumnNames : array($groupByColumnNames);
        $this->enumerationColumnName = $enumerationColumnName;
        $this->subjectColumnNames = is_array($subjectColumnNames) ? $subjectColumnNames : array($subjectColumnNames);

        $this->formattedGroupByColumnNames = $this->formatColumnNames($this->groupByColumnNames, TRUE);
        $this->formattedEnumerationColumnName = $this->formatColumnName($this->enumerationColumnName, TRUE);
        $this->formattedSubjectColumnNames = $this->formatColumnNames($this->subjectColumnNames, TRUE);
    }

    public function __clone() {
        parent::__clone();

        $this->groupByColumnNames = ArrayHelper::copy($this->groupByColumnNames);
        $this->subjectColumnNames = ArrayHelper::copy($this->subjectColumnNames);
        $this->formattedGroupByColumnNames = ArrayHelper::copy($this->formattedGroupByColumnNames);
        $this->formattedSubjectColumnNames = ArrayHelper::copy($this->formattedSubjectColumnNames);
    }

    protected function setRecordSubjectColumnValue(array &$existingRecord, array &$record, $subjectColumnName) {
        $existingRecord[$subjectColumnName . '_' . $record[$this->formattedEnumerationColumnName]] = $record[$subjectColumnName];
    }

    protected function setRecordSubjectColumnValues(array &$existingRecord, array &$record) {
        if (isset($this->formattedSubjectColumnNames)) {
            foreach ($this->formattedSubjectColumnNames as $subjectColumnName) {
                $this->setRecordSubjectColumnValue($existingRecord, $record, $subjectColumnName);
            }
        }
        else {
            foreach ($record as $name => $value) {
                if (in_array($name, $this->formattedGroupByColumnNames) || ($name === $this->formattedEnumerationColumnName)) {
                    continue;
                }

                $this->setRecordSubjectColumnValue($existingRecord, $record, $name);
            }
        }
    }

    protected function registerRecordImpl(array &$records = NULL, $record) {
        parent::registerRecordImpl($records, $record);

        if (isset($records)) {
            // trying to find a record which could be reused
            foreach ($records as &$existingRecord) {
                $isRecordMatched = TRUE;
                foreach ($this->formattedGroupByColumnNames as $groupByColumnName) {
                    if (isset($existingRecord[$groupByColumnName])) {
                        if (isset($record[$groupByColumnName])) {
                            if ($existingRecord[$groupByColumnName] !== $record[$groupByColumnName]) {
                                $isRecordMatched = FALSE;
                            }
                        }
                        else {
                            $isRecordMatched = FALSE;
                        }

                    }
                    elseif (isset($record[$groupByColumnName])) {
                        $isRecordMatched = FALSE;
                    }

                    if (!$isRecordMatched) {
                        break;
                    }
                }

                if ($isRecordMatched) {
                    $this->setRecordSubjectColumnValues($existingRecord, $record);
                    return TRUE;
                }
            }
            unset($existingRecord);
        }

        // preparing new record
        $newRecord = NULL;
        foreach ($record as $name => $value) {
            if ($name === $this->formattedEnumerationColumnName) {
                continue;
            }

            if (isset($this->formattedSubjectColumnNames)) {
                if (in_array($name, $this->formattedSubjectColumnNames)) {
                    continue;
                }
            }
            elseif (!in_array($name, $this->formattedGroupByColumnNames)) {
                continue;
            }

            $newRecord[$name] = $value;
        }
        $this->setRecordSubjectColumnValues($newRecord, $record);
        $records[] = $newRecord;

        return TRUE;
    }

    protected function adjustCubeCountRequestImpl(DataControllerCallContext $callcontext, CubeCountRequest $request) {
        list($dimensionName) = ParameterNameHelper::split($this->enumerationColumnName);

        // we have to find corresponding dimension in this request
        $isDirectionFound = FALSE;
        if (isset($request->dimensions)) {
            foreach ($request->dimensions as $index => $dimension) {
                if ($dimension->name == $dimensionName) {
                    $isDirectionFound = TRUE;
                    unset($request->dimensions[$index]);

                    break;
                }
            }
        }
        if (!$isDirectionFound) {
            throw new IllegalStateException(t(
            	'Could not find configuration for %dimensionName dimension in the request',
                array('%dimensionName' => $dimensionName)));
        }
    }

    protected function isClientSortingRequiredImpl() {
        return TRUE;
    }

    protected function isClientPaginationRequiredImpl() {
        return TRUE;
    }
}