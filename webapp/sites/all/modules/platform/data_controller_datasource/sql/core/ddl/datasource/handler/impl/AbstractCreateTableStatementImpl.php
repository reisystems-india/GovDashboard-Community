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


abstract class AbstractCreateTableStatementImpl extends AbstractObject {

    // *****************************************************************************************************************************
    //      Type: String
    // *****************************************************************************************************************************
    protected function maxLength4VariableLengthString() {
        return 255;
    }

    protected function assembleVariableLengthString(ColumnMetaData $column) {
        $column->type->databaseType = "VARCHAR({$column->type->length})";
    }

    abstract protected function assembleLongString(ColumnMetaData $column);

    protected function prepareVariableLengthStringColumnDatabaseType(ColumnMetaData $column, $length = NULL) {
        $lengthConfigs = array(
            array('length' =>  255, 'overhead' => 0.00),
            array('length' =>  500, 'overhead' => 0.30),
            array('length' =>  750, 'overhead' => 0.60),
            array('length' => 1000, 'overhead' => 0.60),
            array('length' => 4000, 'overhead' => 1.00));

        $selectedLength = MathHelper::max(
            (isset($column->type->length) ? $column->type->length : 0),
            (isset($length) ? $length : 0));

        $maxlength = $this->maxLength4VariableLengthString();

        $isMatchFound = FALSE;
        for ($i = 0, $count = count($lengthConfigs); ($i < $count) && !$isMatchFound; $i++) {
            $threshold = $lengthConfigs[$i]['length'];
            if ($threshold > $maxlength) {
                break;
            }

            $overhead = $lengthConfigs[$i]['overhead'];
            if (($selectedLength * (1.0 + $overhead)) <= $threshold) {
                $selectedLength = $threshold;
                $isMatchFound = TRUE;
            }
        }

        if ($isMatchFound) {
            $column->type->length = $selectedLength;
            $this->assembleVariableLengthString($column);
        }
        else {
            $column->type->length = NULL;
            $this->assembleLongString($column);
        }
    }

    protected function assembleFixedLengthString(ColumnMetaData $column, $selectedLength) {
        $column->type->databaseType = 'CHAR';

        if (isset($selectedLength)) {
            $column->type->databaseType .= "($selectedLength)";
        }
    }

    protected function prepareFixedLengthStringColumnDatabaseType(ColumnMetaData $column, $length = NULL) {
        $this->assembleFixedLengthString($column, $length);
    }

    // *****************************************************************************************************************************
    //      Type: Numeric
    // *****************************************************************************************************************************
    protected function assembleTinyInteger(ColumnMetaData $column) {
        $this->assembleInteger($column);
    }

    protected function assembleSmallInteger(ColumnMetaData $column) {
        $this->assembleInteger($column);
    }

    protected function assembleInteger(ColumnMetaData $column) {
        $column->type->databaseType = 'INTEGER';
    }

    abstract protected function assembleBigInteger(ColumnMetaData $column);

    protected function prepareIntegerColumnDatabaseType(ColumnMetaData $column, $precision = NULL) {
        $possiblePrecision = MathHelper::max($column->type->length, $column->type->precision, $precision);

        if (isset($possiblePrecision)) {
            if ($possiblePrecision < 3) {
                $this->assembleTinyInteger($column);
            }
            elseif ($possiblePrecision < 5) {
                $this->assembleSmallInteger($column);
            }
            elseif ($possiblePrecision < 10) {
                $this->assembleInteger($column);
            }
            else {
                $this->assembleBigInteger($column);
            }
        }
        else {
            $this->assembleInteger($column);
        }
    }

    protected function assembleNumber(ColumnMetaData $column, $selectedPrecision, $selectedScale) {
        $column->type->databaseType = 'DOUBLE PRECISION';
    }

    protected function prepareNumberColumnDatabaseType(ColumnMetaData $column, $precision = NULL, $scale = NULL) {
        $selectedPrecision = MathHelper::max($column->type->precision, $precision);
        $selectedScale = MathHelper::max($column->type->scale, $scale);

        switch ($column->type->applicationType) {
            case CurrencyDataTypeHandler::DATA_TYPE:
                if (!isset($selectedScale)) {
                    $selectedScale = 2;
                }
                break;
            case PercentDataTypeHandler::DATA_TYPE:
                if (isset($selectedScale)) {
                    $selectedScale += 2;
                }
                break;
        }

        $this->assembleNumber($column, $selectedPrecision, $selectedScale);
    }

    // *****************************************************************************************************************************
    //      Type: Date & Time
    // *****************************************************************************************************************************
    protected function assembleTime(ColumnMetaData $column) {
        $column->type->databaseType = 'TIME';
    }

    protected function assembleDate(ColumnMetaData $column) {
        $column->type->databaseType = 'DATE';
    }

    protected function assembleDateTime(ColumnMetaData $column) {
        $column->type->databaseType = 'TIMESTAMP';
    }

    protected function prepareDateColumnDatabaseType(ColumnMetaData $column, $isDate = TRUE, $isTime = FALSE) {
        if ($isDate) {
            if ($isTime) {
                $this->assembleDateTime($column);
            }
            else {
                $this->assembleDate($column);
            }
        }
        else {
            $this->assembleTime($column);
        }
    }

    // *****************************************************************************************************************************
    //      Column
    // *****************************************************************************************************************************
    /*
     * Maps column application data type to database specific type
     */
    protected function prepareColumnDatabaseType(DataSourceHandler $handler, ColumnMetaData $column) {
        if (isset($column->type->applicationType)) {
            $storageDataType = NULL;

            if ($column->type->getReferencedDatasetName() == NULL) {
                $datatypeHandler = DataTypeFactory::getInstance()->getHandler($column->type->applicationType);
                $storageDataType = $datatypeHandler->getStorageDataType();
            }
            else {
                $storageDataType = Sequence::getSequenceColumnType()->applicationType;
            }

            switch ($storageDataType) {
                case StringDataTypeHandler::DATA_TYPE:
                    break;
                case IntegerDataTypeHandler::DATA_TYPE:
                    $this->prepareIntegerColumnDatabaseType($column);
                    break;
                case NumberDataTypeHandler::DATA_TYPE:
                case CurrencyDataTypeHandler::DATA_TYPE:
                case PercentDataTypeHandler::DATA_TYPE:
                    $this->prepareNumberColumnDatabaseType($column);
                    break;
                case BooleanDataTypeHandler::DATA_TYPE:
                    // calculating length of mapping of TRUE and FALSE values
                    $booleanHandler = DataTypeFactory::getInstance()->getHandler(BooleanDataTypeHandler::DATA_TYPE);
                    $valueTrue = $booleanHandler->castToStorageValue(TRUE);
                    $valueFalse = $booleanHandler->castToStorageValue(FALSE);
                    // length of the field depends on length of the mappings
                    $lengthValueTrue = strlen($valueTrue);
                    $lengthValueFalse = strlen($valueFalse);
                    $length = MathHelper::max($lengthValueTrue, $lengthValueFalse);
                    // detecting type for each value and selecting primary type
                    $datatype = DataTypeFactory::getInstance()->selectCompatibleDataType(
                        array(
                            DataTypeFactory::getInstance()->autoDetectDataType($valueTrue),
                            DataTypeFactory::getInstance()->autoDetectDataType($valueFalse)));
                    // for numeric values we use integer storage type, for rest - string
                    if ($datatype === IntegerDataTypeHandler::DATA_TYPE) {
                        $this->prepareIntegerColumnDatabaseType($column, $length);
                    }
                    elseif ($lengthValueTrue === $lengthValueFalse) {
                        $this->prepareFixedLengthStringColumnDatabaseType($column, $length);
                    }
                    else {
                        $this->prepareVariableLengthStringColumnDatabaseType($column, $length);
                    }
                    break;
                case DateTimeDataTypeHandler::DATA_TYPE:
                    $this->prepareDateColumnDatabaseType($column, TRUE, TRUE);
                    break;
                case DateDataTypeHandler::DATA_TYPE:
                    $this->prepareDateColumnDatabaseType($column);
                    break;
                case TimeDataTypeHandler::DATA_TYPE:
                    $this->prepareDateColumnDatabaseType($column, FALSE, TRUE);
                    break;
                default:
                    throw new UnsupportedOperationException(t(
                        'Unsupported data type for %columnName column: %columnType',
                        array('%columnName' => $column->publicName, '%columnType' => $column->type->applicationType)));
            }
        }

        if (!isset($column->type->databaseType)) {
            $this->prepareVariableLengthStringColumnDatabaseType($column);
        }

        return $column->type->databaseType;
    }

    protected function prepareColumnCreateStatement(DataSourceHandler $handler, ColumnMetaData $column, $isKey) {
        $columnName = $column->name;
        $columnType = $this->prepareColumnDatabaseType($handler, $column);
        $columnNullability = ($isKey ? 'NOT ' : '') . 'NULL';
        $columnDefaultValue = $isKey ? '' : ' DEFAULT NULL';

        return "$columnName $columnType $columnNullability{$columnDefaultValue}";
    }

    // *****************************************************************************************************************************
    //      Table
    // *****************************************************************************************************************************
    protected function generatePrimaryKeyConstraintName(DatasetMetaData $dataset) {
        return 'pk_' . $dataset->source;
    }

    protected function preparePrimaryKeyCreateStatement(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $primaryKeyConstraintColumnNames = $dataset->findKeyColumnNames();
        if (!isset($primaryKeyConstraintColumnNames)) {
            return NULL;
        }

        $constraintName = $this->generatePrimaryKeyConstraintName($dataset);
        $columnNames = implode(', ', $primaryKeyConstraintColumnNames);

        return "CONSTRAINT $constraintName PRIMARY KEY ($columnNames)";
    }

    protected function generateForeignKeyConstraintName(DatasetMetaData $dataset, $columnName, $foreignKeyPrefix = NULL) {
        return (isset($foreignKeyPrefix) ? $foreignKeyPrefix : 'fk_') . $dataset->source . '_' . $columnName;
    }

    protected function prepareForeignKeyCreateStatement(DataSourceHandler $handler, DatasetMetaData $dataset, $columnName, $referencedTableName, $referencedColumnName, $foreignKeyPrefix = NULL) {
        $constraintName = $this->generateForeignKeyConstraintName($dataset, $columnName, $foreignKeyPrefix);

        $assembledReferencedTableName = assemble_database_entity_name($handler, $dataset->datasourceName, $referencedTableName);

        return "CONSTRAINT $constraintName FOREIGN KEY ($columnName) REFERENCES $assembledReferencedTableName ({$referencedColumnName})";
    }

    // *****************************************************************************************************************************
    //      Create Table
    // *****************************************************************************************************************************
    protected function prepareColumnCreateStatements(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $columnSQLs = NULL;
        foreach ($dataset->getColumns() as $column) {
            $columnSQLs[$column->columnIndex] = $this->prepareColumnCreateStatement($handler, $column, $column->isKey());
        }
        if (!isset($columnSQLs)) {
            throw new IllegalArgumentException(t(
                "'@datasetName' dataset must have at least one column to create permanent storage",
                array('@datasetName' => $dataset->publicName)));
        }

        // sorting columns by column index
        ksort($columnSQLs);

        return $columnSQLs;
    }

    protected function prepareSystemColumnCreateStatements(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $columnSQLs = NULL;

        // preparing 'version' system column
        $columnVersion = $dataset->initiateColumn();
        $columnVersion->name = DatasetSystemColumnNames::VERSION;
        $columnVersion->description = t('System column to store version of a record');
        $columnVersion->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
        $columnSQLs[] = $this->prepareColumnCreateStatement($handler, $columnVersion, FALSE);

        // preparing 'state' system column
        $columnState = $dataset->initiateColumn();
        $columnState->name = DatasetSystemColumnNames::STATE;
        $columnState->description = t('System column to store internal state of a record');
        $columnState->type->applicationType = IntegerDataTypeHandler::DATA_TYPE;
        $columnSQLs[] = $this->prepareColumnCreateStatement($handler, $columnState, FALSE);

        return $columnSQLs;
    }

    protected function prepareTableCreateEntities(DataSourceHandler $handler, DatasetMetaData $dataset, $indent, &$sql) {}

    protected function prepareTableCreateOptions(DataSourceHandler $handler, DatasetMetaData $dataset, $indent, &$sql) {}

    public function prepareTableCreateStatement(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $indent = str_pad('', SelectStatementPrint::INDENT);

        $assembledTableName = assemble_database_entity_name($handler, $dataset->datasourceName, $dataset->source);

        $sql = "CREATE TABLE $assembledTableName (\n";

        $columnSQLs = $this->prepareColumnCreateStatements($handler, $dataset);
        ArrayHelper::merge($columnSQLs, $this->prepareSystemColumnCreateStatements($handler, $dataset));
        $sql .= $indent . implode(",\n$indent", $columnSQLs);

        $primaryKeyStatement = $this->preparePrimaryKeyCreateStatement($handler, $dataset);
        if (isset($primaryKeyStatement)) {
            $sql .= ",\n$indent" . $primaryKeyStatement;
        }

        $this->prepareTableCreateEntities($handler, $dataset, $indent, $sql);

        $sql .= "\n)";

        $this->prepareTableCreateOptions($handler, $dataset, $indent, $sql);

        return $sql;
    }

    // *****************************************************************************************************************************
    //      Update Table
    // *****************************************************************************************************************************
    protected function getUpdateClauseDelimiter() {
        return ',';
    }

    protected function allowPrimaryKeyCreation(DataSourceHandler $handler, DataControllerCallContext $callcontext, DatasetMetaData $dataset) {
        $primaryKeyColumnNames = $dataset->findKeyColumnNames();
        if (isset($primaryKeyColumnNames)) {
            $environment_metamodel = data_controller_get_environment_metamodel();

            $datasource = $environment_metamodel->getDataSource($dataset->datasourceName);

            $assembledTableName = assemble_database_entity_name($handler, $datasource->name, $dataset->source);

            $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

            // checking if there are any records with NULL values
            $nullValueConditions = array();
            foreach ($primaryKeyColumnNames as $columnName) {
                $nullValueConditions[] = "$columnName IS NULL";
            }
            $nullValueSQL = 'SELECT ' . implode(', ', $primaryKeyColumnNames) . " FROM $assembledTableName WHERE " . implode(' OR ', $nullValueConditions);
            $datasourceQueryHandler->getExtension('applyPagination')->apply($datasourceQueryHandler, $nullValueSQL, 0, 1);
            LogHelper::log_info(new StatementLogMessage('dataset.update.primaryKey.data.check.null', $nullValueSQL));
            $nullColumnRecords = $datasourceQueryHandler->executeQuery($callcontext, $datasource, $nullValueSQL);
            $isTruncateRequired = isset($nullColumnRecords);

            // checking for non-unique values
            if (!$isTruncateRequired) {
                $nonUniqueValueSQL = 'SELECT ' . implode(', ', $primaryKeyColumnNames)
                    . " FROM $assembledTableName GROUP BY " . implode(', ', $primaryKeyColumnNames) . ' HAVING COUNT(*) > 1';
                $datasourceQueryHandler->getExtension('applyPagination')->apply($datasourceQueryHandler, $nonUniqueValueSQL, 0, 1);
                LogHelper::log_info(new StatementLogMessage('dataset.update.primaryKey.data.check', $nonUniqueValueSQL));
                $nonUniqueColumnRecords = $datasourceQueryHandler->executeQuery($callcontext, $datasource, $nonUniqueValueSQL);
                $isTruncateRequired = isset($nonUniqueColumnRecords);
            }

            if ($isTruncateRequired) {
                $truncateDatasetSQL = $handler->getExtension('truncateTable')->generate($handler, $dataset);
                LogHelper::log_info(new StatementLogMessage('dataset.update.primaryKey.data.truncate', $truncateDatasetSQL));
                $handler->executeStatement($datasource, $truncateDatasetSQL);
            }
        }
    }

    protected function preparePrimaryKeyCreateStatement4Update(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $statement = $this->preparePrimaryKeyCreateStatement($handler, $dataset);

        return isset($statement) ? "ADD $statement" : NULL;
    }

    protected function prepareColumnDeleteStatement(DataSourceHandler $handler, DatasetMetaData $dataset, $columnName) {
        return "DROP COLUMN $columnName";
    }

    protected function prepareConstraintDeleteStatement($constraintName) {
        return "DROP CONSTRAINT $constraintName";
    }

    protected function preparePrimaryKeyDeleteStatement(DataSourceHandler $handler, DatasetMetaData $dataset, array $originalKeyColumnNames) {
        return $this->prepareConstraintDeleteStatement($this->generatePrimaryKeyConstraintName($dataset));
    }

    protected function prepareForeignKeyDeleteStatement(DataSourceHandler $handler, DatasetMetaData $dataset, $columnName, $foreignKeyPrefix = NULL) {
        return $this->prepareConstraintDeleteStatement($this->generateForeignKeyConstraintName($dataset, $columnName, $foreignKeyPrefix));
    }

    protected function prepareTableUpdateOperation(DataSourceHandler $handler, DataControllerCallContext $callcontext, DatasetMetaData $dataset, AbstractDatasetStorageOperation $operation, $indent, &$sql) {
        $classname = get_class($operation);
        switch ($classname) {
            case 'CreateColumnOperation':
                $column = $dataset->getColumn($operation->columnName);
                $sql .= "\n{$indent}ADD " . $this->prepareColumnCreateStatement($handler, $column, FALSE);
                break;
            case 'DropColumnOperation':
                $sql .= "\n{$indent}" . $this->prepareColumnDeleteStatement($handler, $dataset, $operation->columnName);
                break;
            case 'CreateColumnReferenceOperation':
                $referencedDataset = DatasetSourceTypeFactory::getInstance()->getTableDataset($operation->referencedDatasetName);

                $referencedTableName = $referencedDataset->source;
                $referencedColumnName = $referencedDataset->getKeyColumn()->name;

                $sql .= "\n{$indent}ADD " . $this->prepareForeignKeyCreateStatement($handler, $dataset, $operation->columnName, $referencedTableName, $referencedColumnName);
                break;
            case 'DropColumnReferenceOperation':
                $sql .= "\n{$indent}" . $this->prepareForeignKeyDeleteStatement($handler, $dataset, $operation->columnName);
                break;
            case 'CreateDatasetKeyOperation':
                $this->allowPrimaryKeyCreation($handler, $callcontext, $dataset);

                $statement = $this->preparePrimaryKeyCreateStatement4Update($handler, $dataset);
                if (isset($statement)) {
                    $sql .= "\n{$indent}{$statement}";
                }
                break;
            case 'DropDatasetKeyOperation':
                $sql .= "\n{$indent}" . $this->preparePrimaryKeyDeleteStatement($handler, $dataset, $operation->originalKeyColumnNames);
                break;
            default:
                throw new IllegalArgumentException(t('Unsupported dataset update operation: %classname', array('%classname' => $classname)));
        }
    }

    public function prepareTableUpdateStatement(DataSourceHandler $handler, DataControllerCallContext $callcontext, DatasetMetaData $dataset, array $operations) {
        $assembledTableName = assemble_database_entity_name($handler, $dataset->datasourceName, $dataset->source);

        $indent = str_pad('', SelectStatementPrint::INDENT);

        $sql = '';
        foreach ($operations as $operation) {
            if ($sql != '') {
                $sql .= $this->getUpdateClauseDelimiter();
            }
            $this->prepareTableUpdateOperation($handler, $callcontext, $dataset, $operation, $indent, $sql);
        }

        return ($sql == '') ? NULL : "ALTER TABLE $assembledTableName{$sql}";
    }
}
