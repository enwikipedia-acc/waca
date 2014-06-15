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

// load the configuration
require_once 'config.inc.php';

// Initialize the session data.
session_start();

// Get all the classes.
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php';

// Check to see if the database is unavailable.
// Uses the true variable as the public uses this page.
if(Offline::isOffline())
{
    echo Offline::getOfflineMessage(false);
    die();
}

//Array of objects containing the deleveopers' information.
$developer = array(
		
		"FastLizard4" =>
			array(
				"IRC" => "FastLizard4",
				"EMail" => "FastLizard4@gmail.com",
				"ToolID" => "18",
				"wiki" => "FastLizard4",
				"WWW" => "http://fastlizard4.org/",
				"Name" => "Andrew Adams",
				"Role" => "Developer",
				"Retired" => NULL,
				"Access" => "Git, Mailing list admin, Labs project",
				"Cloak" => "*!*@wikipedia/pdpc.active.FastLizard4",
				"Other" => NULL,
			),
		"Stwalkerster" =>
			array(
				"IRC" => "Stwalkerster",
				"EMail" => "wikimedia@stwalkerster.co.uk",
				"ToolID" => "7",
				"wiki" => "Stwalkerster",
				"WWW" => "https://stwalkerster.co.uk/",
				"Name" => "Simon Walker",
				"Role" => "Project Lead, Developer",
				"Retired" => NULL,
				"Access" => "Git, Database, Toolserver shell, Mailing list admin, Labs project",
				"Cloak" => "*!*@wikimedia/stwalkerster",
				"Other" => NULL,
			),
		
		"FunPika" =>
			array(
				"IRC" => "FunPika",
				"EMail" => "stevend811@comcast.net",
				"ToolID" => "38",
				"wiki" => "FunPika",
				"WWW" => "https://github.com/FunPika",
				"Name" => NULL,
				"Role" => "Developer",
				"Retired" => NULL,
				"Access" => "Git",
				"Cloak" => "*!*@wikipedia/FunPika",
				"Other" => NULL,
			),
		"DeltaQuad" =>
			array(
				"IRC" => "DeltaQuad",
				"EMail" => "deltaquadwiki@gmail.com",
				"ToolID" => "662",
				"wiki" => "DeltaQuad",
				"WWW" => "http://enwp.org/DeltaQuad",
				"Name" => "DeltaQuad",
				"Role" => "Liaison to WMF, Developer",
				"Retired" => NULL,
				"Access" => "Git, Database, Toolserver shell, Mailing list admin, Labs project",
				"Cloak" => "*!*@wikipedia/DeltaQuad",//I change nicks alot
				"Other" => NULL,
			),
		"John" =>
			array(
				"IRC" => "JohnLewis",
				"EMail" => "johnflewis93@gmail.com",
				"ToolID" => "889",
				"wiki" => "John F. Lewis",
				"WWW" => NULL,
				"Name" => "John Lewis",
				"Role" => "Developer",
				"Retired" => NULL,
				"Access" => "Git, Mailing list moderator",
				"Cloak" => "*!*@wikimedia/John-F-Lewis",
				"Other" => NULL,
			),
		"Manishearth" =>
			array(
				"IRC" => "Manishearth",
				"EMail" => "manishsmail@gmail.com",
				"ToolID" => "607",
				"wiki" => "Manishearth",
				"WWW" => "http://enwp.org/User:Manishearth",
				"Name" => "Manish Goregaokar",
				"Role" => "Developer",
				"Retired" => NULL,
				"Access" => "Git",
				"Cloak" => "*!*@wikipedia/Manishearth",
				"Other" => NULL,
			)
);
// End of the array of developers.

// Inactive developers
$inactiveDeveloper = array(

		"SQL" =>
				array(                                    //Set any of these to NULL to keep them from being displayed.
				"IRC" => "SQLDb, SXT40",                  //IRC Name.
				"EMail" => "sxwiki@gmail.com",            //Public E-mail address.
				"ToolID" => "1",                          //Tool user ID for linking to page in users.php. 
				"wiki" => "SQL",                          //Enwiki Username.
				"WWW" => "http://toolserver.org/~sql",    //Your website.
				"Name" => NULL,                           //Real name.
				"Role" => NULL,	  //Project Role(s).
				"Retired" => "Project Lead",		  // Retired Project Role(s)
				"Access" => "Database, Live shell",       //Project Access levels.
				"Cloak" => "*!*@wikipedia/SQL",           //IRC Cloak.
				"Other" => NULL,                          //Anything else, comments, etc.
			),
		"Cobi" =>
			array(
				"IRC" => "Cobi",
				"EMail" => NULL,
				"ToolID" => "64",
				"wiki" => "Cobi",
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "Git, Database, Toolserver shell",
				"Cloak" => "*!*@cobi.cluenet.org",
				"Other" => NULL,
			),
		"Charlie" =>
			array(
				"IRC" => "charlie, chuck",
				"EMail" => "charlie@yourwiki.net",
				"ToolID" => "67",
				"wiki" => "Cmelbye",
				"WWW" => "http://charlie.yourwiki.net/",
				"Name" => "Charles Melbye",
				"Role" => NULL,
				"Retired" => "Developer, Web designer",
				"Access" => "",
				"Cloak" => "*!*@yourwiki/staff/charlie",
				"Other" => NULL,
			),
      		"Soxred93" =>
			array(
				"IRC" => "|X|",
				"EMail" => NULL,
				"ToolID" => "4",
				"wiki" => "X!",
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "Git",
				"Cloak" => "*!*@wikipedia/Soxred93",
				"Other" => "Wrote the original ACC Tool",
			),
		"Alexfusco5" =>
			array(
				"IRC" => "Alexfusco5",
				"EMail" => "alexfusco5@gmail.com",
				"ToolID" => "34",
				"wiki" => "Alexfusco5",
				"WWW" => "http://en.wikipedia.org/wiki/User:Alexfusco5",
				"Name" => "Alex Fusco",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => NULL,
				"Other" => NULL,
			),
		"OverlordQ" =>
			array(
				"IRC" => "OverlordQ",
				"EMail" => NULL,
				"ToolID" => "36",
				"wiki" => "OverlordQ",
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "Database, Toolserver shell",
				"Cloak" => "*!*@wikipedia/OverlordQ",
				"Other" => NULL,
			),
		"Prodego" =>
			array(
				"IRC" => "Prodego",
				"EMail" => "Prodego@gmail.com",
				"ToolID" => "14",
				"wiki" => "Prodego",
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/Prodego",
				"Other" => NULL,
			),
      		"Prom3th3an" =>
			array(
				"IRC" => "Prom_cat",
				"EMail" => "bretthillebrand@internode.on.net",
				"ToolID" => "91",
				"wiki" => "Promethean",
				"WWW" => "",
				"Name" => "Brett Hillebrand",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikimedia/Promethean",
				"Other" => NULL,
			),
		"Chris" =>
			array(
				"IRC" => "Chris_G",
				"EMail" => "chris@toolserver.org",
				"ToolID" => "20",
				"wiki" => "Chris_G",
				"WWW" => "http://toolserver.org/~chris/",
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/Chris-G",
				"Other" => NULL,
			),
		"LouriePieterse" =>
			array(
				"IRC" => "LouriePieterse",
				"EMail" => "louriepieterse@yahoo.com",
				"ToolID" => "556",
				"wiki" => "LouriePieterse",
				"WWW" => "http://en.wikipedia.org/wiki/User:LouriePieterse",
				"Name" => "Lourie Pieterse",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => NULL,
				"Other" => NULL,
			),
		"Chenzw" => // added by stwalkerster because you have access on sourceforge.net.
			array(
				"IRC" => NULL,
				"EMail" => NULL,
				"ToolID" => NULL,
				"wiki" => "Chenzw",
				"WWW" => NULL,
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => NULL,
				"Access" => "",
				"Cloak" => NULL,
				"Other" => NULL,
			),
		"Thehelpfulone" => // added by stwalkerster because you have access on sourceforge.net.
			array(
				"IRC" => "Thehelpfulone",
				"EMail" => "thehelpfulonewiki@gmail.com",
				"ToolID" => "8",
				"wiki" => "Thehelpfulone",
				"WWW" => "http://en.wikipedia.org/wiki/User:Thehelpfulone",
				"Name" => NULL,
				"Role" => NULL,
				"Retired" => NULL,
				"Access" => "Git",
				"Cloak" => "*!*@wikimedia/Thehelpfulone",
				"Other" => NULL,
			),
		"EdoDodo" =>
			array(
				"IRC" => "EdoDodo",
				"EMail" => "dodo@toolserver.org",
				"ToolID" => "660",
				"wiki" => "EdoDodo",
				"WWW" => "http://toolserver.org/~dodo/",
				"Name" => "Edoardo",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/EdoDodo",
				"Other" => NULL,
			),
		"1234r00t" =>
			array(
				"IRC" => "Mr_R00t",
				"EMail" => "sauronthefish@gmail.com",
				"ToolID" => "718",
				"wiki" => "1234r00t",
				"WWW" => "en.wikipedia.org/wiki/User:1234r00t",
				"Name" => "Max Meisler",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/Mr-R00t",
				"Other" => NULL
			),
		"MacMed" =>
			array(
				"IRC" => "MacMed",
				"EMail" => "",
				"ToolID" => "537",
				"wiki" => "MacMed",
				"WWW" => "",
				"Name" => "",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@Wikipedia/MacMed",
				"Other" => NULL,
			),
		"Logan" =>
			array(
				"IRC" => "Logan_",
				"EMail" => "",
				"ToolID" => "783",
				"wiki" => "Logan",
				"WWW" => "",
				"Name" => "",
				"Role" => NULL,
				"Retired" => "Developer",
				"Access" => "Git",
				"Cloak" => "*!*@ubuntu/member/logan",
				"Other" => NULL,
			)
);

// Sort the array with the developers.
ksort($developer);
ksort($inactiveDeveloper);

$smarty->assign("developer", $developer);
$smarty->assign("inactiveDeveloper", $inactiveDeveloper);

BootstrapSkin::displayInternalHeader();
$smarty->display("team/team.tpl");
BootstrapSkin::displayInternalFooter();