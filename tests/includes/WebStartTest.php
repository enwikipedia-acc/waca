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

	public function setUp() {
		$this->sc = new SiteConfiguration();
		$this->ir = new RequestRouter();
		$this->ws = new WebStart($this->sc, $this->ir);
	}

	public function tearDown() {
		unset($this->ws);
		unset($this->ir);
		unset($this->sc);
	}

	public function testCreatedProperly() {
		$this->assertInstanceOf('Waca\SiteConfiguration', $this->sc);
	}

	public function testRun() {
		$this->markTestSkipped("Not implemented yet.");
	}

	public function testPublic() {
		$newValue = true;

		$this->assertEquals($this->ws->isPublic(), false);

		$this->ws->setPublic($newValue);
		$this->assertEquals($this->ws->isPublic(), $newValue);

		$newValue = false;

		$this->ws->setPublic($newValue);
		$this->assertEquals($this->ws->isPublic(), $newValue);

	}
}
