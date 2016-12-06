<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

/**
 * Class SiteConfiguration
 *
 * IMPORTANT: This class must never throw an exception or trigger an error. It's used in the error handler.
 *
 * @package Waca
 */
class SiteConfiguration
{
    private $baseUrl;
    private $filePath;
    private $schemaVersion = 23;
    private $debuggingTraceEnabled;
    private $dataClearIp = '127.0.0.1';
    private $dataClearEmail = 'acc@toolserver.org';
    private $dataClearInterval = '15 DAY';
    private $forceIdentification = true;
    private $identificationCacheExpiry = '1 DAY';
    private $mediawikiScriptPath = 'https://en.wikipedia.org/w/index.php';
    private $mediawikiWebServiceEndpoint = 'https://en.wikipedia.org/w/api.php';
    private $metaWikimediaWebServiceEndpoint = 'https://meta.wikimedia.org/w/api.php';
    private $enforceOAuth = true;
    private $emailConfirmationEnabled = true;
    private $emailConfirmationExpiryDays = 7;
    private $miserModeLimit = 25;
    private $requestStates = array(
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
    );
    private $squidList = array();
    private $defaultCreatedTemplateId = 1;
    private $defaultRequestStateKey = 'Open';
    private $defaultRequestDeferredStateKey = 'Flagged users';
    private $useStrictTransportSecurity = false;
    private $userAgent = 'Wikipedia-ACC Tool/0.1 (+https://accounts.wmflabs.org/internal.php/team)';
    private $curlDisableVerifyPeer = false;
    private $useOAuthSignup = true;
    private $oauthBaseUrl;
    private $oauthConsumerToken;
    private $oauthConsumerSecret;
    private $xffTrustedHostsFile = '../TrustedXFF/trusted-hosts.txt';
    private $crossOriginResourceSharingHosts = array(
        "http://en.wikipedia.org",
        "https://en.wikipedia.org",
        "http://meta.wikimedia.org",
        "https://meta.wikimedia.org",
    );
    private $ircNotificationType = 1;
    private $ircNotificationsEnabled = true;
    private $ircNotificationsInstance = 'Development';
    private $errorLog = 'errorlog';
    private $titleBlacklistEnabled = false;
    /** @var null|string $locationProviderApiKey */
    private $locationProviderApiKey = null;
    private $torExitPaths = array();

    /**
     * Gets the base URL of the tool
     *
     * If the internal page of the tool is at http://localhost/path/internal.php, this would be set to
     * http://localhost/path
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     *
     * @return SiteConfiguration
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Path on disk to the directory containing the tool's code
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     *
     * @return SiteConfiguration
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return int
     */
    public function getSchemaVersion()
    {
        return $this->schemaVersion;
    }

    /**
     * @return mixed
     */
    public function getDebuggingTraceEnabled()
    {
        return $this->debuggingTraceEnabled;
    }

    /**
     * @param mixed $debuggingTraceEnabled
     *
     * @return SiteConfiguration
     */
    public function setDebuggingTraceEnabled($debuggingTraceEnabled)
    {
        $this->debuggingTraceEnabled = $debuggingTraceEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataClearIp()
    {
        return $this->dataClearIp;
    }

    /**
     * @param string $dataClearIp
     *
     * @return SiteConfiguration
     */
    public function setDataClearIp($dataClearIp)
    {
        $this->dataClearIp = $dataClearIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataClearEmail()
    {
        return $this->dataClearEmail;
    }

    /**
     * @param string $dataClearEmail
     *
     * @return SiteConfiguration
     */
    public function setDataClearEmail($dataClearEmail)
    {
        $this->dataClearEmail = $dataClearEmail;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getForceIdentification()
    {
        return $this->forceIdentification;
    }

    /**
     * @param boolean $forceIdentification
     *
     * @return SiteConfiguration
     */
    public function setForceIdentification($forceIdentification)
    {
        $this->forceIdentification = $forceIdentification;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificationCacheExpiry()
    {
        return $this->identificationCacheExpiry;
    }

    /**
     * @param string $identificationCacheExpiry
     *
     * @return SiteConfiguration
     */
    public function setIdentificationCacheExpiry($identificationCacheExpiry)
    {
        $this->identificationCacheExpiry = $identificationCacheExpiry;

        return $this;
    }

    /**
     * @return string
     */
    public function getMediawikiScriptPath()
    {
        return $this->mediawikiScriptPath;
    }

    /**
     * @param string $mediawikiScriptPath
     *
     * @return SiteConfiguration
     */
    public function setMediawikiScriptPath($mediawikiScriptPath)
    {
        $this->mediawikiScriptPath = $mediawikiScriptPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getMediawikiWebServiceEndpoint()
    {
        return $this->mediawikiWebServiceEndpoint;
    }

    /**
     * @param string $mediawikiWebServiceEndpoint
     *
     * @return SiteConfiguration
     */
    public function setMediawikiWebServiceEndpoint($mediawikiWebServiceEndpoint)
    {
        $this->mediawikiWebServiceEndpoint = $mediawikiWebServiceEndpoint;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaWikimediaWebServiceEndpoint()
    {
        return $this->metaWikimediaWebServiceEndpoint;
    }

    /**
     * @param string $metaWikimediaWebServiceEndpoint
     *
     * @return SiteConfiguration
     */
    public function setMetaWikimediaWebServiceEndpoint($metaWikimediaWebServiceEndpoint)
    {
        $this->metaWikimediaWebServiceEndpoint = $metaWikimediaWebServiceEndpoint;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnforceOAuth()
    {
        return $this->enforceOAuth;
    }

    /**
     * @param boolean $enforceOAuth
     *
     * @return SiteConfiguration
     */
    public function setEnforceOAuth($enforceOAuth)
    {
        $this->enforceOAuth = $enforceOAuth;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEmailConfirmationEnabled()
    {
        return $this->emailConfirmationEnabled;
    }

    /**
     * @param boolean $emailConfirmationEnabled
     *
     * @return $this
     */
    public function setEmailConfirmationEnabled($emailConfirmationEnabled)
    {
        $this->emailConfirmationEnabled = $emailConfirmationEnabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getMiserModeLimit()
    {
        return $this->miserModeLimit;
    }

    /**
     * @param int $miserModeLimit
     *
     * @return SiteConfiguration
     */
    public function setMiserModeLimit($miserModeLimit)
    {
        $this->miserModeLimit = $miserModeLimit;

        return $this;
    }

    /**
     * @return array
     */
    public function getRequestStates()
    {
        return $this->requestStates;
    }

    /**
     * @param array $requestStates
     *
     * @return SiteConfiguration
     */
    public function setRequestStates($requestStates)
    {
        $this->requestStates = $requestStates;

        return $this;
    }

    /**
     * @return array
     */
    public function getSquidList()
    {
        return $this->squidList;
    }

    /**
     * @param array $squidList
     *
     * @return SiteConfiguration
     */
    public function setSquidList($squidList)
    {
        $this->squidList = $squidList;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultCreatedTemplateId()
    {
        return $this->defaultCreatedTemplateId;
    }

    /**
     * @param int $defaultCreatedTemplateId
     *
     * @return SiteConfiguration
     */
    public function setDefaultCreatedTemplateId($defaultCreatedTemplateId)
    {
        $this->defaultCreatedTemplateId = $defaultCreatedTemplateId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultRequestStateKey()
    {
        return $this->defaultRequestStateKey;
    }

    /**
     * @param string $defaultRequestStateKey
     *
     * @return SiteConfiguration
     */
    public function setDefaultRequestStateKey($defaultRequestStateKey)
    {
        $this->defaultRequestStateKey = $defaultRequestStateKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultRequestDeferredStateKey()
    {
        return $this->defaultRequestDeferredStateKey;
    }

    /**
     * @param string $defaultRequestDeferredStateKey
     *
     * @return SiteConfiguration
     */
    public function setDefaultRequestDeferredStateKey($defaultRequestDeferredStateKey)
    {
        $this->defaultRequestDeferredStateKey = $defaultRequestDeferredStateKey;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseStrictTransportSecurity()
    {
        return $this->useStrictTransportSecurity;
    }

    /**
     * @param boolean $useStrictTransportSecurity
     *
     * @return SiteConfiguration
     */
    public function setUseStrictTransportSecurity($useStrictTransportSecurity)
    {
        $this->useStrictTransportSecurity = $useStrictTransportSecurity;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     *
     * @return SiteConfiguration
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getCurlDisableVerifyPeer()
    {
        return $this->curlDisableVerifyPeer;
    }

    /**
     * @param boolean $curlDisableVerifyPeer
     *
     * @return SiteConfiguration
     */
    public function setCurlDisableVerifyPeer($curlDisableVerifyPeer)
    {
        $this->curlDisableVerifyPeer = $curlDisableVerifyPeer;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseOAuthSignup()
    {
        return $this->useOAuthSignup;
    }

    /**
     * @param boolean $useOAuthSignup
     *
     * @return SiteConfiguration
     */
    public function setUseOAuthSignup($useOAuthSignup)
    {
        $this->useOAuthSignup = $useOAuthSignup;

        return $this;
    }

    /**
     * @return string
     */
    public function getOAuthBaseUrl()
    {
        return $this->oauthBaseUrl;
    }

    /**
     * @param string $oauthBaseUrl
     *
     * @return SiteConfiguration
     */
    public function setOAuthBaseUrl($oauthBaseUrl)
    {
        $this->oauthBaseUrl = $oauthBaseUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOAuthConsumerToken()
    {
        return $this->oauthConsumerToken;
    }

    /**
     * @param mixed $oauthConsumerToken
     *
     * @return SiteConfiguration
     */
    public function setOAuthConsumerToken($oauthConsumerToken)
    {
        $this->oauthConsumerToken = $oauthConsumerToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOAuthConsumerSecret()
    {
        return $this->oauthConsumerSecret;
    }

    /**
     * @param mixed $oauthConsumerSecret
     *
     * @return SiteConfiguration
     */
    public function setOAuthConsumerSecret($oauthConsumerSecret)
    {
        $this->oauthConsumerSecret = $oauthConsumerSecret;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataClearInterval()
    {
        return $this->dataClearInterval;
    }

    /**
     * @param string $dataClearInterval
     *
     * @return SiteConfiguration
     */
    public function setDataClearInterval($dataClearInterval)
    {
        $this->dataClearInterval = $dataClearInterval;

        return $this;
    }

    /**
     * @return string
     */
    public function getXffTrustedHostsFile()
    {
        return $this->xffTrustedHostsFile;
    }

    /**
     * @param string $xffTrustedHostsFile
     *
     * @return SiteConfiguration
     */
    public function setXffTrustedHostsFile($xffTrustedHostsFile)
    {
        $this->xffTrustedHostsFile = $xffTrustedHostsFile;

        return $this;
    }

    /**
     * @return array
     */
    public function getCrossOriginResourceSharingHosts()
    {
        return $this->crossOriginResourceSharingHosts;
    }

    /**
     * @param array $crossOriginResourceSharingHosts
     *
     * @return SiteConfiguration
     */
    public function setCrossOriginResourceSharingHosts($crossOriginResourceSharingHosts)
    {
        $this->crossOriginResourceSharingHosts = $crossOriginResourceSharingHosts;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIrcNotificationsEnabled()
    {
        return $this->ircNotificationsEnabled;
    }

    /**
     * @param boolean $ircNotificationsEnabled
     *
     * @return SiteConfiguration
     */
    public function setIrcNotificationsEnabled($ircNotificationsEnabled)
    {
        $this->ircNotificationsEnabled = $ircNotificationsEnabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getIrcNotificationType()
    {
        return $this->ircNotificationType;
    }

    /**
     * @param int $ircNotificationType
     *
     * @return SiteConfiguration
     */
    public function setIrcNotificationType($ircNotificationType)
    {
        $this->ircNotificationType = $ircNotificationType;

        return $this;
    }

    /**
     * @param string $errorLog
     *
     * @return SiteConfiguration
     */
    public function setErrorLog($errorLog)
    {
        $this->errorLog = $errorLog;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * @param int $emailConfirmationExpiryDays
     *
     * @return SiteConfiguration
     */
    public function setEmailConfirmationExpiryDays($emailConfirmationExpiryDays)
    {
        $this->emailConfirmationExpiryDays = $emailConfirmationExpiryDays;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmailConfirmationExpiryDays()
    {
        return $this->emailConfirmationExpiryDays;
    }

    /**
     * @param string $ircNotificationsInstance
     *
     * @return SiteConfiguration
     */
    public function setIrcNotificationsInstance($ircNotificationsInstance)
    {
        $this->ircNotificationsInstance = $ircNotificationsInstance;

        return $this;
    }

    /**
     * @return string
     */
    public function getIrcNotificationsInstance()
    {
        return $this->ircNotificationsInstance;
    }

    /**
     * @param boolean $titleBlacklistEnabled
     *
     * @return SiteConfiguration
     */
    public function setTitleBlacklistEnabled($titleBlacklistEnabled)
    {
        $this->titleBlacklistEnabled = $titleBlacklistEnabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getTitleBlacklistEnabled()
    {
        return $this->titleBlacklistEnabled;
    }

    /**
     * @param string|null $locationProviderApiKey
     *
     * @return SiteConfiguration
     */
    public function setLocationProviderApiKey($locationProviderApiKey)
    {
        $this->locationProviderApiKey = $locationProviderApiKey;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLocationProviderApiKey()
    {
        return $this->locationProviderApiKey;
    }

    /**
     * @param array $torExitPaths
     *
     * @return SiteConfiguration
     */
    public function setTorExitPaths($torExitPaths)
    {
        $this->torExitPaths = $torExitPaths;

        return $this;
    }

    /**
     * @return array
     */
    public function getTorExitPaths()
    {
        return $this->torExitPaths;
    }
}