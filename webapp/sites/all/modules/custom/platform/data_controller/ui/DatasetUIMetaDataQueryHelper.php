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

class DatasetUIMetaDataQueryHelper {

    /**
     * @static
     * @param $datasetName
     * @param $columns
     * @param $parameters
     * @param $orderBy
     * @param $options
     * @return DataControllerQueryObject[]
     */
    public static function prepareDataQueryControllerRequest($datasetName, $columns, $parameters, $orderBy, array $options = NULL) {
        $metamodel = data_controller_get_metamodel();

        list($cleanedColumns, $cleanedParameters, $cleanedOrderBy, $cleanedOptions) = self::cleanFunctionParameters($datasetName, $columns, $parameters, $orderBy, $options);

        // preparing list of columns which are used to filter data

        $parsedUIMetaDataNames = NULL;
        $parameterColumnNames = NULL;
        if (isset($cleanedParameters)) {
            ArrayHelper::addUniqueValues($parameterColumnNames, array_keys($cleanedParameters));
            if (isset($parameterColumnNames)) {
                foreach ($parameterColumnNames as $uiMetaDataName) {
                    $parsedUIMetaDataNames[$uiMetaDataName] = self::parseUIMetaDataName($datasetName, $uiMetaDataName, $cleanedOptions);
                }
            }
        }

        // looking for all datasets/[cubes] we need to work with
        $connectedDatasetNames = array($datasetName);

        $cubes = NULL;

        $wereColumnNamesRequested = isset($cleanedColumns);
        // if no columns were selected we need to use all columns available
        $isCube = FALSE;
        if (!$wereColumnNamesRequested) {
            $assembler = new DatasetUIMetaDataAssembler();
            $uiMetaData = $assembler->assemble($datasetName);

            if (isset($parameterColumnNames)) {
                ArrayHelper::addUniqueValues($connectedDatasetNames, self::detectConnectedDatasetsBySelectedColumns($parsedUIMetaDataNames));

                foreach ($connectedDatasetNames as $connectedDatasetName) {
                    $cube = $metamodel->findCubeByDatasetName($connectedDatasetName);
                    if (isset($cube)) {
                        $cubes[$connectedDatasetName] = $cube;
                    }
                }

                $isCube = self::detectRequestTypeBySelectedColumns($cubes, $parsedUIMetaDataNames, NULL, $parameterColumnNames);
            }

            // we need to load list of all available columns from requested dataset
            $uiMetaData->prepareElementNames($cleanedColumns, FALSE);
        }

        // adding requested columns to list of parsed columns
        if (isset($cleanedColumns)) {
            foreach ($cleanedColumns as $uiMetaDataName) {
                $parsedUIMetaDataNames[$uiMetaDataName] = self::parseUIMetaDataName($datasetName, $uiMetaDataName, $cleanedOptions);
            }
        }

        // updating list of datasets/[cubes] we need to work with
        ArrayHelper::addUniqueValues($connectedDatasetNames, self::detectConnectedDatasetsBySelectedColumns($parsedUIMetaDataNames));

        // loading meta data for additional participating cubes
        foreach ($connectedDatasetNames as $connectedDatasetName) {
            if (!isset($cubes[$connectedDatasetName])) {
                $cube = $metamodel->findCubeByDatasetName($connectedDatasetName);
                if (isset($cube)) {
                    $cubes[$connectedDatasetName] = $cube;
                }
            }
        }

        if ($wereColumnNamesRequested) {
            $isCube = self::detectRequestTypeBySelectedColumns($cubes, $parsedUIMetaDataNames, $cleanedColumns, $parameterColumnNames);
        }

        $request = $isCube ? new DataQueryControllerCubeRequest() : new DataQueryControllerDatasetRequest();
        $request->datasetName = $datasetName;
        $request->options = $cleanedOptions;

        $columnMappings = $isCube
            ? self::prepareCubeColumnMappings($cubes, $parsedUIMetaDataNames)
            : self::prepareListColumnMappings($cubes, $parsedUIMetaDataNames);

        $request->columns = self::mapColumns($parsedUIMetaDataNames, $columnMappings, $cleanedColumns, $isCube);
        $request->parameters = self::mapParameters($parsedUIMetaDataNames, $columnMappings, $cleanedParameters, $isCube);
        $request->orderBy = self::mapOrderBy($parsedUIMetaDataNames, $columnMappings, $cleanedOrderBy, $isCube);
        $request->resultFormatter = new ColumnMappingResultFormatter(array_flip($columnMappings));

        return $request;
    }

    public static function cleanFormulaExpressionColumnNames(ParserCallback $callback, &$callerSession) {
        $uiMetaDataName = $callback->marker;

        list($elementNameSpace, $name) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($uiMetaDataName);
        switch ($elementNameSpace) {
            case AbstractAttributeUIMetaData::NAME_SPACE:
                list($dimensionName, $columnName) = ParameterNameHelper::split($name);
                if (isset($columnName)) {
                    throw new IllegalArgumentException(t(
                        'Function expression cannot be based on a dimension column: %name',
                        array('%name' => $uiMetaDataName)));
                }
                list($datasetName) = ReferencePathHelper::splitReference($dimensionName);
                if (isset($datasetName)) {
                    throw new IllegalArgumentException(t(
                        'Function expression cannot be based on a referenced dimension: %name',
                        array('%name' => $uiMetaDataName)));
                }

                $callback->marker = $dimensionName;
                break;
            case AbstractMeasureUIMetaData::NAME_SPACE:
                throw new IllegalArgumentException(t(
                    'Function expression cannot be based on a measure: %name',
                    array('%name' => $uiMetaDataName)));
                break;
            case FormulaUIMetaData::NAME_SPACE:
                list($datasetName, $formulaName) = ReferencePathHelper::splitReference($name);
                if (isset($datasetName)) {
                    throw new IllegalArgumentException(t(
                        'Function expression cannot be based on a referenced formula: %name',
                        array('%name' => $uiMetaDataName)));
                }

                $callback->marker = $formulaName;
                break;
            default:
                throw new UnsupportedOperationException(t('Unsupported UI Meta Data name space: %uiMetaDataName', array('%uiMetaDataName' => $uiMetaDataName)));
        }
    }

    public static function cleanFunctionParameters($datasetName, $columns, $parameters, $orderBy, array $options = NULL) {
        $metamodel = data_controller_get_metamodel();
        $dataset = $metamodel->getDataset($datasetName);

        $cleanedColumns = ArrayHelper::trim($columns);

        $cleanedParameters = NULL;
        if (isset($parameters)) {
            foreach ($parameters as $key => $value) {
                $key = StringHelper::trim($key);

                // ignoring system parameters
                list($elementNameSpace) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($key);
                if (!isset($elementNameSpace)) {
                    continue;
                }

                $cleanedParameters[$key] = $value;
            }
        }

        $cleanedOrderBy = NULL;
        ArrayHelper::addUniqueValues($cleanedOrderBy, ArrayHelper::trim($orderBy));

        $cleanedOptions = NULL;
        if (isset($options)) {
            foreach ($options as $name => $option) {
                $cleanedOption = NULL;
                if (isset($option)) {
                    if ($name == AbstractQueryRequest::OPTION__FORMULA_DEF) {
                        // cleaning all formulas
                        foreach ($option as $index => $formula) {
                            $cleanedFormula = clone $formula;

                            $parser = new FormulaExpressionParser($cleanedFormula->expressionLanguage);
                            $cleanedFormula->source = $parser->parse($cleanedFormula->source, 'DatasetUIMetaDataQueryHelper::cleanFormulaExpressionColumnNames');

                            $cleanedOption[$index] = $cleanedFormula;
                        }

                        // assembling clean formulas to calculate 'measure' flag
                        if (isset($cleanedOption)) {
                            $columnReferenceFactory = new CompositeColumnReferenceFactory(array(
                                $dataset,
                                new FormulaReferenceFactory($cleanedOption)));

                            $expressionAssembler = new FormulaExpressionAssembler($columnReferenceFactory);
                            foreach ($cleanedOption as $index => $cleanedFormula) {
                                // assembling full expressions to detect if any aggregation function present
                                $expression = $expressionAssembler->assemble($cleanedFormula);
                                // process the formula expression
                                $handler = FormulaExpressionLanguageFactory::getInstance()->getHandler($cleanedFormula->expressionLanguage);
                                $lexemes = $handler->lex($expression);
                                $syntaxTree = $handler->parse($lexemes);
                                // checking if the formula expression contains references to any aggregation functions
                                $cleanedFormula->isMeasure = $handler->isMeasure($syntaxTree);
                            }
                        }
                    }
                    elseif (is_array($option)) {
                        $cleanedOption = ArrayHelper::copy($option);
                    }
                    elseif (is_object($option)) {
                        $cleanedOption = clone $option;
                    }
                    else {
                        $cleanedOption = $option;
                    }
                }

                if (isset($cleanedOption)) {
                    $cleanedOptions[$name] = $cleanedOption;
                }
            }
        }

        // adjusting list of columns we need to return
        $requestedColumnNames = NULL;
        // preparing list of unique column names
        ArrayHelper::addUniqueValues($requestedColumnNames, $cleanedColumns);
        // adding columns which are used to sort result
        if ($cleanedOrderBy) {
            foreach ($cleanedOrderBy as $directionalColumnName) {
                list($columnName, $isSortAscending) = ColumnBasedComparator_AbstractSortingConfiguration::parseDirectionalColumnName($directionalColumnName);
                ArrayHelper::addUniqueValue($requestedColumnNames, $columnName);
            }
        }

        return array($requestedColumnNames, $cleanedParameters, $cleanedOrderBy, $cleanedOptions);
    }

    public static function parseUIMetaDataName($defaultDatasetName, $uiMetaDataName, array $options = NULL) {
        $parsedUIMetaDataName = NULL;

        list($elementNameSpace, $name) = AbstractDatasetUIMetaDataGenerator::splitElementUIMetaDataName($uiMetaDataName);
        switch ($elementNameSpace) {
            case AbstractAttributeUIMetaData::NAME_SPACE:
                $parsedUIMetaDataName = new AttributeParsedUIMetaDataName();
                list($parsedUIMetaDataName->name, $parsedUIMetaDataName->columnName) = ParameterNameHelper::split($name);
                list($parsedUIMetaDataName->datasetName) = ReferencePathHelper::splitReference($parsedUIMetaDataName->name);
                break;
            case AbstractMeasureUIMetaData::NAME_SPACE:
                $parsedUIMetaDataName = new MeasureParsedUIMetaDataName();
                $parsedUIMetaDataName->name = $name;
                list($parsedUIMetaDataName->datasetName) = ReferencePathHelper::splitReference($parsedUIMetaDataName->name);
                break;
            case FormulaUIMetaData::NAME_SPACE:
                list($datasetName, $formulaName) = ReferencePathHelper::splitReference($name);

                // looking for formula configuration
                $selectedFormula = NULL;
                if (isset($options[AbstractQueryRequest::OPTION__FORMULA_DEF])) {
                    $formulas = $options[AbstractQueryRequest::OPTION__FORMULA_DEF];
                    foreach ($formulas as $formula) {
                        if ($formula->name == $formulaName) {
                            $selectedFormula = $formula;
                            break;
                        }
                    }
                }
                if (!isset($selectedFormula)) {
                    throw new IllegalStateException(t(
                        'Undefined formula configuration: %formulaName',
                        array('%formulaName' => $name)));
                }

                $parsedUIMetaDataName = isset($selectedFormula->isMeasure) && $selectedFormula->isMeasure
                    ? new FormulaMeasureParsedUIMetaDataName()
                    : new FormulaAttributeParsedUIMetaDataName();
                $parsedUIMetaDataName->name = $name;
                $parsedUIMetaDataName->datasetName = $datasetName;

                break;
            default:
                throw new UnsupportedOperationException(t('Unsupported UI Meta Data name space: %uiMetaDataName', array('%uiMetaDataName' => $uiMetaDataName)));
        }

        if (!isset($parsedUIMetaDataName->datasetName)) {
            $parsedUIMetaDataName->datasetName = $defaultDatasetName;
        }

        return $parsedUIMetaDataName;
    }

    protected static function detectConnectedDatasetsBySelectedColumns(array $parsedUIMetaDataNames) {
        $datasetNames = NULL;

        foreach ($parsedUIMetaDataNames as $parsedUIMetaDataName) {
            ArrayHelper::addUniqueValue($datasetNames, $parsedUIMetaDataName->datasetName);
        }

        return $datasetNames;
    }

    protected static function detectRequestTypeBySelectedColumns(array $cubes = NULL, array $parsedUIMetaDataNames, array $columnNames = NULL, array $parameterColumnNames = NULL) {
        $columnSelected = $measureSelected = FALSE;
        $mixedPurposeColumnCount = 0;

        // processing requested columns to see what type of columns are selected
        if (isset($columnNames)) {
            foreach ($columnNames as $columnName) {
                $parsedUIMetaDataName = $parsedUIMetaDataNames[$columnName];

                if ($parsedUIMetaDataName instanceof AttributeParsedUIMetaDataName) {
                    $mixedPurposeColumnCount++;
                }
                elseif ($parsedUIMetaDataName instanceof MeasureParsedUIMetaDataName) {
                    $measureSelected = TRUE;
                }
                elseif ($parsedUIMetaDataName instanceof FormulaAttributeParsedUIMetaDataName) {
                    $mixedPurposeColumnCount++;
                }
                elseif ($parsedUIMetaDataName instanceof FormulaMeasureParsedUIMetaDataName) {
                    $measureSelected = TRUE;
                }
            }
        }

        // processing parameters to see if cube-related columns are selected
        if (isset($parameterColumnNames)) {
            foreach ($parameterColumnNames as $parameterColumnName) {
                $parsedUIMetaDataName = $parsedUIMetaDataNames[$parameterColumnName];

                if ($parsedUIMetaDataName instanceof MeasureParsedUIMetaDataName) {
                    $measureSelected = TRUE;
                }
            }
        }

        $isCube = $measureSelected;
        // when we select only one column we might want to get unique values
        if (!$isCube && isset($cubes) && ($mixedPurposeColumnCount == 1)) {
            $isCube = TRUE;
        }

        return $isCube;
    }

    protected static function prepareListColumnMappings(array $cubes = NULL, array $parsedUIMetaDataNames) {
        $columnMappings = NULL;

        foreach ($parsedUIMetaDataNames as $columnName => $parsedUIMetaDataName) {
            $datasetColumnName = NULL;
            if ($parsedUIMetaDataName instanceof AttributeParsedUIMetaDataName) {
                $cube = isset($cubes[$parsedUIMetaDataName->datasetName]) ? $cubes[$parsedUIMetaDataName->datasetName] : NULL;
                if (isset($cube)) {
                    $dimension = $cube->getDimension($parsedUIMetaDataName->name);

                    $references = NULL;
                    if (isset($parsedUIMetaDataName->columnName)) {
                        $column = $cube->factsDataset->getColumn($dimension->attributeColumnName);
                        $branch = $column->findBranch($parsedUIMetaDataName->columnName);
                        // the dimension column can be a branch, not a dimension dataset column
                        if (!isset($branch) && isset($dimension->datasetName)) {
                            list($adjustedDimensionDatasetName) = gd_data_controller_metamodel_adjust_dataset_name($dimension->datasetName);
                            $references[] = ReferencePathHelper::assembleReference($adjustedDimensionDatasetName, $parsedUIMetaDataName->columnName);
                        }
                    }

                    if (isset($references)) {
                        $references[] = ReferencePathHelper::assembleReference($cube->factsDatasetName, $dimension->attributeColumnName);
                        $datasetColumnName = ReferencePathHelper::assembleReferencePath($references);
                    }
                    else {
                        $datasetColumnName = isset($parsedUIMetaDataName->columnName)
                            ? $parsedUIMetaDataName->columnName
                            : $dimension->attributeColumnName;
                    }
                }
                else {
                    $datasetColumnName = $parsedUIMetaDataName->name;
                }
            }
            elseif ($parsedUIMetaDataName instanceof FormulaAttributeParsedUIMetaDataName) {
                $datasetColumnName = $parsedUIMetaDataName->name;
            }
            elseif ($parsedUIMetaDataName instanceof FormulaMeasureParsedUIMetaDataName) {
                $datasetColumnName = $parsedUIMetaDataName->name;
            }

            if (isset($datasetColumnName)) {
                $columnMappings[$columnName] = $datasetColumnName;
            }
        }

        return $columnMappings;
    }

    protected static function prepareCubeColumnMappings(array $cubes, array $parsedUIMetaDataNames) {
        $columnMappings = NULL;

        foreach ($parsedUIMetaDataNames as $columnName => $parsedUIMetaDataName) {
            if ($parsedUIMetaDataName instanceof AttributeParsedUIMetaDataName) {
                $columnMappings[$columnName] = ParameterNameHelper::assemble($parsedUIMetaDataName->name, $parsedUIMetaDataName->columnName);
            }
            elseif ($parsedUIMetaDataName instanceof MeasureParsedUIMetaDataName) {
                $columnMappings[$columnName] = $parsedUIMetaDataName->name;
            }
            elseif ($parsedUIMetaDataName instanceof FormulaAttributeParsedUIMetaDataName) {
                $columnMappings[$columnName] = $parsedUIMetaDataName->name;
            }
            elseif ($parsedUIMetaDataName instanceof FormulaMeasureParsedUIMetaDataName) {
                $columnMappings[$columnName] = $parsedUIMetaDataName->name;
            }
        }

        return $columnMappings;
    }

    protected static function isColumnMappable(AbstractParsedUIMetaDataName $parsedUIMetaDataName, $isAttributeMapped, $isMeasureMapped) {
        $isColumnMappable = FALSE;
        if ($parsedUIMetaDataName instanceof AttributeParsedUIMetaDataName) {
            if ($isAttributeMapped) {
                $isColumnMappable = TRUE;
            }
        }
        elseif ($parsedUIMetaDataName instanceof MeasureParsedUIMetaDataName) {
            if ($isMeasureMapped) {
                $isColumnMappable = TRUE;
            }
        }
        elseif ($parsedUIMetaDataName instanceof FormulaAttributeParsedUIMetaDataName) {
            if ($isAttributeMapped) {
                $isColumnMappable = TRUE;
            }
        }
        elseif ($parsedUIMetaDataName instanceof FormulaMeasureParsedUIMetaDataName) {
            if ($isMeasureMapped) {
                $isColumnMappable = TRUE;
            }
        }

        return $isColumnMappable;
    }

    protected static function mapColumns(array $parsedUIMetaDataNames, array $columnMappings, array $columns = NULL, $isMeasureMapped) {
        $mappedColumns = NULL;

        if (isset($columns)) {
            foreach ($columns as $key => $columnName) {
                $parsedUIMetaDataName = $parsedUIMetaDataNames[$columnName];

                if (self::isColumnMappable($parsedUIMetaDataName, TRUE, $isMeasureMapped)) {
                    $mappedColumns[$key] = $columnMappings[$columnName];
                }
            }
        }

        return $mappedColumns;
    }

    protected static function mapParameters(array $parsedUIMetaDataNames, array $columnMappings, array $parameters = NULL, $isMeasureMapped) {
        $mappedParameters = NULL;

        if (isset($parameters)) {
            foreach ($parameters as $columnName => $value) {
                $parsedUIMetaDataName = $parsedUIMetaDataNames[$columnName];

                if (self::isColumnMappable($parsedUIMetaDataName, TRUE, $isMeasureMapped)) {
                    $mappedParameters[$columnMappings[$columnName]] = $value;
                }
            }
        }

        return $mappedParameters;
    }

    protected static function mapOrderBy(array $parsedUIMetaDataNames, array $columnMappings, array $orderBy = NULL, $isMeasureMapped) {
        $mappedOrderBy = NULL;

        if (isset($orderBy)) {
            foreach ($orderBy as $key => $directionalColumnName) {
                list($columnName, $isSortAscending) = ColumnBasedComparator_AbstractSortingConfiguration::parseDirectionalColumnName($directionalColumnName);
                $parsedUIMetaDataName = $parsedUIMetaDataNames[$columnName];

                if (self::isColumnMappable($parsedUIMetaDataName, TRUE, $isMeasureMapped)) {
                    $mappedOrderBy[$key] = ColumnBasedComparator_AbstractSortingConfiguration::assembleDirectionalColumnName($columnMappings[$columnName], $isSortAscending);
                }
            }
        }

        return $mappedOrderBy;
    }
}

abstract class AbstractParsedUIMetaDataName extends AbstractObject {

    public $datasetName = NULL;
    public $name = NULL;
}

abstract class AbstractAttributeParsedUIMetaDataName extends AbstractParsedUIMetaDataName {}

class AttributeParsedUIMetaDataName extends AbstractAttributeParsedUIMetaDataName {

    public $columnName = NULL;
}

class FormulaAttributeParsedUIMetaDataName extends AbstractAttributeParsedUIMetaDataName {}

abstract class AbstractMeasureParsedUIMetaDataName extends AbstractParsedUIMetaDataName {}

class MeasureParsedUIMetaDataName extends AbstractMeasureParsedUIMetaDataName {}

class FormulaMeasureParsedUIMetaDataName extends AbstractMeasureParsedUIMetaDataName {}
