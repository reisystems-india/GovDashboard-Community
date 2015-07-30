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


namespace GD\Security\Authentication\Storage;

interface Storage
{
    /**
     * Returns true if and only if storage is empty
     *
     * @throws \Exception If it is impossible to determine whether storage is empty
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws \Exception If reading contents from storage is impossible
     * @return mixed
     */
    public function read();

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws \Exception If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents);

    /**
     * Clears contents from storage
     *
     * @throws \Exception If clearing contents from storage is impossible
     * @return void
     */
    public function clear();
}
