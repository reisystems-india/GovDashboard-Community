<?php

/**
 * @file
 * Contains \GD\Tests\Core\GovDashTest.
 */

namespace GD\Tests\Core;

use GD\Tests\UnitTestCase;

/**
 * Tests the GD module.
 *
 * @group GovDashTest
 */
class GovDashTest extends UnitTestCase {

    /**
     * Tests the gd_init method.
     *
     * @covers gd_init
     */
    public function testInit() {
        $this->assertNotNull(GOVDASH_VERSION);
    }
}