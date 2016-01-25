<?php
namespace Waca\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use Waca\Providers\GlobalStateProvider;
use Waca\WebRequest;

class WebRequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var GlobalStateProvider|PHPUnit_Framework_MockObject_MockObject
	 */
	private $globalState;

	public function setUp()
	{
		$this->globalState = $this->getMockBuilder(GlobalStateProvider::class)->getMock();
		WebRequest::setGlobalStateProvider($this->globalState);
	}

	public function testWasPostedNoRequestMethod()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());
		$this->assertFalse(WebRequest::wasPosted());
	}

	public function testWasPostedGetMethod()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('REQUEST_METHOD' => 'GET'));
		$this->assertFalse(WebRequest::wasPosted());
	}

	public function testWasPostedPostMethod()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('REQUEST_METHOD' => 'POST'));
		$this->assertTrue(WebRequest::wasPosted());
	}

	public function testIsHttpsNo()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());
		$this->assertFalse(WebRequest::isHttps());
	}

	public function testIsHttpsYes()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTPS' => 'yes'));
		$this->assertTrue(WebRequest::isHttps());
	}

	/**
	 * PHP Docs say set to a non-empty value. This will, of course, depend entirely on the SAPI.
	 */
	public function testIsHttpsAlsoYes()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTPS' => 'yay'));
		$this->assertTrue(WebRequest::isHttps());
	}

	/**
	 * PHP Docs note that ISAPI sets it to "off" when not HTTPS. Grrrrr....
	 *
	 * https://secure.php.net/reserved.variables.server
	 */
	public function testIsHttpsIIS()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTPS' => 'off'));
		$this->assertFalse(WebRequest::isHttps());
	}

	/**
	 * We can do https-y things if the connection is encrypted between the proxy and the client.
	 */
	public function testIsHttpsXffProto()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTP_X_FORWARDED_PROTO' => 'https'));
		$this->assertTrue(WebRequest::isHttps());
	}

	public function testIsHttpsXffProtoBoth()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'HTTP_X_FORWARDED_PROTO' => 'https',
			'HTTPS'                  => 'yes',
		));
		$this->assertTrue(WebRequest::isHttps());
	}

	/**
	 * Naughty! This is for:
	 *  [client] <= http => [proxy] <= https => [server]
	 */
	public function testIsHttpsNotXffProto()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array(
			'HTTP_X_FORWARDED_PROTO' => 'http',
			'HTTPS'                  => 'yes',
		));
		$this->assertFalse(WebRequest::isHttps());
	}
}