<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

global $session;

// Get all the classes.
require_once 'config.inc.php';
require_once 'functions.php';
require_once 'includes/offlineMessage.php';
require_once 'includes/database.php';
require_once 'includes/skin.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

// Initialize the class objects.
$skin     = new skin();

// Initialize the session data.
session_start();

if( isset( $_SESSION['user'] ) ) {
	$sessionuser = $_SESSION['user'];
} else {
	$sessionuser = "";
}

// patched by stwalkerster to re-enable non-admin searching.
// please note (prodego esp.) non-admins cannot perform
// IP address lookups still, but can search on email and requested name.

// protect against logged out users
if( !$session->hasright($sessionuser, "Admin") && !$session->hasright($sessionuser, "User")) {
		$skin->displayRequestMsg("You must log in to use the search form.<br />\n");	
		$skin->displayIfooter();
		die();
	}

$skin->displayIheader($sessionuser);
echo '<div id="content">';


///////////////// Page code


echo '<h1>Request search tool</h1>';
if( isset($_GET['term'])) {
	$term = sanitize($_GET['term']);
	$type = sanitize($_GET['type']);

	if($term == "" || $term == "%") {
		$skin->displayRequestMsg("No search term entered.<br />\n");	
		$skin->displayIfooter();
		die();
	}

	if( $type == "email") {
		// move this to here, so non-admins can perform searches, but not on IP addresses
		if( !$session->hasright($sessionuser, "Admin") && !$session->isCheckuser($sessionuser)) {
				// Displays both the error message and the footer of the interface.
				$skin->displayRequestMsg("I'm sorry, but only administrators can search for Email Addresses.<br />\n");	
				$skin->displayIfooter();
				die();
		}
		if($term == "@") {
			$skin->displayRequestMsg("Invalid search term entered.<br />\n");	
			$skin->displayIfooter();
			die();
		}			

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
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_email </a></small></td></tr>";
			$html .= $out;
		}
		$html .= "</table>\n";
		$html .= "<b>Results found: </b> $currentrow.";
		echo $html;
	}
	elseif( $type == 'IP') {
		// move this to here, so non-admins can perform searches, but not on IP addresses
		if( !$session->hasright($sessionuser, "Admin") && !$session->isCheckuser($sessionuser)) {
				// Displays both the error message and the footer of the interface.
				$skin->displayRequestMsg("I'm sorry, but only administrators can search for IP Addresses.<br />\n");	
				$skin->displayIfooter();
				die();
		}
		echo "<h2>Searching for IP address: $term ...</h2>";
		
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
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_name</a> ($pend_status) - ($pend_ip @ $pend_date ) </small></td></tr>";
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
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_name </a></small></td></tr>";
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
	if( !$session->hasright($sessionuser, "Admin") && !$session->isCheckuser($sessionuser)) { //Disable the drop-down menu for non-admins/checkusers
	echo '<input name="type" type="hidden" value="Request" />';
	}
	echo '<td><select name="type"';
	if( !$session->hasright($sessionuser, "Admin") && !$session->isCheckuser($sessionuser)) { //Disable the drop-down menu for non-admins/checkusers
	echo ' disabled="disabled"';
	}
	echo'>';
	echo '<option value="Request">as requested username</option>';
	echo '<option value="email">as email address</option>';
	echo '<option value="IP">as IP address</option>';
	echo '</select></td></tr></table><br />';
	echo '<input type="submit" />';
	echo '</form>';
}
$skin->displayIfooter();
?>