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
global $tsSQLlink, $asSQLlink;
list($tsSQLlink, $asSQLlink) = getDBconnections();

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
<ul>
	<li><a href="users.php">User List</a></li>
	<li><a href="oldusers.php">Inactive User Accounts</a></li>
	<li><a href="flaggedusers.php">Flagged User Accounts</a></li>
	<li><a href="nonexistantrequests.php">Erroneous requests</a></li>
	<li><a href="topcreators.php">Top Account Creators</a></li>
	<li><a href="reservedrequests.php">All currently-reserved requests</a></li>
</ul>';
echo showfooter();
?>
