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

session_start();

$link = mysql_connect( $toolserver_host, $toolserver_username, $toolserver_password );
if ( !$link ) {
	die( 'Could not connect: ' . mysql_error( ) );
}
@ mysql_select_db( $toolserver_database ) or print mysql_error( );

if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

if( !hasright($sessionuser, "Admin"))
	die("You are not authorized to use this feature");

echo makehead( $sessionuser );
echo '<div id="content">';


///////////////// Page code


echo '<h1>Request search tool</h1>';
if( isset($_GET['email']) ) {

	echo "<h2>Searching for email address: $_GET[email] ...</h2>";
	$query = "SELECT pend_id FROM acc_pend WHERE pend_email LIKE '".sanitize($_GET['email'])."';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$html = "<table cellspacing=\"0\">\n";
	$currentrow = 0;
	while ( list( $pend_id ) = mysql_fetch_row( $result ) ) {
		$currentrow += 1;
		$out = '<tr';
		if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
		} else {
			$out .= ' class="odd">';
		}
		$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\">Request " . $pend_id . "</a></small></tr>";
		$html .= $out;
	}
	$html .= "</table>\n";
	$html .= "<b>Results found: </b> $currentrow.";
	echo $html;
}
elseif( isset($_GET['ipaddr']) ) {
	echo "<h2>Searching for IP address: $_GET[ipaddr] ...</h2>";
	$query = "SELECT pend_id FROM acc_pend WHERE pend_ip LIKE '".sanitize($_GET['ipaddr'])."';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$html = "<table cellspacing=\"0\">\n";
	$currentrow = 0;
	while ( list( $pend_id ) = mysql_fetch_row( $result ) ) {
		$currentrow += 1;
		$out = '<tr';
		if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
		} else {
			$out .= ' class="odd">';
		}
		$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\">Request " . $pend_id . "</a></small></tr>";
		$html .= $out;
	}
	$html .= "</table>\n";
	$html .= "<b>Results found: </b> $currentrow.";
	echo $html;
}
else {
	echo '<h2>Search:</h2>';
	echo '<form action="search.php" method="get">';
	echo 'Search for: <input type="text" name="term" />';
	echo '<select name="type>';
	echo '<option value="email">as email address</option><br />';
	echo '<option value="IP">as IP address</option><br />';
	echo '</select>';
	echo '<input type="submit" />';
	echo '</form>';
}

echo "</div>";
echo showfooter();
?>