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


class EnvironmentMetaModel extends AbstractMetaModel {

    /**
     * @var DataSourceMetaData[]
     */
    public $datasources = array();

    public function __clone() {
        parent::__clone();

        $this->datasources = ArrayHelper::copy($this->datasources);
    }

    protected function finalize() {
        parent::finalize();

        $this->finalizeDataSources($this->datasources);
    }

    protected function validate() {
        parent::validate();

        $this->validateDataSources($this->datasources);
    }

    public function findDataSource($datasourceName) {
        return isset($this->datasources[$datasourceName])
            ? $this->datasources[$datasourceName]
            : NULL;
    }

    /**
     * @param $datasourceName
     * @return DataSourceMetaData
     */
    public function getDataSource($datasourceName) {
        $datasource = $this->findDataSource($datasourceName);
        if (!isset($datasource)) {
            $this->errorDataSourceNotFound($datasourceName);
        }

        return $datasource;
    }

    public function getDataSources() {
        return $this->datasources;
    }

    protected function finalizeDataSources(array &$datasources) {
        foreach ($datasources as $datasource) {
            $datasource->finalize();
        }
    }

    protected function validateDataSource(DataSourceMetaData $datasource) {}

    protected function validateDataSources(array &$datasources) {
        foreach ($datasources as $datasource) {
            $this->validateDataSource($datasource);
        }
    }

    public function registerDataSource(DataSourceMetaData $datasource) {
        $this->checkAssemblingStarted();

        if (!isset($datasource->name)) {
            throw new IllegalArgumentException(t('DataSource name has not been defined'));
        }

        $datasourceName = $datasource->name;

        if (isset($this->datasources[$datasourceName])) {
            throw new IllegalArgumentException(t(
            	"DataSource with name '@datasourceName' has already been defined",
            	array('@datasourceName' => $datasource->publicName)));
        }

        $this->datasources[$datasourceName] = $datasource;
    }

    public function unregisterDataSource($datasourceName) {
        $this->checkAssemblingStarted();

        if (!isset($this->datasources[$datasourceName])) {
            $this->errorDataSourceNotFound($datasourceName);
        }

        $datasource = $this->datasources[$datasourceName];

        unset($this->datasources[$datasourceName]);

        return $datasource;
    }

    protected function errorDataSourceNotFound($datasourceName) {
        throw new IllegalArgumentException(t('Could not find %datasourceName data source definition', array('%datasourceName' => $datasourceName)));
    }
}