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

// stop all output until we want it
ob_start();

// load the configuration
require_once 'config.inc.php';

// Initialize the session data.
session_start();

// Get all the classes.
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php'; // this needs to be high up, but below config, functions, and database
require_once 'includes/session.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
if (Offline::isOffline()) {
	echo Offline::getOfflineMessage(false);
	die();
}

// Initialize the class objects.
$session = new session();

// Clears the action variable.
$action = '';

// Assign the correct value to the action variable.
// The value is retrieved from the $GET variable.
if (isset($_GET['action'])) {
	$action = $_GET['action'];
}


// Checks whether the user is set - the user should first login.
if (!isset($_SESSION['userID'])) {
	ob_end_clean();
	global $baseurl;
	header("Location: $baseurl/internal.php/login");
	die();
}

// Forces the current user to ogout if necessary.
if (isset($_SESSION['userID'])) {
	$session->forceLogout($_SESSION['userID']);
}

BootstrapSkin::displayInternalHeader();
$session->checksecurity();

if ($action == "oauthdetach") {
	if ($enforceOAuth) {
		BootstrapSkin::displayAccessDenied();
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	global $baseurl;
        
	$currentUser = User::getCurrent();
	$currentUser->detachAccount();
        
	header("Location: {$baseurl}/acc.php?action=logout");
}
elseif ($action == "oauthattach") {
	$database = gGetDb();
	$database->transactionally(function() use ($database)
	{
		try {
			global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal;
            
			$user = User::getCurrent();
            
			// Get a request token for OAuth
			$util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);
			$requestToken = $util->getRequestToken();

			// save the request token for later
			$user->setOAuthRequestToken($requestToken->key);
			$user->setOAuthRequestSecret($requestToken->secret);
			$user->save();
        
			$redirectUrl = $util->getAuthoriseUrl($requestToken);
        
			header("Location: {$redirectUrl}");
        
		}
		catch (Exception $ex) {
			throw new TransactionException($ex->getMessage(), "Connection to Wikipedia failed.", "alert-error", 0, $ex);
		}
	});
}
# If the action specified does not exist, goto the default page.
else {
	ob_end_clean();
	global $baseurl;
	header("Location: $baseurl/internal.php");
	die();
}
