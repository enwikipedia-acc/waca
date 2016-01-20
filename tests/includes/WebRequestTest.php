<?php
namespace Waca\Tests;

use Waca\Tests\TestUtilities\GlobalStateProviderMock;
use Waca\WebRequest;

class WebRequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var GlobalStateProviderMock
	 */
	private $globalState;

	public function setUp()
	{
		$this->globalState = new GlobalStateProviderMock();
		WebRequest::setGlobalStateProvider($this->globalState);
	}

	public function testWasPostedNoRequestMethod()
	{
		$this->assertFalse(WebRequest::wasPosted());
	}

	public function testWasPostedGetMethod(){
		$this->globalState->setServerSuperGlobal(array('REQUEST_METHOD' => 'GET'));
		$this->assertFalse(WebRequest::wasPosted());
	}

	public function testWasPostedPostMethod(){
		$this->globalState->setServerSuperGlobal(array('REQUEST_METHOD' => 'POST'));
		$this->assertTrue(WebRequest::wasPosted());
	}

	public function testIsHttpsNo(){
		$this->assertFalse(WebRequest::isHttps());
	}

	public function testIsHttpsYes(){
		$this->globalState->setServerSuperGlobal(array('HTTPS' => 'yes'));
		$this->assertTrue(WebRequest::isHttps());
	}

	/**
	 * PHP Docs say set to a non-empty value. This will, of course, depend entirely on the SAPI.
	 */
	public function testIsHttpsAlsoYes(){
		$this->globalState->setServerSuperGlobal(array('HTTPS' => 'yay'));
		$this->assertTrue(WebRequest::isHttps());
	}

	/**
	 * PHP Docs note that ISAPI sets it to "off" when not HTTPS. Grrrrr....
	 *
	 * https://secure.php.net/reserved.variables.server
	 */
	public function testIsHttpsIIS(){
		$this->globalState->setServerSuperGlobal(array('HTTPS' => 'off'));
		$this->assertFalse(WebRequest::isHttps());
	}

	/**
	 * We can do https-y things if the connection is encrypted between the proxy and the client.
	 */
	public function testIsHttpsXffProto() {
		$this->globalState->setServerSuperGlobal(array('HTTP_X_FORWARDED_PROTO' => 'https'));
		$this->assertTrue(WebRequest::isHttps());

		$this->globalState->setServerSuperGlobal(array('HTTP_X_FORWARDED_PROTO' => 'https', 'HTTPS' => 'yes'));
		$this->assertTrue(WebRequest::isHttps());
	}

	/**
	 * Naughty! This is for:
	 *  [client] <= http => [proxy] <= https => [server]
	 */
	public function testIsHttpsNotXffProto() {
		$this->globalState->setServerSuperGlobal(array('HTTP_X_FORWARDED_PROTO' => 'http', 'HTTPS' => 'yes'));
		$this->assertFalse(WebRequest::isHttps());
	}

}