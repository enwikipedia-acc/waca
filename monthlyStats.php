<?php
require_once('config.inc.php');
require_once('functions.php');

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
{
	die("You are not authorized to use this feature. Only logged in users may use this statistics page.");
}

echo makehead( $sessionuser ) . '<div id="content"><h2>Requests Closed per Month</h2>';

$qb = new QueryBrowser();

echo $qb->executeQueryToTable("SELECT COUNT(DISTINCT log_id) AS 'Requests Closed', YEAR(log_time) AS 'Year', MONTHNAME(log_time) AS 'Month' FROM acc_log WHERE log_action LIKE 'Closed%' GROUP BY EXTRACT(YEAR_MONTH FROM log_time) ORDER BY YEAR(log_time), MONTH(log_time) ASC;");

echo showfooter();
?>