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

/**
 * Non-Persistent Authentication Storage
 *
 * For authentication that happens again on each request, this will always be
 * re-populated. So there's no need to use sessions, this simple value class
 * will hold the data for rest of the current request.
 */
class NonPersistent implements Storage
{
    /**
     * Holds the actual auth data
     */
    protected $data;

    /**
     * Returns true if and only if storage is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Returns the contents of storage
     * Behavior is undefined when storage is empty.
     *
     * @return mixed
     */
    public function read()
    {
        return $this->data;
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->data = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @return void
     */
    public function clear()
    {
        $this->data = null;
    }
}
