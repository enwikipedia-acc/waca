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
	private $btMock;
	/** @var DebugHelper */
	private $dbh;

	public function setUp()
	{
		//$this->markTestSkipped("Appears to allocate too much memory, we may have a bug here.  Skipping for now.");

		$this->dbh = new DebugHelper();

		$this->btMock = new PHPUnit_Extensions_MockFunction('get_debug_backtrace', $this->dbh);
	}

	public function tearDown()
	{
		$this->btMock = null;
		$this->dbh = null;
	}

	public function testGetBacktrace()
	{

		$mock = $this->getMock("debugHelper", array("get_Debug_backtrace"));
		$mock->expects($this->once())->method("c")->will($this->returnValue(array(
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
		)));
		/*
		$this->btMock->expects($this->any())
			->will($this->returnValue(
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
			));
*/
		$this->assertContains("/tmp/a.php", $mock->getBacktrace());
		$this->assertContains("/tmp/b.php", $mock->getBacktrace());
		$this->assertContains("a_test", $mock->getBacktrace());
		$this->assertContains("include_once", $mock->getBacktrace());
		//$this->assertContains("/tmp/a.php", $this->dbh->getBacktrace());
		//$this->assertContains("/tmp/b.php", $this->dbh->getBacktrace());
		//$this->assertContains("a_test", $this->dbh->getBacktrace());
		//$this->assertContains("include_once", $this->dbh->getBacktrace());
	}
}
