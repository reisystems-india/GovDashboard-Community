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


class MSSQLInitializeConnectionImpl extends AbstractInitializePDOConnectionImpl {

	public function initializePDOConnection(DataSourceHandler $handler, DataSourceMetaData $datasource) {
        // http://www.php.net/manual/en/ref.pdo-sqlsrv.php
        if (!extension_loaded('pdo_sqlsrv')) {
            throw new IllegalStateException(t("'Microsoft SQL Server PDO' PHP extension is not loaded"));
        }

        $dsn = "sqlsrv:Server=$datasource->server";
		if (isset($datasource->port)) {
			$dsn .= ",$datasource->port";
		}
		if (isset($datasource->database)) {
			$dsn .= ";Database=$datasource->database";
		}

        $username = isset($datasource->username) ? $datasource->username : NULL;
        $password = isset($datasource->password) ? $datasource->password : NULL;

		return new PDO($dsn, $username, $password);
	}
}