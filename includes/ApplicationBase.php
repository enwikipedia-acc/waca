<?php

namespace Waca;

use CachedApiAntispoofProvider;
use CachedRDnsLookupProvider;
use Exception;
use FakeLocationProvider;
use PdoDatabase;
use Waca\Exceptions\EnvironmentException;
use Waca\Helpers\EmailHelper;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\OAuthHelper;
use Waca\Helpers\TypeAheadHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\Tasks\ITask;
use XffTrustProvider;

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

		/** @var int $actualVersion */
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
	 */
	abstract protected function main();

	/**
	 * Any cleanup tasks should go here
	 *
	 * Note that we need to be very careful here, as exceptions may have been thrown and handled.
	 * This should *only* be for cleaning up, no logic should go here.
	 */
	abstract protected function cleanupEnvironment();

	/**
	 * @param ITask             $page
	 * @param SiteConfiguration $siteConfiguration
	 * @param PdoDatabase       $database
	 */
	protected function setupHelpers(ITask $page, SiteConfiguration $siteConfiguration, PdoDatabase $database)
	{
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

		// todo: inject from configuration
		$page->setLocationProvider(new FakeLocationProvider($database, null));
		$page->setXffTrustProvider(new XffTrustProvider($siteConfiguration->getSquidList(), $database));

		$page->setRdnsProvider(new CachedRDnsLookupProvider($database));
		$page->setAntiSpoofProvider(new CachedApiAntispoofProvider());
		$page->setTypeAheadHelper(new TypeAheadHelper());
		$page->setOAuthHelper(new OAuthHelper(
			$siteConfiguration->getOAuthBaseUrl(),
			$siteConfiguration->getOAuthConsumerToken(),
			$siteConfiguration->getOAuthConsumerSecret(),
			$httpHelper,
			$siteConfiguration->getMediawikiWebServiceEndpoint()
		));
	}
}