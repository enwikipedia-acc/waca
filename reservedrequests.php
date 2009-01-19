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

$query = "select pend_id, pend_name, pend_status, user_name from acc_pend inner join acc_user on user_id = pend_reserved where pend_reserved != 0;";
$result = mysql_query($query, $tsSQLlink);
if(!$result) die();

echo "<h2>Requests currently reserved by a user</h2><table cellspacing=\"0\"><tr><th>#</th><th>Requested name</th><th>Status of request</th><th>Reserved by</th></tr>";
$currentreq=0;
while($row = mysql_fetch_assoc($result)) {
	$currentreq +=1;

	echo "<tr";
	if ($currentreq % 2 == 0) {
		echo ' class="alternate">';
	} else {
		echo '>';
	}
	echo "<th>".$row['pend_id']."</th><td>".$row['pend_name']."</td><td>".$row['pend_status']."</td><td>".$row['user_name']."</td></tr>";
}
echo "</table>"; 
echo showfooter();

?>