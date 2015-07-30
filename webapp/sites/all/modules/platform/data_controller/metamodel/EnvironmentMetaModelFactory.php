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


class EnvironmentMetaModelFactory extends AbstractMetaModelFactory {

    /**
     * @static
     * @return EnvironmentMetaModelFactory
     */
    public static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new EnvironmentMetaModelFactory();
        }

        return $instance;
    }

    protected function getMetaModelHookName() {
        return 'dp_metamodel_environment_loader';
    }

    protected function initiateMetaModel() {
        return new EnvironmentMetaModel();
    }

    protected function initializeCache($expirationTimePolicyName) {
        return new __EnvironmentMetaModelFactory_CacheFactoryProxy($this, $expirationTimePolicyName);
    }

    /**
     * @return EnvironmentMetaModel
     */
    public function getMetaModel() {
        return parent::getMetaModel();
    }
}

class __EnvironmentMetaModelFactory_CacheFactoryProxy extends AbstractCacheFactoryProxy {

    protected function getCacheHandler() {
        // we cannot use shared cache to store environment meta model for two reasons:
        //   - it could contain sensitive information such as password
        //   - Catch 22: we need environment meta model already in memory to access configuration of external cache
        return CacheFactory::getInstance()->getLocalCacheHandler($this->cacheName);
    }
}
