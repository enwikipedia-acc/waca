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
		$tokenD = $this->tokenManager->getNewToken();

		$this->assertNotEquals($tokenA->getTokenData(), $tokenB->getTokenData());
		$this->assertNotEquals($tokenA->getTokenData(), $tokenC->getTokenData());
		$this->assertNotEquals($tokenA->getTokenData(), $tokenD->getTokenData());

		$this->assertFalse($tokenA->isUsed());
		$this->assertFalse($tokenB->isUsed());
		$this->assertFalse($tokenC->isUsed());
		$this->assertFalse($tokenD->isUsed());

		// should validate...
		$this->assertTrue($this->tokenManager->validateToken($tokenA->getTokenData(), 'foo'));
		$this->assertTrue($this->tokenManager->validateToken($tokenD->getTokenData()));
		// ...once...
		$this->assertFalse($this->tokenManager->validateToken($tokenA->getTokenData(), 'foo'));
		$this->assertFalse($this->tokenManager->validateToken($tokenD->getTokenData()));
	}

	public function testBadToken()
	{
		$token = $this->tokenManager->getNewToken('foo');

		$this->assertFalse($this->tokenManager->validateToken($token->getTokenData(), 'bar'));

		$this->assertFalse($this->tokenManager->validateToken('nonexistent token', 'bar'));
		$this->assertFalse($this->tokenManager->validateToken(null, 'bar'));
		$this->assertFalse($this->tokenManager->validateToken(false, 'bar'));
		$this->assertFalse($this->tokenManager->validateToken('', 'bar'));
		$this->assertFalse($this->tokenManager->validateToken('nonexistent token'));
		$this->assertFalse($this->tokenManager->validateToken(null));
		$this->assertFalse($this->tokenManager->validateToken(false));
		$this->assertFalse($this->tokenManager->validateToken(''));
	}
}