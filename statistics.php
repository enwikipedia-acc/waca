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

echo makehead( $sessionuser );
echo '<div id="content">
<h2>Account Creation Statistics</h2>
<H3>Other statistics pages</h3>
<ul>
	<li><a href="users.php">User List</a></li>
	<li><a href="oldusers.php">Inactive User Accounts</a></li>
	<li><a href="flaggedusers.php">Flagged User Accounts</a></li>
	<!--<li><a href="nonexistantrequests.php">Erroneous requests</a></li>-->
	<li><a href="topcreators.php">Top Account Creators</a></li>
	<li><a href="reservedrequests.php">All currently-reserved requests</a></li>
	<li><a href="welcome-q.php">SQLBot-Hello welcome job queue length</a></li>
	<li><a href="monthlyStats.php">Monthly Statistics <small>inspired by Paxse</small></a></li>
	<li><a href="popularEmailProviders.php">Most Popular Email Providers</a></li>
</ul><h3>Statistics</h3><table>';


$openq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Open' AND pend_mailconfirm = 'Confirmed';";
$result = mysql_query($openq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.1");
$open = mysql_fetch_assoc($result);
echo "<tr><th>Open Requests</th><td>".$open['COUNT(*)']."</td></tr>";

$adminq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Admin' AND pend_mailconfirm = 'Confirmed';";
$result = mysql_query($adminq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.2");
$admin = mysql_fetch_assoc($result);
echo "<tr><th>Requests needing an account creator</th><td>".$admin['COUNT(*)']."</td></tr>";

$unconfirmedq = "SELECT COUNT(*) FROM acc_pend WHERE pend_mailconfirm != 'Confirmed' AND pend_mailconfirm != '';";
$result = mysql_query($unconfirmedq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.2");
$unconfirmed = mysql_fetch_assoc($result);
echo "<tr><th>Unconfirmed requests</th><td>".$unconfirmed['COUNT(*)']."</td></tr>";

$sadminq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'Admin';";
$result = mysql_query($sadminq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.3");
$sadmin = mysql_fetch_assoc($result);
echo "<tr><th>Tool administrators</th><td>".$sadmin['COUNT(*)']."</td></tr>";

$suserq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'User';";
$result = mysql_query($suserq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.4");
$suser = mysql_fetch_assoc($result);
echo "<tr><th>Tool users</th><td>".$suser['COUNT(*)']."</td></tr>";

$ssuspq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'Suspended';";
$result = mysql_query($ssuspq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.5");
$ssusp = mysql_fetch_assoc($result);
echo "<tr><th>Tool suspended users</th><td>".$ssusp['COUNT(*)']."</td></tr>";

$snewq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'New';";
$result = mysql_query($snewq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.6");
$snew = mysql_fetch_assoc($result);
echo "<tr><th>New tool users</th><td>".$snew['COUNT(*)']."</td></tr>";

$mostComments = "select pend_id from acc_cmt group by pend_id order by count(*) desc limit 1;";
$mostCommentsResult = mysql_query($mostComments, $tsSQLlink);
if(!$mostCommentsResult) Die("ERROR: No result returned. (mc)");
$mostCommentsRow = mysql_fetch_assoc($mostCommentsResult);
$mostCommentsId = $mostCommentsRow[0];
echo "<tr><th>Request with most comments</th><td><a href=\"acc.php?action=zoom&id=".$mostCommentsId."\">".$mostCommentsId."</a></td></tr>";


$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));



//Process log for stats
$logq = "select * from acc_log AS A
	JOIN acc_pend AS B ON log_pend = pend_id
	where log_time RLIKE '^$now.*' AND
	log_action RLIKE '^(Closed.*|Deferred.*|Blacklist.*)';";
$result = mysql_query($logq, $tsSQLlink);
if (!$result)
	Die("ERROR: No result returned.7");
$dropped = 0;
$created = 0;
$toosimilar = 0;


echo "</table>";
echo showfooter();
?>
