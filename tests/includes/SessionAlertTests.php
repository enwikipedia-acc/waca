<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Waca\Providers\GlobalState\GlobalStateProvider;
use Waca\SessionAlert;
use Waca\Providers\GlobalState\FakeGlobalStateProvider;
use Waca\WebRequest;

class SessionAlertTests extends TestCase
{
    /**
     * @var GlobalStateProvider|PHPUnit_Framework_MockObject_MockObject
     */
    private $globalState;

    public function setUp() : void
    {
        $this->globalState = $this->getMockBuilder(GlobalStateProvider::class)->getMock();
        WebRequest::setGlobalStateProvider($this->globalState);
    }

    public function testBasicSetup()
    {
        $test = new SessionAlert('foo', 'bar', 'baz', true, false);

        $this->assertEquals($test->getMessage(), 'foo');
        $this->assertEquals($test->getTitle(), 'bar');
        $this->assertEquals($test->getType(), 'baz');
        $this->assertTrue($test->isClosable());
        $this->assertFalse($test->isBlock());
    }

    public function testAppend()
    {
        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);

        $data = &$state->getSessionSuperGlobal();

        $alert = new SessionAlert('foo', 'bar', 'baz', true, false);
        SessionAlert::append($alert);

        $this->assertEquals(count($data['alerts']), 1);
        $this->assertEquals($data['alerts'][0], serialize($alert));

        $alert = new SessionAlert('foo', 'bar', 'baz', true, false);
        SessionAlert::append($alert);

        $this->assertEquals(count($data['alerts']), 2);
        $this->assertEquals($data['alerts'][0], serialize($alert));
        $this->assertEquals($data['alerts'][1], serialize($alert));
    }

    public function testClear()
    {
        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);

        $alert = new SessionAlert('foo', 'bar', 'baz', true, false);

        $data = &$state->getSessionSuperGlobal();
        $data['alerts'] = array(
            serialize($alert),
        );

        SessionAlert::clearAlerts();

        $this->assertFalse(isset($data['alerts']));
    }

    public function testGetAlerts()
    {
        $state = new FakeGlobalStateProvider();
        WebRequest::setGlobalStateProvider($state);

        $alert = new SessionAlert('foo', 'bar', 'baz', true, false);

        $data = &$state->getSessionSuperGlobal();
        $data['alerts'] = array(
            serialize($alert),
            serialize($alert),
        );

        $alertData = SessionAlert::getAlerts();

        $this->assertTrue(is_array($alertData));
        $this->assertEquals(count($alertData), 2);
        $this->assertEquals($alertData[0], $alert);
        $this->assertEquals($alertData[1], $alert);
    }
}