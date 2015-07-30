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


class ProxyCacheHandler extends AbstractCacheHandler {

    private $proxiedHandler = NULL;
    private $localHandler = NULL;

    public function __construct($prefix, CacheHandler $proxiedHandler, $allowCopyInLocalCache) {
        parent::__construct($prefix);

        $this->proxiedHandler = $proxiedHandler;

        if ($allowCopyInLocalCache) {
            $this->localHandler = new InMemoryCacheHandler();
        }
    }

    public function getCacheType() {
        return $this->proxiedHandler->getCacheType() . '[Proxy]';
    }

    protected function isEntryPresentImpl($cacheEntryName) {
        $result = isset($this->localHandler) ? $this->localHandler->isEntryPresent($cacheEntryName) : FALSE;
        if ($result === FALSE) {
            $result = $this->proxiedHandler->isEntryPresent($cacheEntryName);
        }

        return $result;
    }

    protected function loadValue($cacheEntryName, array $options = NULL) {
        $value = isset($this->localHandler) ? $this->localHandler->getValue($cacheEntryName, $options) : NULL;

        // could not find in local cache
        if (!isset($value)) {
            // reading from proxied cache
            $value = $this->proxiedHandler->getValue($cacheEntryName, $options);

            // storing loaded value in internal cache for the future use
            if (isset($this->localHandler) && isset($value)) {
                $this->localHandler->setValue($cacheEntryName, $value, $options);
            }
        }

        return $value;
    }

    protected function loadValues(array $cacheEntryNames, array $options = NULL) {
        $values = isset($this->localHandler) ? $this->localHandler->getValues($cacheEntryNames, $options) : NULL;

        $missingCacheEntryNames = NULL;
        foreach ($cacheEntryNames as $cacheEntryName) {
            if (!isset($values[$cacheEntryName])) {
                $missingCacheEntryNames[$cacheEntryName] = TRUE;
            }
        }

        if (isset($missingCacheEntryNames)) {
            // loading all missing values from proxied cache
            $missingValues = $this->proxiedHandler->getValues(array_keys($missingCacheEntryNames), $options);
            // processing loaded values
            if (isset($missingValues)) {
                foreach ($missingValues as $cacheEntryName => $value) {
                    if (!isset($missingCacheEntryNames[$cacheEntryName])) {
                        throw new IllegalStateException(t(
                            'Loaded value for unregistered cache entry name: %cacheEntryName',
                            array('%cacheEntryName' => $cacheEntryName)));
                    }
                    $values[$cacheEntryName] = $value;

                    // storing loaded value in internal cache for the future use
                    if (isset($this->localHandler)) {
                        $this->localHandler->setValue($cacheEntryName, $value, $options);
                    }
                }
            }
        }

        return $values;
    }

    protected function storeValue($cacheEntryName, $value, $expirationTime, array $options = NULL) {
        // storing into local cache
        if (isset($this->localHandler)) {
            $this->localHandler->setValue($cacheEntryName, $value, $expirationTime, $options);
        }

        // storing into proxied cache
        return $this->proxiedHandler->setValue($cacheEntryName, $value, $expirationTime, $options);
    }

    public function storeValues(array $values, $expirationTime, array $options = NULL) {
        // storing into local cache
        if (isset($this->localHandler)) {
            $this->localHandler->setValues($values, $expirationTime, $options);
        }

        return $this->proxiedHandler->setValues($values, $expirationTime, $options);
    }

    protected function flushImpl($cacheSubsetName) {
        $localResult = isset($this->localHandler) ? $this->localHandler->flush($cacheSubsetName) : TRUE;

        return $this->proxiedHandler->flush($cacheSubsetName) && $localResult;
    }
}
