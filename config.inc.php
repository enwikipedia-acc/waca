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

# YOU *MUST* OVERRIDE THE CONFIGURATION IN THIS FILE IN A FILE CALLED config.local.inc.php 

//name for the tool	
$whichami = 'Live';

//main database location and access details
$toolserver_username = '';
$toolserver_password = '';
$toolserver_host = "";
$toolserver_database = "";

$wikiurl = "en.wikipedia.org"; //Does nothing yet, intended for further localization
$tsurl = "http://stable.toolserver.org/acc"; 
$filepath = "/projects/acc/www/"; // root pathname of the local installation of the tool.
$varfilepath = "/projects/acc/"; // location outside web directory to place temporary files

//set up cookies and session information
$cookiepath = '/acc/';
$sessionname = 'ACC';

//a few options
$enableRenames = 1; // enable account renaming
$enableEmailConfirm = 1; // enable request email confirmation
$enableReserving = 1; // enable request reservations
$enableSQLError = 0; // enable display of SQL errors
$enableDnsblChecks = 1; // enable DNS blacklist checks
$showGraphs = 1; // show graphs on statistics pages

$dontUseDb = 0; // disable the tool completely
$dontUseWikiDb = 0; // disable the wiki database
$dontUseDbReason = ""; // if disabling the tool, please enter a reason here to be displayed internally.
$dontUseDbCulprit = ""; // "     "      "    "     "      "   your name, or the person who broke the tool's name.
	
//antispoof configuration
$antispoof_equivset = "equivset.php";
$antispoof_host = "sql-s1";
$antispoof_db = "enwiki_p";
$antispoof_table = "spoofuser";

// double reserving checks.
// possible values:
//    ignore: ignores the fact that some users have reserved two requests.
//    inform: reserves the second request, and alerts the user that they have several requests reserved
//    warn: asks the user if they are sure they wish to reserve the second request
//    deny: prevents the user from reserving a second request.
$allowDoubleReserving = "inform";

$useCaptcha = false;

$ircBotNickServPassword = ""; // password for ACCBot's nickserv account
$ircBotCommunicationKey = ""; // Key used to communicate with ACCBot
$ircBotNetworkHost = "wolfe.freenode.net";
$ircBotNetworkPort = 6667;
$ircBotChannel = "#wikipedia-en-accounts";
$ircBotNickname = "ACCBot";
$ircBotCommandTrigger = '!';
$ircBotUdpServer = '';
$ircBotUdpPort = '';
	
// By default, reserve to a specific user. Adapted from livehack by st - use the userid, zero for unreserved.
$defaultReserver = 0;

// number of days that are given for a requestor to confirm their email address
$emailConfirmationExpiryDays = 2;

// Should we show the 'You last logged in from' line at the bottom of the page
$enableLastLogin = false;

// perform a newbie check on tool registration
$onRegistrationNewbieCheck = true;
$onRegistrationNewbieCheckEditCount = 20;
$onRegistrationNewbieCheckAge = 5184000;

// should we use PATH_INFO for request parameters to prettify urls?
$usePathInfo = false;

////// Don't add any new config options below this line, as they will not be changable by the local config file.
require_once('config.local.inc.php');
	
	
$ACC = 1; //Keep included files from being executed

require_once ($filepath.'blacklist.php');

ini_set( 'session.cookie_path', $cookiepath );
ini_set( 'session.name', $sessionname );
