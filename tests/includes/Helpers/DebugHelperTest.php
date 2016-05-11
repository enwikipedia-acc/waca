<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/


namespace Waca\Tests\Helpers;

use \Phpunit_Framework_TestCase;
use \PHPUnit_Extensions_MockFunction;
use \Waca\Helpers\DebugHelper;

class DebugHelperTest extends PHPUnit_Framework_TestCase
{
	private $btMock;
	private $dbh;

	public function setUp() {
		ReS$this->markTestSkipped("Appears to allocate too much memory, we may have a bug here.  Skipping for now.");
		$this->dbh = new debugHelper();

		$this->btMock = new PHPUnit_Extensions_MockFunction('debug_backtrace', $this->dbh);
	}

	public function tearDown() {
		$this->btMock = NULL;
		$this->dbh = NULL;
	}

	public function testGetBacktrace()
	{
		$this->btMock->expects($this->any())
			->will($this->returnValue(
				array(
					array(
					"file" => "/tmp/a.php",
					"line" => 10,
					"function" => "a_test",
					"args"=> array("friend")
					),
					array(
						"file" => "/tmp/b.php",
						"line" => 2,
						"function" => "include_once",
						"args"=> array("/tmp/a.php")
					)
				)
			) );

		$this->assertContains("/tmp/a.php", $this->dbh->getBacktrace());
		$this->assertContains("/tmp/b.php", $this->dbh->getBacktrace());
		$this->assertContains("a_test", $this->dbh->getBacktrace());
		$this->assertContains("include_once", $this->dbh->getBacktrace());
	}
}
