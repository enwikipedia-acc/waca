<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit_Extensions_MockFunction;
use PHPUnit_Framework_TestCase;
use Waca\Helpers\DebugHelper;

class DebugHelperTest extends PHPUnit_Framework_TestCase
{
	/** @var PHPUnit_Extensions_MockFunction */
	private $dbh;

	public function setUp()
	{
		//$this->markTestSkipped("Appears to allocate too much memory, we may have a bug here.  Skipping for now.");

		//$this->dbh = new DebugHelper();

		//$this->btMock = new PHPUnit_Extensions_MockFunction('get_debug_backtrace', $this->dbh);

		//$this->dbhMock = $this->getMockBuilder(DebugHelper::class)->getMock();
		$this->dbh = $this->getMock(DebugHelper::class, array("get_debug_backtrace"));
		$this->dbh->method('get_debug_backtrace')->willReturn(
			array(
				array(
					"file"     => "/tmp/a.php",
					"line"     => 10,
					"function" => "a_test",
					"args"     => array("friend"),
				),
				array(
					"file"     => "/tmp/b.php",
					"line"     => 2,
					"function" => "include_once",
					"args"     => array("/tmp/a.php"),
				),
			)
		);
	}

	public function tearDown()
	{
		$this->dbh = null;
	}

	public function testGetBacktrace()
	{
		$this->markTestSkipped("MRB - Still working on this one.");
		$this->assertContains("/tmp/a.php", $this->dbh->getBacktrace());
		$this->assertContains("/tmp/b.php", $this->dbh->getBacktrace());
		$this->assertContains("a_test", $this->dbh->getBacktrace());
		$this->assertContains("include_once", $this->dbh->getBacktrace());
	}
}
