<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
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
class IdentificationVerifierTest extends TestCase
{
    /** @var IdentificationVerifier */
    private $identificationVerifier;
    /** @var HttpHelper */
    private $httpHelper;
    /** @var SiteConfiguration */
    private $dummyConfiguration;

    public function setUp() : void
    {
        $this->dummyConfiguration = new SiteConfiguration();
        $this->dummyConfiguration->setCurlDisableVerifyPeer(true);
        $this->httpHelper = new HttpHelper($this->dummyConfiguration);
        /** @var PdoDatabase|PHPUnit_Framework_MockObject_MockObject $dummyDatabase */
        $dummyDatabase = $this->getMockBuilder(PdoDatabase::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identificationVerifier = new IdentificationVerifier(
            $this->httpHelper,
            $this->dummyConfiguration,
            $dummyDatabase);
    }

    public function tearDown() : void
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
        $this->assertTrue($method->invoke($this->identificationVerifier, "Стефанко1982"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "علاء"));
        $this->assertTrue($method->invoke($this->identificationVerifier, "이강철"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "-rei"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "Стефанко198"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "ظهیی"));
        $this->assertFalse($method->invoke($this->identificationVerifier, "이강"));
    }
}
