<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

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

$toolserver_notification_database = "p_acc_notifications";

// The antispoof configuration.
$antispoof_equivset = "equivset.php";
$antispoof_host = "sql-s1";
$antispoof_db = "enwiki_p";
$antispoof_table = "spoofuser";

/**********************************
 * File paths etc
 */

// Does nothing yet, intended for further localization.
$wikiurl = "en.wikipedia.org";

// URL of the current copy of the tool.
$tsurl = "http://stable.toolserver.org/acc";

// Root pathname of the local installation of the tool.
$filepath = "/projects/acc/www/"; 

// Pathname to the local installation of Peachy.
$peachyPath = ""; 

// Location outside web directory to place temporary files.
$varfilepath = "/projects/acc/"; 

// Set up cookies and session information.
$cookiepath = '/acc/';
$sessionname = 'ACC';

/************************************
 * Tool downtime
 */

$dontUseDb = 0; 			// Disable the tool completely.
$dontUseWikiDb = 0; 		// Disable access to the Wiki database.
$dontUseDbReason = ""; 		// Reason for disabeling the tool.
$dontUseDbCulprit = ""; 	// Your name, or the person who broke the tool.
	
/**************************************
 * ACCBot IRC bot
 */

$ircBotDaemonise = true;					// Run the IRC bot as a daemon, detached from the terminal.

$ircBotNickServPassword = ""; 				// Password for ACCBot's Nickserv account.
$ircBotCommunicationKey = ""; 				// Key used to communicate with the ACCBot.
$ircBotNetworkHost = "chat.freenode.net"; 	// The host to use for connecting.
$ircBotNetworkPort = 6667;					// The port on the particular host.
$ircBotChannel = "#wikipedia-en-accounts";	// The channel in which the discussions are.
$ircBotNickname = "ACCBot";					// The nickname of the ACCBot.
$ircBotCommandTrigger = '!';				// The ACCBot's command trigger.
$ircBotSnsArn = "";							// SNS topic ARN 
	
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

// Parameters for performing a newbie check on tool registration.
$onRegistrationNewbieCheck = true;			// Enable the newbie checking.
$onRegistrationNewbieCheckEditCount = 20;	// Minimum amount of edits on Wikipedia.
$onRegistrationNewbieCheckAge = 5184000;	// Account age on Wikipedia in seconds.

// Enable the use of Captcha for interface registration.
$useCaptcha = false;

// Enable interface account renaming.
$enableRenames = 1; 		

// Allow checkusers to see the useragent
$allowViewingOfUseragent = true;

// Force identification to the foundation
$forceIdentification = false;


/***********************************
 * Reservations
 */

// Reserve requests to a specific user by default.
// Adapted from livehack by st - use the userid, zero for unreserved.
$defaultReserver = 0;

// Allow admins to break any reservation. (stable)
$enableAdminBreakReserve = true;

// Double reserving configuration.
//    ignore: Ignores the fact that some users have reserved two requests.
//    inform: Reserves the second request, but alerts the user.
//    warn: Asks the user if they are sure they wish to reserve the second request.
//    deny: Prevents the user from reserving a second request.
$allowDoubleReserving = "warn";

// protect reserved requests to prevent all but the reserving user from handling the request.
$protectReservedRequests = true;

/************************************
 * Backup Configuration
 */

$BUbasefile = "backup";							// The basefile's name.
$BUdir = "/home/project/a/c/c/acc/backups";		// The directory where backups should be stored.
$BUmonthdir = $BUdir . "/monthly";				// The directory where monthly backups should be stored.
$BUdumper = "/opt/ts/mysql/5.1/bin/mysqldump --defaults-file=~/.my.cnf p_acc_live"; // Add parameters here if they are needed.
$BUgzip = "/usr/bin/gzip"; 							// Add the gzip parameters here if needed.
$BUtar = "/bin/tar -cvf";						// Add the tar parameters here if needed.


/***********************************
 * Other stuff that doesn't fit in.
 */

$enableSQLError = 0; 		// Enable the display of SQL errors.
$enableDnsblChecks = 1; 	// Enable DNS blacklist checks.
$showGraphs = 1; 			// Show graphs on statistics pages.
$enableTitleblacklist = 0;  // Enable Title Blacklist checks.

// Enable the use of PATH_INFO for request parameters to prettify URLs.
$usePathInfo = false;

// user agent of the tool.
$toolUserAgent = "Wikipedia-ACC Tool/0.1 (+http://toolserver.org/~acc/team.php)";

// AWS - ask stwalkerster BEFORE adding new usages, or 
// they'll be unilaterally reverted. You have been warned.
require_once("/home/project/a/c/c/acc/AWSSDKforPHP/sdk.class.php");

// list of squid proxies requests go through.
$squidIpList = array();

$apiDeployPassword = "super secret update password";

// request states
$availableRequestStates = array(
	'open' =>array(
		'deferto' => 'users', // don't change or you'll break old logs
		'header' => 'Open requests',
		),
	'admin'=>array(
		'deferto' => 'flagged users', // don't change or you'll break old logs
		'header' => 'Flagged user needed',
		),
	'checkuser'=>array(
		'deferto' => 'checkusers', // don't change or you'll break old logs
		'header' => 'Checkuser needed',
		),
	);
	
$defaultRequestStateKey = 'open';

/**************************************************************************
**********                   IMPORTANT NOTICE                    **********
***************************************************************************
**     DON'T ADD ANY NEW CONFIGURATION OPTIONS BELOW THIS LINE!!!        **
**     THEY WILL NOT BE CHANGABLE BY THE LOCAL CONFIGURATION FILE.       **
***************************************************************************/

// Retriving the local configuration file.
require_once('config.local.inc.php');

// //Keep the included files from being executed.
$ACC = 1;

// Retrieving the blacklists.
require_once ($filepath.'blacklist.php');

// Sets the values of the cookie configuration options.
ini_set('session.cookie_path', $cookiepath);
ini_set('session.name', $sessionname);
ini_set('user_agent', $toolUserAgent);
