<?php

namespace Waca\Tasks;

use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\Interfaces\IOAuthHelper;
use Waca\Helpers\Interfaces\ITypeAheadHelper;
use Waca\Helpers\IrcNotificationHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IAntiSpoofProvider;
use Waca\Providers\Interfaces\ILocationProvider;
use Waca\Providers\Interfaces\IRDnsProvider;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\SiteConfiguration;

interface ITask
{
	/**
	 * @return IEmailHelper
	 */
	public function getEmailHelper();

	/**
	 * @param IEmailHelper $emailHelper
	 * @return void
	 */
	public function setEmailHelper($emailHelper);

	/**
	 * @return HttpHelper
	 */
	public function getHttpHelper();

	/**
	 * @param HttpHelper $httpHelper
	 * @return void
	 */
	public function setHttpHelper($httpHelper);

	/**
	 * @return WikiTextHelper
	 */
	public function getWikiTextHelper();

	/**
	 * @param WikiTextHelper $wikiTextHelper
	 * @return void
	 */
	public function setWikiTextHelper($wikiTextHelper);

	/**
	 * @return ILocationProvider
	 */
	public function getLocationProvider();

	/**
	 * @param ILocationProvider $locationProvider
	 * @return void
	 */
	public function setLocationProvider(ILocationProvider $locationProvider);

	/**
	 * @return IXffTrustProvider
	 */
	public function getXffTrustProvider();

	/**
	 * @param IXffTrustProvider $xffTrustProvider
	 * @return void
	 */
	public function setXffTrustProvider(IXffTrustProvider $xffTrustProvider);

	/**
	 * @return IRDnsProvider
	 */
	public function getRdnsProvider();

	/**
	 * @param IRDnsProvider $rdnsProvider
	 * @return void
	 */
	public function setRdnsProvider($rdnsProvider);

	/**
	 * @return IAntiSpoofProvider
	 */
	public function getAntiSpoofProvider();

	/**
	 * @param IAntiSpoofProvider $antiSpoofProvider
	 * @return void
	 */
	public function setAntiSpoofProvider($antiSpoofProvider);

	/**
	 * @return PdoDatabase
	 */
	public function getDatabase();

	/**
	 * @param PdoDatabase $database
	 * @return void
	 */
	public function setDatabase($database);

	/**
	 * @return ITypeAheadHelper
	 */
	public function getTypeAheadHelper();

	/**
	 * @param ITypeAheadHelper $typeAheadHelper
	 * @return void
	 */
	public function setTypeAheadHelper(ITypeAheadHelper $typeAheadHelper);

	/**
	 * @return IOAuthHelper
	 */
	public function getOAuthHelper();

	/**
	 * @param IOAuthHelper $oauthHelper
	 * @return void
	 */
	public function setOAuthHelper($oauthHelper);

	/**
	 * @return void
	 */
	public function execute();

	/**
	 * Sets the site configuration object for this page
	 *
	 * @param SiteConfiguration $configuration
	 * @return void
	 */
	public function setSiteConfiguration($configuration);

	/**
	 * @return IrcNotificationHelper
	 */
	public function getNotificationHelper();

	/**
	 * @param IrcNotificationHelper $notificationHelper
	 * @return void
	 */
	public function setNotificationHelper($notificationHelper);
}