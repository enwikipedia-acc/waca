<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use \PHPUnit_Framework_TestCase;
use \PHPUnit_Extensions_MockFunction;
use \Waca\Helpers\BlacklistHelper;
use \Waca\Helpers\HttpHelper;

class BlacklistHelperTest extends PHPUnit_Framework_TestCase
{
	private $httpHelperMock;
	private $blh;

	public function setUp()
	{
		if (!extension_loaded('runkit')) {
			$this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');

			return;
		}
		$this->httpHelperMock = $this->getMockBuilder(HttpHelper::class)
			//->setMethods(array("fetchColumn","bindValue","execute","fetchObject"))
			->disableOriginalConstructor()
			->getMock();

		$this->blh = new BlacklistHelper($this->httpHelperMock, "http://127.0.0.1");
	}

	public function tearDown()
	{
		$this->blh = null;
	}

	public function testIsBlacklisted()
	{
		// First a bad entry.
		// Sorry about the language - MRB
		$this->httpHelperMock
			->expects($this->at(1))
			->method("get")
			->willReturn("a:1:{s:14:\"titleblacklist\";a:4:{s:6:\"result\";s:11:\"blacklisted\";s:6:\"reason\";s:527:\"<table id=\"mw-protectedpagetext\" class=\"plainlinks fmbox fmbox-warning\" role=\"presentation\"><tr><td class=\"mbox-text\">The user name \"Fuck\" [[Mediawiki talk:Titleblacklist|has been blacklisted]] from creation. Wikipedia [[WP:U|username policy]] does not allow names that are misleading, promotional, offensive or disruptive. Please select another username that complies with [[WP:U|policy]], or if you want to seek approval for a username, you can do so by filing a request at [[Wikipedia:Request an account]].</td></tr></table>\";s:7:\"message\";s:36:\"titleblacklist-forbidden-new-account\";s:4:\"line\";s:72:\".*FU[C(K]+K+                            &lt;newaccountonly|antispoof&gt;\";}}");

		$this->assertNotEquals("ok", $this->blh->isBlacklisted("fuck"));
		$this->assertEquals(".*FU[C(K]+K+                            &lt;newaccountonly|antispoof&gt;",
			$this->blh->isBlacklisted("fuck"));

		// Next an OK entry.
		// Sorry again about the language.  But I guess you can't help it when you're testing a title blacklist. - MRB
		$this->httpHelperMock
			->expects($this->at(1))
			->method("get")
			->willReturn("a:1:{s:14:\"titleblacklist\";a:1:{s:6:\"result\";s:2:\"ok\";}}");
		$this->assertEquals(false, $this->blh->isBlacklisted("poop"));
		$this->assertNotEquals("ok", $this->blh->isBlacklisted("poop"));
	}
}
