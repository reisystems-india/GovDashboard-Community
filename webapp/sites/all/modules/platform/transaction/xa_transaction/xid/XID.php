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


define('MAXGTRIDSIZE', 64); // maximum size in bytes of global transaction identifier (gtrid)
define('MAXBQUALSIZE', 64); // maximum size in bytes of branch qualifier (bqual)

define('XID_FORMAT_NULL',   -1);

/*
 * Transaction branch identifier (Section 4.2)
 */
class XID extends AbstractObject {

    // check XID_FORMAT_* variables
    // > 0: other (custom) naming format
    public $formatId = XID_FORMAT_NULL;

    public $globalTransactionId = NULL;
    public $branchQualifier = NULL;

    public function isNull() {
        return $this->formatId == XID_FORMAT_NULL;
    }

    public function equals(XID $xid) {
        return (isset($xid))
            && ($this->formatId == $xid->formatId)
            && ($this->globalTransactionId == $xid->globalTransactionId)
            && ($this->branchQualifier == $xid->branchQualifier);
    }
}
