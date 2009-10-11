<?php
/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
** SQL ( http://en.wikipedia.org/User:SQL )                 **
** Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
** FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
** Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
** Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
** Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
** OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
** Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
** FunPika    ( http://en.wikipedia.org/wiki/User:FunPika )   **
**************************************************************/

// Get all the classes.
require_once 'config.inc.php';
require_once 'includes/StatisticsPage.php';
require_once 'includes/messages.php';
require_once 'includes/database.php';
require_once 'functions.php';
require_once 'devlist.php';
require_once 'includes/offlineMessage.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

$messages = new messages();
global $toolserver_host, $toolserver_username, $toolserver_password,$toolserver_database;
$tsSQL = new database( "toolserver");

global $dontUseWikiDb;
if($dontUseWikiDb == 0)
{
	$asSQL = new database( "antispoof" );
}

$page = isset($_GET['page']) ? $_GET['page'] : 'Main';

if(isset($_SERVER['PATH_INFO']))
{
	$page = substr($_SERVER['PATH_INFO'],1);
}

$sp = StatisticsPage::Create($page);

$sp->Show();
?>