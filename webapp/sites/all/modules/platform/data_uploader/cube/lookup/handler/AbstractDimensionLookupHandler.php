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


// REVIEWED 01/21/2014
abstract class AbstractDimensionLookupHandler extends AbstractObject implements DimensionLookupHandler {

    private static $CACHE_SIZE__IDENTIFIERS = 1000;
    private $cachedIdentifiers = NULL;

    public function __construct($datatype) {
        parent::__construct();
    }

    // *****************************************************************************************************************************
    //
    // Supporting functions for prepareDatasetColumnLookupIds()
    //
    // *****************************************************************************************************************************
    protected function prepareLookupCacheKey($datasetName) {
        return $datasetName;
    }

    protected static function normalizeValue($value) {
        return isset($value) ? preg_replace('/(\p{M})/ui', '', normalizer_normalize($value, Normalizer::FORM_D)) : NULL;
    }

    public static function prepareLookupKey($items) {
        $lookupKey = NULL;

        if (is_array($items)) {
            $normalizedItems = NULL;
            foreach ($items as $item) {
                $normalizedItems[] = self::normalizeValue($item);
            }

            $lookupKey = ArrayHelper::prepareCompositeKey($normalizedItems);
        }
        else {
            $lookupKey = self::normalizeValue($items);
        }
        
        $lookupKey = strtoupper($lookupKey);

        return $lookupKey;
    }

    protected function freeSpaceInIdentifierCache($datasetName) {
        $lookupCacheKey = $this->prepareLookupCacheKey($datasetName);

        // there is no cache for the dataset yet
        if (!isset($this->cachedIdentifiers[$lookupCacheKey])) {
            return;
        }

        $cacheSize = count($this->cachedIdentifiers[$lookupCacheKey]);
        if ($cacheSize > self::$CACHE_SIZE__IDENTIFIERS) {
            // deleting from beginning of the array
            $offset = $cacheSize - self::$CACHE_SIZE__IDENTIFIERS;
            $this->cachedIdentifiers[$lookupCacheKey] = array_slice(
                $this->cachedIdentifiers[$lookupCacheKey], $offset, self::$CACHE_SIZE__IDENTIFIERS, TRUE);
        }
    }

    protected function loadIdentifiers($lookupDatasetName, array $uniqueSetColumns, array &$lookupValues) {
        $dataQueryController = data_controller_get_instance();
        $metamodel = data_controller_get_metamodel();

        $lookupDataset = $metamodel->getDataset($lookupDatasetName);
        $identifierColumnName = $lookupDataset->getKeyColumn()->name;

        $lookupCacheKey = $this->prepareLookupCacheKey($lookupDataset->name);

        $isCompositeUniqueSet = count($uniqueSetColumns) > 1;

        // preparing parameters for the query
        $queryParameters = NULL;
        foreach ($lookupValues as $lookupKey => $lookupValue) {
            if (isset($lookupValue->identifier)) {
                continue;
            }

            if (isset($this->cachedIdentifiers[$lookupCacheKey][$lookupKey])) {
                $lookupValues[$lookupKey]->identifier = $this->cachedIdentifiers[$lookupCacheKey][$lookupKey];
                continue;
            }

            if ($isCompositeUniqueSet) {
                $keyColumnValues = NULL;
                foreach ($uniqueSetColumns as $column) {
                    $columnName = $column->name;
                    $keyColumnValues[$columnName] = $lookupValue->$columnName;
                }

                $queryParameters[] = $keyColumnValues;
            }
            else {
                $columnName = $uniqueSetColumns[0]->name;
                $queryParameters[$columnName][] = $lookupValue->$columnName;
            }
        }
        if (!isset($queryParameters)) {
            return;
        }

        // preparing columns for the query
        $queryColumns = array($identifierColumnName);
        foreach ($uniqueSetColumns as $column) {
            ArrayHelper::addUniqueValue($queryColumns, $column->name);
        }

        // loading data from database for 'missing' records
        $loadedLookupProperties = $dataQueryController->queryDataset($lookupDataset->name, $queryColumns, $queryParameters);

        // processing found records
        if (isset($loadedLookupProperties)) {
            $foundUnmatchedIdentifiers = FALSE;
            foreach ($loadedLookupProperties as $lookupProperties) {
                $identifier = $lookupProperties[$identifierColumnName];

                // preparing lookup key
                $keyItems = NULL;
                foreach ($uniqueSetColumns as $column) {
                    $keyItems[] = $lookupProperties[$column->name];
                }
                $lookupKey = self::prepareLookupKey($keyItems);

                if (!isset($lookupValues[$lookupKey])) {
                    if (count($lookupValues) == 1) {
                        // 04/23/2014 if only one record requested and one record received, but the received key does not match the request
                        // it means that character encoding functionality is more sophisticated on server and we actually have a match

                        // storing the value into cache for further usage
                        $this->cachedIdentifiers[$lookupCacheKey][$lookupKey] = $identifier;

                        reset($lookupValues);
                        $alternativeLookupKey = key($lookupValues);
                        $lookupKey = $alternativeLookupKey;
                    }
                    else {
                        $foundUnmatchedIdentifiers = TRUE;
                        continue;
                    }
                }
                if (isset($lookupValues[$lookupKey]->identifier)) {
                    $searchCriteria = array();
                    foreach ($uniqueSetColumns as $column) {
                        $searchCriteria[$column->name] = $lookupProperties[$column->name];
                    }
                    LogHelper::log_error(t(
                        'Key: @searchCriteria. Loaded identifiers: @identifiers',
                        array(
                            '@searchCriteria' => ArrayHelper::serialize($searchCriteria, ', ', TRUE, FALSE),
                            '@identifiers' => ArrayHelper::serialize(array($lookupValues[$lookupKey]->identifier, $identifier), ', ', TRUE, FALSE))));
                    throw new IllegalArgumentException(t(
                        'Several records in %datasetName dataset match search criteria',
                        array('%datasetName' => $lookupDataset->publicName)));
                }
                $lookupValues[$lookupKey]->identifier = $identifier;

                // storing the value into cache for further usage
                $this->cachedIdentifiers[$lookupCacheKey][$lookupKey] = $identifier;
            }

            // found unmatched values. Processing unprocessed lookups one by one
            if ($foundUnmatchedIdentifiers) {
                foreach ($lookupValues as $lookupKey => $lookupValue) {
                    if (!isset($lookupValue->identifier)) {
                        $singleLookupValue = array($lookupKey => $lookupValue);
                        $this->loadIdentifiers($lookupDatasetName, $uniqueSetColumns, $singleLookupValue);
                    }
                }
            }

            $this->freeSpaceInIdentifierCache($lookupDataset->name);
        }
    }

    protected function selectLookupValuesWithMissingIdentifier(array &$lookupValues) {
        $missingIdentifierLookupValues = NULL;

        foreach ($lookupValues as $lookupKey => $lookupValue) {
            if (isset($lookupValue->identifier)) {
                continue;
            }

            $missingIdentifierLookupValues[$lookupKey] = $lookupValue;
        }

        return $missingIdentifierLookupValues;
    }

    protected function generateAndStoreIdentifiers($lookupDatasetName, array $uniqueSetColumns, array $nonKeyColumns = NULL, $sequenceName, array &$lookupValues) {
        $dataManipulationController = data_controller_dml_get_instance();
        $metamodel = data_controller_get_metamodel();

        $lookupDataset = $metamodel->getDataset($lookupDatasetName);
        $identifierColumnName = $lookupDataset->getKeyColumn()->name;

        $lookupCacheKey = $this->prepareLookupCacheKey($lookupDataset->name);

        // preparing insert operation meta data
        $recordsHolder = new IndexedRecordsHolder();
        $recordsHolder->recordMetaData = new RecordMetaData();
        // registering 'identifier' column
        $column = $recordsHolder->recordMetaData->registerColumn($identifierColumnName);
        $column->initializeTypeFrom(Sequence::getSequenceColumnType());
        // registering columns which represent lookup key
        foreach ($uniqueSetColumns as $uniqueSetColumn) {
            $column = $recordsHolder->recordMetaData->registerColumn($uniqueSetColumn->name);
            $column->initializeTypeFrom($uniqueSetColumn->type);
            $column->key = TRUE;
        }
        // registering non key columns
        if (isset($nonKeyColumns)) {
            foreach ($nonKeyColumns as $nonKeyColumn) {
                $column = $recordsHolder->recordMetaData->registerColumn($nonKeyColumn->name);
                $column->initializeTypeFrom($nonKeyColumn->type);
            }
        }

        // generating identifiers for source table
        $identifiers = Sequence::getNextSequenceValues($sequenceName, count($lookupValues));

        // preparing records for insert operation
        foreach ($lookupValues as $lookupKey => $lookupValue) {
            $identifier = array_pop($identifiers);

            $record = array($identifier);

            foreach ($uniqueSetColumns as $uniqueSetColumn) {
                $columnName = $uniqueSetColumn->name;
                $record[] = $lookupValue->$columnName;
            }

            if (isset($nonKeyColumns)) {
                $lookupValue = $lookupValues[$lookupKey];
                foreach ($nonKeyColumns as $nonKeyColumn) {
                    $record[] = $lookupValue->{$nonKeyColumn->name};
                }
            }

            $recordInstance = $recordsHolder->initiateRecordInstance();
            $recordInstance->initializeFrom($record);
            $recordsHolder->registerRecordInstance($recordInstance);

            $lookupValue->identifier = $identifier;
            $this->cachedIdentifiers[$lookupCacheKey][$lookupKey] = $identifier;
        }

        $this->freeSpaceInIdentifierCache($lookupDataset->name);

        // storing 'missing' records
        $dataManipulationController->insertDatasetRecordBatch($lookupDataset->name, $recordsHolder);
    }

    protected function prepareIdentifiers($lookupDatasetName, array $uniqueSetColumns, array $nonKeyColumns = NULL, $sequenceName, array &$lookupValues) {
        $this->loadIdentifiers($lookupDatasetName, $uniqueSetColumns, $lookupValues);

        $missingIdentifierLookupValues = $this->selectLookupValuesWithMissingIdentifier($lookupValues);
        if (isset($missingIdentifierLookupValues)) {
            $this->generateAndStoreIdentifiers($lookupDatasetName, $uniqueSetColumns, $nonKeyColumns, $sequenceName, $missingIdentifierLookupValues);
        }
    }

    // *****************************************************************************************************************************
    //
    // Supporting functions to implement unprepareDimension()
    //
    // *****************************************************************************************************************************
    public function unprepareDimension(MetaModel $metamodel, DatasetMetaData $dataset, $columnName) {}

    // *****************************************************************************************************************************
    //
    // Supporting functions to implement adjustReferencePointColumn()
    //
    // *****************************************************************************************************************************
    public function adjustReferencePointColumn(AbstractMetaModel $metamodel, $datasetName, $columnName) {
        $shared = FALSE;

        return array($datasetName, $columnName, $shared);
    }
}


abstract class DimensionLookupHandler__AbstractLookupValue extends AbstractObject {

    public $identifier = NULL;
}

class DimensionLookupHandler__LookupValue extends DimensionLookupHandler__AbstractLookupValue {

    public function getPropertyValue($name) {
        return $this->$name;
    }

    public function setPropertyValue($name, $value) {
        $this->$name = $value;
    }
}
