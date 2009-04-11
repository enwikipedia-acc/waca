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

if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

if( !(hasright($sessionuser, "Admin") || hasright($sessionuser, "User")))
	die("You are not authorized to use this feature. Only logged in users may use this statistics page.");

$qb = new QueryBrowser();
$qb->numberedList = true;
$qb->numberedListTitle = "Postition";
	
/*
 * Retrieve all-time stats
 */

$top5aout = $qb->executeQueryToTable('SELECT COUNT(*) AS "# Created", CONCAT("<a href=\"'.$tsurl.'/users.php?viewuser=" , `user_id`, "\">",`log_user`,"</a>") AS "Username" FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE `log_action` = "Closed 1" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');
	
/*
 * Retrieve today's stats (so far)
 */

$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")));
$top5out = $qb->executeQueryToTable('SELECT COUNT(*) AS "# Created", CONCAT("<a href=\"/users.php?viewuser=" , `user_id`, "\">",`log_user`,"</a>") AS "Username" FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE `log_action` = "Closed 1" AND `log_time` LIKE "'.$now.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');

/*
 * Retrieve Yesterday's stats
 */

$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));
$top5yout = $qb->executeQueryToTable('SELECT COUNT(*) AS "# Created", CONCAT("<a href=\"/users.php?viewuser=" , `user_id`, "\">",`log_user`,"</a>") AS "Username" FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE `log_action` = "Closed 1" AND `log_time` LIKE "'.$yesterday.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');

/*
 *  Retrieve last 7 days
 */

$lastweek = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7));
$top5wout = $qb->executeQueryToTable('SELECT COUNT(*) AS "# Created", CONCAT("<a href=\"/users.php?viewuser=" , `user_id`, "\">",`log_user`,"</a>") AS "Username" FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE `log_action` = "Closed 1" AND `log_time` LIKE "'.$lastweek.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');
 

/*
 * Retrieve last month's stats
 */

$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 28));
$top5mout = $qb->executeQueryToTable('SELECT COUNT(*) AS "# Created", CONCAT("<a href=\"/users.php?viewuser=" , `user_id`, "\">",`log_user`,"</a>") AS "Username" FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE `log_action` = "Closed 1" AND `log_time` LIKE "'.$lastmonth.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');

/*
 *  Output
 */

echo makehead( $sessionuser );
echo '<div id="content">';
echo "<h2>Contents</h2><ul><li><a href=\"#today\">Today's creators</a></li><li><a href=\"#yesterday\">Yesterday's creators</a></li><li><a href=\"#lastweek\">Last 7 days</a></li><li><a href=\"#lastmonth\">Last 28 days</a></li></ul>";

echo $top5aout;
echo '<a name="today"></a><h3>Today\'s creators</h3>';
echo $top5out;
echo '<a name="yesterday"></a><h3>Yesterday\'s creators</h3>';
echo $top5yout;
echo '<a name="lastweek"></a><h3>Last 7 days</h3>';
echo $top5wout;
echo '<a name="lastmonth"></a><h3>Last 28 days</h3>';
echo $top5mout;
echo showfooter();
?>
