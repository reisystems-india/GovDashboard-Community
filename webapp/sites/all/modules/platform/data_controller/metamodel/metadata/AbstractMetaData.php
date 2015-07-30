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


abstract class AbstractMetaData extends AbstractObject {

    const ACCESS__PUBLIC = 'public';        // accessible by everyone
    const ACCESS__PROTECTED = 'protected';  // accessible within its context
    const ACCESS__PRIVATE = 'private';      // not accessible by public

    public $name = NULL;
    public $publicName = NULL;
    public $description = NULL;

    // access level to this object
    public $access = NULL;

    public $used = NULL;
    // TRUE: the meta data created temporarily to process a particular request
    public $temporary = NULL;
    // all meta data is prepared/calculated
    public $complete = NULL;

    // internal version of the meta data
    public $version = NULL;
    // loader which loaded the meta data
    public $loaderName = NULL;

    protected function prepareUnserializablePropertyNames(&$names) {}

    public function __sleep() {
        $unserializableNames = NULL;
        $this->prepareUnserializablePropertyNames($unserializableNames);

        $names = array();
        foreach ($this as $name => $value) {
            if (isset($value) && !isset($unserializableNames[$name])) {
                $names[] = $name;
            }
        }

        return $names;
    }

    public function initializeFrom($source) {
        $this->initializeInstanceFrom($source);
    }

    public function initializeInstanceFrom($source) {
        ObjectHelper::mergeWith($this, $source);
    }

    public function finalize() {
        if (!isset($this->publicName)) {
            $this->publicName = $this->name;
        }
    }

    public function isPublic() {
        return isset($this->access) ? ($this->access == self::ACCESS__PUBLIC) : TRUE;
    }

    public function markAsPublic() {
        $this->access = self::ACCESS__PUBLIC;
    }

    public function isProtected() {
        return isset($this->access) ? ($this->access == self::ACCESS__PROTECTED) : FALSE;
    }

    public function markAsProtected() {
        $this->access = self::ACCESS__PROTECTED;
    }

    public function isPrivate() {
        return isset($this->access) ? ($this->access == self::ACCESS__PRIVATE) : FALSE;
    }

    public function markAsPrivate() {
        $this->access = self::ACCESS__PRIVATE;
    }

    public function isUsed() {
        return isset($this->used) ? $this->used : TRUE;
    }

    public function isComplete() {
        return isset($this->complete) ? $this->complete : TRUE;
    }

    public function isTemporary() {
        return isset($this->temporary) ? $this->temporary : FALSE;
    }

    protected function markAsIncomplete() {
        $this->complete = FALSE;
    }

    public function markAsComplete() {
        $this->complete = TRUE;
    }
}
