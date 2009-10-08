<?php
require_once('config.inc.php');
require_once('functions.php');

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
{
	die("You are not authorized to use this feature. Only logged in users may use this statistics page.");
}

echo makehead( $sessionuser ) . '<div id="content"><a href="#tld">Jump to top level domain frequency</a><h2>100 most popular email providers</h2>';

$qb = new QueryBrowser();
$qb->numberedList = true;
$qb->numberedListTitle = "Rank";
echo $qb->executeQueryToTable("SELECT LOWER(SUBSTR(p.`pend_email`,INSTR(p.`pend_email`,'@')+1)) AS 'Domain', COUNT(*) AS 'Frequency' FROM acc_pend p GROUP BY LOWER(SUBSTR(p.`pend_email`,INSTR(p.`pend_email`,'@')+1)) ORDER BY COUNT(*) DESC LIMIT 100;");

echo "<a name=\"tld\" ></a><h2>Top level domain frequency</h2>";

echo $qb->executeQueryToTable("select lower(reverse( substring( reverse(pend_email), 1, instr( reverse(pend_email), '.' )-1 ) ) ) as 'Top-level domain', count(*) as 'frequency' from acc_pend group by lower(reverse( substring( reverse(pend_email), 1, instr( reverse(pend_email), '.' )-1 ) )) order by count(*) desc;");

echo showfooter();

?>