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

	public function setUp() {
		if (!extension_loaded('runkit')) {
			$this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');

			return;
		}

		/*
		$this->stmt = $this->getMock('PDOStatement', array ('execute','fetchAll'));
		$this->stmt->expects($this->any())
			->method('execute')
			->will($this->returnValue(true));
		$this->stmt->expects($this->any())
			->method('fetchAll')
			->will($this->returnValue(NULL));
*/
		/*$this->db = $this->getMock('PdoDatabase', array('prepare'),
			/*array('sqlite:dbname=:memory')* / array(),'PdoMock',true);
		$this->db->expects($this->any())
			->method('prepare')
			->will($this->returnValue($this->stmt));*/

		$this->markTestSkipped("Testing");

		$this->dbMock = $this->getMockBuilder(PdoDatabase::class)->disableOriginalConstructor()->getMock();
		$this->statement = $this->getMockBuilder(PDOStatement::class)->disableOriginalConstructor()->getMock();
		$this->statement->method('fetchColumn')->willReturn(0);
		$this->statement->method('bindValue')->with($this->anything())->willReturn(0);
		$this->dbMock->method('prepare')->willReturn($this->statement);

		$this->bh = new BanHelper($this->dbMock);
	}

	public function tearDown() {
		$this->bh = NULL;
	}

	public function testNameIsBanned() {
		$name = "Testing";
		$this->assertEquals($this->bh->nameIsBanned($name), false);
	}
}
