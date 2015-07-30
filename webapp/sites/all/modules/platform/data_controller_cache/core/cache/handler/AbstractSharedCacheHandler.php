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


abstract class AbstractSharedCacheHandler extends AbstractCacheHandler {

    public static $ENTRY_EXPIRATION_TIME__DEFAULT = 180; // seconds
    public static $ENTRY_EXPIRATION_TIME__UPDATE_IN_PROGRESS = 300; // seconds

    // expiration time for a lock
    public static $LOCK_EXPIRATION_TIME = 30;
    // number of 'item refreshes pre thread'. Once the limit is reached 'stale' data is returned
    public static $LOCK_LIMIT_PER_THREAD = 3;
    // total time in seconds current thread can wait for the lock to be released. If the lock is not released the thread proceeds on its own
    public static $LOCK_WAIT_TIME = 1;
    // number of times current thread tries to check if a lock is released
    public static $LOCK_CHECK_COUNT = 2;

    private static $currentThreadLockCount = 0;

    public function __construct($prefix, DataSourceMetaData $datasource = NULL) {
        LogHelper::log_notice(t('[@cacheType] Initializing PHP extension ...', array('@cacheType' => $this->getCacheType())));

        // taking into account possible datasource's nested name space
        $adjustedPrefix = isset($datasource->nestedNameSpace)
            ? (isset($prefix) ? NameSpaceHelper::addNameSpace($datasource->nestedNameSpace, $prefix) : $datasource->nestedNameSpace)
            : $prefix;
        parent::__construct($adjustedPrefix);

        if ($this->initialize($prefix, $datasource) !== FALSE) {
            $this->checkAccessibility(FALSE);
        }
    }

    // returns FALSE if initialization failed
    abstract protected function initialize($prefix, DataSourceMetaData $datasource = NULL);

    // *************************************************************************
    // Naming Convention
    // *************************************************************************

    protected function assembleCacheEntryName($name) {
        $cacheEntryName = parent::assembleCacheEntryName($name);

        // a cache storage might not support space in key name
        $adjustedCacheEntryName = isset($cacheEntryName) ? str_replace(' ', '_', $cacheEntryName) : NULL;

        return $adjustedCacheEntryName;
    }

    // *************************************************************************
    // Calculating function execution time
    // *************************************************************************

    public function isEntryPresent($name) {
        $timeStart = microtime(TRUE);

        $result = parent::isEntryPresent($name);

        LogHelper::log_info(t(
            "[@cacheType] Execution time for@successFlag checking of '@entryName' entry is !executionTime",
            array(
                '@cacheType' => $this->getCacheType(),
                '@entryName' => $this->assembleCacheEntryName($name),
                '!executionTime' => LogHelper::formatExecutionTime($timeStart),
                '@successFlag' => ($result ? '' : (' ' . t('UNSUCCESSFUL'))))));

        return $result;
    }

    public function getValue($name, array $options = NULL) {
        $timeStart = microtime(TRUE);

        $value = parent::getValue($name, $options);

        LogHelper::log_info(t(
            "[@cacheType] Execution time for@successFlag retrieving of '@entryName' entry is !executionTime",
            array(
                '@cacheType' => $this->getCacheType(),
                '@entryName' => $this->assembleCacheEntryName($name),
                '!executionTime' => LogHelper::formatExecutionTime($timeStart),
                '@successFlag' => (isset($value) ? '' : (' ' . t('UNSUCCESSFUL'))))));

        return $value;
    }

    public function getValues(array $names, array $options = NULL) {
        $timeStart = microtime(TRUE);

        $values = parent::getValues($names, $options);

        $nameCount = count($names);
        $loadedValueCount = count($values);

        LogHelper::log_debug(t(
            '[@cacheType] Requested entries: @entryNames',
            array(
                '@cacheType' => $this->getCacheType(),
                '@entryNames' => ArrayHelper::serialize(array_values($names), ', ', TRUE, FALSE))));
        LogHelper::log_debug(
            t('[@cacheType] Retrieved entries: @entryNames',
                array(
                    '@cacheType' => $this->getCacheType(),
                    '@entryNames' => (($nameCount == $loadedValueCount)
                        ? 'ALL'
                        : (isset($values) ? ArrayHelper::serialize(array_keys($values), ', ', TRUE, FALSE) : t('NONE'))))));
        LogHelper::log_info(t(
            '[@cacheType] Execution time for retrieving @entryCount entry(-ies) is !executionTime@successFlag',
            array(
                '@cacheType' => $this->getCacheType(),
                '@entryCount' => $nameCount,
                '!executionTime' => LogHelper::formatExecutionTime($timeStart),
                '@successFlag' => (
                    isset($values)
                        ? (($nameCount == $loadedValueCount)
                            ? ''
                            : t(" (cache hit for ONLY @loadedValueCount entry(-ies) out of @nameCount)", array('@loadedValueCount' => $loadedValueCount, '@nameCount' => $nameCount)))
                        : (' (' . t('cache was NOT hit') . ')')))));

        return $values;
    }

    protected function calculateEntryExpirationTime($expirationTime) {
        return isset($expirationTime) ? $expirationTime : self::$ENTRY_EXPIRATION_TIME__DEFAULT;
    }

    public function setValue($name, $value, $expirationTime = NULL, array $options = NULL) {
        $adjustedExpirationTime = $this->calculateEntryExpirationTime($expirationTime);

        $timeStart = microtime(TRUE);

        $result = parent::setValue($name, $value, $adjustedExpirationTime, $options);

        LogHelper::log_info(t(
            "[@cacheType] Execution time for@successFlag storing of '@entryName' entry is !executionTime",
            array(
                '@cacheType' => $this->getCacheType(),
                '@entryName' => $this->assembleCacheEntryName($name),
                '!executionTime' => LogHelper::formatExecutionTime($timeStart),
                '@successFlag' => (($result === FALSE) ? (' ' . t('UNSUCCESSFUL')) : ''))));

        return $result;
    }

    public function setValues(array $values, $expirationTime = NULL, array $options = NULL) {
        $adjustedExpirationTime = $this->calculateEntryExpirationTime($expirationTime);

        $timeStart = microtime(TRUE);

        $errorEntryNames = parent::setValues($values, $adjustedExpirationTime, $options);

        $entryCount = count($values);
        $errorEntryCount = count($errorEntryNames);
        $successfulEntryCount = $entryCount - $errorEntryCount;

        LogHelper::log_info(t(
            "[@cacheType] Execution time for@successFlag storing of @entryCount entry(-ies) is !executionTime",
            array(
                '@cacheType' => $this->getCacheType(),
                '@entryCount' => (
                ($errorEntryCount == 0)
                    ? $entryCount // no error at all
                    : (($successfulEntryCount == 0)
                    ? $entryCount // all errors
                    : ($successfulEntryCount . ' ' . t('out of') . ' ' . $entryCount))), // some errors but also some success
                '!executionTime' => LogHelper::formatExecutionTime($timeStart),
                '@successFlag' => (
                ($errorEntryCount == 0) ? '' : (' ' . (($successfulEntryCount == 0) ? t('UNSUCCESSFUL') : t('PARTIALLY SUCCESSFUL')))))));

        return $errorEntryNames;
    }

    public function flush($subsetName = NULL) {
        $timeStart = microtime(TRUE);

        $result = parent::flush($subsetName);

        LogHelper::log_info(t(
            "[@cacheType] Execution time for@successFlag cache flush time is !executionTime",
            array(
                '@cacheType' => $this->getCacheType(),
                '!executionTime' => LogHelper::formatExecutionTime($timeStart),
                '@successFlag' => t(($result === FALSE) ? (' ' . t('UNSUCCESSFUL')) : ''))));

        return $result;
    }

    // *************************************************************************
    // wrapping values in envelope, support locking
    // *************************************************************************

    abstract protected function loadValuesImpl(array $cacheEntryNames);
    abstract protected function storeValuesImpl(array $values, $expirationTime);

    protected function assembleCacheLockEntryName($cacheEntryName) {
        return $cacheEntryName . '(LOCK)';
    }

    protected function openEnvelope($cacheEntryName, $envelope, array $options = NULL) {
        if (isset($envelope)) {
            $sourceDataAsOfDateTime = $this->getSourceDataAsOfDateTime($options);
            if ($envelope->isEnvelopeStale($sourceDataAsOfDateTime)) {
                // data source was refreshed after the envelope was created
                LogHelper::log_debug(t(
                    'Forced envelope refresh for the cache entry name: @cacheEntryName',
                    array('@cacheEntryName' => $cacheEntryName)));
                $envelope = NULL;
            }
        }

        // found unexpired data
        if (isset($envelope) && !$envelope->isDataStale()) {
            return $envelope->data;
        }

        $cacheLockEntryName = $this->assembleCacheLockEntryName($cacheEntryName);
        $isLockPresent = $this->isEntryPresentImpl($cacheLockEntryName);

        if ($isLockPresent) {
            if (isset($envelope)) {
                // found expired data which is in process of refreshing - returning stale data
                LogHelper::log_notice(t(
                    'Using stale data for the cache entry name: @cacheEntryName',
                    array('@cacheEntryName' => $cacheEntryName)));
                return $envelope->data;
            }
            else {
                // there is no data, but someone started to generate it ... waiting ... for limited time
                $lockWaitTime = 1000000 * self::$LOCK_WAIT_TIME / self::$LOCK_CHECK_COUNT;
                // going into sleep mode and hope the lock is released
                for ($i = 0; $i < self::$LOCK_CHECK_COUNT; $i++) {
                    usleep($lockWaitTime);

                    $envelopes = $this->loadValuesImpl(array($cacheEntryName, $cacheLockEntryName));

                    // checking if value present now
                    if (isset($envelopes[$cacheEntryName])) {
                        return $envelopes[$cacheEntryName]->data;
                    }

                    // checking if the lock is still present
                    if (isset($envelopes[$cacheLockEntryName])) {
                        continue;
                    }

                    // the lock disappeared. There is nothing can be done now
                    break;
                }
            }
        }
        else {
            if (isset($envelope)) {
                if (self::$currentThreadLockCount >= self::$LOCK_LIMIT_PER_THREAD) {
                    // this thread done enough. From now on returning stale data for this thread
                    LogHelper::log_notice(t(
                        'Using stale data for the cache entry name (lock limit reached): @cacheEntryName',
                        array('@cacheEntryName' => $cacheEntryName)));
                    return $envelope->data;
                }
            }

            // preparing lock
            $lock = new CacheEntryLock();
            // calculating lock expiration time
            $lockExpirationTime = self::$LOCK_EXPIRATION_TIME;
            if ($lockExpirationTime < self::$LOCK_WAIT_TIME) {
                $lockExpirationTime = self::$LOCK_WAIT_TIME;
            }
            // setting the lock and ignoring if the operation was successful or not
            $this->storeValuesImpl(
                array($cacheLockEntryName => new CacheEntryEnvelope($lock)),
                $lockExpirationTime);

            self::$currentThreadLockCount++;
        }

        return NULL;
    }

    protected function loadValue($cacheEntryName, array $options = NULL) {
        $envelope = $this->loadValueImpl($cacheEntryName);

        return $this->openEnvelope($cacheEntryName, $envelope, $options);
    }

    protected function loadValues(array $cacheEntryNames, array $options = NULL) {
        $envelopes = $this->loadValuesImpl($cacheEntryNames);

        $result = NULL;
        foreach ($cacheEntryNames as $cacheEntryName) {
            $envelope = isset($envelopes[$cacheEntryName]) ? $envelopes[$cacheEntryName] : NULL;

            $value = $this->openEnvelope($cacheEntryName, $envelope, $options);
            if (isset($value)) {
                $result[$cacheEntryName] = $value;
            }
        }

        return $result;
    }

    protected function calculateEnvelopeEntryExpirationTime($expirationTime, array $options = NULL) {
        $isSourceDataUpdateInProgress = $this->isSourceDataUpdateInProgress($options);

        return $isSourceDataUpdateInProgress
            ? NULL
            : (isset($expirationTime) ? round($expirationTime / 2.0) : NULL);
    }

    protected function calculateEnvelopeExpirationTime($expirationTime, array $options = NULL) {
        $adjustedExpirationTime = round($expirationTime);

        $isSourceDataUpdateInProgress = $this->isSourceDataUpdateInProgress($options);

        if ($isSourceDataUpdateInProgress) {
            if ($adjustedExpirationTime > self::$ENTRY_EXPIRATION_TIME__UPDATE_IN_PROGRESS) {
                $adjustedExpirationTime = self::$ENTRY_EXPIRATION_TIME__UPDATE_IN_PROGRESS;
            }
        }

        return $adjustedExpirationTime;
    }

    protected function storeValue($cacheEntryName, $value, $expirationTime, array $options = NULL) {
        // wrapping value into an envelope
        $envelope = NULL;
        if (isset($value)) {
            $entryExpirationTime = $this->calculateEnvelopeEntryExpirationTime($expirationTime, $options);
            $envelope = new CacheEntryEnvelope($value, $entryExpirationTime);
        }

        $cacheLockEntryName = $this->assembleCacheLockEntryName($cacheEntryName);

        $entries = array(
            $cacheEntryName => $envelope,
            // removing possible lock for the value
            $cacheLockEntryName => NULL);

        $result = $this->storeValuesImpl($entries, $this->calculateEnvelopeExpirationTime($expirationTime, $options));

        return isset($result) ? (!in_array($cacheEntryName, $result)) : TRUE;
    }

    protected function storeValues(array $values, $expirationTime, array $options = NULL) {
        $entries = $entryExpirationTime = NULL;
        foreach ($values as $cacheEntryName => $value) {
            // wrapping the value into an envelope
            $envelope = NULL;
            if (isset($value)) {
                if (!isset($entryExpirationTime)) {
                    $entryExpirationTime = $this->calculateEnvelopeEntryExpirationTime($expirationTime, $options);
                }
                $envelope = new CacheEntryEnvelope($value, $entryExpirationTime);
            }
            $entries[$cacheEntryName] = $envelope;

            // removing possible lock for the value
            $cacheLockEntryName = $this->assembleCacheLockEntryName($cacheEntryName);
            $entries[$cacheLockEntryName] = NULL;
        }

        $result = $this->storeValuesImpl($entries, $this->calculateEnvelopeExpirationTime($expirationTime, $options));

        $errorEntryNames = NULL;
        if (isset($result)) {
            foreach ($result as $cacheEntryName) {
                if (isset($values[$cacheEntryName])) {
                    $errorEntryNames[] = $cacheEntryName;
                }
            }
        }

        return $errorEntryNames;
    }
}

class CacheEntryEnvelope extends AbstractObject {

    public $envelopeDateTime = NULL;
    public $expirationDT = NULL;
    public $data = NULL;

    public function __construct($data, $expirationTime = NULL) {
        parent::__construct();

        $this->data = $data;
        $this->setEnvelopeDateTime();
        $this->setExpirationDateTime($expirationTime);
    }

    public function setEnvelopeDateTime() {
        $dt = new DateTime();

        $this->envelopeDateTime = $dt->format(DateTimeDataTypeHandler::$FORMAT_STORAGE);
    }

    public function setExpirationDateTime($expirationTime) {
        $this->expirationDateTime = NULL;

        if (isset($expirationTime)) {
            $dt = new DateTime();
            if (isset($expirationTime)) {
                $dt->add(new DateInterval("PT{$expirationTime}S"));
            }
            $this->expirationDateTime = $dt->format(DateTimeDataTypeHandler::$FORMAT_STORAGE);
        }
    }

    public function isEnvelopeStale(DateTime $sourceDataAsOfDateTime = NULL) {
        if (!isset($sourceDataAsOfDateTime)) {
            return FALSE;
        }

        $envelopeDateTime = DateTime::createFromFormat(DateTimeDataTypeHandler::$FORMAT_STORAGE, $this->envelopeDateTime);

        return $envelopeDateTime < $sourceDataAsOfDateTime;
    }
    
    public function isDataStale() {
        if (!isset($this->expirationDateTime)) {
            return FALSE;
        }

        $now = new DateTime();
        $expirationDT = DateTime::createFromFormat(DateTimeDataTypeHandler::$FORMAT_STORAGE, $this->expirationDateTime);

        return $expirationDT < $now;
    }
}

class CacheEntryLock extends AbstractObject {}
