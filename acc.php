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
$date = new DateTime();

// initialise providers
global $squidIpList;
/** @var ILocationProvider $locationProvider */
$locationProvider = new $locationProviderClass(gGetDb('acc'), $locationProviderApiKey);

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


// When no action is specified the default Internal ACC are displayed.
// TODO: Improve way the method is called.
if ($action == '') {
	ob_end_clean();
	global $baseurl;
	header("Location: $baseurl/internal.php");
	die();
}

elseif ($action == "sreg") {
	global $useOauthSignup, $smarty;
        
	// TODO: check blocked
	// TODO: check age.
    
	// check if user checked the "I have read and understand the interface guidelines" checkbox
	if (!isset($_REQUEST['guidelines'])) {
		$smarty->display("registration/alert-interfaceguidelines.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
		$smarty->display("registration/alert-invalidemail.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if ($_REQUEST['pass'] !== $_REQUEST['pass2']) {
		$smarty->display("registration/alert-passwordmismatch.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (!$useOauthSignup) {
		if (!((string)(int)$_REQUEST['conf_revid'] === (string)$_REQUEST['conf_revid']) || $_REQUEST['conf_revid'] == "") {
			$smarty->display("registration/alert-confrevid.tpl");
			BootstrapSkin::displayInternalFooter();
			die();		
		}
	}
    
	if (User::getByUsername($_REQUEST['name'], gGetDb()) != false) {
		$smarty->display("registration/alert-usernametaken.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$query = gGetDb()->prepare("SELECT * FROM user WHERE email = :email LIMIT 1;");
	$query->execute(array(":email" => $_REQUEST['email']));
	if ($query->fetchObject("User") != false) {
		$smarty->display("registration/alert-emailtaken.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	$query->closeCursor();

	$database = gGetDb();
    
	$database->transactionally(function() use ($database, $useOauthSignup)
	{
    
		$newUser = new User();
		$newUser->setDatabase($database);
    
		$newUser->setUsername($_REQUEST['name']);
		$newUser->setPassword($_REQUEST['pass']);
		$newUser->setEmail($_REQUEST['email']);
        
		if (!$useOauthSignup) {
			$newUser->setOnWikiName($_REQUEST['wname']);
			$newUser->setConfirmationDiff($_REQUEST['conf_revid']);
		}
        
		$newUser->save();
    
		global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal, $useOauthSignup;
    
		if ($useOauthSignup) {
			try {
				// Get a request token for OAuth
				$util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);
				$requestToken = $util->getRequestToken();
    
				// save the request token for later
				$newUser->setOAuthRequestToken($requestToken->key);
				$newUser->setOAuthRequestSecret($requestToken->secret);
				$newUser->save();
            
				Notification::userNew($newUser);
        
				$redirectUrl = $util->getAuthoriseUrl($requestToken);
            
				header("Location: {$redirectUrl}");
			}
			catch (Exception $ex) {
				throw new TransactionException(
					$ex->getMessage(), 
					"Connection to Wikipedia failed.", 
					"alert-error", 
					0, 
					$ex);
			}
		}
		else {
			global $baseurl;
			Notification::userNew($newUser);
			header("Location: {$baseurl}/acc.php?action=registercomplete");
		}
	});
    
	die();
}
elseif ($action == "register") {
	global $useOauthSignup, $smarty;
	$smarty->assign("useOauthSignup", $useOauthSignup);
	$smarty->display("registration/register.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "registercomplete") {
	$smarty->display("registration/alert-registrationcomplete.tpl");
	BootstrapSkin::displayInternalFooter();
}


elseif ($action == "oauthdetach") {
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
