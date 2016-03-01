<?php

namespace Waca\Tests\Validation;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\DataObjects\Request;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\PdoDatabase;
use Waca\Tests\Utility\MockableDatabase;
use Waca\Validation\RequestValidationHelper;

/**
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class RequestValidationHelperTest extends PHPUnit_Framework_TestCase
{
	/** @var Request */
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
		/** @var PdoDatabase|PHPUnit_Framework_MockObject_MockObject $dbMock */
		$dbMock = $this->getMockBuilder(MockableDatabase::class)->getMock();

		$dbStatementMock = $this->getMockBuilder('PDOStatement')->disableOriginalConstructor()->getMock();
		$dbMock->method('prepare')->willReturn($dbStatementMock);

		/** @var IBanHelper|PHPUnit_Framework_MockObject_MockObject $banHelperMock */
		$banHelperMock = $this->getMockBuilder(IBanHelper::class)->getMock();
		$banHelperMock->method('emailIsBanned')->willReturn(false);
		$banHelperMock->method('nameIsBanned')->willReturn(false);
		$banHelperMock->method('ipIsBanned')->willReturn(false);

		// arrange
		$validationHelper = new RequestValidationHelper(
			$banHelperMock,
			$this->request,
			$this->request->getEmail(),
			$dbMock);

		// act
		$result = $validationHelper->validateName();

		// assert
		$this->assertEmpty($result);
	}
}
