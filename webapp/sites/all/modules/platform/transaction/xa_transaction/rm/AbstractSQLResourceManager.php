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


abstract class AbstractSQLResourceManager extends AbstractResourceManager {

    protected function formatXID(XID $xid) {
        return "'$xid->globalTransactionId','$xid->branchQualifier',$xid->formatId";
    }

    protected function getStartTransactionQuery($xid) {
        $formattedXID = $this->formatXID($xid);

        return "XA START $formattedXID";
    }

    protected function getEndTransactionQuery($xid) {
        $formattedXID = $this->formatXID($xid);

        return "XA END $formattedXID";
    }

    protected function getPrepareTransactionQuery($xid) {
        $formattedXID = $this->formatXID($xid);

        return "XA PREPARE $formattedXID";
    }

    protected function getCommitTransactionQuery($xid, $flags) {
        $formattedXID = $this->formatXID($xid);

        $sql = "XA COMMIT $formattedXID";
        if (($flags & TMONEPHASE) == TMONEPHASE) {
            $sql .= ' ONE PHASE';
        }

        return $sql;
    }

    protected function getRollbackTransactionQuery($xid) {
        $formattedXID = $this->formatXID($xid);

        return "XA ROLLBACK $formattedXID";
    }

    protected function getRecoverTransactionQuery($xid) {
        $formattedXID = $this->formatXID($xid);

        return "XA RECOVER $formattedXID";
    }
}
