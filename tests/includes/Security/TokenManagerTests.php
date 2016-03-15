<?php

namespace Waca\Tests\Security;

use PHPUnit_Framework_TestCase;
use Waca\Security\TokenManager;
use Waca\Tests\Utility\TestStateProvider;
use Waca\WebRequest;

class TokenManagerTests extends PHPUnit_Framework_TestCase
{
	/** @var TokenManager */
	private $tokenManager;
	/** @var TestStateProvider */
	private $stateProvider;

	public function setUp()
	{
		$this->stateProvider = new TestStateProvider();
		$this->stateProvider->session = array();

		WebRequest::setGlobalStateProvider($this->stateProvider);
		$this->tokenManager = new TokenManager();
	}

	public function testTokenGeneration()
	{
		$tokenA = $this->tokenManager->getNewToken('foo');
		$tokenB = $this->tokenManager->getNewToken('bar');
		$tokenC = $this->tokenManager->getNewToken('foo');

		$this->assertEquals($tokenA->getTokenData(), $tokenC->getTokenData());
		$this->assertNotEquals($tokenA->getTokenData(), $tokenB->getTokenData());

		$this->assertFalse($tokenA->isUsed());
		$this->assertFalse($tokenB->isUsed());
		$this->assertFalse($tokenC->isUsed());

		// should validate...
		$this->assertTrue($this->tokenManager->validateToken('foo', $tokenA->getTokenData()));
		// ...once...
		$this->assertFalse($this->tokenManager->validateToken('foo', $tokenA->getTokenData()));
		$this->assertFalse($this->tokenManager->validateToken('foo', $tokenC->getTokenData()));
	}

	public function testBadToken()
	{
		$token = $this->tokenManager->getNewToken('foo');

		$this->assertFalse($this->tokenManager->validateToken('bar', $token->getTokenData()));
		$this->assertFalse($this->tokenManager->validateToken('bar', 'nonexistent token'));
		$this->assertFalse($this->tokenManager->validateToken('bar', null));
		$this->assertFalse($this->tokenManager->validateToken('bar', false));
		$this->assertFalse($this->tokenManager->validateToken('bar', ''));
	}
}