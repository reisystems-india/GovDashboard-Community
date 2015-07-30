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


abstract class AbstractCacheFactoryProxy extends AbstractObject {

    protected $cacheName = NULL;
    protected $expirationTimePolicyName = NULL;
    protected $expirationTime = NULL;

    public function __construct($owner, $expirationTimePolicyName = NULL) {
        parent::__construct();

        $this->cacheName = $this->prepareCacheName($owner);
        $this->expirationTimePolicyName = $expirationTimePolicyName;
    }

    protected function prepareCacheName($owner) {
        return get_class($owner);
    }

    protected function getExpirationTime() {
        if (!isset($this->expirationTime)) {
            // preparing value for cache entry expiration time
            $cacheConfig = Environment::getInstance()->getConfigurationSection('Cache');
            $cacheExpirationConfig = isset($cacheConfig['Entry Expiration']) ? $cacheConfig['Entry Expiration'] : NULL;
            // at first trying to use provided policy
            if (isset($this->expirationTimePolicyName) && isset($cacheExpirationConfig[$this->expirationTimePolicyName])) {
                $this->expirationTime = $cacheExpirationConfig[$this->expirationTimePolicyName];
            }
            // falling back to default policy
            if (!isset($this->expirationTime) && isset($cacheExpirationConfig['default'])) {
                $this->expirationTime = $cacheExpirationConfig['default'];
            }

            if (!isset($this->expirationTime)) {
                $this->expirationTime = FALSE;
            }
        }

        return ($this->expirationTime === FALSE) ? NULL : $this->expirationTime;
    }

    protected function getCacheOptions() {
        return NULL;
    }

    abstract protected function getCacheHandler();

    public function getCachedEntry($entryName) {
        return $this->getCacheHandler()->getValue($entryName, $this->getCacheOptions());
    }

    public function getCachedEntries(array $entryNames) {
        return $this->getCacheHandler()->getValues($entryNames, $this->getCacheOptions());
    }

    public function cacheEntry($entryName, $entry = NULL) {
        $this->getCacheHandler()->setValue($entryName, $entry, $this->getExpirationTime(), $this->getCacheOptions());
    }

    public function cacheEntries(array $namedEntries) {
        $this->getCacheHandler()->setValues($namedEntries, $this->getExpirationTime(), $this->getCacheOptions());
    }

    public function expireCacheEntry($entryName) {
        $this->cacheEntry($entryName, NULL);
    }

    public function expireCacheEntries(array $entryNames) {
        $namedEntries = NULL;
        foreach ($entryNames as $entryName) {
            $namedEntries[$entryName] = NULL;
        }

        $this->cacheEntries($namedEntries);
    }
}
