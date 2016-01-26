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

	#region wasPosted()
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
	#endregion

	#region isHttps()
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
	#endregion

	#region pathInfo()

	public function testPathInfoUnset()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(0, count($pathInfo));
	}

	public function testPathInfoEmpty()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => ''));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(0, count($pathInfo));
	}

	public function testPathInfoSlash()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => '/'));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(0, count($pathInfo));
	}

	public function testPathInfoDoubleSlash()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => '//'));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(0, count($pathInfo));
	}

	public function testPathInfoOneItem()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => '/foo'));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(1, count($pathInfo));
		$this->assertEquals('foo', $pathInfo[0]);
	}

	public function testPathInfoOneItemDoubleSlash()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => '//foo'));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(1, count($pathInfo));
		$this->assertEquals('foo', $pathInfo[0]);
	}

	public function testPathInfoTwoItems()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => '/foo/bar'));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(2, count($pathInfo));
		$this->assertEquals('foo', $pathInfo[0]);
		$this->assertEquals('bar', $pathInfo[1]);
	}

	public function testPathInfoTwoItemsDoubleSlash()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('PATH_INFO' => '/foo//bar'));
		$pathInfo = WebRequest::pathInfo();
		$this->assertEquals(2, count($pathInfo));
		$this->assertEquals('foo', $pathInfo[0]);
		$this->assertEquals('bar', $pathInfo[1]);
	}

	#endregion

	#region remoteAddress() and forwardedAddress()

	public function testRemoteAddressUnset()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());
		$remoteAddress = WebRequest::remoteAddress();
		$this->assertNull($remoteAddress);
	}

	public function testRemoteAddressSet()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('REMOTE_ADDR' => '1.2.3.4'));
		$remoteAddress = WebRequest::remoteAddress();
		$this->assertEquals('1.2.3.4', $remoteAddress);
	}

	public function testForwardedAddressUnset()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array());
		$forwardedAddress = WebRequest::forwardedAddress();
		$this->assertNull($forwardedAddress);
	}

	public function testForwardedAddressSet()
	{
		$this->globalState->method('getServerSuperGlobal')->willReturn(array('HTTP_X_FORWARDED_FOR' => '1.2.3.4'));
		$forwardedAddress = WebRequest::forwardedAddress();
		$this->assertEquals('1.2.3.4', $forwardedAddress);
	}

	#endregion

	#region string

	public function testPostString()
	{
		$this->globalState->method('getPostSuperGlobal')->willReturn(array('foo' => 'bar', 'baz' => ''));

		$actual = WebRequest::postString('foo');
		$this->assertEquals('bar', $actual);

		$actual = WebRequest::postString('baz');
		$this->assertNull($actual);
	}

	public function testGetString()
	{
		$this->globalState->method('getGetSuperGlobal')->willReturn(array('foo' => 'bar', 'baz' => ''));

		$actual = WebRequest::getString('foo');
		$this->assertEquals('bar', $actual);

		$actual = WebRequest::getString('baz');
		$this->assertNull($actual);
	}

	#endregion

	#region boolean
	public function testGetBoolean()
	{
		$this->globalState->method('getGetSuperGlobal')->willReturn(array(
			'foo'    => '',
			'bar'    => 'something',
			'baz'    => 'on',
			'quux'   => 'yes',
			'grunt'  => true,
			'wubble' => 1,
			'snork'  => -1, // not sure about this

			'qux'     => 'off',
			'flob'    => 'no',
			'norf'    => false,
			'blurgle' => 0,

			'quuux' => null, // it's present, so it counts

			'ook' => array() // it's present, so it counts
			// quuuux is not present on purpose.
		));

		$this->assertTrue(WebRequest::getBoolean('foo'));
		$this->assertTrue(WebRequest::getBoolean('bar'));
		$this->assertTrue(WebRequest::getBoolean('baz'));
		$this->assertFalse(WebRequest::getBoolean('qux'));
		$this->assertTrue(WebRequest::getBoolean('quux'));
		$this->assertTrue(WebRequest::getBoolean('quuux'));
		$this->assertFalse(WebRequest::getBoolean('quuuux'));
		$this->assertFalse(WebRequest::getBoolean('norf'));
		$this->assertTrue(WebRequest::getBoolean('grunt'));
		$this->assertFalse(WebRequest::getBoolean('flob'));
		$this->assertTrue(WebRequest::getBoolean('wubble'));
		$this->assertFalse(WebRequest::getBoolean('blurgle'));
		$this->assertTrue(WebRequest::getBoolean('snork'));
		$this->assertTrue(WebRequest::getBoolean('ook'));
	}

	public function testPostBoolean()
	{
		$this->globalState->method('getPostSuperGlobal')->willReturn(array(
			'foo'    => '',
			'bar'    => 'something',
			'baz'    => 'on',
			'quux'   => 'yes',
			'grunt'  => true,
			'wubble' => 1,
			'snork'  => -1,

			'qux'     => 'off',
			'flob'    => 'no',
			'norf'    => false,
			'blurgle' => 0,

			'quuux' => null, // it's present, so it counts.

			'ook' => array() // it's present, so it counts.
			// quuuux is not present on purpose.
		));

		$this->assertTrue(WebRequest::postBoolean('foo'));
		$this->assertTrue(WebRequest::postBoolean('bar'));
		$this->assertTrue(WebRequest::postBoolean('baz'));
		$this->assertFalse(WebRequest::postBoolean('qux'));
		$this->assertTrue(WebRequest::postBoolean('quux'));
		$this->assertTrue(WebRequest::postBoolean('quuux'));
		$this->assertFalse(WebRequest::postBoolean('quuuux'));
		$this->assertFalse(WebRequest::postBoolean('norf'));
		$this->assertTrue(WebRequest::postBoolean('grunt'));
		$this->assertFalse(WebRequest::postBoolean('flob'));
		$this->assertTrue(WebRequest::postBoolean('wubble'));
		$this->assertFalse(WebRequest::postBoolean('blurgle'));
		$this->assertTrue(WebRequest::postBoolean('snork'));
		$this->assertTrue(WebRequest::postBoolean('ook'));
	}
	#endregion

	#region int

	public function testPostInt()
	{
		$this->globalState->method('getPostSuperGlobal')->willReturn(array(
			// Simple tests
			'foo'    => 1,
			'bar'    => 0,
			'baz'    => -1,

			// From string
			'qux'    => '1',
			'quux'   => '0',
			'quuux'  => '-1',

			// Odd strings
			'wubble' => '0xF',
			'snork'  => '010',
			'garply' => '0b10',

			'norf'    => 'not a number',
			'flob'    => '',

			// number, but not an int
			'blurgle' => 0.1,

			// Other types
			'grunt'   => true,
			'ook'     => array(),
			'toto'    => null,
			// pip not present on purpose
		));

		$this->assertTrue(is_int(WebRequest::postInt('foo')));
		$this->assertEquals(1, WebRequest::postInt('foo'));

		$this->assertTrue(is_int(WebRequest::postInt('bar')));
		$this->assertEquals(0, WebRequest::postInt('bar'));

		$this->assertTrue(is_int(WebRequest::postInt('baz')));
		$this->assertEquals(-1, WebRequest::postInt('baz'));

		$this->assertTrue(is_int(WebRequest::postInt('qux')));
		$this->assertEquals(1, WebRequest::postInt('qux'));

		$this->assertTrue(is_int(WebRequest::postInt('quux')));
		$this->assertEquals(0, WebRequest::postInt('quux'));

		$this->assertTrue(is_int(WebRequest::postInt('quuux')));
		$this->assertEquals(-1, WebRequest::postInt('quuux'));

		$this->assertFalse(is_int(WebRequest::postInt('wubble')));
		$this->assertNull(WebRequest::postInt('wubble'));

		$this->assertFalse(is_int(WebRequest::postInt('snork')));
		$this->assertNull(WebRequest::postInt('snork'));

		$this->assertFalse(is_int(WebRequest::postInt('garply')));
		$this->assertNull(WebRequest::postInt('garply'));

		$this->assertFalse(is_int(WebRequest::postInt('norf')));
		$this->assertNull(WebRequest::postInt('norf'));

		$this->assertFalse(is_int(WebRequest::postInt('flob')));
		$this->assertNull(WebRequest::postInt('flob'));

		$this->assertFalse(is_int(WebRequest::postInt('blurgle')));
		$this->assertNull(WebRequest::postInt('blurgle'));

		$this->assertTrue(is_int(WebRequest::postInt('grunt')));
		$this->assertEquals(1, WebRequest::postInt('grunt'));

		$this->assertFalse(is_int(WebRequest::postInt('ook')));
		$this->assertNull(WebRequest::postInt('ook'));

		$this->assertFalse(is_int(WebRequest::postInt('toto')));
		$this->assertNull(WebRequest::postInt('toto'));

		$this->assertFalse(is_int(WebRequest::postInt('pip')));
		$this->assertNull(WebRequest::postInt('pip'));
	}

	public function testGetInt()
	{
		$this->globalState->method('getGetSuperGlobal')->willReturn(array(
			// Simple tests
			'foo'    => 1,
			'bar'    => 0,
			'baz'    => -1,

			// From string
			'qux'    => '1',
			'quux'   => '0',
			'quuux'  => '-1',

			// Odd strings
			'wubble' => '0xF',
			'snork'  => '010',
			'garply' => '0b10',

			'norf'    => 'not a number',
			'flob'    => '',

			// number, but not an int
			'blurgle' => 0.1,

			// Other types
			'grunt'   => true,
			'ook'     => array(),
			'toto'    => null,
			// pip not present on purpose
		));

		$this->assertTrue(is_int(WebRequest::getInt('foo')));
		$this->assertEquals(1, WebRequest::getInt('foo'));

		$this->assertTrue(is_int(WebRequest::getInt('bar')));
		$this->assertEquals(0, WebRequest::getInt('bar'));

		$this->assertTrue(is_int(WebRequest::getInt('baz')));
		$this->assertEquals(-1, WebRequest::getInt('baz'));

		$this->assertTrue(is_int(WebRequest::getInt('qux')));
		$this->assertEquals(1, WebRequest::getInt('qux'));

		$this->assertTrue(is_int(WebRequest::getInt('quux')));
		$this->assertEquals(0, WebRequest::getInt('quux'));

		$this->assertTrue(is_int(WebRequest::getInt('quuux')));
		$this->assertEquals(-1, WebRequest::getInt('quuux'));

		$this->assertFalse(is_int(WebRequest::getInt('wubble')));
		$this->assertNull(WebRequest::getInt('wubble'));

		$this->assertFalse(is_int(WebRequest::getInt('snork')));
		$this->assertNull(WebRequest::getInt('snork'));

		$this->assertFalse(is_int(WebRequest::getInt('garply')));
		$this->assertNull(WebRequest::getInt('garply'));

		$this->assertFalse(is_int(WebRequest::getInt('norf')));
		$this->assertNull(WebRequest::getInt('norf'));

		$this->assertFalse(is_int(WebRequest::getInt('flob')));
		$this->assertNull(WebRequest::getInt('flob'));

		$this->assertFalse(is_int(WebRequest::getInt('blurgle')));
		$this->assertNull(WebRequest::getInt('blurgle'));

		$this->assertTrue(is_int(WebRequest::getInt('grunt')));
		$this->assertEquals(1, WebRequest::getInt('grunt'));

		$this->assertFalse(is_int(WebRequest::getInt('ook')));
		$this->assertNull(WebRequest::getInt('ook'));

		$this->assertFalse(is_int(WebRequest::getInt('toto')));
		$this->assertNull(WebRequest::getInt('toto'));

		$this->assertFalse(is_int(WebRequest::getInt('pip')));
		$this->assertNull(WebRequest::getInt('pip'));
	}

	#endregion

	#region email

	#endregion
}