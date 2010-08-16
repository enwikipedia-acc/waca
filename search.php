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

$skin->displayIheader($sessionuser);
echo '<div id="content">';
// protect against logged out users
if( !$session->hasright($sessionuser, "Admin") && !$session->hasright($sessionuser, "User")) {
		$skin->displayRequestMsg("You must log in to use the search form.<br />\n");	
		$skin->displayIfooter();
		die();
	}


///////////////// Page code


echo '<h1>Request search tool</h1>';
if( isset($_GET['term'])) {
	$term = sanitize($_GET['term']);
	$type = sanitize($_GET['type']);
	$cidr = sanitize($_GET['cidr']);

	if($term == "" || $term == "%") {
		$skin->displayRequestMsg("No search term entered.<br />\n");	
		$skin->displayIfooter();
		die();
	}

	if( $type == "email") {
		// move this to here, so non-admins can perform searches, but not on IP addresses or emails
//		if( !$session->hasright($sessionuser, "Admin") && !$session->isCheckuser($sessionuser)) {
//				// Displays both the error message and the footer of the interface.
//				$skin->displayRequestMsg("I'm sorry, but only administrators and checkusers can search for Email Addresses.<br />\n");	
//				$skin->displayIfooter();
//				die();
//		}
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
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"$tsurl/acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_email </a></small></td></tr>";
			$html .= $out;
		}
		$html .= "</table>\n";
		$html .= "<b>Results found: </b> $currentrow.";
		echo $html;
	}
	elseif( $type == 'IP') {
		// move this to here, so non-admins can perform searches, but not on IP addresses or emails
		if( !$session->hasright($sessionuser, "Admin") && !$session->isCheckuser($sessionuser)) {
				// Displays both the error message and the footer of the interface.
				$skin->displayRequestMsg("I'm sorry, but only administrators and checkusers can search for IP Addresses.<br />\n");	
				$skin->displayIfooter();
				die();
		}
		if ($cidr == '32') {
			echo "<h2>Searching for IP address: $term ...</h2>";
		}
		else { 
			echo '<h2>Searching for IP range: ' . $term . '/' . $cidr . '...</h2>';
		}
		
		if ($cidr != '32') {
			$endrange = $term + pow(2, (32-$cidr)) - 1;
			$termlong = ip2long($term);
			$endrange = ip2long($endrange);
			echo ip2long($_GET['term']);
			$query = "SELECT pend_id,pend_ip,pend_name,pend_date,pend_status FROM acc_pend WHERE inet_aton('pend_ip') between '$termlong' and '$endrange';";
			echo $query;
		}
		else {
		$query = "SELECT pend_id,pend_ip,pend_name,pend_date,pend_status FROM acc_pend WHERE pend_ip LIKE '%$term%';";
		}
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
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"$tsurl/acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_name</a> ($pend_status) - ($pend_ip @ $pend_date ) </small></td></tr>";
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
			$out .= "<td><b>$currentrow.</b></td><td><small><a style=\"color:blue\" href=\"$tsurl/acc.php?action=zoom&amp;id=" . $pend_id . "\"> $pend_name </a></small></td></tr>";
			$html .= $out;
		}
		$html .= "</table>\n";
		$html .= "<b>Results found: </b> $currentrow.";
		echo $html;
	}
	else
	{
		$skin->displayRequestMsg("Unknown search type.<br />\n");	
		$skin->displayIfooter();
		die();
	}
}
else {
	echo '<h2>Search:</h2>';
	echo '<form action="search.php" method="get">';
	echo 'Search for:<br />';
	echo '<table><tr><td><input type="text" name="term" /></td>';
	echo '<td>';
	echo '<select name="type">';
	echo '<option value="Request">as requested username</option>';
	echo '<option value="email">as email address</option>';
	if( $session->hasright($sessionuser, "Admin") || $session->isCheckuser($sessionuser)) { //Enable the IP search for admins and CU's
		echo '<option value="IP">as IP address</option>';
	}
	echo '</select></td>';
	if( $session->hasright($sessionuser, "Admin") || $session->isCheckuser($sessionuser)) {
		//TODO: Find some way to make this not show up when requested username/email address is selected (probably with Javascript). 
		echo '<td><select name="cidr">';
		echo '<option value = "32">IP CIDR (optional)</option>'; //Default to /32 (1 IP Address).
		for($i = "32"; $i >= 16; $i--) { //Use for to show options for /32 (yes I know its redundant) through /16
			echo '<option value = "' . $i . '">/' . $i . '</option>'; 
		}
		echo '</select></td>';
	}
	echo '</tr></table><br />';
	echo '<input type="submit" />';
	echo '</form>';
}
$skin->displayIfooter();
?>