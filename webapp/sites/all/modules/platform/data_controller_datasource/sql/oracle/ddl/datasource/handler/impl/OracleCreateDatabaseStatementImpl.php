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


class OracleCreateDatabaseStatementImpl extends AbstractCreateDatabaseStatementImpl {

    public function generate(DataSourceHandler $handler, DataSourceMetaData $datasource, array $options = NULL) {
        // a user needs to have 'CREATE SESSION' and 'CREATE USER' system privilege to execute the following statement
        $createUserSQL = "CREATE USER {$datasource->database} IDENTIFIED EXTERNALLY";

        $initialPrivilegeSQL = "GRANT CREATE SESSION TO {$datasource->database}";

        // additional privileges for the future:
        //  * GRANT CREATE TABLE TO {$datasource->database}
        //  * GRANT CREATE VIEW TO {$datasource->database}
        //  * GRANT UNLIMITED TABLESPACE TO {$datasource->database}

        return array($createUserSQL, $initialPrivilegeSQL);
    }
}
