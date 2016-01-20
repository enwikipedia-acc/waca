<?php
namespace Waca\Tests;

use User;
use Waca\SecurityConfiguration;

class SecurityConfigurationTest extends \PHPUnit_Framework_TestCase
{
	// @var  \PHPUnit_Framework_MockObject_MockObject */
	private $user;

	public function setUp()
	{
		$this->user = $this->getMockBuilder(User::class)->getMock();
		$this->user->method('isAdmin')->willReturn(false);
		$this->user->method('isUser')->willReturn(false);
		$this->user->method('isCheckuser')->willReturn(false);
		$this->user->method('isDeclined')->willReturn(false);
		$this->user->method('isSuspended')->willReturn(false);
		$this->user->method('isNew')->willReturn(false);
		$this->user->method('isCommunityUser')->willReturn(false);
	}

	public function testAllowsAdmin()
	{
		$this->user->method('isAdmin')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsUser()
	{
		$this->user->method('isUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setUser(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsCheckuser()
	{
		$this->user->method('isCheckuser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setCheckuser(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsDeclined()
	{
		$this->user->method('isDeclined')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setDeclined(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsSuspended()
	{
		$this->user->method('isSuspended')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setSuspended(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsNew()
	{
		$this->user->method('isNew')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setNew(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsCommunity()
	{
		$this->user->method('isCommunityUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setCommunity(SecurityConfiguration::ALLOW);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsAdminWithNonApplicableDeny()
	{
		$this->user->method('isAdmin')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::ALLOW)->setNew(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsUserWithNonApplicableDeny()
	{
		$this->user->method('isUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setUser(SecurityConfiguration::ALLOW)->setNew(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsCheckuserWithNonApplicableDeny()
	{
		$this->user->method('isCheckuser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setCheckuser(SecurityConfiguration::ALLOW)->setNew(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsDeclinedWithNonApplicableDeny()
	{
		$this->user->method('isDeclined')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setDeclined(SecurityConfiguration::ALLOW)->setNew(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsSuspendedWithNonApplicableDeny()
	{
		$this->user->method('isSuspended')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setSuspended(SecurityConfiguration::ALLOW)->setNew(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsNewWithNonApplicableDeny()
	{
		$this->user->method('isNew')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setNew(SecurityConfiguration::ALLOW)->setAdmin(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsCommunityWithNonApplicableDeny()
	{
		$this->user->method('isCommunityUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setCommunity(SecurityConfiguration::ALLOW)->setNew(SecurityConfiguration::DENY);
		$this->assertTrue($config->allows($this->user));
	}

	public function testAllowsAdminWithApplicableDeny()
	{
		$this->user->method('isAdmin')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setAdmin(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsUserWithApplicableDeny()
	{
		$this->user->method('isUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setUser(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsCheckuserWithApplicableDeny()
	{
		$this->user->method('isCheckuser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setCheckuser(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsDeclinedWithApplicableDeny()
	{
		$this->user->method('isDeclined')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setDeclined(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsSuspendedWithApplicableDeny()
	{
		$this->user->method('isSuspended')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setSuspended(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsNewWithApplicableDeny()
	{
		$this->user->method('isNew')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setNew(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsCommunityWithApplicableDeny()
	{
		$this->user->method('isCommunityUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$config->setCommunity(SecurityConfiguration::DENY);
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsAdminWithDefault()
	{
		$this->user->method('isAdmin')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsUserWithDefault()
	{
		$this->user->method('isUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsCheckuserWithDefault()
	{
		$this->user->method('isCheckuser')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsDeclinedWithDefault()
	{
		$this->user->method('isDeclined')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsSuspendedWithDefault()
	{
		$this->user->method('isSuspended')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsNewWithDefault()
	{
		$this->user->method('isNew')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}

	public function testAllowsCommunityWithDefault()
	{
		$this->user->method('isCommunityUser')->willReturn(true);
		$config = new SecurityConfiguration();
		$this->assertFalse($config->allows($this->user));
	}
}