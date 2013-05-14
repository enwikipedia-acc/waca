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
require_once 'includes/SmartyInit.php';
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
$bskin     = new BootstrapSkin();

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


BootstrapSkin::displayInternalHeader();

// protect against logged out users
if( !$session->hasright($sessionuser, "Admin") && !$session->hasright($sessionuser, "User")) {
    BootstrapSkin::displayAlertBox("You must log in to use the search form.","alert-error","Error!", true, false);
    BootstrapSkin::displayInternalFooter();
	die();
}


///////////////// Page code

echo <<<HTML
<div class="page-header">
  <h1>Search<small> for a request</small></h1>
</div>

<div class="row">
    <div class="span12">
HTML;

BootstrapSkin::pushTagStack("</div>"); // span12
BootstrapSkin::pushTagStack("</div>"); // row
    
    
if( isset($_GET['term'])) {
	$term = sanitize($_GET['term']);
	$type = sanitize($_GET['type']);

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
		
		$termexp = explode("/", $term, 2);
		$term = $termexp[0];
		$cidr = $termexp[1];
		
		if( !isset($cidr)) {
			$cidr = '32';
		}
		
		if ($cidr < '16' || $cidr > '32') {
				$skin->displayRequestMsg("The CIDR must be between /16 and /32!<br />\n");	
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
			$termlong = ip2long($term);
			$termlong = sprintf("%u\n", $termlong);
			$endrange = $termlong + pow(2, (32-$cidr)) - 1;
			$query = "SELECT pend_id,pend_ip,pend_name,pend_date,pend_status FROM acc_pend WHERE inet_aton(pend_ip) between $termlong and $endrange;";
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
	echo <<<HTML
<form action="search.php" method="get" class="form-horizontal">
    <div class="control-group">
        <label class="control-label" for="term">Search term</label>
        <div class="controls">
            <input type="text" id="term" name="term" placeholder="Search for...">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="term">Search as ...</label>
        <div class="controls">
            <select name="type">
                <option value="Request">... requested username</option>
                <option value="email">... email address</option>
HTML;
    if( $session->hasright($sessionuser, "Admin") || $session->isCheckuser($sessionuser)) { //Enable the IP search for admins and CUs
        echo '                <option value="IP">... IP address/range</option>';
    }
    echo <<<HTML
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="icon-search icon-white"></i>&nbsp;Search</button>
    </div>
</form>    
HTML;
}
BootstrapSkin::displayInternalFooter();
?>