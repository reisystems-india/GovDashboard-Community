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

abstract class AbstractAssemblerDatasetSourceTypeHandler extends AbstractDatasetSourceTypeHandler {

    abstract protected function getAssemblerName(DatasetMetaData $dataset);

    public function assemble(AbstractSQLDataSourceQueryHandler $datasourceHandler, AbstractQueryRequest $request, DatasetMetaData $dataset, array $columnNames = NULL) {
        $assemblerName = $this->getAssemblerName($dataset);
        $assemblerConfiguration = $dataset->configuration;

        $assembler = DatasetSourceAssemblerFactory::getInstance()->getHandler($assemblerName, $assemblerConfiguration);

        $statement = $assembler->assemble($datasourceHandler, $request, $dataset, $columnNames);

        return $statement;
    }
}
