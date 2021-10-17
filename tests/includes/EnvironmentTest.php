<?php

/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    private $toolVersion;
    private $environment;

    public function setUp()
    {
        $this->environment = new \Waca\Environment();
        $this->toolVersion = exec("git describe --always --dirty");
    }

    public function tearDown()
    {
        $this->environment = null;
        $this->toolVersion = null;
    }

    public function testGetToolVersion()
    {
        $this->assertEquals($this->environment->getToolVersion(), $this->toolVersion);
        $this->assertNotEquals($this->environment->getToolVersion(), null);
        $this->assertNotEquals($this->environment->getToolVersion(), "");
    }
}
