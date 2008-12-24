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

session_start();

mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
@ mysql_select_db($toolserver_database) or print mysql_error();

if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

if( !(hasright($sessionuser, "Admin") || hasright($sessionuser, "User")))
	die("You are not authorized to use this feature");



$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")));

$topqa = "select log_user,count(*) from acc_log where log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC;";
$result = mysql_query($topqa);
if (!$result)
	Die("ERROR: No result returned.6");
$top5a = array ();
while ($topa = mysql_fetch_assoc($result)) {
	array_push($top5a, $topa);
}
$top5aout = "<h2>All time top account creators</h2>";
$top5aout .= "<table cellspacing=\"0\"><tr><th>Position</th><th># Created</th><th>Username</th></tr>";
$currentreq = 0;
foreach ($top5a as $top1a) {
	$currentreq+=1;
	$userq = "SELECT user_id FROM acc_user WHERE user_name = \"".$top1a['log_user']."\";";
	$userr = mysql_query($userq);
	$user = mysql_fetch_assoc($userr);
	
	$top5aout .= "<tr";
	if ($currentreq % 2 == 0) {
		$top5aout .= ' class="alternate">';
	} else {
		$top5aout .= '>';
	}
	$top5aout .= "<th>$currentreq.</th><td>".$top1a['count(*)']."</td><td><a href=\"users.php?viewuser=".$user['user_id']."\">".$top1a['log_user'] . "</a></td></tr>";
}
$top5aout .= "</table>";


$topq = "select log_user,count(*) from acc_log where log_time like '$now%' and log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC;";
$result = mysql_query($topq);
if (!$result)
	Die("ERROR: No result returned.6");
$top5 = array ();
while ($top = mysql_fetch_assoc($result)) {
	array_push($top5, $top);
}

//Get today's top 5
$top5out = "<h2>Today's account creators</h2>";
$top5out .= "<table cellspacing=\"0\"><tr><th>Position</th><th># Created</th><th>Username</th></tr>";
$currentreq=0;
foreach ($top5 as $top1) {
	$currentreq +=1;
	$userq = "SELECT user_id FROM acc_user WHERE user_name = \"".$top1['log_user']."\";";
	$userr = mysql_query($userq);
	$user = mysql_fetch_assoc($userr);
		$top5out .= "<tr";
	if ($currentreq % 2 == 0) {
		$top5out .= ' class="alternate">';
	} else {
		$top5out .= '>';
	}
	$top5out .= "<th>$currentreq.</th><td>".$top1['count(*)']."</td><td><a href=\"users.php?viewuser=".$user['user_id']."\">".$top1['log_user'] . "</a></td></tr>";
}
$top5out .= "</table>";

$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));

$topyq = "select log_user,count(*) from acc_log where log_time like '$yesterday%' and log_action = 'Closed 1' group by log_user ORDER BY count(*) DESC;";
$result = mysql_query($topyq);
if (!$result)
	Die("ERROR: No result returned.");
$top5y = array ();
while ($topy = mysql_fetch_assoc($result)) {
	array_push($top5y, $topy);
}

//Get yesterday's top 5
$top5yout = "<h2>Yesterday's account creators</h2>";
$top5yout .= "<table cellspacing=\"0\"><tr><th>Position</th><th># Created</th><th>Username</th></tr>";
foreach ($top5y as $topy1) {
	$userq = "SELECT user_id FROM acc_user WHERE user_name = \"".$topy1['log_user']."\";";
	$userr = mysql_query($userq);
	$user = mysql_fetch_assoc($userr);
	$top5yout .= "<tr";
	if ($currentreq % 2 == 0) {
		$top5yout .= ' class="alternate">';
	} else {
		$top5yout .= '>';
	}
	$top5yout .= "<th>$currentreq.</th><td>".$topy1['count(*)']."</td><td><a href=\"users.php?viewuser=".$user['user_id']."\">".$topy1['log_user'] . "</a></td></tr>";
}
$top5yout .= "</table>";


echo makehead( $sessionuser );
echo '<div id="content">';
echo $top5aout;
echo $top5out;
echo $top5yout;
echo "</div>";
echo showfooter();
?>