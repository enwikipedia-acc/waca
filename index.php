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
require_once 'AntiSpoof.php';

// Used to check if a request complies to the automated tests.
// See the finalChecks method in request.php for details.
$fail = 0;

// Get all the classes.
require_once 'includes/offlineMessage.php';
require_once 'includes/database.php';
require_once 'includes/request.php';
require_once 'includes/skin.php';
require_once 'includes/messages.php';
require_once 'includes/accbotSend.php';

// Check to see if the database is unavailable.
// Uses the true variable as the public uses this page.
$offlineMessage = new offlineMessage(true);
$offlineMessage->check();

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Initialize the class objects.
$request  = new accRequest();
$messages = new messages();
$accbot   = new accbotSend();
$skin     = new skin();

// Display the header of the interface.
$skin->displayheader();

// Clears the action variable.
unset($action);

// Checks whether the $_GET['action'] is set.
// Assigns it to the action variable if so.
if(isset($_GET['action'])) {
	$action = $_GET['action'];
}

// Checks whether the $_GET['id'] is set.
// Uses the setID method to assign it to the request.
if(isset($_GET['id'])) {
	$request->setID($_GET['id']);
}

$request->checkConfirmEmail();

if (isset ($_POST['name']) && isset ($_POST['email'])) {
	
	$_POST['name'] = str_replace(" ", "_", $_POST['name']);
	$_POST['name'] = trim(ucfirst($_POST['name']));
	
	global $dontUseWikiDb;

	// Initialize Variables
	$user = $tsSQL->escape(trim($_POST['name']));
	$email = $tsSQL->escape(trim($_POST['email']));
	
	// Delete old bans
	$tsSQL->query('DELETE FROM `acc_ban` WHERE `ban_duration` < UNIX_TIMESTAMP() AND ban_duration != -1');

	// Check for bans
	$request->isTOR(); // is it a TOR node?
	$request->checkBan('IP',$_SERVER['REMOTE_ADDR']);
	$request->checkBan('Name',$_POST['name']);
	$request->checkBan('EMail',$_POST['email']);
	$request->blockedOnEn();
	
	// Check the blacklists
	$request->checkBlacklist($emailblacklist,$_POST['email'],$_POST['email'],'Email-Bl');
	$request->checkBlacklist($nameblacklist,$_POST['name'],$_POST['email'],'Name-Bl');
	$request->doDnsBlacklistCheck();

	// Do automated checks on the username and email adress.
	$request->finalChecks($user,$email);

	// Insert the request if all the automated tests are passed.
	$request->insertRequest($user,$email);
} else {
	// Displayes the form if nothing has been filled in on page load.
	// Happens as default when the page is loaded for the first time.
	$request->displayform();
}
// Display the footer of the interface.
$skin->displayfooter();
?>