<?php

use Waca\Validation\RequestValidationHelper;

class RequestValidationHelperTest extends PHPUnit_Framework_TestCase
{
	private $request;

	public function setUp()
	{
		$this->request = new Request();
		$this->request->setName("TestName");
		$this->request->setEmail("test@example.com");
		$this->request->setIp("1.2.3.4");
	}

	public function testValidateGoodName()
	{
		$banHelperMock = $this->getMockBuilder('IBanHelper')->getMock();
		$banHelperMock->method('emailIsBanned')->willReturn(false);
		$banHelperMock->method('nameIsBanned')->willReturn(false);
		$banHelperMock->method('ipIsBanned')->willReturn(false);

		// arrange
		$validationHelper = new RequestValidationHelper($banHelperMock, $this->request, $this->request->getEmail());

		// act
		$result = $validationHelper->validateName();

		// assert
		$this->assertEmpty($result);
	}
}
