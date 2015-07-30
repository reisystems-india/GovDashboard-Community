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

class DefaultDatasetSourceTypeFactory extends DatasetSourceTypeFactory {

    private $handlerConfigurations = NULL;
    private $handlers = NULL;

    public function __construct() {
        parent::__construct();

        $this->handlerConfigurations = module_invoke_all('dp_dataset_source_type');
    }

    protected function getHandlerClassName($sourceType) {
        $classname = isset($this->handlerConfigurations[$sourceType]['classname']) ? $this->handlerConfigurations[$sourceType]['classname'] : NULL;
        if (!isset($classname)) {
            throw new IllegalArgumentException(t('Unsupported dataset source type handler: %type', array('%type' => $sourceType)));
        }

        return $classname;
    }

    public function detectSourceType(DatasetMetaData $dataset) {
        if (isset($dataset->sourceType)) {
            return $dataset->sourceType;
        }

        if (isset($dataset->source)) {
            $source = trim($dataset->source);
            $isTableName = strpos($source, ' ') === FALSE;

            return $isTableName ? TableDatasetSourceTypeHandler::SOURCE_TYPE : SQLDatasetSourceTypeHandler::SOURCE_TYPE;
        }

        LogHelper::log_debug($dataset);
        throw new IllegalArgumentException(t(
            'Could not detect type of source for the dataset: %datasetName',
            array('%datasetName' => $dataset->publicName)));
    }

    public function getTableDataset($datasetName) {
        $metamodel = data_controller_get_metamodel();

        $dataset = $metamodel->getDataset($datasetName);

        $datasetSourceType = $this->detectSourceType($dataset);
        if ($datasetSourceType != TableDatasetSourceTypeHandler::SOURCE_TYPE) {
            throw new IllegalArgumentException(t(
                'Only a table can be used as a source for %datasetName dataset: %datasetSourceType',
                array('%datasetName' => $dataset->publicName, '%datasetSourceType' => $datasetSourceType)));
        }

        return $dataset;
    }

    public function getHandler($sourceType) {
        if (!isset($this->handlers[$sourceType])) {
            $classname = $this->getHandlerClassName($sourceType);

            $this->handlers[$sourceType] = new $classname();
        }

        return $this->handlers[$sourceType];
    }
}