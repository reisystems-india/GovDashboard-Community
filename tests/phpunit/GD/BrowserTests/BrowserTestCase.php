<?php

/**
 * @file
 * Contains \GD\Tests\BrowserTestCase.
 */

namespace GD\BrowserTests;

/**
 * Provides a base class and helpers for GovDash browser tests.
 *
 * @ingroup testing
 */
abstract class BrowserTestCase extends \PHPUnit_Extensions_Selenium2TestCase {

    protected function setUp() {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://www.example.com/');
    }

    public function testTitle() {
        $this->url('http://www.example.com/');
        $this->assertEquals('Example WWW Page', $this->title());
    }

}
