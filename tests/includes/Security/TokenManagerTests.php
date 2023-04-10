<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Security;

use PHPUnit\Framework\TestCase;
use Waca\Security\TokenManager;
use Waca\Providers\GlobalState\FakeGlobalStateProvider;
use Waca\WebRequest;

class TokenManagerTests extends TestCase
{
    /** @var TokenManager */
    private $tokenManager;
    /** @var \Waca\Providers\GlobalState\FakeGlobalStateProvider */
    private $stateProvider;

    public function setUp() : void
    {
        $this->stateProvider = new FakeGlobalStateProvider();
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