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
use Waca\Helpers\IrcNotificationHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\SiteConfiguration;

abstract class TaskBase implements ITask
{
	/** @var SiteConfiguration */
	private $siteConfiguration;
	/** @var IEmailHelper */
	private $emailHelper;
	/** @var HttpHelper */
	private $httpHelper;
	/** @var WikiTextHelper */
	private $wikiTextHelper;
	/** @var ILocationProvider */
	private $locationProvider;
	/** @var IXffTrustProvider */
	private $xffTrustProvider;
	/** @var IRDnsProvider */
	private $rdnsProvider;
	/** @var IAntiSpoofProvider */
	private $antiSpoofProvider;
	/** @var IOAuthHelper */
	private $oauthHelper;
	/** @var PdoDatabase */
	private $database;
	/** @var ITypeAheadHelper */
	private $typeAheadHelper;
	/** @var IrcNotificationHelper */
	private $notificationHelper;

	/**
	 * @return IEmailHelper
	 */
	final public function getEmailHelper()
	{
		return $this->emailHelper;
	}

	/**
	 * @param IEmailHelper $emailHelper
	 */
	final public function setEmailHelper($emailHelper)
	{
		$this->emailHelper = $emailHelper;
	}

	/**
	 * @return HttpHelper
	 */
	final public function getHttpHelper()
	{
		return $this->httpHelper;
	}

	/**
	 * @param HttpHelper $httpHelper
	 */
	final public function setHttpHelper($httpHelper)
	{
		$this->httpHelper = $httpHelper;
	}

	/**
	 * @return WikiTextHelper
	 */
	final public function getWikiTextHelper()
	{
		return $this->wikiTextHelper;
	}

	/**
	 * @param WikiTextHelper $wikiTextHelper
	 */
	final public function setWikiTextHelper($wikiTextHelper)
	{
		$this->wikiTextHelper = $wikiTextHelper;
	}

	/**
	 * @return ILocationProvider
	 */
	final public function getLocationProvider()
	{
		return $this->locationProvider;
	}

	/**
	 * @param ILocationProvider $locationProvider
	 */
	final public function setLocationProvider(ILocationProvider $locationProvider)
	{
		$this->locationProvider = $locationProvider;
	}

	/**
	 * @return IXffTrustProvider
	 */
	final public function getXffTrustProvider()
	{
		return $this->xffTrustProvider;
	}

	/**
	 * @param IXffTrustProvider $xffTrustProvider
	 */
	final public function setXffTrustProvider(IXffTrustProvider $xffTrustProvider)
	{
		$this->xffTrustProvider = $xffTrustProvider;
	}

	/**
	 * @return IRDnsProvider
	 */
	final public function getRdnsProvider()
	{
		return $this->rdnsProvider;
	}

	/**
	 * @param IRDnsProvider $rdnsProvider
	 */
	public function setRdnsProvider($rdnsProvider)
	{
		$this->rdnsProvider = $rdnsProvider;
	}

	/**
	 * @return IAntiSpoofProvider
	 */
	public function getAntiSpoofProvider()
	{
		return $this->antiSpoofProvider;
	}

	/**
	 * @param IAntiSpoofProvider $antiSpoofProvider
	 */
	public function setAntiSpoofProvider($antiSpoofProvider)
	{
		$this->antiSpoofProvider = $antiSpoofProvider;
	}

	/**
	 * @return PdoDatabase
	 */
	final public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * @param PdoDatabase $database
	 */
	final public function setDatabase($database)
	{
		$this->database = $database;
	}

	/**
	 * @return ITypeAheadHelper
	 */
	public function getTypeAheadHelper()
	{
		return $this->typeAheadHelper;
	}

	/**
	 * @param ITypeAheadHelper $typeAheadHelper
	 */
	public function setTypeAheadHelper(ITypeAheadHelper $typeAheadHelper)
	{
		$this->typeAheadHelper = $typeAheadHelper;
	}

	/**
	 * @return IOAuthHelper
	 */
	public function getOAuthHelper()
	{
		return $this->oauthHelper;
	}

	/**
	 * @param IOAuthHelper $oauthHelper
	 */
	public function setOAuthHelper($oauthHelper)
	{
		$this->oauthHelper = $oauthHelper;
	}

	abstract public function execute();

	/**
	 * @return IrcNotificationHelper
	 */
	public function getNotificationHelper()
	{
		return $this->notificationHelper;
	}

	/**
	 * @param IrcNotificationHelper $notificationHelper
	 */
	public function setNotificationHelper($notificationHelper)
	{
		$this->notificationHelper = $notificationHelper;
	}

	/**
	 * Gets the site configuration object
	 *
	 * @return SiteConfiguration
	 */
	final protected function getSiteConfiguration()
	{
		return $this->siteConfiguration;
	}

	/**
	 * Sets the site configuration object for this page
	 *
	 * @param SiteConfiguration $configuration
	 */
	final public function setSiteConfiguration($configuration)
	{
		$this->siteConfiguration = $configuration;
	}
}