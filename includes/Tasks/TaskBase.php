<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tasks;

use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\Interfaces\IOAuthHelper;
use Waca\Helpers\IrcNotificationHelper;
use Waca\Helpers\WikiTextHelper;
use Waca\PdoDatabase;
use Waca\Providers\Interfaces\IAntiSpoofProvider;
use Waca\Providers\Interfaces\ILocationProvider;
use Waca\Providers\Interfaces\IRDnsProvider;
use Waca\Providers\Interfaces\IXffTrustProvider;
use Waca\Providers\TorExitProvider;
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
    /** @var IrcNotificationHelper */
    private $notificationHelper;
    /** @var TorExitProvider */
    private $torExitProvider;

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

    /**
     * @return void
     */
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
     * @return TorExitProvider
     */
    public function getTorExitProvider()
    {
        return $this->torExitProvider;
    }

    /**
     * @param TorExitProvider $torExitProvider
     */
    public function setTorExitProvider($torExitProvider)
    {
        $this->torExitProvider = $torExitProvider;
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