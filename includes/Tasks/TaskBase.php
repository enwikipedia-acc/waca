<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Tasks;

use Waca\Helpers\HttpHelper;
use Waca\Helpers\Interfaces\IEmailHelper;
use Waca\Helpers\Interfaces\IOAuthProtocolHelper;
use Waca\Helpers\IrcNotificationHelper;
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
    /** @var ILocationProvider */
    private $locationProvider;
    /** @var IXffTrustProvider */
    private $xffTrustProvider;
    /** @var IRDnsProvider */
    private $rdnsProvider;
    /** @var IAntiSpoofProvider */
    private $antiSpoofProvider;
    /** @var IOAuthProtocolHelper */
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
     * @return IOAuthProtocolHelper
     */
    public function getOAuthProtocolHelper()
    {
        return $this->oauthHelper;
    }

    /**
     * @param IOAuthProtocolHelper $oauthProtocolHelper
     */
    public function setOAuthProtocolHelper($oauthProtocolHelper)
    {
        $this->oauthHelper = $oauthProtocolHelper;
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