<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\ApplicationBase;
use Waca\SiteConfiguration;

class ApplicationBaseTest extends PHPUnit_Framework_TestCase
{
    private $si;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $ab;

    public function setUp()
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