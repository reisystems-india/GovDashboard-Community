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


class MemcacheHandler extends AbstractSharedCacheHandler {

    const CACHE__TYPE = 'memcache';

    public static $DEFAULT__COMPRESSION_THRESHOLD = 10240; // bytes
    public static $DEFAULT__COMPRESSION_SAVINGS_MIN = 0.3; // 30% savings

    /**
     * @var Memcache | null
     */
    private $memcache = NULL;

    public function getCacheType() {
        return self::CACHE__TYPE;
    }

    protected function initialize($prefix, DataSourceMetaData $datasource = NULL) {
        $result = TRUE;

        if (class_exists('Memcache')) {
            $this->memcache = new Memcache();

            $successfulRegistrationCount = $unsuccessfulRegistrationCount = 0;

            // adding servers
            if (isset($datasource->host)) {
                $serverResult = $this->registerServer($datasource->host, $datasource->port);
                if ($serverResult) {
                    $successfulRegistrationCount++;
                }
                else {
                    $unsuccessfulRegistrationCount++;
                }
            }
            if (isset($datasource->servers)) {
                foreach ($datasource->servers as $server) {
                    $serverResult = $this->registerServer($server->host, $server->port);
                    if ($serverResult) {
                        $successfulRegistrationCount++;
                    }
                    else {
                        $unsuccessfulRegistrationCount++;
                    }
                }
            }

            if ($successfulRegistrationCount == 0) {
                $this->memcache = NULL;
            }
            else {
                $this->memcache->setCompressThreshold(self::$DEFAULT__COMPRESSION_THRESHOLD, self::$DEFAULT__COMPRESSION_SAVINGS_MIN);
            }

            if ($unsuccessfulRegistrationCount > 0) {
                $result = FALSE;
            }
        }

        return $result;
    }

    protected function registerServer($host, $port) {
        $result = $this->memcache->addServer($host, $port);
        if (!$result) {
            LogHelper::log_error(t(
                '[@cacheType] Could not add server (@host:@port)',
                array(
                    '@cacheType' => self::CACHE__TYPE,
                    '@host' => $host,
                    '@port' => $port)));
        }

        return $result;
    }

    public function __destruct() {
        if (isset($this->memcache)) {
            $this->memcache->close();
            $this->memcache = NULL;
        }
        parent::__destruct();
    }

    public function isAccessible() {
        return parent::isAccessible() && isset($this->memcache);
    }

    protected function isEntryPresentImpl($cacheEntryName) {
        $value = $this->memcache->get($cacheEntryName);

        return ($value === FALSE) ? FALSE : isset($value);
    }

    protected function loadValueImpl($cacheEntryName) {
        $value = $this->memcache->get($cacheEntryName);
        if ($value === FALSE) {
            $value = NULL;
        }

        return $value;
    }

    protected function loadValuesImpl(array $cacheEntryNames) {
        $values = $this->memcache->get($cacheEntryNames);
        if ($values === FALSE) {
            $values = NULL;
        }

        return $values;
    }

    protected function storeValuesImpl(array $values, $expirationTime) {
        $errorCacheEntryNames = NULL;

        $adjustedExpirationTime = $expirationTime;
        if (!isset($adjustedExpirationTime)) {
            $adjustedExpirationTime = 0;
        }

        foreach ($values as $cacheEntryName => $value) {
            $result = isset($value)
                ? $this->memcache->set($cacheEntryName, $value, 0, $adjustedExpirationTime)
                : $this->memcache->delete($cacheEntryName);
            if ($result === FALSE) {
                $errorCacheEntryNames[] = $cacheEntryName;
            }
        }

        return $errorCacheEntryNames;
    }

    protected function flushImpl($cacheSubsetName) {
        return $this->memcache->flush();
    }
}
