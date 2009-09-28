<?php
 
///////////////////////////////////////////////////////////////
// English Wikipedia Account Request Interface               //
// Wikipedia Account Request Graphic Design by               //
// Charles Melbye is licensed under a Creative               //
// Commons Attribution-Noncommercial-Share Alike             //
// 3.0 United States License. All other code                 //
// released under Public Domain by the ACC                   //
// Development Team.                                         //
//             Developers:                                   //
//  SQL ( http://en.wikipedia.org/User:SQL )                 //
//  Cobi ( http://en.wikipedia.org/User:Cobi )               //
// Cmelbye ( http://en.wikipedia.org/User:cmelbye )          //
//FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   //
//Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) //
//Soxred93 ( http://en.wikipedia.org/User:Soxred93)          //
//Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      //
//OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  //
//                                                           //
///////////////////////////////////////////////////////////////

# YOU *MUST* OVERRIDE THE CONFIGURATION IN THIS FILE IN A FILE CALLED config.local.inc.php 

//name for the tool	
$whichami = 'Live';

//main database location and access details
$toolserver_mycnf = parse_ini_file("/projects/acc/.my.cnf"); //location of  a .my.cnf file with connection data in, if one exists
$toolserver_username = $toolserver_mycnf['user'];
$toolserver_password = $toolserver_mycnf['password'];
$toolserver_host = "sql";
$toolserver_database = "p_acc";
unset ($toolserver_mycnf);

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
	
// By default, reserve to a specific user. Adapted from livehack by st - use the userid, zero for unreserved.
$defaultReserver = 0;

// number of days that are given for a requestor to confirm their email address
$emailConfirmationExpiryDays = 2;

// Should we show the 'You last logged in from' line at the bottom of the page
$enableLastLogin = false;

////// Don't add any new config options below this line, as they will not be changable by the local config file.
require_once('config.local.inc.php');
	
	
$ACC = 1; //Keep included files from being executed

require_once ($filepath.'blacklist.php');

ini_set( 'session.cookie_path', $cookiepath );
ini_set( 'session.name', $sessionname );
