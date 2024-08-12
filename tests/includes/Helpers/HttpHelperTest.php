<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Waca\Helpers\HttpHelper;

class HttpHelperTest extends TestCase
{
    private $hh;

    public function setUp() : void
    {
    }

    public function tearDown() : void
    {
        $this->hh = null;
    }

    public function testGet()
    {
        $this->markTestIncomplete("Still being built");
    }

    public function testPost()
    {
        $this->markTestIncomplete("Still being built");
    }
}
