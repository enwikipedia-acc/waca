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
use Waca\Helpers\FakeBlacklistHelper;

class FakeBlacklistHelperTest extends TestCase
{
    /** @var FakeBlacklistHelper */
    private $blacklistHelper;

    public function setUp() : void
    {
        $this->blacklistHelper = new FakeBlacklistHelper();
    }

    public function tearDown() : void
    {
        $this->blacklistHelper = null;
    }

    public function testIsBlacklisted()
    {
        $username = 'badname';

        $this->assertEquals(false, $this->blacklistHelper->isBlacklisted($username));
    }
}
