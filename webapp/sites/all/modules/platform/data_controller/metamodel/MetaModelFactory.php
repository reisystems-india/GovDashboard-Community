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


class MetaModelFactory extends AbstractMetaModelFactory {

    /**
     * @static
     * @return MetaModelFactory
     */
    public static function getInstance() {
        $instance = &drupal_static(__CLASS__ . '::' . __FUNCTION__);
        if (!isset($instance)) {
            $instance = new MetaModelFactory();
        }

        return $instance;
    }

    protected function getMetaModelHookName() {
        return 'dp_metamodel_loader';
    }

    protected function initiateMetaModel() {
        return new MetaModel();
    }

    protected function initializeCache($expirationTimePolicyName) {
        // Note: there is no need to store another copy in local cache. This object already have a copy of the meta model as a property
        return new SharedCacheFactoryProxy($this, $expirationTimePolicyName, FALSE);
    }

    /**
     * @return MetaModel
     */
    public function getMetaModel() {
        return parent::getMetaModel();
    }
}