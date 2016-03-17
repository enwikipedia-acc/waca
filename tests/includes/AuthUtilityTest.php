<?php

namespace Waca\Tests;

use PHPUnit_Extensions_MockFunction;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use \Waca\AuthUtility;

class AuthUtilityTest extends \PHPUnit_Framework_TestCase
{
	private $au;

	public function setUp() {
		$this->au = new AuthUtility();

	}

	public function tearDown() {
		$this->au = null;
	}

	public function testTestCredentials() {
		$test_pw_1 = "Test Password";
		$test_res_1 = ":2:x:$2y$10$/Ifpg2PmJXwPyD0c.Q.Fr.JLv/4fpNDwQC3j2Rz5lU3yo2DCRdGu";
		$test_pw_2 = "t3stPassw0rd";
		$test_res_2 = ":2:x:$2y$10$2rYhyeRnkMTZWQTBTZwvXu6R4NFAR7dlPmgnluXjapCXOlDN/X6yK";
		$this->assertFalse($this->au->testCredentials("This string doesn't have a colon in front of it.", "So it should fail"));

		//$this->assertFalse($this->au->testCredentials($test_pw_1));
		//$this->assertTrue($this->au->testCredentials($test_res_1));
		//$this->assertFalse($this->au->testCredentials($test_pw_2));
		//$this->assertTrue($this->au->testCredentials($test_res_2));

		//$this->assert(0, "This is a test");

	}

	public function testIsCredentialVersionLatest() {
		// Happy path
		$this->assertTrue($this->au->isCredentialVersionLatest(":2:"));

		// Sad path
		$this->assertFalse($this->au->isCredentialVersionLatest(":1:"));
		$this->assertFalse($this->au->isCredentialVersionLatest(":3:"));
	}

	public function testEncryptPassword() {
		$test_pw_1 = "Test Password";
		$test_pw_2 = "t3stPassw0rd";

		// Happy path
		$this->assertTrue(password_verify($test_pw_1, explode(":", $this->au->encryptPassword($test_pw_1))[3]));
		$this->assertTrue(password_verify($test_pw_2, explode(":", $this->au->encryptPassword($test_pw_2))[3]));

		// Sad path
		$this->assertFalse(password_verify($test_pw_2, explode(":", $this->au->encryptPassword($test_pw_1))[3]));
		$this->assertFalse(password_verify($test_pw_1, explode(":", $this->au->encryptPassword($test_pw_2))[3]));
		$this->assertNotEquals($this->au->encryptPassword($test_pw_1), null);
		$this->assertNotEquals($this->au->encryptPassword($test_pw_2), null);


	}
}
