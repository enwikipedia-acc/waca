<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use Waca\SiteConfiguration;
use Waca\WebStart;
use Waca\Router\RequestRouter;

class WebStartTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SiteConfiguration */
    private $sc;
    /** @var  RequestRouter */
    private $ir;
    /** @var  WebStart */
    private $ws;

    public function setUp()
    {
        $this->sc = new SiteConfiguration();
        $this->ir = new RequestRouter();
        $this->ws = new WebStart($this->sc, $this->ir);
    }

    public function tearDown()
    {
        unset($this->ws);
        unset($this->ir);
        unset($this->sc);
    }

    public function testCreatedProperly()
    {
        $this->assertInstanceOf('Waca\SiteConfiguration', $this->sc);
    }

    public function testRun()
    {
        // I'm disabling this for now.  Someone changed phpunit versions on me, and this new version
        // attempts to do a database connection with the mocked method... for some reason. - MRB 2017/02/17
        $this->markTestSkipped("Mocking issues.  See comment.");

        // ob_end_clean makes this a risky test.  We have to mock it to prevent closing of the wrong output buffers
        $wsMock = $this->getMockBuilder(WebStart::class)
            ->setMethods(["ob_end_clean"])
            ->disableOriginalConstructor()
            ->getMock();
        $wsMock->method('ob_end_clean')->willReturn(null);

        // Not sure what else we can do here, run() doesn't return anything and there are no accessors to ensure that
        // the data is set correctly.  So we'll run it and make sure that it doesn't error and that it doesn't return
        // anything.
        $this->assertNull($wsMock->run());
    }

    public function testPublic()
    {
        // Test default values first
        $this->assertFalse($this->ws->isPublic());
        $this->assertNotNull($this->ws->isPublic());

        // Setters and getters
        $this->ws->setPublic(true);
        $this->assertTrue($this->ws->isPublic());

        $this->ws->setPublic(false);
        $this->assertFalse($this->ws->isPublic());
    }
}
