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


interface CacheHandler {

    const OPTION__DATA_RESET_DATETIME = 'reset_datetime';
    const OPTION__DATA_UPDATE_IN_PROGRESS = 'update_in_progress';

    function getCacheType();
    function isAccessible();

    function isEntryPresent($name);

    function getValue($name, array $options = NULL);
    function getValues(array $names, array $options = NULL);

    function setValue($name, $value, $expirationTime = NULL, array $options = NULL);
    function setValues(array $values, $expirationTime = NULL, array $options = NULL);

    function flush($subsetName = NULL);
}
