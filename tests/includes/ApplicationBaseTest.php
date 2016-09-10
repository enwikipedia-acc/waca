<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 2/22/16
 * Time: 13:53
 */

namespace Waca\Tests;

use \Waca\ApplicationBase;

class ApplicationBaseTest extends \PHPUnit_Framework_TestCase
{
	private $si;
    private $ab;

	public function setUp() {
		$this->si = new \Waca\SiteConfiguration();

		$this->ab = $this->getMockForAbstractClass(ApplicationBase::class, [$this->si]);
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
		$config = $this->ab->getConfiguration();

		$this->assertInstanceOf('\Waca\SiteConfiguration', $config);
		// We will actually test this in the SiteConfiguration.php test - for now we can be satisfied that it returns valid.
		
	}

}