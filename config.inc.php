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
//                                                           //
///////////////////////////////////////////////////////////////
if(file_exists("config.local.inc.php")) {
	include("config.local.inc.php"); //Allow for less painful configuration.
} else {
	$ACC = 1; //Keep included files from being executed
	$whichami = 'Live';
	$toolserver_mycnf = parse_ini_file("/home/".get_current_user()."/.my.cnf");
	$toolserver_username = $toolserver_mycnf['user'];
	$toolserver_password = $toolserver_mycnf['password'];
	$toolserver_host = "sql-s1";
	$toolserver_database = "p_acc";
	$cookiepath = '/~sql/';
	$sessionname = 'ACC';
	$wikiurl = "en.wikipedia.org"; //Does nothing yet, intended for further localization
	$tsurl = "http://stable.toolserver.org/acc"; //Does nothing yet, intended for further localization
	unset($toolserver_mycnf);
}
require_once('blacklist.php');
?>
