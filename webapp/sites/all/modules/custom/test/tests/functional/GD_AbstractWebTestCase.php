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

abstract class GD_AbstractWebTestCase extends DrupalWebTestCase {

    protected $datasourceTest = NULL;

    public function __construct($test_id = NULL) {
        parent::__construct($test_id);
        $this->profile = $this->getTestProfile();
    }

    protected function getTestProfile() {
        // returns current profile
        return drupal_get_profile();
    }

    protected function setUp() {
        // preparing list of modules
        $modules = func_get_args();
        if (isset($modules[0]) && is_array($modules[0])) {
            $modules = $modules[0];
        }
        // to enable test datasets
        $modules[] = 'gd_test';

        parent::setUp($modules);

        // 1) the property was set by init hook function
        // 2) the value was stored using drupal_static
        // 3) drupal_static_reset() was called by setUp()
        Sequence::registerDataSource(DrupalDatabaseEnvironmentMetaModelGenerator::$DATASOURCE_NAME__DEFAULT);

        // creating and populating test database
        $this->prepareTestDatabase();
    }

    protected function tearDown() {
        // dropping test database
        $this->dropTestDatabase();

        parent::tearDown();
    }

    protected function createDatasetStorage($tableName) {
        $metamodel = data_controller_get_metamodel();

        $dataStructureController = data_controller_ddl_get_instance();

        $datasetName = NameSpaceHelper::addNameSpace(DATASOURCE_NAME__TEST, $tableName);
        $dataset = $metamodel->getDataset($datasetName);

        $dataStructureController->createDatasetStorage($dataset);
    }

    protected function isDataFileSuitable($filename) {
        return strrpos($filename, '.csv') === (strlen($filename) - strlen('.csv'));
    }

    protected function populateDataset($dataset, $rootFolderName) {
        $handle = opendir($rootFolderName);
        if ($handle !== FALSE) {
            while (($name = readdir($handle)) !== FALSE) {
                $fullname = $rootFolderName . DIRECTORY_SEPARATOR . $name;

                if (is_dir($fullname)) {
                    if ($name[0] != '.') {
                        $this->populateDataset($dataset, $fullname);
                    }
                }
                elseif ($this->isDataFileSuitable($fullname)) {
                    $request = new DelimiterSeparatedFileUploadRequest();
                    $request->fullFileName = $fullname;
                    $request->metadata = $dataset;

                    // initializing data submitter
                    $dataSubmitters = array(
                        new TransactionSupporter($dataset->datasourceName),
                        new EmptyRecordSkipper(),
                        gd_data_controller_ddl_initialize_column_name_preparer($dataset->datasourceName, GD_NamingConvention::$PREFIX_NAME__COLUMN),
                        new ColumnValueTypeAdjuster(COLUMN_VALUE__EXCEPTION_POOL_SIZE),
                        new ColumnValueTrimmer(COLUMN_VALUE__MAXIMUM_LENGTH),
                        new FlatSchemaDataSubmitter($dataset->name));

                    $request->prepareDataParser()->parse($request->prepareDataProvider(), $dataSubmitters);
                }
            }
            closedir($handle);
        }
    }

    protected function populateDatasets() {
        $metamodel = data_controller_get_metamodel();

        $namespaceParts = explode(NameSpaceHelper::NAME_SPACE_SEPARATOR, DATASOURCE_NAME__TEST);
        $path = realpath(drupal_get_path('module', 'gd_test'))
            . DIRECTORY_SEPARATOR . '_db'
            . DIRECTORY_SEPARATOR . 'data'
            . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $namespaceParts);

        $handle = opendir($path);
        if ($handle !== FALSE) {
            while (($name = readdir($handle)) !== FALSE) {
                if ($name[0] == '.') {
                    continue;
                }

                $fullname = $path . DIRECTORY_SEPARATOR . $name;

                if (!is_dir($fullname)) {
                    continue;
                }

                $datasetName = NameSpaceHelper::addNameSpace(DATASOURCE_NAME__TEST, $name);
                $dataset = $metamodel->findDataset($datasetName);
                if (!isset($dataset)) {
                    continue;
                }

                $folderName = $fullname;
                $this->populateDataset($dataset, $folderName);
            }
            closedir($handle);
        }
    }

    protected function prepareTestDatabase() {
        $environment_metamodel = data_controller_get_environment_metamodel();

        // registering test data source and creating corresponding database
        $datasource = $environment_metamodel->findDataSource(DATASOURCE_NAME__TEST);
        if (!isset($datasource)) {
            $databaseNameSuffix = GD_NamingConvention::generateDataMartName() . '_ts';
            $datasourceInfo = array('name' => DATASOURCE_NAME__TEST, 'publicName' => 'Test Suite', 'database' => $databaseNameSuffix);
            gd_datasource_create($datasourceInfo);

            // creating test tables
            $this->createDatasetStorage(TABLE_NAME__LEAF);
            $this->createDatasetStorage(TABLE_NAME__BRANCH);
            $this->createDatasetStorage(TABLE_NAME__MAIN);

            // populating the datasets
            $this->populateDatasets();
        }
    }

    protected function dropTestDatabase() {
        // unregistering test data source and dropping corresponding database
        $environment_metamodel = data_controller_get_environment_metamodel();
        $datasource = $environment_metamodel->findDataSource(DATASOURCE_NAME__TEST);
        if (isset($datasource)) {
            gd_datasource_drop($datasource->name);
        }
    }
}
