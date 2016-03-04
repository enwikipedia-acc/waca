<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 2/22/16
 * Time: 13:53
 */

namespace Waca\Tests;

class ApplicationBaseTest extends \PHPUnit_Framework_TestCase
{
	private $si;
    private $ab;

	public function setUp() {
		$this->si = new \Waca\siteConfiguration();

		$this->ab = $this->getMockForAbstractClass('\Waca\ApplicationBase', [$this->si]);
	}

	public function testConstruct() {

		$this->ab->expects($this->any())
			-> method("run")
			-> will($this->returnValue(NULL));

	}


	function testRun() {
		$this->markTestIncomplete("Not fully implemented yet.");

		$this->ab->expects($this->any())
			-> method("run")
			-> will($this->returnValue(NULL));
	}

	function testGetConfiguration() {
		$this->markTestIncomplete("Not fully implemented yet.");
		$this->ab->expects($this->any())
			-> method("getConfiguration")
			-> will($this->returnValue(NULL));
	}

}