<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;
use Waca\ApplicationBase;
use Waca\SiteConfiguration;

class ApplicationBaseTest extends TestCase
{
    private $si;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $ab;

    public function setUp() : void
    {
        $this->si = new SiteConfiguration();

        $this->ab = $this->getMockForAbstractClass(ApplicationBase::class, [$this->si]);
    }

    function testRun()
    {
        $this->markTestIncomplete("Not fully implemented yet.");

        $this->ab->expects($this->any())
            ->method("run")
            ->will($this->returnValue(null));
    }

    function testGetConfiguration()
    {
        $config = $this->ab->getConfiguration();

        // We will actually test this in the SiteConfiguration.php test - for now we can be satisfied that it returns
        // valid.
        $this->assertInstanceOf(SiteConfiguration::class, $config);
    }
}