<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 3/3/16
 * Time: 18:38
 */

namespace Waca\Tests;

use PHPUnit_Extensions_MockFunction;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use \Waca\Offline;

class OfflineTest extends PHPUnit_Framework_TestCase
{
	private $offline;
	private $offMock;

	public function setUp() {
		global $dontUseDb;

		$dontUseDb = true;

		$this->offline = new Offline();

		$this->offMock = new PHPUnit_Extensions_MockFunction('header', $this->offline);
	}

	public function testIsOffline() {
		global $dontUseDb;

		$dontUseDb = true;

		$this->assertEquals($this->offline->isOffline(), $dontUseDb);
		$this->assertNotEquals($this->offline->isOffline(), !$dontUseDb);

		$dontUseDb = false;

		$this->assertEquals($this->offline->isOffline(), $dontUseDb);
		$this->assertNotEquals($this->offline->isOffline(), !$dontUseDb);

		$dontUseDb = true;

		$this->assertEquals($this->offline->isOffline(), $dontUseDb);
		$this->assertNotEquals($this->offline->isOffline(), !$dontUseDb);

		$dontUseDb = false;

		$this->assertEquals($this->offline->isOffline(), $dontUseDb);
		$this->assertNotEquals($this->offline->isOffline(), !$dontUseDb);
	}

	public function testGetOfflineMessage() {

		$external = false;
		$message = "This is a test message.";


		$this->offMock->expects($this->any())
			->with("HTTP/1.1 503 Service Unavailable")
			->will($this->returnValue(null));

		ob_start();

		$this->offline->getOfflineMessage($external, $message);

		$text1 = ob_get_contents();

		ob_clean();

		$this->assertContains("We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed.", $text1);
		$this->assertContains($message, $text1);

		$this->assertNotContains("After much experimentation, someone finally managed to kill ACC.", $text1);
	}

}
