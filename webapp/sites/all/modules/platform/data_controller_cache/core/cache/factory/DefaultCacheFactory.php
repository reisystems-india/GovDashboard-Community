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


class DefaultCacheFactory extends CacheFactory {

    private $handlerConfigurations = NULL;
    private $handlers = NULL;

    public function __construct() {
        parent::__construct();
        $this->handlerConfigurations = module_invoke_all('dp_cache');
    }

    protected function prepareHandlerClassName($type, $required) {
        $classname = isset($this->handlerConfigurations[$type]['classname']) ? $this->handlerConfigurations[$type]['classname'] : NULL;
        if (!isset($classname) && $required) {
            throw new IllegalArgumentException(t('Unsupported cache handler: %type', array('%type' => $type)));
        }

        return $classname;
    }

    protected function initializeSharedCacheHandler(DataSourceMetaData $cacheDataSource) {
        $handler = NULL;

        if (isset($cacheDataSource)) {
            $sharedCacheHandlerKey = get_class($this) . '(' . $cacheDataSource->type . ')';
            if (isset($this->handlers[$sharedCacheHandlerKey])) {
                $handler = $this->handlers[$sharedCacheHandlerKey];
            }

            if (!isset($handler)) {
                $classname = $this->prepareHandlerClassName($cacheDataSource->type, FALSE);

                if (isset($classname)) {
                    $handler = new $classname(NULL /* we use prefix on ProxyCacheHandler level */, $cacheDataSource);
                    if ($handler->isAccessible()) {
                        $this->handlers[$sharedCacheHandlerKey] = $handler;
                    }
                    else {
                        $handler = NULL;
                    }
                }
            }
        }

        return $handler;
    }

    protected function isCacheDataSource(DataSourceMetaData $datasource) {
        return isset($datasource->category) && ($datasource->category == self::$DATASOURCE__CATEGORY);
    }

    protected function prepareSharedCacheHandler($cacheDataSourceName) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        $handler = NULL;
        if (isset($cacheDataSourceName)) {
            $cacheDataSource = $environment_metamodel->findDataSource($cacheDataSourceName);
            $handler = $this->initializeSharedCacheHandler($cacheDataSource);
        }
        else {
            foreach ($environment_metamodel->datasources as $datasource) {
                if (!$this->isCacheDataSource($datasource)) {
                    continue;
                }

                $handler = $this->initializeSharedCacheHandler($datasource);
                // selecting first accessible cache
                if (isset($handler)) {
                    break;
                }
            }
        }

        return $handler;
    }

    protected function proxySharedCacheHandler(CacheHandler $handler, $prefix, $allowCopyInLocalCache) {
        $adjustedPrefix = '[' . strtoupper($_SERVER['SERVER_NAME']) . ']';
        if (isset($prefix)) {
            $adjustedPrefix .= $prefix;
        }

        return new ProxyCacheHandler($adjustedPrefix, $handler, $allowCopyInLocalCache);
    }

    public function getSharedCacheHandler($prefix, $allowCopyInLocalCache = FALSE, $cacheDataSourceName = NULL) {
        $handlerKey = isset($prefix) ? $prefix : (get_class($this) . '.shared');
        if (isset($cacheDataSourceName)) {
            $handlerKey = NameSpaceHelper::addNameSpace($handlerKey, NameSpaceHelper::addNameSpace('datasource', $cacheDataSourceName));
        }

        if (isset($this->handlers[$handlerKey])) {
            $handler = $this->handlers[$handlerKey];
        }
        else {
            $handler = $this->prepareSharedCacheHandler($cacheDataSourceName);

            $handler = isset($handler)
                ? $this->proxySharedCacheHandler($handler, $prefix, $allowCopyInLocalCache)
                : $this->getLocalCacheHandler($prefix);

            $this->handlers[$handlerKey] = $handler;
        }

        return $handler;
    }

    protected function initializeLocalCache($prefix) {
        $classname = $this->prepareHandlerClassName(InMemoryCacheHandler::CACHE__TYPE, TRUE);

        return new $classname($prefix);
    }

    public function getLocalCacheHandler($prefix) {
        $handlerKey = isset($prefix) ? $prefix : (get_class($this) . '.local');
        $handlerKey = NameSpaceHelper::addNameSpace($handlerKey, InMemoryCacheHandler::CACHE__TYPE);

        if (isset($this->handlers[$handlerKey])) {
            $handler = $this->handlers[$handlerKey];
        }
        else {
            $handler = $this->initializeLocalCache($prefix);
            $this->handlers[$handlerKey] = $handler;
        }

        return $handler;
    }

    public function flush($subsetName = NULL) {
        $environment_metamodel = data_controller_get_environment_metamodel();

        foreach ($environment_metamodel->datasources as $datasource) {
            if (!$this->isCacheDataSource($datasource)) {
                continue;
            }

            $handler = $this->initializeSharedCacheHandler($datasource);
            if (!isset($handler)) {
                continue;
            }

            $handler = $this->proxySharedCacheHandler($handler, $subsetName, FALSE);
            $handler->flush();
        }

        $this->handlers = NULL;
    }
}
