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


/*
 * PECL memcached >= 2.0.0
 *      - use quit() in __destruct
 *      - use deleteMulti in storeValues for NULL values
 */

class MemcachedHandler extends AbstractSharedCacheHandler {

    const CACHE__TYPE = 'memcached';

    /**
     * @var Memcached | null
     */
    private $memcached = NULL;

    public function getCacheType() {
        return self::CACHE__TYPE;
    }

    protected function initialize($prefix, DataSourceMetaData $datasource = NULL) {
        $result = TRUE;

        if (class_exists('Memcached')) {
            $this->memcached = new Memcached();

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
                $this->memcached = NULL;
            }

            if ($unsuccessfulRegistrationCount > 0) {
                $result = FALSE;
            }
        }

        return $result;
    }

    protected function registerServer($host, $port) {
        $result = $this->memcached->addServer($host, $port);
        if (!$result) {
            LogHelper::log_error(t(
                '[@cacheType] Could not add server (@host:@port): @message',
                array(
                    '@cacheType' => self::CACHE__TYPE,
                    '@host' => $host,
                    '@port' => $port,
                    '@message' => $this->memcached->getResultMessage())));
        }

        return $result;
    }

    public function __destruct() {
        if (isset($this->memcached)) {
            $this->memcached = NULL;
        }
        parent::__destruct();
    }

    public function isAccessible() {
        return parent::isAccessible() && isset($this->memcached);
    }

    protected function isEntryPresentImpl($cacheEntryName) {
        $value = $this->memcached->get($cacheEntryName);

        return ($value === FALSE) ? FALSE : isset($value);
    }

    protected function loadValueImpl($cacheEntryName) {
        $value = $this->memcached->get($cacheEntryName);

        if ($value === FALSE) {
            if ($this->memcached->getResultCode() != Memcached::RES_NOTFOUND) {
                LogHelper::log_error(t(
                    '[@cacheType] Could not get value (@cacheEntryName): @message',
                    array(
                        '@cacheType' => self::CACHE__TYPE,
                        '@cacheEntryName' => $cacheEntryName,
                        '@message' => $this->memcached->getResultMessage())));
            }
        }

        return $value;
    }

    protected function loadValuesImpl(array $cacheEntryNames) {
        $values = $this->memcached->getMulti($cacheEntryNames);

        if ($values === FALSE) {
            if ($this->memcached->getResultCode() != Memcached::RES_NOTFOUND) {
                LogHelper::log_error(t(
                    '[@cacheType] Could not get values (@cacheEntryNames): @message',
                    array(
                        '@cacheType' => self::CACHE__TYPE,
                        '@cacheEntryNames' => implode(', ', $cacheEntryNames),
                        '@message' => $this->memcached->getResultMessage())));
            }
        }

        return $values;
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
                $result = $this->memcached->delete($deletableCacheEntryName);
                if (($result === FALSE) && ($this->memcached->getResultCode() != Memcached::RES_NOTFOUND)) {
                    $errorCacheEntryNames[] = $deletableCacheEntryName;

                    LogHelper::log_error(t(
                        '[@cacheType] Internal error during value deletion: @message',
                        array('@cacheType' => self::CACHE__TYPE, '@message' => $this->memcached->getResultMessage())));
                }
            }
        }

        if (isset($storableValues)) {
            $result = $this->memcached->setMulti($storableValues, $adjustedExpirationTime);
            if ($result === FALSE) {
                LogHelper::log_error(t(
                    '[@cacheType] Internal error during value storing: @message',
                    array('@cacheType' => self::CACHE__TYPE, '@message' => $this->memcached->getResultMessage())));
                ArrayHelper::appendValue($errorCacheEntryNames, array_keys($storableValues));
            }
        }

        return $errorCacheEntryNames;
    }

    protected function flushImpl($cacheSubsetName) {
        if (isset($cacheSubsetName)) {
            $cacheEntryNames = $this->memcached->getAllKeys();
            if ($cacheEntryNames === FALSE) {
                return  FALSE;
            }

            $result = TRUE;
            foreach ($cacheEntryNames as $cacheEntryName) {
                if ($this->inSubset($cacheSubsetName, $cacheEntryName)) {
                    $result = $this->memcached->delete($cacheEntryName) && $result;
                }
            }

            return $result;

        }
        else {
            return $this->memcached->flush();
        }
    }
}
