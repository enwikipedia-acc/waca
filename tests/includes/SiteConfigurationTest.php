<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use PHPUnit\Framework\TestCase;
use Waca\SiteConfiguration;

class SiteConfigurationTest extends TestCase
{
    /** @var SiteConfiguration */
    private $si;

    function setUp() : void
    {
        $this->si = new SiteConfiguration();
    }

    function tearDown() : void
    {
        unset($this->si);
    }

    function testSetUp()
    {
        $this->assertInstanceOf(SiteConfiguration::class, $this->si);
    }

    function testBaseUrl()
    {
        $newValue = "http://localhost/testAwesome/";

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setBaseUrl($newValue));
        $this->assertEquals($this->si->getBaseUrl(), $newValue);
    }

    function testFilePath()
    {
        $newValue = "/var/www/waca";

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setFilePath($newValue));
        $this->assertEquals($this->si->getFilePath(), $newValue);
    }

    function testSchemaVersion()
    {
        $this->assertGreaterThan(20, $this->si->getSchemaVersion());
        $this->assertNotEquals($this->si->getSchemaVersion(), null);
    }

    function testDebuggingTraceEnabled()
    {
        $newValue = true;

        $this->assertEquals($this->si->getDebuggingTraceEnabled(), null);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDebuggingTraceEnabled($newValue));
        $this->assertEquals($this->si->getDebuggingTraceEnabled(), $newValue);

        $newValue = false;

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDebuggingTraceEnabled($newValue));
        $this->assertEquals($this->si->getDebuggingTraceEnabled(), $newValue);
    }

    function testDataClearIp()
    {
        $newValue = "10.0.0.1";

        $this->assertEquals($this->si->getDataClearIp(), "127.0.0.1");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDataClearIp($newValue));
        $this->assertEquals($this->si->getDataClearIp(), $newValue);
    }

    function testDataClearEmail()
    {
        $newValue = "everything_is_awesome@wikimedia.org";

        $this->assertEquals($this->si->getDataClearEmail(), "acc@toolserver.org");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDataClearEmail($newValue));
        $this->assertEquals($this->si->getDataClearEmail(), $newValue);
    }

    function testForceIdentification()
    {
        $this->assertTrue($this->si->getForceIdentification());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setForceIdentification(false));
        $this->assertFalse($this->si->getForceIdentification());
    }

    function testIdentificationCacheExpiry()
    {
        $newValue = "44 Day";

        $this->assertEquals($this->si->getIdentificationCacheExpiry(), "1 DAY");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setIdentificationCacheExpiry($newValue));
        $this->assertEquals($this->si->getIdentificationCacheExpiry(), $newValue);
    }

    function testMetaWikimediaWebServiceEndpoint()
    {
        $newValue = "https://meta2.wikimedia.org/w/api.php";

        $this->assertEquals($this->si->getMetaWikimediaWebServiceEndpoint(), "https://meta.wikimedia.org/w/api.php");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setMetaWikimediaWebServiceEndpoint($newValue));
        $this->assertEquals($this->si->getMetaWikimediaWebServiceEndpoint(), $newValue);
    }

    function testEnforceOAuth()
    {
        $this->assertFalse($this->si->getEnforceOAuth());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setEnforceOAuth(true));
        $this->assertTrue($this->si->getEnforceOAuth());
    }

    function testEmailConfirmationEnabled()
    {
        $this->assertTrue($this->si->getEmailConfirmationEnabled());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setEmailConfirmationEnabled(false));
        $this->assertFalse($this->si->getEmailConfirmationEnabled());
    }

    function testMiserModeLimit()
    {
        $newValue = "150";

        $this->assertEquals($this->si->getMiserModeLimit(), "25");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setMiserModeLimit($newValue));
        $this->assertEquals($this->si->getMiserModeLimit(), $newValue);
    }

    function testSquidList()
    {
        $newValue = array("this" => "that");

        $test1 = $this->si->getSquidList();
        $this->assertIsArray($test1);
        $this->assertArrayNotHasKey("this", $test1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setSquidList($newValue));

        $test2 = $this->si->getSquidList();
        $this->assertIsArray($test2);
        $this->assertIsArray($test2);
        $this->assertArrayHasKey("this", $test2);
    }

    function testUseStrictTransportSecurity()
    {
        $this->assertFalse($this->si->getUseStrictTransportSecurity());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setUseStrictTransportSecurity(true));
        $this->assertTrue($this->si->getUseStrictTransportSecurity());
    }

    function testUserAgent()
    {
        $newValue = "Monkeyscript 1.00.22 because reasons";

        $this->assertEquals($this->si->getUserAgent(),
            "Wikipedia-ACC Tool/0.1 (+https://accounts.wmflabs.org/internal.php/team)");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setUserAgent($newValue));
        $this->assertEquals($this->si->getUserAgent(), $newValue);
    }

    function testCurlDisableVerifyPeer()
    {
        $this->assertFalse($this->si->getCurlDisableVerifyPeer());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setCurlDisableVerifyPeer(true));
        $this->assertTrue($this->si->getCurlDisableVerifyPeer());
    }

    function testUseOAuthSignup()
    {
        $this->assertTrue($this->si->getUseOAuthSignup());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setUseOAuthSignup(false));
        $this->assertFalse($this->si->getUseOAuthSignup());
    }

    function testOAuthConsumerToken()
    {
        $newValue = "ThisTokenIsNotSecretPleaseDontEverUseMe";

        $this->assertEquals($this->si->getOAuthConsumerToken(), null);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setOAuthConsumerToken($newValue));
        $this->assertEquals($this->si->getOAuthConsumerToken(), $newValue);
    }

    function testOAuthConsumerSecret()
    {
        $newValue = "ThisSecretIsntSecretIsIt";

        $this->assertEquals($this->si->getOAuthConsumerSecret(), null);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setOAuthConsumerSecret($newValue));
        $this->assertEquals($this->si->getOAuthConsumerSecret(), $newValue);
    }

    function testDataClearInterval()
    {
        $newValue = "31 DAY";

        $this->assertEquals($this->si->getDataClearInterval(), "15 DAY");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDataClearInterval($newValue));
        $this->assertEquals($this->si->getDataClearInterval(), $newValue);
    }

    function testXffTrustedHostsFile()
    {
        $newValue = "../TrustedXFF/trust-the-lizard-overlords.txt";

        $this->assertEquals($this->si->getXffTrustedHostsFile(), "../TrustedXFF/trusted-hosts.txt");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setXffTrustedHostsFile($newValue));
        $this->assertEquals($this->si->getXffTrustedHostsFile(), $newValue);
    }

    function testCrossOriginResourceSharingHosts()
    {
        $newValue = array(
            "http://en.wikipedia.org",
            "https://en.wikipedia.org",
            "http://meta.wikimedia.org",
            "https://meta.wikimedia.org",
            "http://localhost/awesomeHosts",
        );

        $this->assertIsArray($this->si->getCrossOriginResourceSharingHosts());
        $this->assertNotContains("http://localhost/awesomeHosts", $this->si->getCrossOriginResourceSharingHosts());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setCrossOriginResourceSharingHosts($newValue));
        $this->assertContains("http://localhost/awesomeHosts", $this->si->getCrossOriginResourceSharingHosts());
    }

    function testIrcNotificationsEnabled()
    {
        $this->assertTrue($this->si->getIrcNotificationsEnabled());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setIrcNotificationsEnabled(false));
        $this->assertFalse($this->si->getIrcNotificationsEnabled());
    }

    function testErrorLog()
    {
        $newValue = "elephantlog";

        $this->assertEquals($this->si->getErrorLog(), "errorlog");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setErrorLog($newValue));
        $this->assertEquals($this->si->getErrorLog(), $newValue);
    }

    function testEmailConfirmationExpiryDays()
    {
        $newValue = 512;

        $this->assertEquals($this->si->getEmailConfirmationExpiryDays(), 7);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setEmailConfirmationExpiryDays($newValue));
        $this->assertEquals($this->si->getEmailConfirmationExpiryDays(), $newValue);
    }

    function testIrcNotificationsInstance()
    {
        $newValue = "world";

        $this->assertEquals($this->si->getIrcNotificationsInstance(), "Development");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setIrcNotificationsInstance($newValue));
        $this->assertEquals($this->si->getIrcNotificationsInstance(), $newValue);
    }

    function testTitleBlacklistEnabled()
    {
        $this->assertFalse($this->si->getTitleBlacklistEnabled());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setTitleBlacklistEnabled(true));
        $this->assertTrue($this->si->getTitleBlacklistEnabled());
    }

    function testLocationProviderApiKey()
    {
        $newValue = "TotallyNotASecretLocationProviderAPIKey";

        $this->assertEquals($this->si->getLocationProviderApiKey(), null);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setLocationProviderApiKey($newValue));
        $this->assertEquals($this->si->getLocationProviderApiKey(), $newValue);
    }

    function testTorExitPaths()
    {
        $newValue = array("one" => "The Other");

        $test1 = $this->si->getTorExitPaths();
        $this->assertIsArray($test1);
        $this->assertArrayNotHasKey("one", $test1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setTorExitPaths($newValue));

        $test2 = $this->si->getTorExitPaths();
        $this->assertIsArray($test2);
        $this->assertArrayHasKey("one", $test2);
    }
}
