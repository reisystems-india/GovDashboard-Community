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


class OCIExecuteStatementImpl extends AbstractExecuteStatementImpl {

    public function executeIndividualStatement(DataSourceHandler $handler, $connection, $sql) {
        $statement = OCIImplHelper::oci_parse($connection, $sql);
        try {
            OCIImplHelper::oci_execute($connection, $statement, OCI_NO_AUTO_COMMIT);
            $affectedRecordCount = OCIImplHelper::oci_num_rows($connection, $statement);
        }
        catch (Exception $e) {
            OCIImplHelper::oci_free_statement($connection, $statement);

            throw $e;
        }
        OCIImplHelper::oci_free_statement($connection, $statement);

        return $affectedRecordCount;
    }
}
