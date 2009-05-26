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

#PLEASE MAKE A COPY OF THIS FILE CALLED config.inc.php AND THEN CONFIGURE THAT! 

if (file_exists("config.local.inc.php")) {
	include ("config.local.inc.php"); //Allow for less painful configuration.
} else {
	$ACC = 1; //Keep included files from being executed
	
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
	
	//set up cookies and session information
	$cookiepath = '/acc/';
	$sessionname = 'ACC';
	ini_set( 'session.cookie_path', $cookiepath );
	ini_set( 'session.name', $sessionname );

	//a few options
	$enableRenames = 1;
	$enableEmailConfirm = 1;
	$enableReserving = 1;
	$enableSQLError = 0;
	$enableDnsblChecks = 1;
	$showGraphs = 1;
	
	$dontUseDb = 0;
	$dontUseWikiDb = 0;
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
	//    inform: reserves the second request, and alerts the user that they have s$
	//    warn: asks the user if they are sure they wish to reserve the second requ$
	//    deny: prevents the user from reserving a second request.
	$allowDoubleReserving = "warn";
	
	
}
require_once ('blacklist.php');
?>
