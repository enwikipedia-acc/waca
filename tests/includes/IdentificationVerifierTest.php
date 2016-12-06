<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

use Waca\Exceptions\CurlException;
use Waca\Helpers\HttpHelper;
use Waca\IdentificationVerifier;
use Waca\PdoDatabase;
use Waca\SiteConfiguration;

/**
 * Class IdentificationVerifierTest
 *
 * For now, this file will only contain a very simple unit test that ensures we're getting results we expect from the
 * Meta Wikimedia API call.  More thorough testing will be implemented later.
 *
 * @package Waca\Tests
 */
class IdentificationVerifierTest extends PHPUnit_Framework_TestCase
{
    /** @var IdentificationVerifier */
    private $identificationVerifier;
    /** @var HttpHelper */
    private $httpHelper;
    /** @var SiteConfiguration */
    private $dummyConfiguration;

    public function setUp()
    {
        $this->dummyConfiguration = new SiteConfiguration();
        $this->httpHelper = new HttpHelper($this->dummyConfiguration->getUserAgent(), true);
        /** @var PdoDatabase|PHPUnit_Framework_MockObject_MockObject $dummyDatabase */
        $dummyDatabase = $this->getMockBuilder(PdoDatabase::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identificationVerifier = new IdentificationVerifier(
            $this->httpHelper,
            $this->dummyConfiguration,
            $dummyDatabase);
    }

    public function tearDown()
    {
        $this->identificationVerifier = null;
    }

    /**
     * Test the API still returns the expected results.
     *
     * Since this test actually queries the real Identification Noticeboard, the assertions should be periodically
     * manually verified by a human.
     *
     * This test also serves to ensure this is still usable as a method of testing identification.
     */
    public function testApiReturnsExpectedResults()
    {
        try {
            // Attempt a get
            $this->httpHelper->get($this->dummyConfiguration->getMetaWikimediaWebServiceEndpoint());
        }
        catch (CurlException $ex) {
            // We can't load the endpoint, so we can't really do this test.
            $this->markTestSkipped('Cannot contact Meta endpoint');
        }

        $reflector = new \ReflectionClass($this->identificationVerifier);
        $method = $reflector->getMethod('isIdentifiedOnWiki');
        $method->setAccessible(true);

        setlocale(LC_ALL, 'UTF8');

        $this->assertTrue($method->invoke($this->identificationVerifier, "Stwalkerster"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "stwalkerster"),
            "First character case insensitivity test failed");
        $this->assertTrue($method->invoke($this->identificationVerifier, "FastLizard4"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "fastlizard4"),
            "Username case sensitivity test (for more than first character) failed");
        $this->assertFalse($method->invoke($this->identificationVerifier, "Grawp"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "Willie On Wheels"));

        // Some non-standard or non-Latin names to try out
        $this->assertTrue($method->invoke($this->identificationVerifier, "-revi"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "555"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "Trần Nguyễn Minh Huy"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "محمد شعیب"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "יונה בנדלאק"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "和平奮鬥救地球"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "-rei"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "55"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "TrầnNguyễnMinhHuy"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "محمدشعیب"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "יונהבנדלאק"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "和平奮救地球"));
    }
}
