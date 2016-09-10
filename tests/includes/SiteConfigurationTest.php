<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 9/10/16
 * Time: 00:29
 */

namespace Waca\Tests;

use Waca\SiteConfiguration;

class SiteConfigurationTest extends \PHPUnit_Framework_TestCase
{
	private $si;

	function setUp() {
		$this->si = new SiteConfiguration();
	}

	function tearDown() {
		unset($this->si);
	}

	function testSetUp() {
		$this->assertInstanceOf('\Waca\SiteConfiguration', $this->si);
	}

	function testBaseUrl() {
		$newValue = "http://localhost/testAwesome/";

		$this->assertEquals($this->si->getBaseUrl(), null);

		$this->assertInstanceOf('Waca\SiteConfiguration', $this->si->setBaseUrl($newValue));
		$this->assertEquals($this->si->getBaseUrl(), $newValue);
	}

	function testFilePath() {
		$newValue = "/var/www/waca";

		$this->assertEquals($this->si->getFilePath(), null);

		$this->assertInstanceOf('Waca\SiteConfiguration', $this->si->setFilePath($newValue));
		$this->assertEquals($this->si->getFilePath(), $newValue);
	}

	function testSchemaVersion() {
		$currentVersion = 22;   // NOTE: Update when you update the main file... otherwise this test will fail!

		$this->assertEquals($this->si->getSchemaVersion(), $currentVersion);
		$this->assertNotEquals($this->si->getSchemaVersion(), null);
	}

	function testDebuggingTraceEnabled() {
		$newValue = true;

		$this->assertEquals($this->si->getDebuggingTraceEnabled(), null);

		$this->assertInstanceOf('Waca\SiteConfiguration', $this->si->setDebuggingTraceEnabled($newValue));
		$this->assertEquals($this->si->getDebuggingTraceEnabled(), $newValue);

		$newValue = false;

		$this->assertInstanceOf('Waca\SiteConfiguration', $this->si->setDebuggingTraceEnabled($newValue));
		$this->assertEquals($this->si->getDebuggingTraceEnabled(), $newValue);
	}
}
