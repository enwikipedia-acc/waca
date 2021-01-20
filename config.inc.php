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

$toolserver_notification_database = "notifications";
$toolserver_notification_dbhost = "dbmaster.srv.stwalkerster.net";
$notifications_username = "";
$notifications_password = "";

// The antispoof configuration.
$antispoof_equivset = "equivset.php";
$antispoof_host = "sql-s1";
$antispoof_db = "enwiki_p";
$antispoof_table = "spoofuser";

/**********************************
 * File paths etc
 */

$mediawikiWebServiceEndpoint = "https://en.wikipedia.org/w/api.php";
$mediawikiScriptPath = "https://en.wikipedia.org/w/index.php";
$metaWikimediaWebServiceEndpoint = "https://meta.wikimedia.org/w/api.php";

// URL of the current copy of the tool.
$baseurl = "https://accounts.wmflabs.org";

// Pathname to the local installation of Peachy.
$peachyPath = "";

// Location outside web directory to place temporary files.
$varfilepath = "/projects/acc/";

// Set up cookies and session information.
$cookiepath = '/acc/';
$sessionname = 'ACC';

$xff_trusted_hosts_file = '../TrustedXFF/trusted-hosts.txt';
/************************************
 * Tool downtime
 */

$dontUseDb = 0; // Disable the tool completely.
$dontUseWikiDb = 0; // Disable access to the Wiki database.
$dontUseDbReason = ""; // Reason for disabling the tool.
$dontUseDbCulprit = ""; // Your name, or the person who broke the tool.

/**************************************
 * ACCBot IRC bot
 */

$ircBotDaemonise = true; // Run the IRC bot as a daemon, detached from the terminal.

$ircBotNickServPassword = ""; // Password for ACCBot's Nickserv account.
$ircBotCommunicationKey = ""; // Key used to communicate with the ACCBot.
$ircBotNetworkHost = "chat.freenode.net"; // The host to use for connecting.
$ircBotNetworkPort = 6667; // The port on the particular host.
$ircBotChannel = "#wikipedia-en-accounts"; // The channel in which the discussions are.
$ircBotNickname = "ACCBot"; // The nickname of the ACCBot.
$ircBotCommandTrigger = '!'; // The ACCBot's command trigger.

$ircBotNotificationType = 1; // Helpmebot's notification type ID.
$ircBotNotificationsEnabled = 1; // Enable Helpmebot's notifications.
// Name of this instance of the tool.
// This name would be used by the bot as reference point.	
$whichami = 'Live';

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

// Parameters for performing a newbie check on tool registration.
$onRegistrationNewbieCheck = true; // Enable the newbie checking.
$onRegistrationNewbieCheckEditCount = 20; // Minimum amount of edits on Wikipedia.
$onRegistrationNewbieCheckAge = 5184000; // Account age on Wikipedia in seconds.

// Force identification to the foundation
$forceIdentification = true;

// Time to cache positive automatic identification results, as a MySQL time interval
$identificationCacheExpiry = "1 DAY";

// minimum password version
//   0 = hashed
//   1 = hashed, salted
$minimumPasswordVersion = 0;

$communityUsername = "[Community]";

/***********************************
 * Reservations
 */

// Reserve requests to a specific user by default.
// Adapted from livehack by st - use the userid, zero for unreserved.
$defaultReserver = 0;

/************************************
 * OAuth Configuration
 */

$oauthConsumerToken = "";
$oauthSecretToken = "";

// path to Special:OAuth on target wiki.
// don't use pretty urls, see [[bugzilla:57500]]
$oauthBaseUrl = "https://en.wikipedia.org/w/index.php?title=Special:OAuth";
// use this for requests from the server, if some special url is needed.
$oauthBaseUrlInternal = "https://en.wikipedia.org/w/index.php?title=Special:OAuth";

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
$locationProviderClass = "FakeLocationProvider";
$locationProviderApiKey = "super secret"; // ipinfodb api key

// RDNS Provider ( RDnsLookupProvider / CachedRDnsLookupProvider / FakeRDnsLookupProvider)
$rdnsProviderClass = "CachedRDnsLookupProvider";

$antispoofProviderClass = "FakeAntiSpoofProvider";
$xffTrustProviderClass = "XffTrustProvider";

/***********************************
 * Data clear script
 */

$dataclear_interval = '15 DAY';

/***********************************
 * Other stuff that doesn't fit in.
 */

$enableSQLError = 0; // Enable the display of SQL errors.
$enableTitleblacklist = 0; // Enable Title Blacklist checks.

// Enable the use of PATH_INFO for request parameters to prettify URLs.
$usePathInfo = true;

// user agent of the tool.
$toolUserAgent = "Wikipedia-ACC Tool/0.1 (+https://accounts.wmflabs.org/internal.php/team)";

// list of squid proxies requests go through.
$squidIpList = array();

// request states
$availableRequestStates = array(
    'Open'          => array(
        'defertolog' => 'users', // don't change or you'll break old logs
        'deferto'    => 'users',
        'header'     => 'Open requests',
        'api'        => "open",
        'queuehelp'  => null
    ),
    'Flagged users' => array(
        'defertolog' => 'flagged users', // don't change or you'll break old logs
        'deferto'    => 'flagged users',
        'header'     => 'Flagged user needed',
        'api'        => "admin",
        'queuehelp'  => 'This queue lists the requests which require a user with the <code>accountcreator</code> flag to create.<br />If creation is determined to be the correct course of action, requests here will require the overriding the AntiSpoof checks or the title blacklist in order to create. It is recommended to try to create the account <em>without</em> checking the flags to validate the results of the AntiSpoof and/or title blacklist hits.'
    ),
    'Checkuser'     => array(
        'defertolog' => 'checkusers', // don't change or you'll break old logs
        'deferto'    => 'checkusers',
        'header'     => 'Checkuser needed',
        'api'        => "checkuser",
        'queuehelp'  => null
    ),
);

$defaultRequestStateKey = 'Open';

$providerCacheExpiry = $dataclear_interval;

// miser mode
$requestLimitShowOnly = 25;

// Enables the Smarty debugging console. This should only be used for development and even then
// be left false when you don't need it, since this will open a popup window on every page load.
$smartydebug = false;

// ID of the Email template used for the main "Created!" close reason.
$createdid = 1;

// HSTS expiry - use false to disable header.
$strictTransportSecurityExpiry = false;

// CSP violation report URI
$cspReportUri = null;

// Must be disabled in production.
$enableErrorTrace = false;

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
    "wikipedia"     => array(
        "dsrcname" => "mysql:host=" . $antispoof_host . ";dbname=" . $antispoof_db,
        "username" => $toolserver_username,
        "password" => $toolserver_password,
        "options"  => array(),
    ),
    "notifications" => array(
        "dsrcname" => "mysql:host=" . $toolserver_notification_dbhost . ";dbname=" . $toolserver_notification_database,
        "username" => $notifications_username,
        "password" => $notifications_password,
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
    "curl", // mediawiki api access etc
    "date",
    "dom",
    "gmp",
    "json",
    "mbstring", // unicode and stuff
    "openssl", // token generation
    "pcre", // core stuff
    "pdo",
    "pdo_mysql", // new database module
    "session",
    "simplexml",
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
    ->setForceIdentification($forceIdentification)
    ->setIdentificationCacheExpiry($identificationCacheExpiry)
    ->setMediawikiScriptPath($mediawikiScriptPath)
    ->setMediawikiWebServiceEndpoint($mediawikiWebServiceEndpoint)
    ->setMetaWikimediaWebServiceEndpoint($metaWikimediaWebServiceEndpoint)
    ->setEnforceOAuth($enforceOAuth)
    ->setEmailConfirmationEnabled($enableEmailConfirm == 1)
    ->setEmailConfirmationExpiryDays($emailConfirmationExpiryDays)
    ->setMiserModeLimit($requestLimitShowOnly)
    ->setRequestStates($availableRequestStates)
    ->setSquidList($squidIpList)
    ->setDefaultCreatedTemplateId($createdid)
    ->setDefaultRequestStateKey($defaultRequestStateKey)
    ->setUseStrictTransportSecurity($strictTransportSecurityExpiry)
    ->setUserAgent($toolUserAgent)
    ->setCurlDisableVerifyPeer($curlDisableSSLVerifyPeer)
    ->setUseOAuthSignup($useOauthSignup)
    ->setOAuthBaseUrl($oauthBaseUrl)//
    ->setOAuthConsumerToken($oauthConsumerToken)
    ->setOAuthConsumerSecret($oauthSecretToken)
    ->setOauthMediaWikiCanonicalServer($oauthMediaWikiCanonicalServer)
    ->setDataClearInterval($dataclear_interval)
    ->setXffTrustedHostsFile($xff_trusted_hosts_file)
    ->setIrcNotificationsEnabled($ircBotNotificationsEnabled == 1)
    ->setIrcNotificationType($ircBotNotificationType)
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
    ->setBanMaxIpBlockRange($banMaxIpBlockRange);
