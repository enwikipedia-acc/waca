<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 2/22/16
 * Time: 13:53
 */

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

	public function testConstruct()
	{
		// -- stw 2016-09-22
		$this->markTestIncomplete("Broken, not sure what this should be doing");

		$this->ab->expects($this->any())
			->method("run")
			->will($this->returnValue(null));
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
		$this->markTestIncomplete("Not fully implemented yet.");
		$this->ab->expects($this->any())
			->method("getConfiguration")
			->will($this->returnValue(null));
	}
}