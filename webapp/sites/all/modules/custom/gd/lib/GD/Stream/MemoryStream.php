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

namespace GD\Stream;

class MemoryStream extends AbstractStream {

    protected $buffer = array();

    public function get($key) {
        if ( isset($this->buffer[$key]) ) {
            return $this->buffer[$key];
        } else {
            return null;
        }
    }

    public function set($key, $data) {
        if ( $key == null ) {
            $this->buffer = (array) $data;
        } else {
            $this->buffer[$key] = $data;
        }
    }

    public function flush() {
        $buffer = $this->buffer;
        $this->buffer = array();
        return $buffer;
    }
}