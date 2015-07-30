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


class SharedCacheFactoryProxy extends AbstractCacheFactoryProxy {

    protected $allowCopyInLocalCache = FALSE;

    public function __construct($owner, $expirationTimePolicyName = NULL, $allowCopyInLocalCache = FALSE) {
        parent::__construct($owner, $expirationTimePolicyName);
        $this->allowCopyInLocalCache = $allowCopyInLocalCache;
    }

    protected function getCacheHandler() {
        return CacheFactory::getInstance()->getSharedCacheHandler($this->cacheName, $this->allowCopyInLocalCache);
    }

    protected function getCacheOptions() {
        $cacheSynchronizationOptions = &drupal_static(__CLASS__ . '::cacheSynchronizationOptions');

        if (!isset($cacheSynchronizationOptions[$this->expirationTimePolicyName])) {
            $cacheKey = 'dp_cache_sync:' . $this->expirationTimePolicyName;
            $cache = CacheFactory::getInstance()->getSharedCacheHandler(get_class($this));

            $cacheSyncOptions = $cache->getValue($cacheKey);
            if (!isset($cacheSyncOptions)) {
                $isSourceDataUpdateInProgress = FALSE;
                $sourceDataAsOfDateTime = NULL;

                $syncOptions = module_invoke_all('dp_cache_sync', $this->expirationTimePolicyName);
                if (isset($syncOptions)) {
                    foreach ($syncOptions as $sourceDataOptions) {
                        if (!$isSourceDataUpdateInProgress && isset($sourceDataOptions[CacheHandler::OPTION__DATA_UPDATE_IN_PROGRESS])) {
                            $isSourceDataUpdateInProgress = $sourceDataOptions[CacheHandler::OPTION__DATA_UPDATE_IN_PROGRESS];
                        }
                        if (isset($sourceDataOptions[CacheHandler::OPTION__DATA_RESET_DATETIME])) {
                            $dt = $sourceDataOptions[CacheHandler::OPTION__DATA_RESET_DATETIME];
                            if (!isset($sourceDataAsOfDateTime) || ($sourceDataAsOfDateTime < $dt)) {
                                $sourceDataAsOfDateTime = $dt;
                            }
                        }
                    }
                }

                if ($isSourceDataUpdateInProgress) {
                    $cacheSyncOptions[CacheHandler::OPTION__DATA_UPDATE_IN_PROGRESS] = $isSourceDataUpdateInProgress;
                }
                if (isset($sourceDataAsOfDateTime)) {
                    $cacheSyncOptions[CacheHandler::OPTION__DATA_RESET_DATETIME] = $sourceDataAsOfDateTime;
                }

                // to prevent repeatable hook invocations
                if (!isset($cacheSyncOptions)) {
                    $cacheSyncOptions = FALSE;
                }

                $cache->setValue($cacheKey, $cacheSyncOptions);
            }
            $cacheSynchronizationOptions[$this->expirationTimePolicyName] = $cacheSyncOptions;
        }

        return ($cacheSynchronizationOptions[$this->expirationTimePolicyName] === FALSE) ? NULL : $cacheSynchronizationOptions[$this->expirationTimePolicyName];
    }
}
