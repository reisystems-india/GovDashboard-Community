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


class APCHandler extends AbstractSharedCacheHandler {

    const CACHE__TYPE = 'APC';

    protected $accessible = FALSE;

    public function getCacheType() {
        return self::CACHE__TYPE;
    }

    protected function initialize($prefix, DataSourceMetaData $datasource = NULL) {
        $this->accessible = function_exists('apc_fetch') && function_exists('apc_store') && (PHP_SAPI !== 'cli');

        return TRUE;
    }

    public function isAccessible() {
        return parent::isAccessible() && $this->accessible;
    }

    protected function isEntryPresentImpl($cacheEntryName) {
        return apc_exists($cacheEntryName);
    }

    protected function loadValueImpl($cacheEntryName) {
        $value = apc_fetch($cacheEntryName);
        if ($value === FALSE) {
            $value = NULL;
        }

        return $value;
    }

    protected function loadValuesImpl(array $cacheEntryNames) {
        $value = apc_fetch($cacheEntryNames);
        if ($value === FALSE) {
            $value = NULL;
        }

        return $value;
    }

    protected function storeValuesImpl(array $values, $expirationTime) {
        $errorCacheEntryNames = NULL;

        $adjustedExpirationTime = $expirationTime;
        if (!isset($adjustedExpirationTime)) {
            $adjustedExpirationTime = 0;
        }

        $storableValues = $deletableCacheEntryNames = NULL;
        foreach ($values as $cacheEntryName => $value) {
            if (isset($value)) {
                $storableValues[$cacheEntryName] = $value;
            }
            else {
                $deletableCacheEntryNames[] = $cacheEntryName;
            }
        }

        if (isset($deletableCacheEntryNames)) {
            foreach ($deletableCacheEntryNames as $deletableCacheEntryName) {
                $result = apc_delete($deletableCacheEntryName);
                if ($result === FALSE) {
                    $errorCacheEntryNames[] = $deletableCacheEntryName;
                }
            }
        }

        if (isset($storableValues)) {
            $unused = FALSE;
            $result = apc_store($storableValues, $unused, $adjustedExpirationTime);
            if ($result === FALSE) {
                ArrayHelper::appendValue($errorCacheEntryNames, array_keys($storableValues));
            }
        }

        return $errorCacheEntryNames;
    }

    protected function flushImpl($cacheSubsetName) {
        if (isset($cacheSubsetName)) {
            $cacheInfo = apc_cache_info('user');
            if ($cacheInfo === FALSE) {
                return  FALSE;
            }

            $result = TRUE;
            if (isset($cacheInfo['cache_list'])) {
                foreach ($cacheInfo['cache_list'] as $entryInfo) {
                    if (!isset($entryInfo['info'])) {
                        continue;
                    }
                    $cacheEntryName = $entryInfo['info'];

                    if ($this->inSubset($cacheSubsetName, $cacheEntryName)) {
                        $result = apc_delete($cacheEntryName) && $result;
                    }
                }
            }

            return $result;
        }
        else {
            return apc_clear_cache('user');
        }
    }
}
