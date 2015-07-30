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


abstract class AbstractMetaModelFactory extends AbstractFactory {

    private $globalModificationStarted = FALSE;

    /**
     * @var AbstractMetaModel
     */
    private $cachedMetaModel = NULL;
    private $cache = NULL;

    private $loaders = NULL;

    private $adhocFilters = NULL;

    protected function __construct() {
        parent::__construct();
        $this->cache = $this->initializeCache('metadata');

        $this->initiateLoaders();
    }

    // *****************************************************************************************************************************
    // * Filters for Meta Model Loaders
    // *****************************************************************************************************************************
    protected function getMetaModelFilterHookName() {
        return $this->getMetaModelHookName() . '_filter';
    }

    protected function processMetaModelFilters(array &$processedFilters = NULL, array $filters = NULL) {
        if (!isset($filters)) {
            return;
        }

        foreach ($filters as $className => $properties) {
            foreach ($properties as $propertyName => $values) {
                $uniqueValues = isset($processedFilters[$className][$propertyName])
                    ? $processedFilters[$className][$propertyName]
                    : NULL;
                if ($uniqueValues === FALSE) {
                    // this property should be ignored
                }
                else {
                    foreach ($values as $value) {
                        if (isset($value)) {
                            ArrayHelper::addUniqueValue($uniqueValues, $value);
                        }
                        else {
                            // if there is at least one NULL value we ignore the property completely
                            $uniqueValues = FALSE;
                            break;
                        }
                    }
                }

                $processedFilters[$className][$propertyName] = $uniqueValues;
            }
        }
    }

    protected function getMetaModelFilters() {
        $hookName = $this->getMetaModelFilterHookName();

        $preparedFilters = NULL;

        // processing preset filters
        $this->processMetaModelFilters($preparedFilters, module_invoke_all($hookName));
        // processing ad hoc filters
        if (isset($this->adhocFilters)) {
            $this->processMetaModelFilters($preparedFilters, $this->adhocFilters);
        }

        if (!isset($preparedFilters)) {
            return NULL;
        }

        // removing all filters which should be ignored
        $filters = NULL;
        foreach ($preparedFilters as $className => $properties) {
            foreach ($properties as $propertyName => $values) {
                if ($values === FALSE) {
                    continue;
                }

                $filters[$className][$propertyName] = $values;
            }
        }

        return $filters;
    }

    public function registerAdHocMetaModelFilter($className, $propertyName, $propertyValue) {
        $this->adhocFilters[$className][$propertyName][] = $propertyValue;

        $this->releaseFromLocalCache();
    }

    // *****************************************************************************************************************************
    // * Meta Model Loaders
    // *****************************************************************************************************************************
    abstract protected function getMetaModelHookName();

    protected function initiateLoaders() {
        $this->loaders = NULL;

        $hookName = $this->getMetaModelHookName();
        $loaderConfigurations = module_invoke_all($hookName);
        foreach ($loaderConfigurations as $loaderConfiguration) {
            $classname = $loaderConfiguration['classname'];
            $priority = isset($loaderConfiguration['priority']) ? $loaderConfiguration['priority'] : 0;

            $loader = new $classname();

            $this->loaders[$priority][] = $loader;
        }

        // sorting the list by priority
        if (isset($this->loaders)) {
            ksort($this->loaders);
        }
    }

    /**
     * @param $loaderName
     * @return MetaModelLoader
     */
    public function getLoader($loaderName) {
        if (isset($this->loaders)) {
            foreach ($this->loaders as $priority => $loaders) {
                foreach ($loaders as $loader) {
                    if ($loader->getName() === $loaderName) {
                        return $loader;
                    }
                }
            }
        }

        throw new IllegalArgumentException(t('Could not find %loaderName meta model loader', array('%loaderName' => $loaderName)));
    }

    // *****************************************************************************************************************************
    // * Loading Meta Model
    // *****************************************************************************************************************************
    abstract protected function initiateMetaModel();

    protected function loadMetaModel(AbstractMetaModel $metamodel) {
        $metaModelName = get_class($this);

        LogHelper::log_notice(t('Loading @metamodelName ...', array('@metamodelName' => $metaModelName)));

        $metamodelTimeStart = microtime(TRUE);
        $metamodelMemoryUsage = memory_get_usage();

        if (isset($this->loaders)) {
            // preparing each loader for load operation
            foreach ($this->loaders as $priority => $loaders) {
                foreach ($loaders as $loader) {
                    $loader->prepare($metamodel);
                }
            }

            $filters = $this->getMetaModelFilters();
            foreach ($this->loaders as $priority => $loaders) {
                foreach ($loaders as $loader) {
                    $loaderClassName = get_class($loader);

                    $loaderTimeStart = microtime(TRUE);
                    $loader->load($metamodel, $filters);
                    LogHelper::log_notice(t(
                        "'@loaderClassName' Meta Model Loader execution time: !executionTime",
                        array('@loaderClassName' => $loaderClassName, '!executionTime' => LogHelper::formatExecutionTime($loaderTimeStart))));
                }
            }

            // finalizing loading operation
            foreach ($this->loaders as $priority => $loaders) {
                foreach ($loaders as $loader) {
                    $loader->finalize($metamodel);
                }
            }
        }

        LogHelper::log_notice(t(
            '@metamodelName loading time: !loadingTime; Memory consumed: !memoryUsage',
            array(
                '@metamodelName' => $metaModelName,
                '!loadingTime' => LogHelper::formatExecutionTime($metamodelTimeStart),
                '!memoryUsage' => (memory_get_usage() - $metamodelMemoryUsage))));
    }

    // *****************************************************************************************************************************
    // * Caching Meta Model
    // *****************************************************************************************************************************
    abstract protected function initializeCache($expirationTimePolicyName);

    protected function prepareCacheEntryName() {
        $entryName = NULL;

        $filters = $this->getMetaModelFilters();
        if (isset($filters)) {
            $suffix = NULL;

            ksort($filters);
            foreach ($filters as $className => $properties) {
                if (isset($suffix)) {
                    $suffix .= ',';
                }
                $suffix .= $className . '{';

                $propertySuffix = NULL;

                ksort($properties);
                foreach ($properties as $propertyName => $filterValues) {
                    sort($filterValues);

                    if (isset($propertySuffix)) {
                        $propertySuffix .= ',';
                    }
                    $propertySuffix .= $propertyName . '=' . ArrayHelper::serialize($filterValues, ',', TRUE, FALSE);
                }

                $suffix .= $propertySuffix . '}';
            }

            $entryName = $suffix;
        }

        return $entryName;
    }

    /**
     * @return AbstractMetaModel|null
     */
    protected function loadCachedMetaModel() {
        $cacheEntryName = $this->prepareCacheEntryName();

        return $this->cache->getCachedEntry($cacheEntryName);
    }

    protected function cacheMetaModel(AbstractMetaModel $metamodel = NULL) {
        $cacheEntryName = $this->prepareCacheEntryName();

        $this->cache->cacheEntry($cacheEntryName, $metamodel);
    }

    public function releaseFromLocalCache() {
        $this->cachedMetaModel = NULL;
    }

    protected function releaseFromSharedCache() {
        $this->cacheMetaModel(NULL);
        $this->releaseFromLocalCache();
    }

    /**
     * @return AbstractMetaModel
     */
    public function getMetaModel() {
        // checking local cache first
        $metamodel = $this->cachedMetaModel;
        if (isset($metamodel)) {
            return $metamodel;
        }

        // checking external cache
        $metamodel = $this->loadCachedMetaModel();
        // assembling meta model (keep in mind that it is expensive operation)
        if (!isset($metamodel)) {
            $metamodel = $this->initiateMetaModel();
            $this->loadMetaModel($metamodel);
            $metamodel->markAsAssembled();

            // storing loaded meta model into external cache
            $this->cacheMetaModel($metamodel);
        }
        // synchronizing with started transaction
        if ($this->globalModificationStarted) {
            $metamodel->startAssembling();
        }

        // storing into local cache
        $this->cachedMetaModel = $metamodel;

        return $metamodel;
    }

    // *****************************************************************************************************************************
    // * Global Changes to Meta Model
    // *****************************************************************************************************************************
    public function startGlobalModification() {
        if ($this->globalModificationStarted) {
            throw new IllegalStateException(t('Meta Model modification has already been started'));
        }

        $this->globalModificationStarted = TRUE;

        if (isset($this->cachedMetaModel)) {
            $this->cachedMetaModel->startAssembling();
        }
    }

    public function finishGlobalModification($commit) {
        if (!$this->globalModificationStarted) {
            throw new IllegalStateException(t('Meta Model modification has not been started'));
        }

        $this->globalModificationStarted = FALSE;

        if (isset($this->cachedMetaModel)) {
            if ($commit) {
                $this->cachedMetaModel->markAsAssembled();

                // checking if someone updated the meta model while we were in global modification transaction
                $externalCachedMetaModel = $this->loadCachedMetaModel();
                if (isset($externalCachedMetaModel)) {
                    if ($externalCachedMetaModel->version == $this->cachedMetaModel->version) {
                        $this->cacheMetaModel($this->cachedMetaModel);
                    }
                    else {
                        // we need to remove data from external cache
                        // because different version of the meta model was changed and the cached meta model became obsolete
                        $this->releaseFromSharedCache();
                    }
                }
            }
            else {
                // rolling back whatever we tried to change without removing external cache
                $this->releaseFromLocalCache();
            }
        }
    }
}
