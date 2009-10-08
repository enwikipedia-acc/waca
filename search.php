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

// includes, header stuff.

require_once( 'config.inc.php' );
require_once ( 'functions.php' );

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

// patched by stwalkerster to re-enable non-admin searching.
// please note (prodego esp.) non-admins cannot perform
// IP address lookups still, but can search on email and requested name.

// protect against logged out users
if( !hasright($sessionuser, "Admin") && !hasright($sessionuser, "User"))
	die("You are not authorized to use this feature. Please check you are logged in.");

echo makehead( $sessionuser );
echo '<div id="content">';


///////////////// Page code


echo '<h1>Request search tool</h1>';
if( isset($_GET['term'])) {
	$term = sanitize($_GET['term']);
	$type = sanitize($_GET['type']);

	if($term == "" || $term == "%")
		die("No search term specified.");

	if( $type == "email") {
		if($term == "@")
			die("Invalid search term");		

		echo "<h2>Searching for email address: $term ...</h2>";
		$query = "SELECT pend_id,pend_email FROM acc_pend WHERE pend_email LIKE '%$term%';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$html = "<table cellspacing=\"0\">\n";
		$currentrow = 0;
		while ( list( $pend_id,$pend_email ) = mysql_fetch_row( $result ) ) {
			$currentrow += 1;
			$out = '<tr';
			if ($currentrow % 2 == 0) {
				$out .= ' class="even">';
			} else {
				$out .= ' class="odd">';
			}
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_email </a></small></tr>";
			$html .= $out;
		}
		$html .= "</table>\n";
		$html .= "<b>Results found: </b> $currentrow.";
		echo $html;
	}
	elseif( $type == 'IP') {
		echo "<h2>Searching for IP address: $term ...</h2>";
		
		// move this to here, so non-admins can perform searches, but not on IP addresses
		if( !hasright($sessionuser, "Admin"))
			die("You are not authorized to use this feature");
		
		$query = "SELECT pend_id,pend_ip,pend_name,pend_date,pend_status FROM acc_pend WHERE pend_ip LIKE '%$term%';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$html = "<table cellspacing=\"0\">\n";
		$currentrow = 0;
		while ( list( $pend_id,$pend_ip,$pend_name,$pend_date,$pend_status ) = mysql_fetch_row( $result ) ) {
			$currentrow += 1;
			$out = '<tr';
			if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
			} else {
				$out .= ' class="odd">';
			}
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_name</a> ($pend_status) - ($pend_ip @ $pend_date ) </small></tr>";
			$html .= $out;
		}
		$html .= "</table>\n";
		$html .= "<b>Results found: </b> $currentrow.";
		echo $html;
	}
	elseif( $type == 'Request') {
		echo "<h2>Searching for requested username: $term ...</h2>";
		$query = "SELECT pend_id,pend_name FROM acc_pend WHERE pend_name LIKE '%$term%';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$html = "<table cellspacing=\"0\">\n";
		$currentrow = 0;
		while ( list( $pend_id, $pend_name ) = mysql_fetch_row( $result ) ) {
			$currentrow += 1;
			$out = '<tr';
			if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
			} else {
				$out .= ' class="odd">';
			}
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_name </a></small></tr>";
			$html .= $out;
		}
		$html .= "</table>\n";
		$html .= "<b>Results found: </b> $currentrow.";
		echo $html;
	}
}
else {
	echo '<h2>Search:</h2>';
	echo '<form action="search.php" method="get">';
	echo 'Search for:<br />';
	echo '<table><tr><td><input type="text" name="term" /></td>';
	echo '<td><select name="type">';
	echo '<option value="email">as email address</option>';
	echo '<option value="IP">as IP address (tool admins only)</option>';
	echo '<option value="Request">as requested username</option>';
	echo '</select></td></tr></table><br />';
	echo '<input type="submit" />';
	echo '</form>';
}

echo showfooter();
?>
