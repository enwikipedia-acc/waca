<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use Waca\StringFunctions;
use PHPUnit\Framework\TestCase;

class StringFunctionsTest extends TestCase
{
    /**
     * @var StringFunctions
     */
    private $e;

	public function setUp(): void
    {
        $this->e = new StringFunctions();
    }

	public function tearDown(): void
    {
        $this->e = null;
    }

    public function testUcFirst()
    {
        $this->assertEquals('Abc', $this->e->upperCaseFirst('abc'));
        $this->assertEquals('ABC', $this->e->upperCaseFirst('ABC'));
        $this->assertEquals('123', $this->e->upperCaseFirst('123'));

        $this->assertEquals('Trần Nguyễn Minh Huy', $this->e->upperCaseFirst('Trần Nguyễn Minh Huy'));
        $this->assertEquals('和平奮鬥救地球', $this->e->upperCaseFirst('和平奮鬥救地球'));
    }
}
