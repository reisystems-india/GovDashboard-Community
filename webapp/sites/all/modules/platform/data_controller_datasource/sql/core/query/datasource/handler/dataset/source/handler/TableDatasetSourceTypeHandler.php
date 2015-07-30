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

class TableDatasetSourceTypeHandler extends AbstractDatasetSourceTypeHandler {

    const SOURCE_TYPE = 'SQL.table';

    public function assemble(AbstractSQLDataSourceQueryHandler $datasourceHandler, AbstractQueryRequest $request, DatasetMetaData $dataset, array $columnNames = NULL) {
        $engine = QueryEngineFactory::getInstance()->getHandler();

        $statement = $engine->newSelectStatement();

        // FIXME use the following code
/*      // the following code was commended out because functionality in Statement::findColumnTableByReferencePath() $table->dataset->name
        $tableName = assemble_database_entity_name($datasourceHandler, $dataset->datasourceName, $dataset->source);

        $table = $statement->newTable($tableName);
*/
        $table = new DatasetSection($dataset);
        $statement->tables[] = $table;

        if (isset($columnNames)) {
            $columnReferenceFactory = new CompositeColumnReferenceFactory(array(
                $dataset,
                new FormulaReferenceFactory($request->getFormulas())));

            $expressionAssembler = new FormulaExpressionAssembler($columnReferenceFactory);

            foreach ($columnNames as $columnName) {
                $column = $columnReferenceFactory->findColumn($columnName);

                if (isset($column) && ($column->persistence == FormulaMetaData::PERSISTENCE__CALCULATED)) {
                    $table->newCalculatedColumn($expressionAssembler->assemble($column), $columnName);
                }
                else {
                    $table->newColumn($columnName);
                }
            }
        }

        return $statement;
    }
}
