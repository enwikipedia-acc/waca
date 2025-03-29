<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
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
    private $baseUrl = 'https://accounts.wmflabs.org';
    private $filePath = __DIR__ . '/..';
    private $schemaVersion = 51;
    private $debuggingTraceEnabled = false;
    private $debuggingCssBreakpointsEnabled = false;
    private $dataClearIp = '127.0.0.1';
    private $dataClearEmail = 'acc@toolserver.org';
    private $dataClearInterval = '15 DAY';
    private $forceIdentification = true;
    private $identificationCacheExpiry = '1 DAY';
    private $metaWikimediaWebServiceEndpoint = 'https://meta.wikimedia.org/w/api.php';
    private $enforceOAuth = false;
    private $emailConfirmationEnabled = true;
    private $emailConfirmationExpiryDays = 7;
    private $miserModeLimit = 25;
    private $squidList = array();
    private $useStrictTransportSecurity = false;
    private $userAgent = 'Wikipedia-ACC Tool/0.1 (+https://accounts.wmflabs.org/internal.php/team)';
    private $curlDisableVerifyPeer = false;
    private $useOAuthSignup = true;
    private $oauthConsumerToken;
    /** @var array */
    private $oauthLegacyConsumerTokens;
    private $oauthConsumerSecret;
    private $oauthIdentityGraceTime = '24 hours';
    private $oauthMediaWikiCanonicalServer = 'https://en.wikipedia.org';
    private $xffTrustedHostsFile = '../TrustedXFF/trusted-hosts.txt';
    private $crossOriginResourceSharingHosts = array(
        "https://en.wikipedia.org",
        "https://meta.wikimedia.org",
    );
    private $ircNotificationsEnabled = true;
    private $ircNotificationsInstance = 'Development';
    private $errorLog = 'errorlog';
    private $titleBlacklistEnabled = false;
    /** @var null|string $locationProviderApiKey */
    private $locationProviderApiKey = null;
    private $torExitPaths = array();
    private $creationBotUsername = '';
    private $creationBotPassword = '';
    private $curlCookieJar = __DIR__ . '/../../cookies.txt';
    private $yubicoApiId = 0;
    private $yubicoApiKey = "";
    private $totpEncryptionKey = "1234";
    private $identificationNoticeboardPage = 'Access to nonpublic personal data policy/Noticeboard';
    private $identificationNoticeboardWebserviceEndpoint = 'https://meta.wikimedia.org/w/api.php';
    private $registrationAllowed = true;
    private $cspReportUri = null;
    private $resourceCacheEpoch = 1;
    private $commonEmailDomains = ['gmail.com', 'hotmail.com', 'outlook.com'];
    private $banMaxIpBlockRange = [4 => 20, 6 => 48];
    private $banMaxIpRange = [4 => 16, 6 => 32];
    private $jobQueueBatchSize = 10;
    private $amqpConfiguration = ['host' => 'localhost', 'port' => 5672, 'user' => 'guest', 'password' => 'guest', 'vhost' => '/', 'exchange' => '', 'tls' => false];
    private $emailSender = 'accounts@wmflabs.org';
    private $acceptClientHints = [];
    private string $cookiePath = '/';
    private string $cookieSessionName = 'ACC';
    private array $offline = ['offline' => false, 'reason' => '', 'culprit' => ''];
    private array $databaseConfig = [
        'datasource' => 'mysql:host=localhost;dbname=waca',
        'username' => 'waca',
        'password' => 'waca'
    ];
    private string $privacyStatementPath = '';

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
     * @param int $schemaVersion
     *
     * @return SiteConfiguration
     */
    public function setSchemaVersion($schemaVersion)
    {
        $this->schemaVersion = $schemaVersion;

        return $this;
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

    public function getDebuggingCssBreakpointsEnabled() : bool
    {
        return $this->debuggingCssBreakpointsEnabled;
    }

    public function setDebuggingCssBreakpointsEnabled(bool $debuggingCssBreakpointsEnabled) : SiteConfiguration
    {
        $this->debuggingCssBreakpointsEnabled = $debuggingCssBreakpointsEnabled;

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

    /**
     * @param string $oauthIdentityGraceTime
     *
     * @return SiteConfiguration
     */
    public function setOauthIdentityGraceTime($oauthIdentityGraceTime)
    {
        $this->oauthIdentityGraceTime = $oauthIdentityGraceTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getOauthIdentityGraceTime()
    {
        return $this->oauthIdentityGraceTime;
    }

    /**
     * @param string $oauthMediaWikiCanonicalServer
     *
     * @return SiteConfiguration
     */
    public function setOauthMediaWikiCanonicalServer($oauthMediaWikiCanonicalServer)
    {
        $this->oauthMediaWikiCanonicalServer = $oauthMediaWikiCanonicalServer;

        return $this;
    }

    /**
     * @return string
     */
    public function getOauthMediaWikiCanonicalServer()
    {
        return $this->oauthMediaWikiCanonicalServer;
    }

    /**
     * @param string $creationBotUsername
     *
     * @return SiteConfiguration
     */
    public function setCreationBotUsername($creationBotUsername)
    {
        $this->creationBotUsername = $creationBotUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreationBotUsername()
    {
        return $this->creationBotUsername;
    }

    /**
     * @param string $creationBotPassword
     *
     * @return SiteConfiguration
     */
    public function setCreationBotPassword($creationBotPassword)
    {
        $this->creationBotPassword = $creationBotPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreationBotPassword()
    {
        return $this->creationBotPassword;
    }

    /**
     * @param string|null $curlCookieJar
     *
     * @return SiteConfiguration
     */
    public function setCurlCookieJar($curlCookieJar)
    {
        $this->curlCookieJar = $curlCookieJar;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurlCookieJar()
    {
        return $this->curlCookieJar;
    }

    public function getYubicoApiId()
    {
        return $this->yubicoApiId;
    }

    public function setYubicoApiId($id)
    {
        $this->yubicoApiId = $id;

        return $this;
    }

    public function getYubicoApiKey()
    {
        return $this->yubicoApiKey;
    }

    public function setYubicoApiKey($key)
    {
        $this->yubicoApiKey = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getTotpEncryptionKey()
    {
        return $this->totpEncryptionKey;
    }

    /**
     * @param string $totpEncryptionKey
     *
     * @return SiteConfiguration
     */
    public function setTotpEncryptionKey($totpEncryptionKey)
    {
        $this->totpEncryptionKey = $totpEncryptionKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentificationNoticeboardPage()
    {
        return $this->identificationNoticeboardPage;
    }

    /**
     * @param string $identificationNoticeboardPage
     *
     * @return SiteConfiguration
     */
    public function setIdentificationNoticeboardPage($identificationNoticeboardPage)
    {
        $this->identificationNoticeboardPage = $identificationNoticeboardPage;

        return $this;
    }

    public function setIdentificationNoticeboardWebserviceEndpoint(string $identificationNoticeboardWebserviceEndpoint
    ): SiteConfiguration {
        $this->identificationNoticeboardWebserviceEndpoint = $identificationNoticeboardWebserviceEndpoint;

        return $this;
    }

    public function getIdentificationNoticeboardWebserviceEndpoint(): string
    {
        return $this->identificationNoticeboardWebserviceEndpoint;
    }

    public function isRegistrationAllowed(): bool
    {
        return $this->registrationAllowed;
    }

    public function setRegistrationAllowed(bool $registrationAllowed): SiteConfiguration
    {
        $this->registrationAllowed = $registrationAllowed;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCspReportUri()
    {
        return $this->cspReportUri;
    }

    /**
     * @param string|null $cspReportUri
     *
     * @return SiteConfiguration
     */
    public function setCspReportUri($cspReportUri)
    {
        $this->cspReportUri = $cspReportUri;

        return $this;
    }

    /**
     * @return int
     */
    public function getResourceCacheEpoch(): int
    {
        return $this->resourceCacheEpoch;
    }

    /**
     * @param int $resourceCacheEpoch
     *
     * @return SiteConfiguration
     */
    public function setResourceCacheEpoch(int $resourceCacheEpoch): SiteConfiguration
    {
        $this->resourceCacheEpoch = $resourceCacheEpoch;

        return $this;
    }

    /**
     * @return array
     */
    public function getCommonEmailDomains(): array
    {
        return $this->commonEmailDomains;
    }

    /**
     * @param array $commonEmailDomains
     *
     * @return SiteConfiguration
     */
    public function setCommonEmailDomains(array $commonEmailDomains): SiteConfiguration
    {
        $this->commonEmailDomains = $commonEmailDomains;

        return $this;
    }

    /**
     * @param int[] $banMaxIpBlockRange
     *
     * @return SiteConfiguration
     */
    public function setBanMaxIpBlockRange(array $banMaxIpBlockRange): SiteConfiguration
    {
        $this->banMaxIpBlockRange = $banMaxIpBlockRange;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getBanMaxIpBlockRange(): array
    {
        return $this->banMaxIpBlockRange;
    }

    /**
     * @param int[] $banMaxIpRange
     *
     * @return SiteConfiguration
     */
    public function setBanMaxIpRange(array $banMaxIpRange): SiteConfiguration
    {
        $this->banMaxIpRange = $banMaxIpRange;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getBanMaxIpRange(): array
    {
        return $this->banMaxIpRange;
    }

    /**
     * @param array $oauthLegacyConsumerTokens
     *
     * @return SiteConfiguration
     */
    public function setOauthLegacyConsumerTokens(array $oauthLegacyConsumerTokens): SiteConfiguration
    {
        $this->oauthLegacyConsumerTokens = $oauthLegacyConsumerTokens;

        return $this;
    }

    /**
     * @return array
     */
    public function getOauthLegacyConsumerTokens(): array
    {
        return $this->oauthLegacyConsumerTokens;
    }

    /**
     * @return int
     */
    public function getJobQueueBatchSize(): int
    {
        return $this->jobQueueBatchSize;
    }

    /**
     * @param int $jobQueueBatchSize
     *
     * @return SiteConfiguration
     */
    public function setJobQueueBatchSize(int $jobQueueBatchSize): SiteConfiguration
    {
        $this->jobQueueBatchSize = $jobQueueBatchSize;

        return $this;
    }

    /**
     * @return array
     */
    public function getAmqpConfiguration(): array
    {
        return $this->amqpConfiguration;
    }

    /**
     * @param array $amqpConfiguration
     *
     * @return SiteConfiguration
     */
    public function setAmqpConfiguration(array $amqpConfiguration): SiteConfiguration
    {
        $this->amqpConfiguration = $amqpConfiguration;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailSender(): string
    {
        return $this->emailSender;
    }

    /**
     * @param string $emailSender
     *
     * @return SiteConfiguration
     */
    public function setEmailSender(string $emailSender): SiteConfiguration
    {
        $this->emailSender = $emailSender;

        return $this;
    }

    /**
     * @param array $acceptClientHints
     *
     * @return SiteConfiguration
     */
    public function setAcceptClientHints(array $acceptClientHints): SiteConfiguration
    {
        $this->acceptClientHints = $acceptClientHints;

        return $this;
    }

    /**
     * @return array
     */
    public function getAcceptClientHints(): array
    {
        return $this->acceptClientHints;
    }

    public function setCookiePath(string $cookiePath): SiteConfiguration
    {
        $this->cookiePath = $cookiePath;

        return $this;
    }

    public function getCookiePath(): string
    {
        return $this->cookiePath;
    }

    public function setCookieSessionName(string $cookieSessionName): SiteConfiguration
    {
        $this->cookieSessionName = $cookieSessionName;

        return $this;
    }

    public function getCookieSessionName(): string
    {
        return $this->cookieSessionName;
    }

    public function setOffline(array $offline): SiteConfiguration
    {
        $this->offline = $offline;

        return $this;
    }

    public function getOffline(): array
    {
        return $this->offline;
    }

    public function setDatabaseConfig(array $databaseConfig): SiteConfiguration
    {
        $this->databaseConfig = $databaseConfig;

        return $this;
    }

    public function getDatabaseConfig(): array
    {
        return $this->databaseConfig;
    }

    public function getPrivacyStatementPath(): string
    {
        return $this->privacyStatementPath;
    }

    public function setPrivacyStatementPath(string $privacyStatementPath): SiteConfiguration
    {
        $this->privacyStatementPath = $privacyStatementPath;

        return $this;
    }
}
