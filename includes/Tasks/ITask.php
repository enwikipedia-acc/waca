<?php

namespace Waca\Tasks;

use IAntiSpoofProvider;
use ILocationProvider;
use IRDnsProvider;
use IXffTrustProvider;
use PdoDatabase;
use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\Interfaces\IOAuthHelper;
use Waca\Helpers\Interfaces\ITypeAheadHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\SiteConfiguration;

interface ITask
{
	/**
	 * @return IEmailHelper
	 */
	public function getEmailHelper();

	/**
	 * @param IEmailHelper $emailHelper
	 */
	public function setEmailHelper($emailHelper);

	/**
	 * @return HttpHelper
	 */
	public function getHttpHelper();

	/**
	 * @param HttpHelper $httpHelper
	 */
	public function setHttpHelper($httpHelper);

	/**
	 * @return WikiTextHelper
	 */
	public function getWikiTextHelper();

	/**
	 * @param WikiTextHelper $wikiTextHelper
	 */
	public function setWikiTextHelper($wikiTextHelper);

	/**
	 * @return ILocationProvider
	 */
	public function getLocationProvider();

	/**
	 * @param ILocationProvider $locationProvider
	 */
	public function setLocationProvider(ILocationProvider $locationProvider);

	/**
	 * @return IXffTrustProvider
	 */
	public function getXffTrustProvider();

	/**
	 * @param IXffTrustProvider $xffTrustProvider
	 */
	public function setXffTrustProvider(IXffTrustProvider $xffTrustProvider);

	/**
	 * @return IRDnsProvider
	 */
	public function getRdnsProvider();

	/**
	 * @param IRDnsProvider $rdnsProvider
	 */
	public function setRdnsProvider($rdnsProvider);

	/**
	 * @return IAntiSpoofProvider
	 */
	public function getAntiSpoofProvider();

	/**
	 * @param IAntiSpoofProvider $antiSpoofProvider
	 */
	public function setAntiSpoofProvider($antiSpoofProvider);

	/**
	 * @return PdoDatabase
	 */
	public function getDatabase();

	/**
	 * @param PdoDatabase $database
	 */
	public function setDatabase($database);

	/**
	 * @return ITypeAheadHelper
	 */
	public function getTypeAheadHelper();

	/**
	 * @param ITypeAheadHelper $typeAheadHelper
	 */
	public function setTypeAheadHelper(ITypeAheadHelper $typeAheadHelper);

	/**
	 * @return IOAuthHelper
	 */
	public function getOAuthHelper();

	/**
	 * @param IOAuthHelper $oauthHelper
	 */
	public function setOAuthHelper($oauthHelper);

	public function execute();

	/**
	 * Sets the site configuration object for this page
	 *
	 * @param SiteConfiguration $configuration
	 */
	public function setSiteConfiguration($configuration);
}