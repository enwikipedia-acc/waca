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

// Get all the classes.
require_once 'functions.php';
initialiseSession();
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php';

// Check to see if the database is unavailable.
// Uses the true variable as the public uses this page.
if (Offline::isOffline()) {
	echo Offline::getOfflineMessage(false);
	die();
}

//Array of objects containing the deleveopers' information.
$developer = array(
		
		"FastLizard4" =>
			array(
				"IRC" => "FastLizard4",
				"ToolID" => "18",
				"wiki" => "FastLizard4",
				"WWW" => "http://fastlizard4.org/",
				"Name" => "Andrew Adams",
				"Role" => "Project Lead, Developer",
				"Retired" => null,
				"Access" => "Git, Mailing list admin, Labs project",
				"Cloak" => "*!*@wikipedia/pdpc.active.FastLizard4",
				"Other" => null,
			),
		"Stwalkerster" =>
			array(
				"IRC" => "Stwalkerster",
				"ToolID" => "7",
				"wiki" => "Stwalkerster",
				"WWW" => "https://stwalkerster.co.uk/",
				"Name" => "Simon Walker",
				"Role" => "Project Lead, Developer",
				"Retired" => null,
				"Access" => "Git, Mailing list admin, Labs project",
				"Cloak" => "*!*@wikimedia/stwalkerster",
				"Other" => null,
			),
		
		"FunPika" =>
			array(
				"IRC" => "FunPika",
				"ToolID" => "38",
				"wiki" => "FunPika",
				"WWW" => "https://github.com/FunPika",
				"Name" => null,
				"Role" => "Developer",
				"Retired" => null,
				"Access" => "Git",
				"Cloak" => "*!*@wikipedia/FunPika",
				"Other" => null,
			),
		"DeltaQuad" =>
			array(
				"IRC" => "DeltaQuad",
				"ToolID" => "662",
				"wiki" => "DeltaQuad",
				"WWW" => "http://enwp.org/DeltaQuad",
				"Name" => "DeltaQuad",
				"Role" => "",
				"Retired" => "Liaison to WMF, Developer, Project Lead",
				"Access" => "Git, Labs project",
				"Cloak" => "*!*@wikipedia/DeltaQuad", //I change nicks alot
				"Other" => null,
			),
		"Manishearth" =>
			array(
				"IRC" => "Manishearth",
				"ToolID" => "607",
				"wiki" => "Manishearth",
				"WWW" => "http://enwp.org/User:Manishearth",
				"Name" => "Manish Goregaokar",
				"Role" => "Developer",
				"Retired" => null,
				"Access" => "Git",
				"Cloak" => "*!*@wikipedia/Manishearth",
				"Other" => null,
			),
		"Cyberpower678" =>
			array(
				"IRC" => "Cyberpower678",
				"ToolID" => "850",
				"wiki" => "Cyberpower678",
				"WWW" => "",
				"Name" => "",
				"Role" => "",
				"Retired" => null,
				"Access" => "Mailing list mod",
				"Cloak" => "*!*@wikipedia/Cyberpower678",
				"Other" => null,
			),
		"Matthewrbowker" =>
			array(
				"IRC" => "Matthew_",
				"ToolID" => "788",
				"wiki" => "Matthewrbowker",
				"WWW" => "",
				"Name" => "Matthew Bowker",
				"Role" => "",
				"Retired" => null,
				"Access" => "",
				"Cloak" => "*!*@wikimedia/Matthewrbowker",
				"Other" => null,
			)
);
// End of the array of developers.

// Inactive developers
$inactiveDeveloper = array(

		"SQL" =>
				array(                                    //Set any of these to null to keep them from being displayed.
				"IRC" => "SQLDb, SXT40", //IRC Name.
				"ToolID" => "1", //Tool user ID for linking to page in users.php. 
				"wiki" => "SQL", //Enwiki Username.
				"WWW" => "http://toolserver.org/~sql", //Your website.
				"Name" => null, //Real name.
				"Role" => null, //Project Role(s).
				"Retired" => "Project Lead", // Retired Project Role(s)
				"Access" => null, //Project Access levels.
				"Cloak" => "*!*@wikipedia/SQL", //IRC Cloak.
				"Other" => "Original developer", //Anything else, comments, etc.
			),
		"Cobi" =>
			array(
				"IRC" => "Cobi",
				"ToolID" => "64",
				"wiki" => "Cobi",
				"WWW" => null,
				"Name" => null,
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "Git",
				"Cloak" => "*!*@cobi.cluenet.org",
				"Other" => null,
			),
		"Charlie" =>
			array(
				"IRC" => "charlie, chuck",
				"ToolID" => "67",
				"wiki" => "Cmelbye",
				"WWW" => "http://charlie.yourwiki.net/",
				"Name" => "Charles Melbye",
				"Role" => null,
				"Retired" => "Developer, Web designer",
				"Access" => "",
				"Cloak" => "*!*@yourwiki/staff/charlie",
				"Other" => null,
			),
	  		"Soxred93" =>
			array(
				"IRC" => "|X|",
				"ToolID" => "4",
				"wiki" => "X!",
				"WWW" => null,
				"Name" => null,
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "Git",
				"Cloak" => "*!*@wikipedia/Soxred93",
				"Other" => "Wrote the original ACC Tool, 'Incubez'",
			),
		"Alexfusco5" =>
			array(
				"IRC" => "Alexfusco5",
				"ToolID" => "34",
				"wiki" => "Alexfusco5",
				"WWW" => "http://en.wikipedia.org/wiki/User:Alexfusco5",
				"Name" => "Alex Fusco",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => null,
				"Other" => null,
			),
		"OverlordQ" =>
			array(
				"IRC" => "OverlordQ",
				"ToolID" => "36",
				"wiki" => "OverlordQ",
				"WWW" => null,
				"Name" => null,
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/OverlordQ",
				"Other" => null,
			),
		"Prodego" =>
			array(
				"IRC" => "Prodego",
				"ToolID" => "14",
				"wiki" => "Prodego",
				"WWW" => null,
				"Name" => null,
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/Prodego",
				"Other" => null,
			),
	  		"Prom3th3an" =>
			array(
				"IRC" => "Prom_cat",
				"ToolID" => "91",
				"wiki" => "Promethean",
				"WWW" => "",
				"Name" => "Brett Hillebrand",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikimedia/Promethean",
				"Other" => null,
			),
		"Chris" =>
			array(
				"IRC" => "Chris_G",
				"ToolID" => "20",
				"wiki" => "Chris_G",
				"WWW" => "http://toolserver.org/~chris/",
				"Name" => null,
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/Chris-G",
				"Other" => null,
			),
		"LouriePieterse" =>
			array(
				"IRC" => "LouriePieterse",
				"ToolID" => "556",
				"wiki" => "LouriePieterse",
				"WWW" => "http://en.wikipedia.org/wiki/User:LouriePieterse",
				"Name" => "Lourie Pieterse",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => null,
				"Other" => null,
			),
		"Thehelpfulone" => // added by stwalkerster because you have access on sourceforge.net.
			array(
				"IRC" => "Thehelpfulone",
				"ToolID" => "8",
				"wiki" => "Thehelpfulone",
				"WWW" => "http://en.wikipedia.org/wiki/User:Thehelpfulone",
				"Name" => null,
				"Role" => null,
				"Retired" => null,
				"Access" => "Git",
				"Cloak" => "*!*@wikimedia/Thehelpfulone",
				"Other" => null,
			),
		"EdoDodo" =>
			array(
				"IRC" => "EdoDodo",
				"ToolID" => "660",
				"wiki" => "EdoDodo",
				"WWW" => "http://toolserver.org/~dodo/",
				"Name" => "Edoardo",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/EdoDodo",
				"Other" => null,
			),
		"1234r00t" =>
			array(
				"IRC" => "Mr_R00t",
				"ToolID" => "718",
				"wiki" => "1234r00t",
				"WWW" => "en.wikipedia.org/wiki/User:1234r00t",
				"Name" => "Max Meisler",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikipedia/Mr-R00t",
				"Other" => null
			),
		"MacMed" =>
			array(
				"IRC" => "MacMed",
				"ToolID" => "537",
				"wiki" => "MacMed",
				"WWW" => "",
				"Name" => "",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@Wikipedia/MacMed",
				"Other" => null,
			),
		"Logan" =>
			array(
				"IRC" => "Logan_",
				"ToolID" => "783",
				"wiki" => "Logan",
				"WWW" => "",
				"Name" => "",
				"Role" => null,
				"Retired" => "Developer",
				"Access" => "Git",
				"Cloak" => "*!*@ubuntu/member/logan",
				"Other" => null,
			),
		"John" =>
			array(
				"IRC" => "JohnLewis",
				"ToolID" => "889",
				"wiki" => "John F. Lewis",
				"WWW" => null,
				"Name" => "John Lewis",
				"Role" => "",
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikimedia/John-F-Lewis",
				"Other" => null,
			),
		"Technical 13" =>
			array(
				"IRC" => "Technical_13",
				"ToolID" => "890",
				"wiki" => "Technical 13",
				"WWW" => "https://github.com/Technical-13/waca",
				"Name" => "Donald J. Fortier II",
				"Role" => "",
				"Retired" => "Developer",
				"Access" => "",
				"Cloak" => "*!*@wikimedia/Technical-13",
				"Other" => null,
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
