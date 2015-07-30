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


abstract class AbstractInformationSchemaMetaModelGenerator extends AbstractSQLSystemTableMetaModelGenerator{

    protected function loadColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners(
            $callcontext,
            (isset($datasource->schema) ? $datasource->schema : NULL));
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT table_schema AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       table_name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       column_name AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       (ordinal_position - 1) AS ' . self::CN_COLUMN_INDEX . ",\n" .
            '       data_type AS ' . self::CN_COLUMN_TYPE . ",\n" .
            '       character_maximum_length AS ' . self::CN_COLUMN_TYPE_LENGTH . ",\n" .
            '       numeric_precision AS ' . self::CN_COLUMN_TYPE_PRECISION . ",\n" .
            '       numeric_scale AS ' . self::CN_COLUMN_TYPE_SCALE . "\n" .
            "  FROM information_schema.columns\n" .
            " WHERE table_catalog = '{$datasource->database}'";
        $this->appendOwnerStatementCondition($sql, TRUE, 'table_schema', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'table.column', $sql);
    }

    protected function convertConstraintTypes(array $constraintTypes) {
        $types = NULL;

        foreach ($constraintTypes as $constraintType) {
            if ($constraintType == self::CONSTRAINT_TYPE__PRIMARY_KEY) {
                $types[] = 'PRIMARY KEY';
            }
            elseif ($constraintType == self::CONSTRAINT_TYPE__REFERENCE) {
                $types[] = 'FOREIGN KEY';
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
        $eligibleOwners = $this->getEligibleOwners(
            $callcontext,
            (isset($datasource->schema) ? $datasource->schema : NULL));
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT constraint_schema AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       constraint_name AS ' . self::CN_OBJECT_NAME . ",\n" .
            "       '" . self::CONSTRAINT_TYPE__PRIMARY_KEY . "' AS " . self::CN_CONSTRAINT_TYPE . "\n" .
            "  FROM information_schema.table_constraints\n" .
            " WHERE table_catalog = '{$datasource->database}'\n" .
            "   AND constraint_catalog = table_catalog\n" .
            "   AND constraint_type = 'PRIMARY KEY'";
        $this->appendOwnerStatementCondition($sql, TRUE, 'constraint_schema', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'constraint.primaryKey', $sql);
    }

    protected function loadConstraintColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, array $constraintTypes) {
        $eligibleOwners = $this->getEligibleOwners(
            $callcontext,
            (isset($datasource->schema) ? $datasource->schema : NULL));
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $types = $this->convertConstraintTypes($constraintTypes);

        $sql =
            'SELECT c.constraint_schema AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       cu.constraint_name AS ' . self::CN_OBJECT_NAME . ",\n" .
            '       cu.table_name AS ' . self::CN_TABLE_NAME . ",\n" .
            '       cu.column_name AS ' . self::CN_COLUMN_NAME . ",\n" .
            '       (cu.ordinal_position - 1) AS ' . self::CN_COLUMN_INDEX . "\n" .
            "  FROM information_schema.key_column_usage cu JOIN information_schema.table_constraints c ON cu.constraint_name = c.constraint_name AND cu.constraint_catalog = c.constraint_catalog\n" .
            " WHERE cu.table_catalog = '{$datasource->database}'\n" .
            "   AND cu.constraint_catalog = cu.table_catalog\n" .
            '   AND c.constraint_type IN (' . ArrayHelper::serialize($types) . ')';
        $this->appendOwnerStatementCondition($sql, TRUE, 'c.constraint_schema', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'constraint.column', $sql);
    }

    protected function loadReferenceConstraintsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        $eligibleOwners = $this->getEligibleOwners(
            $callcontext,
            (isset($datasource->schema) ? $datasource->schema : NULL));
        $ineligibleOwners = $this->getIneligibleOwners($callcontext);

        $sql =
            'SELECT constraint_schema AS ' . self::CN_TABLE_OWNER . ",\n" .
            '       constraint_name AS ' . self::CN_OBJECT_NAME . ",\n" .
            '       unique_constraint_name AS ' . self::CN_REFERENCED_OBJECT_NAME . "\n" .
            "  FROM information_schema.referential_constraints\n" .
            " WHERE constraint_catalog = '{$datasource->database}'\n" .
            "   AND unique_constraint_catalog = constraint_catalog";
        $this->appendOwnerStatementCondition($sql, TRUE, 'constraint_schema', $eligibleOwners, $ineligibleOwners);

        return $this->executeQuery($datasource, 'constraint.reference', $sql);
    }
}
