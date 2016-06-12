<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PDOStatement;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\DataObjects\Ban;
use Waca\Helpers\BanHelper;
use Waca\PdoDatabase;

class BanHelperTest extends PHPUnit_Framework_TestCase
{
	/** @var BanHelper */
	private $banHelper;
	/** @var PHPUnit_Framework_MockObject_MockObject|PdoDatabase */
	private $dbMock;
	/** @var PHPUnit_Framework_MockObject_MockObject|PDOStatement */
	private $statement;

	public function setUp()
	{
		if (!extension_loaded('runkit')) {
			$this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');

			return;
		}

		$this->dbMock = $this->getMockBuilder(PdoDatabase::class)->disableOriginalConstructor()->getMock();

		$this->statement = $this->getMockBuilder(PDOStatement::class)
			->setMethods(array("fetchColumn", "bindValue", "execute", "fetchObject"))
			->getMock();
		$this->dbMock->method('prepare')->willReturn($this->statement);

		$this->banHelper = new BanHelper($this->dbMock);
	}

	public function tearDown()
	{
		$this->banHelper = null;
	}

	public function testNameIsNotBanned()
	{
		$name = "Testing";
		$this->statement->method("fetchObject")->willReturn(false);
		$this->assertEquals($this->banHelper->nameIsBanned($name), false);
	}

	public function testNameIsBanned()
	{
		$name = "Testing";
		$banObj = new Ban();
		$banObj->setTarget($name);
		$banObj->setType("Name");

		$this->statement->method("fetchObject")->willReturn($banObj);

		$this->assertEquals($this->banHelper->nameIsBanned($name), $banObj);
	}
}
