<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PDOStatement;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Waca\DataObjects\Ban;
use Waca\Helpers\BanHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Providers\XffTrustProvider;

class BanHelperTest extends PHPUnit_Framework_TestCase
{
    /** @var BanHelper */
    private $banHelper;
    /** @var PHPUnit_Framework_MockObject_MockObject|PdoDatabase */
    private $dbMock;
    /** @var PHPUnit_Framework_MockObject_MockObject|PDOStatement */
    private $statement;
    /** @var PHPUnit_Framework_MockObject_MockObject|IXffTrustProvider */
    private $trustHelper;

    public function setUp()
    {
        if (!extension_loaded('runkit')) {
            $this->markTestSkipped('Dependencies for test are not available. Please install zenovich/runkit');

            return;
        }

        $this->dbMock = $this->getMockBuilder(PdoDatabase::class)->disableOriginalConstructor()->getMock();
        $this->trustHelper = $this->getMockBuilder(IXffTrustProvider::class)->disableOriginalConstructor()->getMock();

        $this->statement = $this->getMockBuilder(PDOStatement::class)
            ->setMethods(array("fetchColumn", "bindValue", "execute", "fetchObject"))
            ->getMock();
        $this->dbMock->method('prepare')->willReturn($this->statement);

        $this->banHelper = new BanHelper($this->dbMock, $this->trustHelper);
    }

    public function tearDown()
    {
        $this->banHelper = null;
    }
}
