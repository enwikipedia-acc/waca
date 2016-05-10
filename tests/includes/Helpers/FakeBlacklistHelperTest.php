<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Created by PhpStorm.
 * User: bowkerm
 * Date: 5/10/2016
 * Time: 3:02 PM
 */

namespace Waca\Tests\Helpers;

use \PhpUnit_Framework_TestCase;
use \Waca\Helpers\FakeBlacklistHelper;

class FakeBlacklistHelperTest extends PHPUnit_Framework_TestCase
{
	private $fblh;

	public function setUp() {
		$this->fblh = new FakeBlacklistHelper();
	}

	public function tearDown() {
		$this->fblh = NULL;
	}

	public function testIsBlacklisted() {
		$username = "fuck";

		$this->assertEquals(false, $this->fblh->isBlacklisted($username));
		$this->assertNotEquals(true, $this->fblh->isBlacklisted($username));
		$this->assertNotEquals("", $this->fblh->isBlacklisted($username));

		$username = "poop";

		$this->assertEquals(false, $this->fblh->isBlacklisted($username));
		$this->assertNotEquals(true, $this->fblh->isBlacklisted($username));
		$this->assertNotEquals("", $this->fblh->isBlacklisted($username));
	}
}
