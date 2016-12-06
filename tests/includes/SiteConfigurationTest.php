<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests;

use Waca\SiteConfiguration;

class SiteConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var SiteConfiguration */
    private $si;

    function setUp()
    {
        $this->si = new SiteConfiguration();
    }

    function tearDown()
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

        $this->assertEquals($this->si->getBaseUrl(), null);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setBaseUrl($newValue));
        $this->assertEquals($this->si->getBaseUrl(), $newValue);
    }

    function testFilePath()
    {
        $newValue = "/var/www/waca";

        $this->assertEquals($this->si->getFilePath(), null);

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

    function testMediawikiScriptPath()
    {
        $newValue = "https://de.wikipedia.org/w/index.php";

        $this->assertEquals($this->si->getMediawikiScriptPath(), "https://en.wikipedia.org/w/index.php");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setMediawikiScriptPath($newValue));
        $this->assertEquals($this->si->getMediawikiScriptPath(), $newValue);
    }

    function testMediawikiWebServiceEndpoint()
    {
        $newValue = "https://de.wikipedia.org/w/api.php";

        $this->assertEquals($this->si->getMediawikiWebServiceEndpoint(), "https://en.wikipedia.org/w/api.php");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setMediawikiWebServiceEndpoint($newValue));
        $this->assertEquals($this->si->getMediawikiWebServiceEndpoint(), $newValue);
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
        $this->assertTrue($this->si->getEnforceOAuth());

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setEnforceOAuth(false));
        $this->assertFalse($this->si->getEnforceOAuth());
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

    function testRequestStates()
    {
        $newValue = array(
            'Open'          => array(
                'defertolog' => 'users', // don't change or you'll break old logs
                'deferto'    => 'users',
                'header'     => 'Open requests',
                'api'        => "open",
            ),
            'Flagged users' => array(
                'defertolog' => 'flagged users', // don't change or you'll break old logs
                'deferto'    => 'flagged users',
                'header'     => 'Flagged user needed',
                'api'        => "admin",
            ),
            'Checkuser'     => array(
                'defertolog' => 'checkusers', // don't change or you'll break old logs
                'deferto'    => 'checkusers',
                'header'     => 'Checkuser needed',
                'api'        => "checkuser",
            ),
            'Dummy'         => array(
                'defertolog' => 'dummy', // don't change or you'll break old logs
                'deferto'    => 'dummy',
                'header'     => 'Dummy needed',
                'api'        => "dummy",
            ),
        );

        $test1 = $this->si->getRequestStates();
        $this->assertInternalType('array', $test1);
        $this->assertArrayHasKey("Open", $test1);
        $this->assertArrayNotHasKey("Dummy", $test1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setRequestStates($newValue));

        $test2 = $this->si->getRequestStates();
        $this->assertInternalType('array', $test2);
        $this->assertArrayHasKey("Open", $test2);
        $this->assertArrayHasKey("Dummy", $test2);
    }

    function testSquidList()
    {
        $newValue = array("this" => "that");

        $test1 = $this->si->getSquidList();
        $this->assertInternalType('array', $test1);
        $this->assertArrayNotHasKey("this", $test1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setSquidList($newValue));

        $test2 = $this->si->getSquidList();
        $this->assertInternalType('array', $test2);
        $this->assertArrayHasKey("this", $test2);
    }

    function testDefaultCreatedTemplateId()
    {
        $newValue = 5;

        $this->assertEquals($this->si->getDefaultCreatedTemplateId(), 1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDefaultCreatedTemplateId($newValue));
        $this->assertEquals($this->si->getDefaultCreatedTemplateId(), $newValue);
    }

    function testDefaultRequestStateKey()
    {
        $newValue = "Romeo";

        $this->assertEquals($this->si->getDefaultRequestStateKey(), "Open");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDefaultRequestStateKey($newValue));
        $this->assertEquals($this->si->getDefaultRequestStateKey(), $newValue);
    }

    function testDefaultRequestDeferredStateKey()
    {
        $newValue = "Juliet";

        $this->assertEquals($this->si->getDefaultRequestDeferredStateKey(), "Flagged users");

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setDefaultRequestDeferredStateKey($newValue));
        $this->assertEquals($this->si->getDefaultRequestDeferredStateKey(), $newValue);
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

    function testOAuthBaseUrl()
    {
        $newValue = "http://localhost/oauthAwesome/";

        $this->assertEquals($this->si->getOAuthBaseUrl(), null);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setOAuthBaseUrl($newValue));
        $this->assertEquals($this->si->getOAuthBaseUrl(), $newValue);
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

        $this->assertInternalType("array", $this->si->getCrossOriginResourceSharingHosts());
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

    function testIrcNotificationType()
    {
        $newValue = 256;

        $this->assertEquals($this->si->getIrcNotificationType(), 1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setIrcNotificationType($newValue));
        $this->assertEquals($this->si->getIrcNotificationType(), $newValue);
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
        $this->assertInternalType('array', $test1);
        $this->assertArrayNotHasKey("one", $test1);

        $this->assertInstanceOf(SiteConfiguration::class, $this->si->setTorExitPaths($newValue));

        $test2 = $this->si->getTorExitPaths();
        $this->assertInternalType('array', $test2);
        $this->assertArrayHasKey("one", $test2);
    }
}
