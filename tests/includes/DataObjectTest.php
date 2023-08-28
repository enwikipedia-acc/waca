<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit\Framework\TestCase;

class DataObjectTest extends TestCase
{
    private $do;
    private $dbh;

    public function setUp(): void
    {
        $this->do = $this->getMockForAbstractClass("\Waca\DataObject");

        $this->dbh = $this->getMockBuilder('\Waca\PdoDatabase')
            ->setMockClassName('PdoDatabase')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testID()
    {
        $this->assertTrue($this->do->isNew());
        $this->assertEquals($this->do->getID(), 0);
    }

    public function testUpdateVersion()
    {
        $this->assertEquals($this->do->getUpdateVersion(), 0);

        $this->assertEquals($this->do->setUpdateVersion(42), null);

        $this->assertEquals($this->do->getUpdateVersion(), 42);
    }

    public function testDatabase()
    {
        $this->assertNull($this->do->getDatabase());

        $this->assertNull($this->do->setDatabase($this->dbh));

        $this->assertEquals($this->do->getDatabase(), $this->dbh);
    }
}
