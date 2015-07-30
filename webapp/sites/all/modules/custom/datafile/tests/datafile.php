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


$_SERVER['REMOTE_ADDR'] = '';

define('DRUPAL_ROOT', getcwd());
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

class DatafileTests extends PHPUnit_Framework_TestCase {

    protected $datafile;

    protected function setUp()
    {
        $this->datafile->nid = 2;
        $this->setUpSession();

    }

    protected function setUpSession()
    {
        $_SESSION = array(
            'datamart' => 1,
            'messages' => array(
                'error' => array("Test error 1",
                    "Test error 2",
                    "Test error 3"
                ),
                'warning' => array("Test warning 1",
                    "Test warning 2",
                    "Test warning 3"
                )
            )
        );
    }

    public function test_dataset_menu()
    {
        $this->assertCount(2, gd_datafile_menu());
    }

    public function test_datafile_upload_resource_access()
    {
        //todo: find way to load undefined function "services_error" in "services.runtime.inc"
        //$this->assertTrue(gd_datafile_upload_resource_access());
    }

    public function test_datafile_load()
    {
        $nid = 2;
        $vid = 2;

        $this->assertThat(gd_datafile_load($nid, $vid), $this->logicalNot($this->isNull()));

    }

    public function test_datafile_prepare_error_from_session()
    {

        $some_object = new stdClass();
        $some_object->errors = array();

        $some_object = gd_datafile_prepare_error_from_session($some_object);

        $this->assertThat($some_object->errors,
            $this->logicalAnd(
                $this->contains("Test error 1"),
                $this->contains("Test error 2"),
                $this->contains("Test error 3")
            )
        );


    }

    public function test_datafile_prepare_warning_from_session()
    {

        $some_object = new stdClass();
        $some_object->warnings = array();

        $some_object = gd_datafile_prepare_warning_from_session($some_object);

        $this->assertThat($some_object->warnings,
                            $this->logicalAnd(
                                $this->contains("Test warning 1"),
                                $this->contains("Test warning 2"),
                                $this->contains("Test warning 3")
                            )
        );


    }

    public function test_datafile_api_upload_get_datafile()
    {
        //$file = file_load(1);
        //$this->assertNull(gd_datafile_api_upload_get_datafile($file));
        $file = file_load(2);
        $datafile = gd_datafile_api_upload_get_datafile($file);
        $this->assertThat($datafile, $this->logicalNot($this->isNull()));

        print_r($datafile);
    }

}