<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Fragments;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Waca\DataObjects\Request;
use Waca\DataObjects\User;
use Waca\Fragments\RequestData;
use Waca\Providers\GlobalState\FakeGlobalStateProvider;
use Waca\WebRequest;

/**
 * Tests the isAllowedPrivateData method to check the user is allowed to access private data correctly.
 *
 * @package Waca\Tests\Fragments
 */
class RequestDataPrivateDataTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|RequestData */
    private $requestDataMock;
    /** @var ReflectionMethod */
    private $reflectionMethod;
    /** @var PHPUnit_Framework_MockObject_MockObject|Request */
    private $request;
    /** @var PHPUnit_Framework_MockObject_MockObject|User */
    private $currentUser;

    public function setUp()
    {
        $this->requestDataMock = $this->getMockForTrait(RequestData::class);

        $this->reflectionMethod = new ReflectionMethod(get_class($this->requestDataMock), 'isAllowedPrivateData');
        $this->reflectionMethod->setAccessible(true);

        $this->request = $this->getMockBuilder(Request::class)->getMock();
        $this->currentUser = $this->getMockBuilder(User::class)->getMock();
    }

    /**
     * This checks against the standard security barrier
     */
    public function testBarrierCall()
    {
        // arrange
        $this->requestDataMock->expects($this->once())->method('barrierTest')->willReturn(true);

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertTrue($result);
    }

    public function testReserved()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturnCallback(
            function($right) {
                if($right === 'seePrivateDataWhenReserved') return true;
                return false;
            }
        );

        $this->request->method('getReserved')->willReturn(3);
        $this->currentUser->method('getId')->willReturn(3);

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertTrue($result);
    }

    public function testReservedNoRight()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturn(false);

        $this->request->method('getReserved')->willReturn(3);
        $this->currentUser->method('getId')->willReturn(3);

        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertFalse($result);
    }

    public function testNotReservedByMe()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturnCallback(
            function($right) {
                if($right === 'seePrivateDataWhenReserved') return true;
                return false;
            }
        );

        $this->request->method('getReserved')->willReturn(4);
        $this->request->method('getRevealHash')->willReturn('1232');
        $this->currentUser->method('getId')->willReturn(3);

        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);
        $data = &$state->getGetSuperGlobal();
        $data['hash'] = '123';

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertFalse($result);
    }

    public function testNotReserved()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturnCallback(
            function($right) {
                if($right === 'seePrivateDataWhenReserved') return true;
                return false;
            }
        );

        $this->request->method('getReserved')->willReturn(null);
        $this->request->method('getRevealHash')->willReturn('1223');
        $this->currentUser->method('getId')->willReturn(3);

        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);
        $data = &$state->getGetSuperGlobal();
        $data['hash'] = '123';

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertFalse($result);
    }

    public function testRevealHashNoRight()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturn(false);

        $this->request->method('getReserved')->willReturn(null);
        $this->request->method('getRevealHash')->willReturn('123');
        $this->currentUser->method('getId')->willReturn(3);

        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);
        $data = &$state->getGetSuperGlobal();
        $data['hash'] = '123';

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertFalse($result);
    }

    public function testRevealHash()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturnCallback(
            function($right) {
                if($right === 'seePrivateDataWithHash') return true;
                return false;
            }
        );

        $this->request->method('getReserved')->willReturn(null);
        $this->request->method('getRevealHash')->willReturn('123');
        $this->currentUser->method('getId')->willReturn(3);

        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);
        $data = &$state->getGetSuperGlobal();
        $data['hash'] = '123';

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertTrue($result);
    }

    public function testRevealHashWrong()
    {
        // arrange
        $this->requestDataMock->method('barrierTest')->willReturnCallback(
            function($right) {
                if($right === 'seePrivateDataWithHash') return true;
                return false;
            }
        );

        $this->request->method('getReserved')->willReturn(null);
        $this->request->method('getRevealHash')->willReturn('1323');
        $this->currentUser->method('getId')->willReturn(3);

        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);
        $data = &$state->getGetSuperGlobal();
        $data['hash'] = '123';

        // act
        $result = $this->reflectionMethod->invoke($this->requestDataMock, $this->request, $this->currentUser);

        // assert
        $this->assertFalse($result);
    }
}