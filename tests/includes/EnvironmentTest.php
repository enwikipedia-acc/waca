<?php

/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    private $toolVersion;
    private $environment;

    public function setUp() : void
    {
        $this->environment = new \Waca\Environment();
        $this->toolVersion = exec("git describe --always --dirty");
    }

    public function tearDown() : void
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
