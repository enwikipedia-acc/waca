<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 3/3/16
 * Time: 18:38
 */

namespace Waca\Tests;

class OfflineTest extends \PHPUnit_Framework_TestCase
{
	private $offline;

	public function setUp() {
		global $dontUseDb;

		$dontUseDb = true;
		$this->offline = new \Offline();
	}

	/*
	public function testCheck() {
		global $smarty, $dontUseDb, $dontUseDbCulprit, $dontUseDbReason;
		$smarty = new \Smarty();
		$dontUseDb = true;
		$smarty->assign("baseurl", '/');
		$smarty->assign("toolversion", '0.1awesome1');

		ob_start();

		\Offline::check(true);

		$text1 = ob_get_contents();

		ob_flush();

		$this->assertContains("We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed.", $text1);

		$this->assertNotContains("After much experimentation, someone finally managed to kill ACC.", $text1);
	}*/

	public function testIsOffline() {
		global $dontUseDb;

		$dontUseDb = true;

		$this->assertEquals(\Offline::isOffline(), $dontUseDb);
		$this->assertNotEquals(\Offline::isOffline(), !$dontUseDb);

		$dontUseDb = false;

		$this->assertEquals(\Offline::isOffline(), $dontUseDb);
		$this->assertNotEquals(\Offline::isOffline(), !$dontUseDb);

		$dontUseDb = true;

		$this->assertEquals(\Offline::isOffline(), $dontUseDb);
		$this->assertNotEquals(\Offline::isOffline(), !$dontUseDb);

		$dontUseDb = false;

		$this->assertEquals(\Offline::isOffline(), $dontUseDb);
		$this->assertNotEquals(\Offline::isOffline(), !$dontUseDb);
	}

	/*
	public function testGetOfflineMessage() {

		$external = false;
		$message = "This is a test message.";

		ob_start();

		\Offline::getOfflineMessage($external, $message);

		$text1 = ob_get_contents();

		ob_flush();

		$this->assertContains("We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed.", $text1);
		$this->assertContains($message, $text1);

		$this->assertNotContains("After much experimentation, someone finally managed to kill ACC.", $text1);
	}
	*/
}
