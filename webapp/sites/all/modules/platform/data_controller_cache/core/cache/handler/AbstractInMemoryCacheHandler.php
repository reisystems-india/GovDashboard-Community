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


abstract class AbstractInMemoryCacheHandler extends AbstractCacheHandler {

    private $cache = NULL;

    protected function isEntryPresentImpl($cacheEntryName) {
        return isset($this->cache[$cacheEntryName]);
    }

    protected function loadValue($cacheEntryName, array $options = NULL) {
        return isset($this->cache[$cacheEntryName])
            ? (is_object($this->cache[$cacheEntryName]) ? clone $this->cache[$cacheEntryName] : $this->cache[$cacheEntryName])
            : NULL;
    }

    protected function loadValues(array $cacheEntryNames, array $options = NULL) {
        $values = NULL;

        foreach ($cacheEntryNames as $cacheEntryName) {
            $value = $this->loadValue($cacheEntryName);
            if (isset($value)) {
                $values[$cacheEntryName] = $value;
            }
        }

        return $values;
    }

    protected function storeValue($cacheEntryName, $value, $expirationTime, array $options = NULL) {
        // Note: we do not need to support $expirationTime parameter because instance of this class lives only for time of the request

        // we need to clone the value to preserve further modification of values in this cache
        if (isset($value)) {
            $this->cache[$cacheEntryName] = is_object($value) ? clone $value : $value;
        }
        else {
            unset($this->cache[$cacheEntryName]);
        }

        return TRUE;
    }

    protected function storeValues(array $values, $expirationTime, array $options = NULL) {
        $errorCacheEntryNames = NULL;

        foreach ($values as $cacheEntryName => $value) {
            $result = $this->storeValue($cacheEntryName, $value, $expirationTime);
            if ($result === FALSE) {
                $errorCacheEntryNames[] = $cacheEntryName;
            }
        }

        return $errorCacheEntryNames;
    }

    protected function flushImpl($cacheSubsetName) {
        if (isset($cacheSubsetName)) {
            if (isset($this->cache)) {
                foreach ($this->cache as $cacheEntryName => $value) {
                    if (!$this->inSubset($cacheSubsetName, $cacheEntryName)) {
                        continue;
                    }

                    unset($this->cache[$cacheEntryName]);
                }
            }
        }
        else {
            $this->cache = NULL;
        }

        return TRUE;
    }
}
