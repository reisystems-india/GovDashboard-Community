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


class MySQLInitializeConnectionImpl extends AbstractInitializePDOConnectionImpl {

    public function initializePDOConnection(DataSourceHandler $handler, DataSourceMetaData $datasource) {
        if (!extension_loaded('pdo_mysql')) {
            throw new IllegalStateException(t("'MySQL PDO' PHP extension is not loaded"));
        }

        $dsn = "mysql:host=$datasource->host";
        if (isset($datasource->port)) {
            $dsn .= ";port=$datasource->port";
        }
        if (isset($datasource->database)) {
            $dsn .= ";dbname=$datasource->database";
        }
        $dsn .= ';charset=utf8';

        $connection = new PDO($dsn, $datasource->username, $datasource->password);

        $collate = isset($datasource->collation) ? $datasource->collation : 'utf8_unicode_ci';

        $sql = array(
            'SET NAMES utf8 COLLATE ' . $collate);

        $statementExecutor = new PDOExecuteStatementImpl();
        $statementExecutor->execute($handler, $connection, $sql);

        return $connection;
    }
}
