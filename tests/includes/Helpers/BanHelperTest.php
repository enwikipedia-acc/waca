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
use Waca\Helpers\BanHelper;
use Waca\PdoDatabase;

class BanHelperTest extends PHPUnit_Framework_TestCase
{
	private $bh;
	private $dbMock;
	private $statement;

	public function setUp()
	{
		if (!extension_loaded('runkit')) {
			$this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');

			return;
		}

		$this->dbMock = $this->getMockBuilder(PdoDatabase::class)->disableOriginalConstructor()->getMock();

		$this->statement = $this->getMockBuilder(PDOStatement::class)
			//->disableOriginalConstructor()
			->setMethods(array("fetchColumn", "bindValue", "execute", "fetchObject"))
			->getMock();
		$this->dbMock->method('prepare')->willReturn($this->statement);

		//var_dump(get_class_methods(get_class($this->statement)));

		$this->bh = new BanHelper($this->dbMock);
	}

	public function tearDown()
	{
		$this->bh = null;
	}

	public function testNameIsBanned()
	{
		$name = "Testing";
		$this->statement->method("fetchObject")->willReturn(false);
		$this->assertEquals($this->bh->nameIsBanned($name), false);
	}
}
