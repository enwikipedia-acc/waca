<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use \PHPUnit_Framework_TestCase;
use \Waca\Helpers\HttpHelper;

class HttpHelperTest extends PHPUnit_Framework_TestCase
{
	private $hh;

	public function setUp() {

	}

	public function tearDown() {
		$this->hh = NULL;
	}

	public function testGet() {
		$this->markTestIncomplete("Still being built");
	}

	public function testPost() {
		$this->markTestIncomplete("Still being built");
	}
}
