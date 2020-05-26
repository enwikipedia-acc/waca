<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Exception;
use Waca\Exceptions\EnvironmentException;
use Waca\Helpers\EmailHelper;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\IrcNotificationHelper;
use Waca\Helpers\OAuthProtocolHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\Providers\CachedApiAntispoofProvider;
use Waca\Providers\CachedRDnsLookupProvider;
use Waca\Providers\FakeLocationProvider;
use Waca\Providers\IpLocationProvider;
use Waca\Providers\TorExitProvider;
use Waca\Providers\XffTrustProvider;
use Waca\Tasks\ITask;

abstract class ApplicationBase
{
    private $configuration;

    public function __construct(SiteConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Application entry point.
     *
     * Sets up the environment and runs the application, performing any global cleanup operations when done.
     */
    public function run()
    {
        try {
            if ($this->setupEnvironment()) {
                $this->main();
            }
        }
        catch (Exception $ex) {
            print $ex->getMessage();
        }
        finally {
            $this->cleanupEnvironment();
        }
    }

    /**
     * Environment setup
     *
     * This method initialises the tool environment. If the tool cannot be initialised correctly, it will return false
     * and shut down prematurely.
     *
     * @return bool
     * @throws EnvironmentException
     */
    protected function setupEnvironment()
    {
        $this->setupDatabase();

        return true;
    }

    /**
     * @return PdoDatabase
     * @throws EnvironmentException
     * @throws Exception
     */
    protected function setupDatabase()
    {
        // check the schema version
        $database = PdoDatabase::getDatabaseConnection('acc');

        $actualVersion = (int)$database->query('SELECT version FROM schemaversion')->fetchColumn();
        if ($actualVersion !== $this->getConfiguration()->getSchemaVersion()) {
            throw new EnvironmentException('Database schema is wrong version! Please either update configuration or database.');
        }

        return $database;
    }

    /**
     * @return SiteConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Main application logic
     * @return void
     */
    abstract protected function main();

    /**
     * Any cleanup tasks should go here
     *
     * Note that we need to be very careful here, as exceptions may have been thrown and handled.
     * This should *only* be for cleaning up, no logic should go here.
     *
     * @return void
     */
    abstract protected function cleanupEnvironment();

    /**
     * @param ITask             $page
     * @param SiteConfiguration $siteConfiguration
     * @param PdoDatabase       $database
     * @param PdoDatabase       $notificationsDatabase
     *
     * @return void
     */
    protected function setupHelpers(
        ITask $page,
        SiteConfiguration $siteConfiguration,
        PdoDatabase $database,
        PdoDatabase $notificationsDatabase = null
    ) {
        $page->setSiteConfiguration($siteConfiguration);

        // setup the global database object
        $page->setDatabase($database);

        // set up helpers and inject them into the page.
        $httpHelper = new HttpHelper(
            $siteConfiguration->getUserAgent(),
            $siteConfiguration->getCurlDisableVerifyPeer()
        );

        $page->setEmailHelper(new EmailHelper());
        $page->setHttpHelper($httpHelper);
        $page->setWikiTextHelper(new WikiTextHelper($siteConfiguration, $page->getHttpHelper()));

        if ($siteConfiguration->getLocationProviderApiKey() === null) {
            $page->setLocationProvider(new FakeLocationProvider());
        }
        else {
            $page->setLocationProvider(
                new IpLocationProvider(
                    $database,
                    $siteConfiguration->getLocationProviderApiKey(),
                    $httpHelper
                ));
        }

        $page->setXffTrustProvider(new XffTrustProvider($siteConfiguration->getSquidList(), $database));

        $page->setRdnsProvider(new CachedRDnsLookupProvider($database));

        $page->setAntiSpoofProvider(new CachedApiAntispoofProvider(
            $database,
            $this->getConfiguration()->getMediawikiWebServiceEndpoint(),
            $httpHelper));

        $page->setOAuthProtocolHelper(new OAuthProtocolHelper(
            $siteConfiguration->getOAuthBaseUrl(),
            $siteConfiguration->getOAuthConsumerToken(),
            $siteConfiguration->getOAuthConsumerSecret(),
            $httpHelper,
            $siteConfiguration->getMediawikiWebServiceEndpoint()
        ));

        $page->setNotificationHelper(new IrcNotificationHelper(
            $siteConfiguration,
            $database,
            $notificationsDatabase));

        $page->setTorExitProvider(new TorExitProvider($database));
    }
}