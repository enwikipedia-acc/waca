<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use \PHPUnit_Framework_TestCase;

class DataObjectTest extends PHPUnit_Framework_TestCase
{
	private $do;
	private $dbh;

	public function setUp() {
		$this->do = $this->getMockForAbstractClass("\Waca\DataObject");

		$this->dbh = $this->getMockBuilder('\Waca\PdoDatabase')
			->setMockClassName('PdoDatabase')
			->disableOriginalConstructor()
			->getMock();

	}

	public function testID() {
		$this->assertTrue($this->do->isNew());
		$this->assertEquals($this->do->getID(), 0);
	}

	public function testUpdateVersion()
	{
		$this->assertEquals($this->do->getUpdateVersion(), 0);

		$this->assertEquals($this->do->setUpdateVersion(42), null);

		$this->assertEquals($this->do->getUpdateVersion(), 42);
	}

	public function testDatabase() {
		$this->assertNull($this->do->getDatabase());

		$this->assertNull($this->do->setDatabase($this->dbh));

		$this->assertEquals($this->do->getDatabase(), $this->dbh);
	}
}
