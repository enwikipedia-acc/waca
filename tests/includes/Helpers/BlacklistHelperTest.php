<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Helpers;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;
use Waca\Helpers\BlacklistHelper;
use Waca\Helpers\HttpHelper;

class BlacklistHelperTest extends TestCase
{
    public function setUp() : void
    {
        $this->markTestSkipped("runkit-based tests broken since PHPUnit upgrade");

        if (!extension_loaded('runkit7')) {
            $this->markTestSkipped('Dependencies for test are not available. Please install runkit7/runkit7');

            return;
        }
    }

    public function testIsBlacklisted()
    {
        $apiResult = array(
            'titleblacklist' =>
                array(
                    'result'  => 'blacklisted',
                    'reason'  => 'some reason',
                    'message' => 'titleblacklist-forbidden-new-account',
                    'line'    => '.*badname.*            &lt;newaccountonly|antispoof&gt;',
                ),
        );

        /** @var $httpHelperMock PHPUnit_Framework_MockObject_MockObject|HttpHelper */
        $httpHelperMock = $this->getMockBuilder(HttpHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpHelperMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(serialize($apiResult));

        $blh = new BlacklistHelper($httpHelperMock, "http://127.0.0.1");

        // act
        $result = $blh->isBlacklisted("badname");

        // assert
        $this->assertNotEquals(false, $result);
        $this->assertEquals($apiResult['titleblacklist']['line'], $result);
    }

    public function testIsBlacklistedCache()
    {
        $apiResult = array(
            'titleblacklist' =>
                array(
                    'result'  => 'blacklisted',
                    'reason'  => 'some reason',
                    'message' => 'titleblacklist-forbidden-new-account',
                    'line'    => '.*badname.*            &lt;newaccountonly|antispoof&gt;',
                ),
        );

        /** @var $httpHelperMock PHPUnit_Framework_MockObject_MockObject|HttpHelper */
        $httpHelperMock = $this->getMockBuilder(HttpHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpHelperMock
            ->expects($this->once())
            ->method('get')
            ->willReturn(serialize($apiResult));

        $blh = new BlacklistHelper($httpHelperMock, "http://127.0.0.1");

        // act
        $blh->isBlacklisted("badname");
        $blh->isBlacklisted("badname");
    }

    public function testIsNotBlacklisted()
    {
        /** @var $httpHelperMock PHPUnit_Framework_MockObject_MockObject|HttpHelper */
        $httpHelperMock = $this->getMockBuilder(HttpHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpHelperMock
            ->expects($this->once())
            ->method("get")
            ->willReturn("a:1:{s:14:\"titleblacklist\";a:1:{s:6:\"result\";s:2:\"ok\";}}");

        $blh = new BlacklistHelper($httpHelperMock, "http://127.0.0.1");

        $result = $blh->isBlacklisted("poop");

        $this->assertEquals(false, $result);
    }

    public function testIsNotBlacklistedCache()
    {
        /** @var $httpHelperMock PHPUnit_Framework_MockObject_MockObject|HttpHelper */
        $httpHelperMock = $this->getMockBuilder(HttpHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpHelperMock
            ->expects($this->once())
            ->method("get")
            ->willReturn("a:1:{s:14:\"titleblacklist\";a:1:{s:6:\"result\";s:2:\"ok\";}}");

        $blh = new BlacklistHelper($httpHelperMock, "http://127.0.0.1");

        // act
        $result = $blh->isBlacklisted("poop");
        $result = $blh->isBlacklisted("poop");
    }
}
