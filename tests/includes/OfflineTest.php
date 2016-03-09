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
use Offline;

class OfflineTest extends PHPUnit_Framework_TestCase
{
	private $offline;

	public function setUp() {
		global $dontUseDb;

		$dontUseDb = true;

		$this->offline = new \Waca\Offline();
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

		$offMock = new PHPUnit_Extensions_MockFunction('getOfflineMessage', $this->offline);

		$offMock->expects($this->once())
			->with($external, $message)
			->will($this->returnValue(NULL));

		/*
		ob_start();

		$offMock->getOfflineMessage();

		$text1 = ob_get_contents();

		ob_clean();

		$this->assertContains("We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed.", $text1);
		$this->assertContains($message, $text1);

		$this->assertNotContains("After much experimentation, someone finally managed to kill ACC.", $text1);
		*/
	}

}
