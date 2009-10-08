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
**  SQL ( http://en.wikipedia.org/User:SQL )                 **
**  Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
**FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
**Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
**Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
**Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**FunPika    ( http://en.wikipedia.org/wiki/User:FunPika )   **
**************************************************************/
require_once ('config.inc.php');
require_once('functions.php');

// check to see if the database is unavailable
readOnlyMessage();

session_start();

// retrieve database connections
global $tsSQLlink, $asSQLlink, $toolserver_database;
list($tsSQLlink, $asSQLlink) = getDBconnections();
@ mysql_select_db($toolserver_database, $tsSQLlink);
require_once('includes/database.php');
global $toolserver_username, $toolserver_password, $toolserver_host;
$tsSQL = new database($toolserver_username, $toolserver_password, $toolserver_host);
$tsSQL->selectDb($toolserver_database);
if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

if( !(hasright($sessionuser, "Admin") || hasright($sessionuser, "User")))
	die("You are not authorized to use this feature. Only logged in users may use this statistics page.");

echo makehead( $sessionuser ) . '<div id="content"><h2>All currently reserved requests</h2>';

$qb = new QueryBrowser();
echo $qb->executeQueryToTable('SELECT CONCAT("<a href=\"'.$tsurl.'/acc.php?action=zoom&id=", p.`pend_id`, "\">",p.`pend_id`,"</a>") AS "#", p.`pend_name` AS "Requested Name", p.`pend_status` AS "Status", u.`user_name` AS "Reserved by" FROM `acc_pend` p INNER JOIN `acc_user` u on u.`user_id` = p.`pend_reserved` WHERE `pend_reserved` != 0;');

echo showfooter();

?>