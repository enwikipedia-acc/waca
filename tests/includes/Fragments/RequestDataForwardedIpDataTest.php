<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests\Fragments;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Waca\DataObjects\Request;
use Waca\Fragments\RequestData;
use Waca\Providers\Interfaces\ILocationProvider;
use Waca\Providers\Interfaces\IRDnsProvider;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Providers\XffTrustProvider;

class RequestDataForwardedIpDataTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|RequestData */
    private $requestDataMock;
    /** @var Request|PHPUnit_Framework_MockObject_MockObject */
    private $request;
    /** @var ReflectionMethod */
    private $reflectionMethod;
    /** @var PHPUnit_Framework_MockObject_MockObject|IXffTrustProvider */
    private $xffProvider;
    /** @var PHPUnit_Framework_MockObject_MockObject|IRDnsProvider */
    private $rdnsProvider;
    /** @var PHPUnit_Framework_MockObject_MockObject|ILocationProvider */
    private $locationProvider;

    public function setUp() : void
    {
        $this->requestDataMock = $this->getMockForTrait(RequestData::class);

        $this->reflectionMethod = new ReflectionMethod(get_class($this->requestDataMock), 'setupForwardedIpData');
        $this->reflectionMethod->setAccessible(true);

        $this->request = $this->getMockBuilder(Request::class)->getMock();

        $this->xffProvider = $this->getMockBuilder(XffTrustProvider::class)->disableOriginalConstructor()->getMock();
        $this->rdnsProvider = $this->getMockBuilder(IRDnsProvider::class)->getMock();
        $this->locationProvider = $this->getMockBuilder(ILocationProvider::class)->getMock();

        $this->requestDataMock->method('getXffTrustProvider')->willReturn($this->xffProvider);
        $this->requestDataMock->method('getRdnsProvider')->willReturn($this->rdnsProvider);
        $this->requestDataMock->method('getLocationProvider')->willReturn($this->locationProvider);

        $this->rdnsProvider->method('getReverseDNS')->willReturnCallback(function($x) {
            return "$x.in-addr.arpa.test";
        });

        $this->locationProvider->method('getIpLocation')->willReturn(null);
    }

    public function testNoForwarding()
    {
        // arrange
        // Assuming [client (1)] <=> [us]
        $this->request->method('getForwardedIp')->willReturn(null);
        $this->request->method('getIp')->willReturn("1.1.1.1");

        $xffTrustMap = [];
        $this->xffProvider->method('isTrusted')->willReturnMap($xffTrustMap);

        $actualRequestProxyData = null;
        $actualForwardedOrigin = null;

        $this->requestDataMock->method('assign')
            ->willReturnCallback(function($x, $val) use (&$actualForwardedOrigin, &$actualRequestProxyData) {
                // there's got to be a better way of doing this phpunit...
                if ($x === 'requestProxyData') {
                    $actualRequestProxyData = $val;
                }

                if ($x === 'forwardedOrigin') {
                    $actualForwardedOrigin = $val;
                }

                return null;
            });

        // act
        $this->reflectionMethod->invoke($this->requestDataMock, $this->request);

        // assert
        $this->assertNull($actualRequestProxyData);
        $this->assertNull($actualForwardedOrigin);
    }

    public function testSingleProxy()
    {
        // arrange
        // Assuming [client (2)] <=> [proxy (1)] <=> [us]
        $this->request->method('getForwardedIp')->willReturn("2.2.2.2");
        $this->request->method('getIp')->willReturn("1.1.1.1");

        $xffTrustMap = [["1.1.1.1", true]];
        $this->xffProvider->method('isTrusted')->willReturnMap($xffTrustMap);

        $actualRequestProxyData = null;
        $actualForwardedOrigin = null;

        $this->requestDataMock->method('assign')
            ->willReturnCallback(function($x, $val) use (&$actualForwardedOrigin, &$actualRequestProxyData) {
                // there's got to be a better way of doing this phpunit...
                if ($x === 'requestProxyData') {
                    $actualRequestProxyData = $val;
                }

                if ($x === 'forwardedOrigin') {
                    $actualForwardedOrigin = $val;
                }

                return null;
            });

        // act
        $this->reflectionMethod->invoke($this->requestDataMock, $this->request);

        // assert
        $this->assertEquals($actualForwardedOrigin, "2.2.2.2");

        $this->assertEquals(count($actualRequestProxyData), 2);

        $this->assertEquals($actualRequestProxyData[0]['ip'], '1.1.1.1');
        $this->assertEquals($actualRequestProxyData[0]['trust'], true);
        $this->assertEquals($actualRequestProxyData[0]['trustedlink'], true);
        $this->assertEquals($actualRequestProxyData[0]['routable'], true);
        $this->assertEquals($actualRequestProxyData[0]['showlinks'], false);

        $this->assertEquals($actualRequestProxyData[1]['ip'], '2.2.2.2');
        $this->assertEquals($actualRequestProxyData[1]['trust'], true);
        $this->assertEquals($actualRequestProxyData[1]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[1]['routable'], true);
        $this->assertEquals($actualRequestProxyData[1]['showlinks'], true);
    }

    public function testDualTrustedProxy()
    {
        // arrange
        // Assuming [client (3)] <=> [proxy (2)] <=> [proxy (1)] <=> [us]
        $this->request->method('getForwardedIp')->willReturn("3.3.3.3, 2.2.2.2");
        $this->request->method('getIp')->willReturn("1.1.1.1");

        $xffTrustMap = [["1.1.1.1", true], ["2.2.2.2", true]];
        $this->xffProvider->method('isTrusted')->willReturnMap($xffTrustMap);

        $actualRequestProxyData = null;
        $actualForwardedOrigin = null;

        $this->requestDataMock->method('assign')
            ->willReturnCallback(function($x, $val) use (&$actualForwardedOrigin, &$actualRequestProxyData) {
                // there's got to be a better way of doing this phpunit...
                if ($x === 'requestProxyData') {
                    $actualRequestProxyData = $val;
                }

                if ($x === 'forwardedOrigin') {
                    $actualForwardedOrigin = $val;
                }

                return null;
            });

        // act
        $this->reflectionMethod->invoke($this->requestDataMock, $this->request);

        // assert
        $this->assertEquals($actualForwardedOrigin, "3.3.3.3");

        $this->assertEquals(count($actualRequestProxyData), 3);

        $this->assertEquals($actualRequestProxyData[0]['ip'], '1.1.1.1');
        $this->assertEquals($actualRequestProxyData[0]['trust'], true);
        $this->assertEquals($actualRequestProxyData[0]['trustedlink'], true);
        $this->assertEquals($actualRequestProxyData[0]['routable'], true);
        $this->assertEquals($actualRequestProxyData[0]['showlinks'], false);

        $this->assertEquals($actualRequestProxyData[1]['ip'], '2.2.2.2');
        $this->assertEquals($actualRequestProxyData[1]['trust'], true);
        $this->assertEquals($actualRequestProxyData[1]['trustedlink'], true);
        $this->assertEquals($actualRequestProxyData[1]['routable'], true);
        $this->assertEquals($actualRequestProxyData[1]['showlinks'], false);

        $this->assertEquals($actualRequestProxyData[2]['ip'], '3.3.3.3');
        $this->assertEquals($actualRequestProxyData[2]['trust'], true);
        $this->assertEquals($actualRequestProxyData[2]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[2]['routable'], true);
        $this->assertEquals($actualRequestProxyData[2]['showlinks'], true);
    }

    public function testDualSplitProxy()
    {
        // arrange
        // Assuming [client (3)] <=> [proxy (2)] <=> [proxy (1)] <=> [us]
        $this->request->method('getForwardedIp')->willReturn("3.3.3.3, 2.2.2.2");
        $this->request->method('getIp')->willReturn("1.1.1.1");

        $xffTrustMap = [["1.1.1.1", true], ["2.2.2.2", false]];
        $this->xffProvider->method('isTrusted')->willReturnMap($xffTrustMap);

        $actualRequestProxyData = null;
        $actualForwardedOrigin = null;

        $this->requestDataMock->method('assign')
            ->willReturnCallback(function($x, $val) use (&$actualForwardedOrigin, &$actualRequestProxyData) {
                // there's got to be a better way of doing this phpunit...
                if ($x === 'requestProxyData') {
                    $actualRequestProxyData = $val;
                }

                if ($x === 'forwardedOrigin') {
                    $actualForwardedOrigin = $val;
                }

                return null;
            });

        // act
        $this->reflectionMethod->invoke($this->requestDataMock, $this->request);

        // assert
        $this->assertEquals($actualForwardedOrigin, "3.3.3.3");

        $this->assertEquals(count($actualRequestProxyData), 3);

        $this->assertEquals($actualRequestProxyData[0]['ip'], '1.1.1.1');
        $this->assertEquals($actualRequestProxyData[0]['trust'], true);
        $this->assertEquals($actualRequestProxyData[0]['trustedlink'], true);
        $this->assertEquals($actualRequestProxyData[0]['routable'], true);
        $this->assertEquals($actualRequestProxyData[0]['showlinks'], false);

        $this->assertEquals($actualRequestProxyData[1]['ip'], '2.2.2.2');
        $this->assertEquals($actualRequestProxyData[1]['trust'], false); // hmm? shouldn't this be true?
        $this->assertEquals($actualRequestProxyData[1]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[1]['routable'], true);
        $this->assertEquals($actualRequestProxyData[1]['showlinks'], true);

        $this->assertEquals($actualRequestProxyData[2]['ip'], '3.3.3.3');
        $this->assertEquals($actualRequestProxyData[2]['trust'], false);
        $this->assertEquals($actualRequestProxyData[2]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[2]['routable'], true);
        $this->assertEquals($actualRequestProxyData[2]['showlinks'], true);
    }

    public function testDualSplitProxyReverse()
    {
        // arrange
        // Assuming [client (3)] <=> [proxy (2)] <=> [proxy (1)] <=> [us]
        $this->request->method('getForwardedIp')->willReturn("3.3.3.3, 2.2.2.2");
        $this->request->method('getIp')->willReturn("1.1.1.1");

        $xffTrustMap = [["1.1.1.1", false], ["2.2.2.2", true]];
        $this->xffProvider->method('isTrusted')->willReturnMap($xffTrustMap);

        $actualRequestProxyData = null;
        $actualForwardedOrigin = null;

        $this->requestDataMock->method('assign')
            ->willReturnCallback(function($x, $val) use (&$actualForwardedOrigin, &$actualRequestProxyData) {
                // there's got to be a better way of doing this phpunit...
                if ($x === 'requestProxyData') {
                    $actualRequestProxyData = $val;
                }

                if ($x === 'forwardedOrigin') {
                    $actualForwardedOrigin = $val;
                }

                return null;
            });

        // act
        $this->reflectionMethod->invoke($this->requestDataMock, $this->request);

        // assert
        $this->assertEquals($actualForwardedOrigin, "3.3.3.3");

        $this->assertEquals(count($actualRequestProxyData), 3);

        $this->assertEquals($actualRequestProxyData[0]['ip'], '1.1.1.1');
        $this->assertEquals($actualRequestProxyData[0]['trust'], false);    // this should be trusted... right?
        $this->assertEquals($actualRequestProxyData[0]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[0]['routable'], true);
        $this->assertEquals($actualRequestProxyData[0]['showlinks'], true);

        $this->assertEquals($actualRequestProxyData[1]['ip'], '2.2.2.2');
        $this->assertEquals($actualRequestProxyData[1]['trust'], false);
        $this->assertEquals($actualRequestProxyData[1]['trustedlink'], true);
        $this->assertEquals($actualRequestProxyData[1]['routable'], true);
        $this->assertEquals($actualRequestProxyData[1]['showlinks'], true);

        $this->assertEquals($actualRequestProxyData[2]['ip'], '3.3.3.3');
        $this->assertEquals($actualRequestProxyData[2]['trust'], false);
        $this->assertEquals($actualRequestProxyData[2]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[2]['routable'], true);
        $this->assertEquals($actualRequestProxyData[2]['showlinks'], true);
    }

    public function testPrivateRangeProxy()
    {
        // arrange
        // Assuming [client (3)] <=> [proxy (2)] <=> [proxy (1)] <=> [us]
        $this->request->method('getForwardedIp')->willReturn("1.1.1.1");
        $this->request->method('getIp')->willReturn("10.0.0.1");

        $xffTrustMap = [['10.0.0.1', true]];
        $this->xffProvider->method('isTrusted')->willReturnMap($xffTrustMap);

        $this->xffProvider->method('ipInRange')->willReturnCallback(function($ipList, $ip) {
            if ($ip === '10.0.0.1') {
                return true;
            }

            return false;
        });

        $actualRequestProxyData = null;
        $actualForwardedOrigin = null;

        $this->requestDataMock->method('assign')
            ->willReturnCallback(function($x, $val) use (&$actualForwardedOrigin, &$actualRequestProxyData) {
                // there's got to be a better way of doing this phpunit...
                if ($x === 'requestProxyData') {
                    $actualRequestProxyData = $val;
                }

                if ($x === 'forwardedOrigin') {
                    $actualForwardedOrigin = $val;
                }

                return null;
            });

        // act
        $this->reflectionMethod->invoke($this->requestDataMock, $this->request);

        // assert
        $this->assertEquals($actualForwardedOrigin, "1.1.1.1");

        $this->assertEquals(count($actualRequestProxyData), 2);

        $this->assertEquals($actualRequestProxyData[0]['ip'], '10.0.0.1');
        $this->assertEquals($actualRequestProxyData[0]['trust'], true);
        $this->assertEquals($actualRequestProxyData[0]['trustedlink'], true);
        $this->assertEquals($actualRequestProxyData[0]['routable'], false);
        $this->assertEquals($actualRequestProxyData[0]['showlinks'], false);

        $this->assertEquals($actualRequestProxyData[1]['ip'], '1.1.1.1');
        $this->assertEquals($actualRequestProxyData[1]['trust'], true);
        $this->assertEquals($actualRequestProxyData[1]['trustedlink'], false);
        $this->assertEquals($actualRequestProxyData[1]['routable'], true);
        $this->assertEquals($actualRequestProxyData[1]['showlinks'], true);
    }
}