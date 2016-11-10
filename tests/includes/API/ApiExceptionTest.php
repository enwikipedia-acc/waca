<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Api;

use \Waca\API\ApiException;

class ApiExceptionTest extends \PHPUnit_Framework_TestCase
{
	/** @var  string */
	private $message;
	
	/** @var ApiException */
	private $ex;

	public function setUp()
	{
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
