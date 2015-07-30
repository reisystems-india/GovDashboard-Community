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


abstract class AbstractSQLSystemTableMetaModelGenerator extends AbstractSystemTableMetaModelGenerator {

    protected function appendOwnerStatementCondition(&$sql, $isWhereSectionPresent, $ownerColumnName, array $eligibleOwners = NULL, array $ineligibleOwners = NULL) {
        $condition = NULL;
        if (isset($eligibleOwners)) {
            $condition = "$ownerColumnName IN ('" . implode("', '", $eligibleOwners) . "')";
        }
        elseif (isset($ineligibleOwners)) {
            $condition = "$ownerColumnName NOT IN ('" . implode("', '", $ineligibleOwners) . "')";
        }
        if (isset($condition)) {
            $sql .= "\n" . ($isWhereSectionPresent ? '   AND ' : ' WHERE ') . $condition;
        }
    }

    protected function executeQuery(DataSourceMetaData $datasource, $operationName, $sql) {
        LogHelper::log_info(new StatementLogMessage("metamodel.system.{$operationName}[{$datasource->type}][{$datasource->name}]", $sql));

        $executionCallContext = new DataControllerCallContext();

        $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);

        return $datasourceQueryHandler->executeQuery($executionCallContext, $datasource, $sql);
    }

    protected function generateColumnApplicationType(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, ColumnMetaData $column) {
        $classcontext = $callcontext->getClassContext($this);

        if (isset($classcontext->statementExecutionCallback[$datasource->type])) {
            $statementExecutionCallback = $classcontext->statementExecutionCallback[$datasource->type];
        }
        else {
            $datasourceQueryHandler = DataSourceQueryFactory::getInstance()->getHandler($datasource->type);
            $statementExecutionCallback = $datasourceQueryHandler->prepareQueryStatementExecutionCallbackInstance();

            $classcontext->statementExecutionCallback[$datasource->type] = $statementExecutionCallback;
        }

        $statementExecutionCallback->generateColumnType($column);
    }
}
