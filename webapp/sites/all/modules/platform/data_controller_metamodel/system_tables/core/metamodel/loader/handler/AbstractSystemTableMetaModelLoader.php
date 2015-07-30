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


abstract class AbstractSystemTableMetaModelLoader extends AbstractMetaModelLoader {

    // [C]olumn [N]ames
    const CN_TABLE_OWNER = 'schema_owner';
    const CN_TABLE_NAME = 'table_name';
    const CN_COLUMN_NAME = 'column_name';
    const CN_COLUMN_INDEX = 'column_index';
    const CN_COLUMN_TYPE = 'column_type';
    const CN_COLUMN_APPLICATION_TYPE = 'column_application_type';
    const CN_COLUMN_TYPE_LENGTH = 'column_type_length';
    const CN_COLUMN_TYPE_PRECISION = 'column_type_precision';
    const CN_COLUMN_TYPE_SCALE = 'column_type_scale';
    const CN_COMMENT = 'comment_body';
    const CN_CONSTRAINT_TYPE = 'constraint_type';
    const CN_OBJECT_NAME = 'object_name';
    const CN_REFERENCED_OBJECT_NAME = 'r_object_name';

    protected $commentGenerator = NULL;

    public function __construct() {
        parent::__construct();
        $this->commentGenerator = new ColumnCommentGenerator();
    }

    protected function isDataSourceAcceptable(DataSourceMetaData $datasource, array $filters = NULL) {
        $eligibleDataSources = isset($filters['DatasetMetaData']['datasourceName']) ? $filters['DatasetMetaData']['datasourceName'] : NULL;

        return !isset($eligibleDataSources) || in_array($datasource->name, $eligibleDataSources);
    }

    protected function prepareLoaderEnvironment(DataSourceMetaData $datasource, SystemTableMetaModelLoaderEnvironment $environment) {
        if (!isset($datasource->metamodel_config->loader)) {
            return;
        }

        // copying eligible properties from data source configuration to loader environment object
        $config = $datasource->metamodel_config->loader;
        foreach ($environment as $propertyName => $currentValue) {
            if (!isset($config->$propertyName)) {
                continue;
            }

            $environment->$propertyName = $config->$propertyName;
        }
    }

    protected function adjustOwnerName($owner) {
        return $owner;
    }

    protected function getEligibleOwners(SystemTableMetaModelLoaderCallContext $callcontext, $defaultOwner = NULL) {
        $owners = array();

        if (isset($callcontext->environment->options->table->owner->include)) {
            foreach ($callcontext->environment->options->table->owner->include as $owner) {
                $owners[] = $this->adjustOwnerName($owner);
            }
        }

        if (isset($defaultOwner)) {
            $adjustedDefaultOwner = $this->adjustOwnerName($defaultOwner);
            if (!in_array($adjustedDefaultOwner, $owners)) {
                $owners[] = $adjustedDefaultOwner;
            }
        }

        return $owners;
    }

    protected function getIneligibleOwners(SystemTableMetaModelLoaderCallContext $callcontext) {
        $owners = NULL;

        if (isset($callcontext->environment->options->table->owner->exclude)) {
            foreach ($callcontext->environment->options->table->owner->exclude as $owner) {
                $owners[] = $this->adjustOwnerName($owner);
            }
        }

        return $owners;
    }

    protected function applyPattern(SystemTableMetaModelLoaderCallContext $callcontext, $patterns, $name, $default) {
        $classcontext = $callcontext->getClassContext($this);

        if (isset($patterns->exclude)) {
            if (isset($classcontext->namePatternCache[FALSE][$patterns->exclude][$name])) {
                $result = $classcontext->namePatternCache[FALSE][$patterns->exclude][$name];
            }
            else {
                $result = preg_match($patterns->exclude, $name);
                $classcontext->namePatternCache[FALSE][$patterns->exclude][$name] = $result;
            }
            if ($result === FALSE) {
                // possible error in the expression
                LogHelper::log_error(t(
                    "Regular expression matching error for '@name' value: @error",
                    array('@name' => $name, '@error' => preg_last_error())));
                return FALSE;
            }
            elseif ($result == 0) {
                // the column name does not match the pattern
            }
            else {
                return FALSE;
            }
        }

        if (isset($patterns->include)) {
            if (isset($classcontext->namePatternCache[TRUE][$patterns->include][$name])) {
                $result = $classcontext->namePatternCache[TRUE][$patterns->include][$name];
            }
            else {
                $result = preg_match($patterns->include, $name);
                $classcontext->namePatternCache[TRUE][$patterns->include][$name] = $result;
            }
            if ($result === FALSE) {
                // possible error in the expression
                LogHelper::log_error(t(
                    "Regular expression matching error for '@name' value: @error",
                    array('@name' => $name, '@error' => preg_last_error())));
                return FALSE;
            }
            elseif ($result == 0) {
                // the column name does not match the pattern
                return FALSE;
            }
            else {
                return TRUE;
            }
        }

        return $default;
    }

    abstract protected function loadColumnsProperties(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource);

    protected function adjustTableName($tableName) {
        return strtolower($tableName);
    }

    protected function generateTablePublicName($tableName) {
        return $tableName;
    }

    protected function adjustColumnName($columnName) {
        return strtolower($columnName);
    }

    protected function generateColumnPublicName($columnName) {
        return $columnName;
    }

    abstract protected function generateColumnApplicationType(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource, ColumnMetaData $column);

    protected function adjustColumnVisibility(SystemTableMetaModelLoaderCallContext $callcontext, ColumnMetaData $column) {
        if (isset($callcontext->environment->options->table->column->pattern)) {
            $isVisible = $this->applyPattern($callcontext, $callcontext->environment->options->table->column->pattern, $column->name, TRUE);
            if (!$isVisible) {
                $column->used = FALSE;
            }
        }
    }

    protected function loadTableComments(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        return NULL;
    }

    protected function findEligibleDatasets4TableComment(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, $tableOwner, $tableName) {
        return NULL;
    }

    protected function processTableComments(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource) {
        if (!isset($callcontext->environment->options->table->metadata)) {
            return;
        }

        $metadata = $callcontext->environment->options->table->metadata;

        $supportedPropertyNames = NULL;
        foreach ($metadata as $propertyName => $propertyCfg) {
            if (isset($propertyCfg->source) && ($propertyCfg->source == 'comment')) {
                $supportedPropertyNames[] = $propertyName;
            }
        }
        if (!isset($supportedPropertyNames)) {
            return;
        }

        $tableCommentsProperties = $this->loadTableComments($callcontext, $datasource);
        if (isset($tableCommentsProperties)) {
            foreach ($tableCommentsProperties as $tableCommentProperties) {
                if (!isset($tableCommentProperties[self::CN_COMMENT])) {
                    continue;
                }

                $tableOwner = $this->adjustOwnerName($tableCommentProperties[self::CN_TABLE_OWNER]);
                $tableName = $this->adjustTableName($tableCommentProperties[self::CN_TABLE_NAME]);

                $datasets = $this->findEligibleDatasets4TableComment($callcontext, $metamodel, $tableOwner, $tableName);
                if (!isset($datasets)) {
                    continue;
                }

                $comment = $tableCommentProperties[self::CN_COMMENT];
                $comment = $this->commentGenerator->generate($comment);

                foreach ($metadata as $propertyName => $propertyCfg) {
                    $parts = isset($propertyCfg->options->delimiter)
                        ? explode($propertyCfg->options->delimiter, $comment)
                        : array($comment);

                    $index = isset($propertyCfg->options->position) ? $propertyCfg->options->position : NULL;

                    $value = isset($index)
                        ? (isset($parts[$index]) ? $parts[$index] : NULL)
                        : ((count($parts) >= 1) ? $parts[0] : NULL);
                    $value = StringHelper::trim($value);
                    if (!isset($value)) {
                        continue;
                    }

                    foreach ($datasets as $dataset) {
                        $dataset->$propertyName = $value;
                    }
                }
            }
        }

        LogHelper::log_info(t(
            'Processed system meta data about @tableCommentCount table comment(s)',
            array('@tableCommentCount' => count($tableCommentsProperties))));
    }

    protected function loadColumnComments(SystemTableMetaModelLoaderCallContext $callcontext, DataSourceMetaData $datasource) {
        return NULL;
    }

    protected function findEligibleColumns4ColumnComment(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, $tableOwner, $tableName, $columnName) {
        return NULL;
    }

    protected function processColumnComments(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource) {
        if (!isset($callcontext->environment->options->table->column->metadata)) {
            return;
        }

        $metadata = $callcontext->environment->options->table->column->metadata;

        $supportedPropertyNames = NULL;
        foreach ($metadata as $propertyName => $propertyCfg) {
            if (isset($propertyCfg->source) && ($propertyCfg->source == 'comment')) {
                $supportedPropertyNames[] = $propertyName;
            }
        }
        if (!isset($supportedPropertyNames)) {
            return;
        }

        $columnCommentsProperties = $this->loadColumnComments($callcontext, $datasource);
        if (isset($columnCommentsProperties)) {
            foreach ($columnCommentsProperties as $columnCommentProperties) {
                if (!isset($columnCommentProperties[self::CN_COMMENT])) {
                    continue;
                }

                $tableOwner = $this->adjustOwnerName($columnCommentProperties[self::CN_TABLE_OWNER]);
                $tableName = $this->adjustTableName($columnCommentProperties[self::CN_TABLE_NAME]);
                $columnName = $this->adjustColumnName($columnCommentProperties[self::CN_COLUMN_NAME]);

                $columns = $this->findEligibleColumns4ColumnComment($callcontext, $metamodel, $tableOwner, $tableName, $columnName);
                if (!isset($columns)) {
                    continue;
                }

                $comment = $columnCommentProperties[self::CN_COMMENT];
                $comment = $this->commentGenerator->generate($comment);

                foreach ($metadata as $propertyName => $propertyCfg) {
                    $parts = isset($propertyCfg->options->delimiter)
                        ? explode($propertyCfg->options->delimiter, $comment)
                        : array($comment);

                    $index = isset($propertyCfg->options->position) ? $propertyCfg->options->position : NULL;

                    $value = isset($index)
                        ? (isset($parts[$index]) ? $parts[$index] : NULL)
                        : ((count($parts) >= 1) ? $parts[0] : NULL);
                    $value = StringHelper::trim($value);
                    if (!isset($value)) {
                        continue;
                    }

                    foreach ($columns as $column) {
                        $column->$propertyName = $value;
                    }
                }
            }
        }

        LogHelper::log_info(t(
            'Processed system meta data about @columnCommentCount column comment(s)',
            array('@columnCommentCount' => count($columnCommentsProperties))));
    }

    abstract protected function initiateCallContext();

    protected abstract function loadFromDataSource(SystemTableMetaModelLoaderCallContext $callcontext, AbstractMetaModel $metamodel, DataSourceMetaData $datasource, array $filters = NULL);

    public function load(AbstractMetaModel $metamodel, array $filters = NULL) {
        $loaderClassName = get_class($this);

        $environment_metamodel = data_controller_get_environment_metamodel();
        foreach ($environment_metamodel->datasources as $datasource) {
            if (!$this->isMetaDataAcceptable($datasource, $filters)) {
                continue;
            }

            if (!$this->isDataSourceAcceptable($datasource, $filters)) {
                continue;
            }

            $environment = new SystemTableMetaModelLoaderEnvironment();
            $this->prepareLoaderEnvironment($datasource, $environment);
            if ($environment->classname != $loaderClassName) {
                continue;
            }

            $callcontext = $this->initiateCallContext();
            $callcontext->environment = $environment;

            $this->loadFromDataSource($callcontext, $metamodel, $datasource, $filters);
        }
    }
}

class SystemTableMetaModelLoaderEnvironment extends AbstractObject {

    public $classname = NULL;
    public $options = NULL;
}

class SystemTableMetaModelLoaderCallContext extends AbstractCallContext {

    public $environment = NULL;
}
