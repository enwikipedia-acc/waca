<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/


class DataObjectTest extends PHPUnit_Framework_TestCase
{
	public $do;

	public function setUp() {
		$this->do = $this->getMockForAbstractClass("\Waca\DataObject");
	}

	public function testID() {
		$this->assertTrue($this->do->isNew());
		$this->assertEquals($this->do->getID(), 0);
	}

	public function testUpdateVersion()
	{
		/*
		$stub = $this->getMockForAbstractClass('AbstractClass');
		$stub->expects($this->any())
			->method('abstractMethod')
			->will($this->returnValue(TRUE));

		$this->assertTrue($stub->concreteMethod());
		*/

		$this->assertEquals($this->do->getUpdateVersion(), 0);

		$this->assertEquals($this->do->setUpdateVersion(42), null);

		$this->assertEquals($this->do->getUpdateVersion(), 42);
	}

	public function testDatabase() {
		$this->assertNull($this->do->getDatabase());
	}
}
