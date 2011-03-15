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

// Get all the classes.
require_once 'config.inc.php';
require_once 'devlist.php';
require_once 'functions.php';
require_once 'includes/offlineMessage.php';
require_once 'includes/imagegen.php';
require_once 'includes/database.php';
require_once 'includes/skin.php';

// Check to see if the database is unavailable.
// Uses the true variable as the public uses this page.
$offlineMessage = new offlineMessage(true);
$offlineMessage->check();

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("anitspoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

// Initialize the class object.
$imagegen = new imagegen();
$skin     = new skin();

// Initialize the session data.
session_start();


// Checks whether it is the public or an interface user.
if (!isset($_SESSION['user'])) {
	// Display the header of the interface.
	$skin->displayPheader();
}
else {
	// Sets the parameter to the username, as it would be displayed.
	$suser = $_SESSION['user'];
	$skin->displayIheader($suser);
	echo "<div id=\"content\">";
}

// Display the page heading.
echo "<h2>IP and User checker (In development)</h2>\n";

// Display details about the ACC hosting.
echo "<br/><p>ACC is kindly hosted by the Wikimedia Toolserver. Our code respository is hosted by SourceForge</p></div>";

// Display the footer of the interface.
$skin->displayPfooter();