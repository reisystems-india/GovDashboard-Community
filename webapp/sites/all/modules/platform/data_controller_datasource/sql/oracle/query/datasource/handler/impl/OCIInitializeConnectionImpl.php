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


class OCIInitializeConnectionImpl extends AbstractInitializeConnectionImpl {

    public function initialize(DataSourceHandler $handler, DataSourceMetaData $datasource) {
        if (!isset($datasource->database)) {
            throw new IllegalStateException(t('Entry name from tnsnames.ora is not provided'));
        }

        $connection = OCIImplHelper::oci_connect($datasource->username, $datasource->password, $datasource->database);

        $oracleDateTimeFormat = $handler->getExtension('formatDateValue')->prepareFormat(DateTimeDataTypeHandler::$FORMAT_DEFAULT, FALSE);
        $oracleDateTimeTZFormat = $handler->getExtension('formatDateValue')->prepareFormat(DateTimeDataTypeHandler::$FORMAT_DEFAULT, TRUE);

        $sql = array(
            'ALTER SESSION SET NLS_SORT=ASCII7_AI',
            'ALTER SESSION SET NLS_COMP=LINGUISTIC',
            "ALTER SESSION SET NLS_DATE_FORMAT='$oracleDateTimeFormat'",
            "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='$oracleDateTimeFormat'",
            "ALTER SESSION SET NLS_TIMESTAMP_TZ_FORMAT='$oracleDateTimeTZFormat'");

        $statementExecutor = new OCIExecuteStatementImpl();
        $statementExecutor->execute($handler, $connection, $sql);

        return $connection;
    }
}
