<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit_Framework_TestCase;
use Waca\Helpers\FakeBlacklistHelper;

class FakeBlacklistHelperTest extends PHPUnit_Framework_TestCase
{
    /** @var FakeBlacklistHelper */
    private $blacklistHelper;

    public function setUp()
    {
        $this->blacklistHelper = new FakeBlacklistHelper();
    }

    public function tearDown()
    {
        $this->blacklistHelper = null;
    }

    public function testIsBlacklisted()
    {
        $username = 'badname';

        $this->assertEquals(false, $this->blacklistHelper->isBlacklisted($username));
    }
}
