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


class OracleMetaModelGenerator extends AbstractSQLSystemTableMetaModelGenerator {

    protected function isDataSourceAcceptable(DataSourceMetaData $datasource, array $filters = NULL) {
        return ($datasource->type == OracleDataSource::TYPE) && parent::isDataSourceAcceptable($datasource, $filters);
    }

    protected function adjustOwnerName($owner) {
        return strtoupper($owner);
    }

    protected function generateTablePublicName($tableName) {
        return strtolower($tableName);
    }

    protected function generateColumnPublicName($columnName) {
        return strtolower($columnName);
    }

    protected function loadColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners($callcontext, $datasource->username);
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT owner AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       table_name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       column_name AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       (column_id - 1) AS ' . self::CN_COLUMN_INDEX . ",\n" .
            '       data_type AS ' . self::CN_COLUMN_TYPE . ",\n" .
            '       data_length AS ' . self::CN_COLUMN_TYPE_LENGTH . ",\n" .
            '       data_precision AS ' . self::CN_COLUMN_TYPE_PRECISION . ",\n" .
            '       data_scale AS ' . self::CN_COLUMN_TYPE_SCALE . "\n" .
            '  FROM all_tab_columns';
        $this->appendOwnerStatementCondition($sql, FALSE, 'owner', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'table.column', $sql);
    }

    protected function loadTableComments(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners($callcontext, $datasource->username);
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT owner AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       table_name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       comments AS ' . self::CN_COMMENT . "\n" .
            "  FROM all_tab_comments\n" .
            ' WHERE comments IS NOT NULL';
        $this->appendOwnerStatementCondition($sql, TRUE, 'owner', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'table.comment', $sql);
    }

    protected function loadColumnComments(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners($callcontext, $datasource->username);
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT owner AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       table_name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       column_name AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       comments AS ' . self::CN_COMMENT . "\n" .
            "  FROM all_col_comments\n" .
            ' WHERE comments IS NOT NULL';
        $this->appendOwnerStatementCondition($sql, TRUE, 'owner', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'column.comment', $sql);
    }

    protected function convertConstraintTypes(array $constraintTypes) {
        $types = NULL;

        foreach ($constraintTypes as $constraintType) {
            if ($constraintType == self::CONSTRAINT_TYPE__PRIMARY_KEY) {
                $types[] = 'P';
            }
            elseif ($constraintType == self::CONSTRAINT_TYPE__REFERENCE) {
                $types[] = 'R';
            }
            else {
                throw new UnsupportedOperationException(t(
                    'Unsupported constraint type: %constraintType',
                    array('%constraintType' => $constraintType)));
            }
        }

        return $types;
    }

    protected function loadPrimaryKeyConstraintsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners($callcontext, $datasource->username);
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT owner AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       constraint_name AS ' . self::CN_OBJECT_NAME . ",\n" .
            "       '" . self::CONSTRAINT_TYPE__PRIMARY_KEY . "' AS " . self::CN_CONSTRAINT_TYPE . "\n" .
            "  FROM all_constraints\n" .
            " WHERE constraint_type = 'P'";
        $this->appendOwnerStatementCondition($sql, TRUE, 'owner', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'constraint.primaryKey', $sql);
    }

    protected function loadConstraintColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, array $constraintTypes) {
        $eligibleOwners = $this->getEligibleOwners($callcontext, $datasource->username);
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $types = $this->convertConstraintTypes($constraintTypes);

        $sql =
            'SELECT c.owner AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       cc.constraint_name AS ' . self::CN_OBJECT_NAME . ",\n" .
            '       cc.table_name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       cc.column_name AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       (cc.position - 1) AS ' . self::CN_COLUMN_INDEX . "\n" .
            "  FROM all_cons_columns cc JOIN all_constraints c ON cc.constraint_name = c.constraint_name AND cc.owner = c.owner\n" .
            ' WHERE c.constraint_type IN (' . ArrayHelper::serialize($types) . ')';
        $this->appendOwnerStatementCondition($sql, TRUE, 'c.owner', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'constraint.column', $sql);
    }

    protected function loadReferenceConstraintsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners($callcontext, $datasource->username);
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT owner AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       constraint_name AS ' . self::CN_OBJECT_NAME . ",\n" .
            '       r_constraint_name AS ' . self::CN_REFERENCED_OBJECT_NAME . "\n" .
            "  FROM all_constraints\n" .
            " WHERE constraint_type = 'R'";
        $this->appendOwnerStatementCondition($sql, TRUE, 'owner', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'constraint.reference', $sql);
    }
}
