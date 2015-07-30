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


class MySQLCreateTableStatementImpl extends AbstractCreateTableStatementImpl {

    // can be set to NULL to use engine by default
    public static $STORAGE_ENGINE__DEFAULT = 'InnoDB';

    protected function maxLength4VariableLengthString() {
        return 65535;
    }

    protected function assembleLongString(ColumnMetaData $column) {
        $column->type->databaseType = 'TEXT';
    }

    protected function assembleBigInteger(ColumnMetaData $column) {
        $column->type->databaseType = 'BIGINT';
    }

    protected function prepareTableCreateOptions(DataSourceHandler $handler, DatasetMetaData $dataset, $indent, &$sql) {
        parent::prepareTableCreateOptions($handler, $dataset, $indent, $sql);

        if (isset(self::$STORAGE_ENGINE__DEFAULT)) {
            $sql .= "\nENGINE = " . self::$STORAGE_ENGINE__DEFAULT;
        }
    }

    protected function preparePrimaryKeyCreateStatement4Update(DataSourceHandler $handler, DatasetMetaData $dataset) {
        $sql = parent::preparePrimaryKeyCreateStatement4Update($handler, $dataset);
        if (!isset($sql)) {
            return NULL;
        }

        $primaryKeyConstraintColumnNames = $dataset->getKeyColumnNames();
        foreach ($primaryKeyConstraintColumnNames as $primaryKeyConstraintColumnName) {
            $sql = $this->fixForeignKeyProblem($handler, $dataset, $primaryKeyConstraintColumnName, TRUE, $sql);
        }

        return $sql;
    }

    protected function prepareColumnDeleteStatement(DataSourceHandler $handler, DatasetMetaData $dataset, $columnName) {
        $sql = parent::prepareColumnDeleteStatement($handler, $dataset, $columnName);

        return $this->fixForeignKeyProblem($handler, $dataset, $columnName, FALSE, $sql);
    }

    protected function preparePrimaryKeyDeleteStatement(DataSourceHandler $handler, DatasetMetaData $dataset, array $originalKeyColumnNames) {
        $sql = 'DROP PRIMARY KEY';

        // we need to use the fix only for first column in primary key
        $firstColumnName = reset($originalKeyColumnNames);

        return $this->fixForeignKeyProblem($handler, $dataset, $firstColumnName, TRUE, $sql) . ', '
            . $this->fixDropPrimaryKeyColumnProblem($handler, $dataset, $originalKeyColumnNames);
    }

    protected function prepareForeignKeyDeleteStatement(DataSourceHandler $handler, DatasetMetaData $dataset, $columnName, $foreignKeyPrefix = NULL) {
        return isset($foreignKeyPrefix)
            ? 'DROP FOREIGN KEY ' . $this->generateForeignKeyConstraintName($dataset, $columnName, $foreignKeyPrefix)
            // if prefix is not available we need to delete 'an appropriate' foreign key constraint
            : $this->fixForeignKeyProblem($handler, $dataset, $columnName, FALSE, NULL);
    }

    protected function fixForeignKeyProblem(DataSourceHandler $handler, DatasetMetaData $dataset, $columnName, $recreateForeignKey, $sql) {
        // we implement the following logic because of restrictions in MySQL which are the following:
        //   - unique key constraint cannot be deleted if there is a foreign key for the column
        //   - to delete the unique constraint we need to alter table and delete the foreign key, the unique key and then recreate the foreign key
        //   - the above approach does not work if name of deleted foreign key is the same as of created one
        //   - to support it we need to re-create foreign key with different name
        //   - Example: delete fk_a and recreate with f2_a name. Next time delete f2_a and create fk_a

        $foreignKeyName = $this->generateForeignKeyConstraintName($dataset, $columnName);
        // preparing an alternative name for the foreign key
        $foreignKeyName2 = $foreignKeyName;
        $foreignKeyName2[1] = '2';

        $availableForeignKeyNames = array($foreignKeyName, $foreignKeyName2);
        $availableForeignKeyPrefixes = array('fk_', 'f2_');

        $schemaName = $handler->getDataSourceOwner($dataset->datasourceName);

        // looking for existing foreign key for the column
        $constraintQuery = db_select('INFORMATION_SCHEMA.key_column_usage', 'c');
        $constraintQuery->fields('c', array('constraint_name', 'referenced_table_name', 'referenced_column_name'));
        $constraintQuery->condition('c.constraint_schema', $schemaName);
        $constraintQuery->condition('c.table_schema', $schemaName);
        $constraintQuery->condition('c.table_name', $dataset->source);
        $constraintQuery->condition('c.column_name', $columnName);
        // preparing pattern to support both fk_* and f2_*
        $foreignKeyPattern = $foreignKeyName;
        $foreignKeyPattern[1] = '_';
        $constraintQuery->condition('c.constraint_name', $foreignKeyPattern, 'LIKE');

        $statement = $constraintQuery->execute();

        $existingForeignKeyName = $referencedTableName = $referencedColumnName = NULL;
        foreach ($statement as $record) {
            if (isset($existingForeignKeyName)) {
                $column = $dataset->getColumn($columnName);
                throw new UnsupportedOperationException(t(
                    'Found several foreign key constraint for the %datasetName dataset %columnName column: [%foreignKey1, %foreignKey2]',
                    array(
                        '%datasetName' => $dataset->publicName,
                        '%columnName' => $column->publicName,
                        '%foreignKey1' => $existingForeignKeyName,
                        '%foreignKey2' => $record->constraint_name)));
            }

            $existingForeignKeyName = $record->constraint_name;
            $referencedTableName = $record->referenced_table_name;
            $referencedColumnName = $record->referenced_column_name;
        }

        $fixedSQL = '';

        // removing foreign key constraint
        $recreatedForeignKeyPrefix = NULL;
        if (isset($existingForeignKeyName)) {
            $index = array_search($existingForeignKeyName, $availableForeignKeyNames);
            if ($index === FALSE) {
                $column = $dataset->getColumn($columnName);
                throw new UnsupportedOperationException(t(
                    '%foundForeignKey foreign key constraint name is not supported for the %datasetName dataset %columnName column. Supported names are %foreignKey1 and %foreignKey2',
                    array(
                        '%foundForeignKey' => $existingForeignKeyName,
                        '%datasetName' => $dataset->publicName,
                        '%columnName' => $column->publicName,
                        '%foreignKey1' => $foreignKeyName,
                        '%foreignKey2' => $foreignKeyName2)));
            }
            $fixedSQL = $this->prepareForeignKeyDeleteStatement($handler, $dataset, $columnName, $availableForeignKeyPrefixes[$index]);

            $recreatedForeignKeyIndex = ($index == 0) ? 1 : 0;
            $recreatedForeignKeyPrefix = $availableForeignKeyPrefixes[$recreatedForeignKeyIndex];
        }

        // registering original SQL statement
        if (isset($sql)) {
            if ($fixedSQL != '') {
                $fixedSQL .= $this->getUpdateClauseDelimiter() . ' ';
            }
            $fixedSQL .= $sql;
        }

        // adding foreign key constraint
        if ($recreateForeignKey && isset($existingForeignKeyName)) {
            $fixedSQL .= $this->getUpdateClauseDelimiter() . ' ADD ' . $this->prepareForeignKeyCreateStatement($handler, $dataset, $columnName, $referencedTableName, $referencedColumnName, $recreatedForeignKeyPrefix);
        }

        return $fixedSQL;
    }

    protected function fixDropPrimaryKeyColumnProblem(DataSourceHandler $handler, DatasetMetaData $dataset, array $originalKeyColumnNames) {
        // when a column is used in primary key MySQL creates 'NOT NULL' constraint automatically
        // when the column is excluded from primary key the constraint is not deleted
        // with the following fix we recreate the column without such constraint

        $sql = '';

        foreach ($originalKeyColumnNames as $columnName) {
            $column = $dataset->getColumn($columnName);

            if ($sql != '') {
                $sql .= ', ';
            }
            $sql .= 'MODIFY COLUMN ' . $this->prepareColumnCreateStatement($handler, $column, FALSE);
        }

        return $sql;
    }
}
