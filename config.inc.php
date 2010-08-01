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

// Name of this instance of the tool.
// This name would be used by the bot as reference point.	
$whichami = 'Live';

// Main database location and access details.
$toolserver_username = "";
$toolserver_password = "";
$toolserver_host = "";
$toolserver_database = "";

// Does nothing yet, intended for further localization.
$wikiurl = "en.wikipedia.org";

// URL of the current copy of the tool.
$tsurl = "http://stable.toolserver.org/acc";

// Root pathname of the local installation of the tool.
$filepath = "/projects/acc/www/"; 

// Location outside web directory to place temporary files.
$varfilepath = "/projects/acc/"; 

// Set up cookies and session information.
$cookiepath = '/acc/';
$sessionname = 'ACC';

// The general interface configuration.
$enableRenames = 1; 		// Enable interface account renaming.
$enableEmailConfirm = 1; 	// Enable request email confirmation.
$enableReserving = 1; 		// Enable request reservations.
$enableSQLError = 0; 		// Enable the display of SQL errors.
$enableDnsblChecks = 1; 	// Enable DNS blacklist checks.
$showGraphs = 1; 			// Show graphs on statistics pages.

$dontUseDb = 0; 			// Disable the tool completely.
$dontUseWikiDb = 0; 		// Disable access to the Wiki database.
$dontUseDbReason = ""; 		// Reason for disabeling the tool.
$dontUseDbCulprit = ""; 	// Your name, or the person who broke the tool.
	
// The antispoof configuration.
$antispoof_equivset = "equivset.php";
$antispoof_host = "sql-s1";
$antispoof_db = "enwiki_p";
$antispoof_table = "spoofuser";

// Double reserving configuration.
//    ignore: Ignores the fact that some users have reserved two requests.
//    inform: Reserves the second request, but alerts the user.
//    warn: Asks the user if they are sure they wish to reserve the second request.
//    deny: Prevents the user from reserving a second request.
$allowDoubleReserving = "warn";

// protect reserved requests to prevent all but the reserving user from handling the request.
$protectReservedRequests = false;

// Enable the use of Captcha for interface registration.
$useCaptcha = false;

// The IRC bot configuration.
$ircBotNickServPassword = ""; 				// Password for ACCBot's Nickserv account.
$ircBotCommunicationKey = ""; 				// Key used to communicate with the ACCBot.
$ircBotNetworkHost = "wolfe.freenode.net"; 	// The host to use for connecting.
$ircBotNetworkPort = 6667;					// The port on the particular host.
$ircBotChannel = "#wikipedia-en-accounts";	// The channel in which the discussions are.
$ircBotNickname = "ACCBot";					// The nickname of the ACCBot.
$ircBotCommandTrigger = '!';				// The ACCBot's command trigger.
$ircBotUdpServer = '';						// The UDP server for connecting.
$ircBotUdpPort = '';						// The port on the particular server.
	
// Reserve requests to a specific user by default.
// Adapted from livehack by st - use the userid, zero for unreserved.
$defaultReserver = 0;

// Number of days that are given for a requestor to confirm their email address.
$emailConfirmationExpiryDays = 2;

// Enable last login statistics.
$enableLastLogin = true;

// Parameters for performing a newbie check on tool registration.
$onRegistrationNewbieCheck = true;			// Enable the newbie checking.
$onRegistrationNewbieCheckEditCount = 20;	// Minimum amount of edits on Wikipedia.
$onRegistrationNewbieCheckAge = 5184000;	// Account age on Wikipedia in seconds.

// Enable the use of PATH_INFO for request parameters to prettify URLs.
$usePathInfo = false;

// Allow admins to break any reservation.
$enableAdminBreakReserve = false;

// The backup configuration.
$BUbasefile = "backup";							// The basefile's name.
$BUdir = "/home/project/a/c/c/acc/backups";		// The directory where backups should be stored.
$BUmonthdir = $dir . "/monthly";				// The directory where monthly backups should be stored.
$BUdumper = "/usr/bin/mysqldump --defaults-file=~/.my.cnf p_acc_live"; // Add parameters here if they are needed.
$BUgzip = "/bin/gzip"; 							// Add the gzip parameters here if needed.
$BUtar = "/bin/tar -cvf";						// Add the tar parameters here if needed.

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