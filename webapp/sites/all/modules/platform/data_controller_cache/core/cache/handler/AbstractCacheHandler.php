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


abstract class AbstractCacheHandler extends AbstractObject implements CacheHandler {

    protected $prefix = NULL;

    public function __construct($prefix = NULL) {
        parent::__construct();
        $this->prefix = $prefix;
    }

    // *************************************************************************
    // Cache Handler accessibility
    // *************************************************************************
    public function isAccessible() {
        return TRUE;
    }

    protected function checkAccessibility($raiseError) {
        if (!$this->isAccessible()) {
            if ($raiseError) {
                $message = t('%cacheType cache cannot be used at this time', array('%cacheType' => $this->getCacheType()));
                throw new IllegalStateException($message);
            }
            else {
                $message = t("'@cacheType' cache cannot be used at this time", array('@cacheType' => $this->getCacheType()));
                LogHelper::log_error($message);
            }
        }
    }

    // *************************************************************************
    // Naming Convention
    // *************************************************************************
    protected function assembleCacheEntryName($name) {
        $cacheEntryName = $name;
        if (isset($this->prefix)) {
            $cacheEntryName = isset($name) ? NameSpaceHelper::addNameSpace($this->prefix, $name) : $this->prefix;
        }

        return $cacheEntryName;
    }

    // *************************************************************************
    // Cache Options
    // *************************************************************************
    protected function isSourceDataUpdateInProgress(array $options = NULL) {
        return isset($options[CacheHandler::OPTION__DATA_UPDATE_IN_PROGRESS])
            ? $options[CacheHandler::OPTION__DATA_UPDATE_IN_PROGRESS]
            : FALSE;
    }

    protected function getSourceDataAsOfDateTime(array $options = NULL) {
        return isset($options[CacheHandler::OPTION__DATA_RESET_DATETIME])
            ? $options[CacheHandler::OPTION__DATA_RESET_DATETIME]
            : NULL;
    }

    // *************************************************************************
    // isEntryPresent()
    // *************************************************************************

    protected function inSubset($cacheSubsetName, $cacheEntryName) {
        $index = strpos($cacheEntryName, $cacheSubsetName);

        return ($index === 0);
    }

    // should return TRUE (present) or FALSE
    abstract protected function isEntryPresentImpl($cacheEntryName);

    public function isEntryPresent($name) {
        $this->checkAccessibility(TRUE);

        $cacheEntryName = $this->assembleCacheEntryName($name);

        try {
            return $this->isEntryPresentImpl($cacheEntryName);
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        return FALSE;
    }

    // *************************************************************************
    // getValue() and getValues()
    // *************************************************************************

    // should return value or NULL
    abstract protected function loadValue($cacheEntryName, array $options = NULL);
    // should return list of values or NULL
    abstract protected function loadValues(array $cacheEntryNames, array $options = NULL);

    public function getValue($name, array $options = NULL) {
        $this->checkAccessibility(TRUE);

        $cacheEntryName = $this->assembleCacheEntryName($name);

        $value = NULL;
        try {
            $value = $this->loadValue($cacheEntryName, $options);
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        return $value;
    }

    public function getValues(array $names, array $options = NULL) {
        $this->checkAccessibility(TRUE);

        $cacheEntryNameMap = NULL;
        foreach ($names as $name) {
            $cacheEntryName = $this->assembleCacheEntryName($name);
            $cacheEntryNameMap[$cacheEntryName] = $name;
        }
        if (!isset($cacheEntryNameMap)) {
            return NULL;
        }

        $result = NULL;
        try {
            $cacheEntries = $this->loadValues(array_keys($cacheEntryNameMap), $options);

            foreach ($cacheEntryNameMap as $cacheEntryName => $name) {
                if (!isset($cacheEntries[$cacheEntryName])) {
                    continue;
                }

                $value = $cacheEntries[$cacheEntryName];

                $result[$name] = $value;

                unset($cacheEntries[$cacheEntryName]);
            }

            // checking for unknown returned entries which are still in the list (were not processed)
            if (isset($cacheEntries)) {
                $unrequestedCacheEntryNames = array_keys($cacheEntries);
                if (count($unrequestedCacheEntryNames) > 0) {
                    throw new IllegalStateException(t(
                        'Received unrequested data for the cache entry names: %cacheEntryName',
                        array('%cacheEntryName' => ArrayHelper::serialize($unrequestedCacheEntryNames, ', ', TRUE, FALSE))));
                }
            }
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        return $result;
    }

    // *************************************************************************
    // setValue() and setValues()
    // *************************************************************************

    // returns TRUE (success) or FALSE (error)
    abstract protected function storeValue($cacheEntryName, $value, $expirationTime, array $options = NULL);
    // returns NULL or names of entries which could not be stored
    abstract protected function storeValues(array $values, $expirationTime, array $options = NULL);

    protected function logUnsuccessfulStoringOccurrence($cacheEntryName) {
        LogHelper::log_error(t(
            "[@cacheType] Could not store cache entry: @cacheEntryName",
            array(
                '@cacheType' => $this->getCacheType(),
                '@cacheEntryName' => $cacheEntryName)));
    }

    public function setValue($name, $value, $expirationTime = NULL, array $options = NULL) {
        $this->checkAccessibility(TRUE);

        $cacheEntryName = $this->assembleCacheEntryName($name);

        $result = FALSE;
        try {
            $result = $this->storeValue($cacheEntryName, $value, $expirationTime, $options);
            if (!$result) {
                $this->logUnsuccessfulStoringOccurrence($cacheEntryName);
            }
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        return $result;
    }

    public function setValues(array $values, $expirationTime = NULL, array $options = NULL) {
        $this->checkAccessibility(TRUE);

        $cacheEntryNameMap = $cacheEntries = NULL;
        foreach ($values as $name => $value) {
            $cacheEntryName = $this->assembleCacheEntryName($name);

            $cacheEntryNameMap[$cacheEntryName] = $name;
            $cacheEntries[$cacheEntryName] = $value;
        }
        if (!isset($cacheEntries)) {
            return NULL;
        }

        $adjustedErrorEntryNames = NULL;
        try {
            $errorCacheEntryNames = $this->storeValues($cacheEntries, $expirationTime, $options);
            if (count($errorCacheEntryNames) > 0) {
                foreach ($errorCacheEntryNames as $errorCacheEntryName) {
                    if (!isset($cacheEntryNameMap[$errorCacheEntryName])) {
                        throw new IllegalStateException(t(
                            'Could not find name by the cache entry name: %cacheEntryName',
                            array('%cacheEntryName' => $errorCacheEntryName)));
                    }
                    $name = $cacheEntryNameMap[$errorCacheEntryName];

                    $this->logUnsuccessfulStoringOccurrence($errorCacheEntryName);

                    $adjustedErrorEntryNames[] = $name;
                }
            }
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        return $adjustedErrorEntryNames;
    }

    // *************************************************************************
    // flush()
    // *************************************************************************

    // returns TRUE (success) or FALSE (error)
    protected function flushImpl($cacheSubsetName) {
        throw new UnsupportedOperationException();
    }

    public function flush($subsetName = NULL) {
        $result = FALSE;
        try {
            $cacheSubsetName = $this->assembleCacheEntryName($subsetName);

            $result = $this->flushImpl($cacheSubsetName);
        }
        catch (Exception $e) {
            LogHelper::log_error($e);
        }

        return $result;
    }
}
