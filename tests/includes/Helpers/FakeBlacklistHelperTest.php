<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Waca\DataObjects\Domain;
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

        $this->assertEquals(false, $this->blacklistHelper->isBlacklisted($username, new Domain()));
    }
}
