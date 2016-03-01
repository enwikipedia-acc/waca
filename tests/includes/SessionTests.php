<?php

namespace Waca\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\Providers\GlobalStateProvider;
use Waca\Session;
use Waca\WebRequest;

class SessionTests extends PHPUnit_Framework_TestCase
{
	/**
	 * @var GlobalStateProvider|PHPUnit_Framework_MockObject_MockObject
	 */
	private $globalState;

	public function setUp()
	{
		if (!extension_loaded('runkit')) {
			$this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');
			return;
		}

		$this->globalState = $this->getMockBuilder(GlobalStateProvider::class)->getMock();
		WebRequest::setGlobalStateProvider($this->globalState);
	}

	public function testSessionStartSecure()
	{
		// arrange
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTPS' => 'yes'));

		$sessionStartMock = new \PHPUnit_Extensions_MockFunction('session_start', $this);
		$sessionStartMock->expects($this->once());

		// act
		Session::start();

		// assert
		$this->assertEquals(ini_get('session.cookie_httponly'), 1);
		$this->assertEquals(ini_get('session.cookie_secure'), 1);

		// restore runkit functions
		$sessionStartMock->restore();
	}

	public function testSessionStartNonSecure()
	{
		// arrange
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());

		$sessionStartMock = new \PHPUnit_Extensions_MockFunction('session_start', $this);
		$sessionStartMock->expects($this->once());

		// act
		Session::start();

		// assert
		$this->assertEquals(ini_get('session.cookie_httponly'), 1);

		// restore runkit functions
		$sessionStartMock->restore();
	}

	public function testSessionDestroy()
	{
		// arrange
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());

		$sessionDestroyMock = new \PHPUnit_Extensions_MockFunction('session_destroy', $this);
		$sessionDestroyMock->expects($this->once());

		// act
		Session::destroy();

		// restore runkit functions
		$sessionDestroyMock->restore();
	}

	public function testSessionRestart()
	{
		// arrange
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTPS' => 'yes'));

		$sessionStartMock = new \PHPUnit_Extensions_MockFunction('session_start', $this);
		$sessionStartMock->expects($this->once());
		$sessionDestroyMock = new \PHPUnit_Extensions_MockFunction('session_destroy', $this);
		$sessionDestroyMock->expects($this->once());

		// act
		Session::restart();

		// assert
		$this->assertEquals(ini_get('session.cookie_httponly'), 1);
		$this->assertEquals(ini_get('session.cookie_secure'), 1);

		// restore runkit functions
		$sessionStartMock->restore();
		$sessionDestroyMock->restore();
	}
}