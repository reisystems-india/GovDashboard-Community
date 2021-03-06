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


interface TransactionStatus {

    const UNKNOWN         = 'Unknown';
    const NO_TRANSACTION  = 'No Transaction';
    const ACTIVE          = 'Active';
    const MARKED_ROLLBACK = 'Marked for Rollback';
    const PREPARING       = 'Preparation in Progress';
    const PREPARED        = 'Prepared';
    const COMMITTING      = 'Commit in Progress';
    const COMMITTED       = 'Committed';
    const ROLLING_BACK    = 'Rollback in Progress';
    const ROLLEDBACK      = 'Rolled back';
}


interface Transaction {

    function enlistResource($resourceName, ResourceManager $resource);
    function findResource($resourceName);

    function setRollbackOnly();

    function commit();
    function rollback();
}
