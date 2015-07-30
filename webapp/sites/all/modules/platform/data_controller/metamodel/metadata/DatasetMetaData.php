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


class DatasetMetaData extends RecordMetaData {

    public $aliases = NULL;

    public $datasourceName = NULL;

    public $sourceType = NULL;
    public $source = NULL;
    public $configuration = NULL;

    public $shared = NULL;

    protected function getEntityName() {
        return t('Dataset');
    }

    public function isAliasMatched($alias) {
        return isset($this->aliases) && in_array($alias, $this->aliases);
    }

    public function initializeFrom($sourceDataset) {
        parent::initializeFrom($sourceDataset);

        $sourceSource = ObjectHelper::getPropertyValue($sourceDataset, 'source');
        if (isset($sourceSource)) {
            $this->initializeSourceFrom($sourceSource);
        }

        $sourceConfiguration = ObjectHelper::getPropertyValue($sourceDataset, 'configuration');
        if (isset($sourceConfiguration)) {
            $this->initializeConfigurationFrom($sourceConfiguration);
        }

        $sourceAliases = ObjectHelper::getPropertyValue($sourceDataset, 'aliases');
        if (isset($sourceAliases)) {
            $this->initializeAliasesFrom($sourceAliases);
        }
    }

    public function initializeSourceFrom($sourceSource, $replace = FALSE) {
        if ($replace) {
            $this->source = NULL;
        }

        if (isset($sourceSource)) {
            ObjectHelper::mergeWith($this->source, $sourceSource, TRUE);
        }
    }

    public function findColumn($columnName) {
        $column = parent::findColumn($columnName);
        if (isset($column)) {
            return $column;
        }

        foreach ($this->columns as $c) {
            $column = $c->findBranch($columnName);
            if (isset($column)) {
                return $column;
            }
        }

        return NULL;
    }

    public function getConfigurationProperty($name, $required = FALSE) {
        if (!isset($this->configuration->$name)) {
            if ($required) {
                throw new IllegalStateException(t(
                    'Unsupported dataset configuration property: %parameterName',
                    array('%parameterName' => $name)));
            }

            return NULL;
        }

        return $this->configuration->$name;
    }

    public function setConfigurationProperty($name, $value) {
        if (!isset($this->configuration)) {
            $this->configuration = new stdClass();
        }

        $this->configuration->$name = $value;
    }

    protected function initializeConfigurationFrom($sourceConfiguration, $replace = FALSE) {
        if ($replace) {
            $this->configuration = NULL;
        }

        if (isset($sourceConfiguration)) {
            ObjectHelper::mergeWith($this->configuration, $sourceConfiguration, TRUE);
        }
    }

    public function initializeAliasesFrom($sourceAliases, $replace = FALSE) {
        if ($replace) {
            $this->aliases = NULL;
        }

        if (isset($sourceAliases)) {
            ObjectHelper::mergeWith($this->aliases, $sourceAliases, TRUE);
        }
    }

    public function isShared() {
        return isset($this->shared) ? $this->shared : FALSE;
    }
}
