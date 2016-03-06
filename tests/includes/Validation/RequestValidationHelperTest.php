<?php

namespace Waca\Tests\Validation;

use PDOStatement;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\DataObjects\Request;
use Waca\Helpers\Interfaces\IBanHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IAntiSpoofProvider;
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
		$dbMock = $this->getMockBuilder(PdoDatabase::class)->disableOriginalConstructor()->getMock();
		$statement = $this->getMockBuilder(PDOStatement::class)->disableOriginalConstructor()->getMock();
		$statement->method('fetchColumn')->willReturn(0);
		$dbMock->method('prepare')->willReturn($statement);

		/** @var IBanHelper|PHPUnit_Framework_MockObject_MockObject $banHelperMock */
		$banHelperMock = $this->getMockBuilder(IBanHelper::class)->getMock();
		$banHelperMock->expects($this->never())->method('emailIsBanned')->willReturn(false);
		$banHelperMock->expects($this->once())->method('nameIsBanned')->willReturn(false);
		$banHelperMock->expects($this->never())->method('ipIsBanned')->willReturn(false);

		/** @var IAntiSpoofProvider|PHPUnit_Framework_MockObject_MockObject $antispoofMock */
		$antispoofMock = $this->getMockBuilder(IAntiSpoofProvider::class)->getMock();
		$antispoofMock->expects($this->never())->method('getSpoofs')->willReturn(array());

		// arrange
		$validationHelper = new RequestValidationHelper(
			$banHelperMock,
			$this->request,
			$this->request->getEmail(),
			$dbMock,
			$antispoofMock);

		// act
		$result = $validationHelper->validateName();

		// assert
		$this->assertEmpty($result);
	}
}
