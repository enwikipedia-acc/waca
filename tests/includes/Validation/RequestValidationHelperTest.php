<?php

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
		// arrange
		$validationHelper = new RequestValidationHelper(new MockBanHelper(), $this->request, $this->request->getEmail());

		// act
		$result = $validationHelper->validateName();

		// assert
		$this->assertEmpty($result);
	}
}
