<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

/**************************************************************************
 **********                   IMPORTANT NOTICE                    **********
 ***************************************************************************
 ** YOU MUST OVERRIDE THE CONFIGURATION IN THIS FILE WITH A LOCAL COPY!!! **
 ** IT IS VERY IMPORTANT THAT THAT FILE IS CALLED config.local.inc.php    **
 ***************************************************************************/

/*********************************
 * Databases and stuff
 */

// Main database location and access details.
$toolserver_username = "";
$toolserver_password = "";
$toolserver_host = "";
$toolserver_database = "";

/**********************************
 * File paths etc
 */

$metaWikimediaWebServiceEndpoint = "https://meta.wikimedia.org/w/api.php";

// URL of the current copy of the tool.
$baseurl = "https://accounts.wmflabs.org";

// Set up cookies and session information.
$cookiepath = '/acc/';
$sessionname = 'ACC';

$xff_trusted_hosts_file = '../TrustedXFF/trusted-hosts.txt';
/************************************
 * Tool downtime
 */

$dontUseDb = 0; // Disable the tool completely.
$dontUseDbReason = ""; // Reason for disabling the tool.
$dontUseDbCulprit = ""; // Your name, or the person who broke the tool.

/**************************************
 * ACCBot IRC bot
 */

$ircBotNotificationsEnabled = 1; // Enable Helpmebot's notifications.
// Name of this instance of the tool.
// This name would be used by the bot as reference point.
$whichami = 'Live';

// AMQP configuration for notifications.
$amqpConfiguration = ['host' => 'localhost', 'port' => 5672, 'user' => 'guest', 'password' => 'guest', 'vhost' => '/', 'exchange' => '', 'tls' => false];

/***************************************
 * Email confirmation
 */

// Enable request email confirmation.
$enableEmailConfirm = 1;
// Number of days that are given for a requestor to confirm their email address.
$emailConfirmationExpiryDays = 7;

/**************************************
 * Interface registration, interface users, etc.
 */

$allowRegistration = true;

// Force identification to the foundation
$forceIdentification = true;

// Time to cache positive automatic identification results, as a MySQL time interval
$identificationCacheExpiry = "1 DAY";

$communityUsername = "[Community]";

/************************************
 * OAuth Configuration
 */

$oauthConsumerToken = "";
$oauthSecretToken = "";

// Formerly-used OAuth tokens to permit reading identities from
$oauthLegacyTokens = [];

$oauthMediaWikiCanonicalServer = "http://en.wikipedia.org";

$useOauthSignup = true;
$enforceOAuth = false;

// Password for the creation bot when this is used in place of OAuth
$creationBotUsername = '';
$creationBotPassword = '';

/************************************
 * Providers Configuration
 */

// IP GeoLocation
// ------------------------
// To set this up, change the class to "IpLocationProvider", and put *your* ipinfodb API key in.
// You'll need to sign up at IpInfoDb.com to get an API key - it's free.
$locationProviderApiKey = null; // ipinfodb api key

/***********************************
 * Data clear script
 */

$dataclear_interval = '15 DAY';

/***********************************
 * Other stuff that doesn't fit in.
 */

$enableTitleblacklist = 0; // Enable Title Blacklist checks.

// user agent of the tool.
$toolUserAgent = "Wikipedia-ACC Tool/0.1 (+https://accounts.wmflabs.org/internal.php/team)";

// list of squid proxies requests go through.
$squidIpList = array();

// miser mode
$requestLimitShowOnly = 25;

// HSTS expiry - use false to disable header.
$strictTransportSecurityExpiry = false;

// CSP violation report URI
$cspReportUri = null;

// Must be disabled in production.
$enableErrorTrace = false;
$enableCssBreakpoints = false;

// Dangerous.
// Don't set this.
// Definitely don't set this if there's sensitive data stored here you care about such as OAuth credentials.
$curlDisableSSLVerifyPeer = false;

// Change this to be outside the web directory.
$curlCookieJar = __DIR__ . '/../cookies.txt';

$yubicoApiId = 0;
$yubicoApiKey = "";

$totpEncryptionKey = "1234";

// external resource cache epoch value. Bump me to force clients to reload assets
$resourceCacheEpoch = 1;

$commonEmailDomains = ['gmail.com', 'hotmail.com', 'outlook.com'];

// limit for block/drop ban actions
$banMaxIpBlockRange = [4 => 20, 6 => 48];
// limit for *all* ban actions, including block/drop.
$banMaxIpRange = [4 => 16, 6 => 32];

$jobQueueBatchSize = 10;

$emailSender = 'accounts@wmflabs.org';

/**************************************************************************
 **********                   IMPORTANT NOTICE                    **********
 ***************************************************************************
 **     DON'T ADD ANY NEW CONFIGURATION OPTIONS BELOW THIS LINE!!!        **
 **     THEY WILL NOT BE CHANGABLE BY THE LOCAL CONFIGURATION FILE.       **
 ***************************************************************************/

// Retriving the local configuration file.
require_once('config.local.inc.php');

$cDatabaseConfig = array(
    "acc"           => array(
        "dsrcname" => "mysql:host=" . $toolserver_host . ";dbname=" . $toolserver_database,
        "username" => $toolserver_username,
        "password" => $toolserver_password,
        "options"  => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'),
    ),
);

// //Keep the included files from being executed.
define("ACC", 1);

// Sets the values of the cookie configuration options.
ini_set('session.cookie_path', $cookiepath);
ini_set('session.name', $sessionname);
ini_set('user_agent', $toolUserAgent);

foreach (array(
    "mbstring", // unicode and stuff
    "pdo",
    "pdo_mysql", // new database module
    "session",
    "date",
    "pcre", // core stuff
    "curl", // mediawiki api access etc
    "openssl", // token generation
) as $x) {
    if (!extension_loaded($x)) {
        die("extension $x is required.");
    }
}

// Set up the AutoLoader
require_once(__DIR__ . "/includes/AutoLoader.php");
spl_autoload_register('Waca\\AutoLoader::load');
require_once(__DIR__ . '/vendor/autoload.php');

// Crap that's needed for libraries. >:(
/**
 * Don't use me. I'm only here because the MediaWiki OAuth library we're using requires it.
 *
 * @param $section
 * @param $message
 * @scrutinizer ignore-unused
 */
function wfDebugLog($section, $message)
{
}

// Initialise the site configuration object
/** @noinspection PhpFullyQualifiedNameUsageInspection */
$siteConfiguration = new \Waca\SiteConfiguration();

$siteConfiguration->setBaseUrl($baseurl)
    ->setFilePath(__DIR__)
    ->setDebuggingTraceEnabled($enableErrorTrace)
    ->setDebuggingCssBreakpointsEnabled($enableCssBreakpoints)
    ->setForceIdentification($forceIdentification)
    ->setIdentificationCacheExpiry($identificationCacheExpiry)
    ->setMetaWikimediaWebServiceEndpoint($metaWikimediaWebServiceEndpoint)
    ->setEnforceOAuth($enforceOAuth)
    ->setEmailConfirmationEnabled($enableEmailConfirm == 1)
    ->setEmailConfirmationExpiryDays($emailConfirmationExpiryDays)
    ->setMiserModeLimit($requestLimitShowOnly)
    ->setSquidList($squidIpList)
    ->setUseStrictTransportSecurity($strictTransportSecurityExpiry)
    ->setUserAgent($toolUserAgent)
    ->setCurlDisableVerifyPeer($curlDisableSSLVerifyPeer)
    ->setUseOAuthSignup($useOauthSignup)
    ->setOAuthConsumerToken($oauthConsumerToken)
    ->setOAuthLegacyConsumerTokens($oauthLegacyTokens)
    ->setOAuthConsumerSecret($oauthSecretToken)
    ->setOauthMediaWikiCanonicalServer($oauthMediaWikiCanonicalServer)
    ->setDataClearInterval($dataclear_interval)
    ->setXffTrustedHostsFile($xff_trusted_hosts_file)
    ->setIrcNotificationsEnabled($ircBotNotificationsEnabled == 1)
    ->setIrcNotificationsInstance($whichami)
    ->setTitleBlacklistEnabled($enableTitleblacklist == 1)
    ->setTorExitPaths(array_merge(gethostbynamel('en.wikipedia.org'), gethostbynamel('accounts.wmflabs.org')))
    ->setCreationBotUsername($creationBotUsername)
    ->setCreationBotPassword($creationBotPassword)
    ->setCurlCookieJar($curlCookieJar)
    ->setYubicoApiId($yubicoApiId)
    ->setYubicoApiKey($yubicoApiKey)
    ->setTotpEncryptionKey($totpEncryptionKey)
    ->setRegistrationAllowed($allowRegistration)
    ->setCspReportUri($cspReportUri)
    ->setResourceCacheEpoch($resourceCacheEpoch)
    ->setLocationProviderApiKey($locationProviderApiKey)
    ->setCommonEmailDomains($commonEmailDomains)
    ->setBanMaxIpRange($banMaxIpRange)
    ->setBanMaxIpBlockRange($banMaxIpBlockRange)
    ->setJobQueueBatchSize($jobQueueBatchSize)
    ->setAmqpConfiguration($amqpConfiguration)
    ->setEmailSender($emailSender);
