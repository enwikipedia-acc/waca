<?php


namespace Waca\Tests\Api;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use \Waca\API\ApiException;

class ApiExceptionTest extends \PHPUnit_Framework_TestCase {

	private $ex;
	private $message;

	public function setUp() {
		$this->message = "This is a test message";
		try {
			throw new ApiException($this->message);
		}
		catch (ApiException $ex) {
			$this->ex = $ex;
		}
	}

	public function testMessage() {
		$this->assertEquals($this->message, $this->ex->getMessage());
		$this->assertNotEquals(NULL, $this->ex->getMessage());
	}
}
