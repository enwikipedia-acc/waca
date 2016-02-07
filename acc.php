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
/** @var IRDnsProvider $rdnsProvider */
$rdnsProvider = new $rdnsProviderClass(gGetDb('acc'));
/** @var IAntiSpoofProvider $antispoofProvider */
$antispoofProvider = new $antispoofProviderClass();
/** @var IXffTrustProvider $xffTrustProvider */
$xffTrustProvider = new $xffTrustProviderClass($squidIpList);

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

elseif ($action == "done" && $_GET['id'] != "") {
	// check for valid close reasons
	global $messages, $baseurl, $smarty;
	
	if (isset($_GET['email'])) {
		if ($_GET['email'] == 0 || $_GET['email'] == "custom") {
			$validEmail = true;
		}
		else {
			$validEmail = EmailTemplate::getById($_GET['email'], gGetDb()) != false;
		}
	}
	else {
		$validEmail = false;
	}
    
	if ($validEmail == false) {
		BootstrapSkin::displayAlertBox("Invalid close reason", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// sanitise this input ready for inclusion in queries
	$request = Request::getById($_GET['id'], gGetDb());
    
	if ($request == false) {
		// Notifies the user and stops the script.
		BootstrapSkin::displayAlertBox("The request ID supplied is invalid!", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$gem = $_GET['email'];
	
	// check the checksum is valid
	if ($request->getChecksum() != $_GET['sum']) {
		BootstrapSkin::displayAlertBox("This is similar to an edit conflict on Wikipedia; it means that you have tried to perform an action on a request that someone else has performed an action on since you loaded the page.", "alert-error", "Invalid Checksum", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// check if an email has already been sent
	if ($request->getEmailSent() == "1" && !isset($_GET['override']) && $gem != 0) {
		$alertContent = "<p>This request has already been closed in a manner that has generated an e-mail to the user, Proceed?</p><br />";
		$alertContent .= "<div class=\"row-fluid\">";
		$alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$baseurl/acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;override=yes&amp;email=" . $_GET['email'] . "\">Yes</a>";
		$alertContent .= "<a class=\"btn btn-danger span3\" href=\"$baseurl/acc.php\">No</a>";
		$alertContent .= "</div>";
        
		BootstrapSkin::displayAlertBox($alertContent, "alert-info", "Warning!", true, false, false, true);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// check the request is not reserved by someone else
	if ($request->getReserved() != 0 && !isset($_GET['reserveoverride']) && $request->getReserved() != User::getCurrent()->getId()) {
		$alertContent = "<p>This request is currently marked as being handled by " . $request->getReservedObject()->getUsername() . ", Proceed?</p><br />";
		$alertContent .= "<div class=\"row-fluid\">";
		$alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$baseurl/acc.php?" . $_SERVER["QUERY_STRING"] . "&reserveoverride=yes\">Yes</a>";
		$alertContent .= "<a class=\"btn btn-danger span3\" href=\"$baseurl/acc.php\">No</a>";
		$alertContent .= "</div>";
        
		BootstrapSkin::displayAlertBox($alertContent, "alert-info", "Warning!", true, false, false, true);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	    
	if ($request->getStatus() == "Closed") {
		BootstrapSkin::displayAlertBox("Cannot close this request. Already closed.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// Checks whether the username is already in use on Wikipedia.
	$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=" . urlencode($request->getName()) . "&format=php");
	$ue = unserialize($userexist);
	if (!isset ($ue['query']['users']['0']['missing'])) {
		$exists = true;
	}
	else {
		$exists = false;
	}
	
	// check if a request being created does not already exist. 
	if ($gem == 1 && !$exists && !isset($_GET['createoverride'])) {
		$alertContent = "<p>You have chosen to mark this request as \"created\", but the account does not exist on the English Wikipedia, proceed?</p><br />";
		$alertContent .= "<div class=\"row-fluid\">";
		$alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$baseurl/acc.php?" . $_SERVER["QUERY_STRING"] . "&amp;createoverride=yes\">Yes</a>";
		$alertContent .= "<a class=\"btn btn-danger span3\" href=\"$baseurl/acc.php\">No</a>";
		$alertContent .= "</div>";
        
		BootstrapSkin::displayAlertBox($alertContent, "alert-info", "Warning!", true, false, false, true);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	$messageBody = null;
    
	// custom close reasons
	if ($gem == 'custom') {
		if (!isset($_POST['msgbody']) or empty($_POST['msgbody'])) {
			// Send it through htmlspecialchars so HTML validators don't complain. 
			$querystring = htmlspecialchars($_SERVER["QUERY_STRING"], ENT_COMPAT, 'UTF-8'); 
            
			$template = false;
			if (isset($_GET['preload'])) {
				$template = EmailTemplate::getById($_GET['preload'], gGetDb());
			}
            
			if ($template != false) {
				$preloadTitle = $template->getName();
				$preloadText = $template->getText();
				$preloadAction = $template->getDefaultAction();
			}
			else {
				$preloadText = "";
				$preloadTitle = "";
				$preloadAction = "";
			}
            
			$smarty->assign("requeststates", $availableRequestStates);
			$smarty->assign("defaultAction", $preloadAction);
			$smarty->assign("preloadtext", $preloadText);
			$smarty->assign("preloadtitle", $preloadTitle);
			$smarty->assign("querystring", $querystring);
			$smarty->assign("request", $request);
			$smarty->assign("iplocation", $locationProvider->getIpLocation($request->getTrustedIp()));
			$smarty->display("custom-close.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		}

		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
		if (!User::getCurrent()->isAdmin() || isset($_POST['ccmailist']) && $_POST['ccmailist'] == "on") {
			$headers .= 'Cc: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
		}

		$headers .= 'X-ACC-Request: ' . $request->getId() . "\r\n";
		$headers .= 'X-ACC-UserID: ' . User::getCurrent()->getId() . "\r\n";

		// Get the closing user's Email signature and append it to the Email.
		if (User::getCurrent()->getEmailSig() != "") {
			$emailsig = html_entity_decode(User::getCurrent()->getEmailSig(), ENT_QUOTES, "UTF-8");
			mail($request->getEmail(), "RE: [ACC #{$request->getId()}] English Wikipedia Account Request", $_POST['msgbody'] . "\n\n" . $emailsig, $headers);
		}
		else {
			mail($request->getEmail(), "RE: [ACC #{$request->getId()}] English Wikipedia Account Request", $_POST['msgbody'], $headers);
		}

		$request->setEmailSent(1);
		$messageBody = $_POST['msgbody'];

		if ($_POST['action'] == EmailTemplate::CREATED || $_POST['action'] == EmailTemplate::NOT_CREATED) {
			$request->setStatus('Closed');

			if ($_POST['action'] == EmailTemplate::CREATED) {
				$gem  = 'custom-y';
				$crea = "Custom, Created";
			}
			else {
				$gem  = 'custom-n';
				$crea = "Custom, Not Created";
			}

			Logger::closeRequest(gGetDb(), $request, $gem, $messageBody);
			
			Notification::requestClosed($request, $crea);
			BootstrapSkin::displayAlertBox(
				"Request " . $request->getId() . " (" . htmlentities($request->getName(), ENT_COMPAT, 'UTF-8') . ") marked as 'Done'.", 
				"alert-success");
		}
		else if ($_POST['action'] == "mail") {
			// no action other than send mail!
			Logger::sentMail(gGetDb(), $request, $messageBody);
			Logger::unreserve(gGetDb(), $request);

			Notification::sentMail($request);
			BootstrapSkin::displayAlertBox("Sent mail to Request {$request->getId()}", 
				"alert-success");
		}
		else if (array_key_exists($_POST['action'], $availableRequestStates)) {
			// Defer

			$request->setStatus($_POST['action']);
			$deto = $availableRequestStates[$_POST['action']]['deferto'];
			$detolog = $availableRequestStates[$_POST['action']]['defertolog'];

			Logger::sentMail(gGetDb(), $request, $messageBody);
			Logger::deferRequest(gGetDb(), $request, $detolog);
			
			Notification::requestDeferredWithMail($request);
			BootstrapSkin::displayAlertBox("Request {$request->getId()} deferred to $deto, sending an email.", 
				"alert-success");
		}
		else {
			// hmm. not sure what happened. Log that we sent the mail anyway.
			Logger::sentMail(gGetDb(), $request, $messageBody);
			Logger::unreserve(gGetDb(), $request);

			Notification::sentMail($request);
			BootstrapSkin::displayAlertBox("Sent mail to Request {$request->getId()}", 
				"alert-success");
		}

		$request->setReserved(0);
		$request->save();
		
		$request->updateChecksum();
		$request->save();

		echo defaultpage();
		BootstrapSkin::displayInternalFooter();
		die();		
	}
	else {
		// Not a custom close, just a normal close
	    
		$request->setStatus('Closed');
		$request->setReserved(0);
		
		// TODO: make this transactional
		$request->save();
		
		Logger::closeRequest(gGetDb(), $request, $gem, $messageBody);
		
		if ($gem == '0') {
			$crea = "Dropped";
		}
		else {
			$template = EmailTemplate::getById($gem, gGetDb());
			$crea = $template->getName();
		}

		Notification::requestClosed($request, $crea);
		BootstrapSkin::displayAlertBox("Request " . $request->getId() . " (" . htmlentities($request->getName(), ENT_COMPAT, 'UTF-8') . ") marked as 'Done'.", "alert-success");
		
		$towhom = $request->getEmail();
		if ($gem != "0") {
			sendemail($gem, $towhom, $request->getId());
			$request->setEmailSent(1);
		}
		
		$request->updateChecksum();
		$request->save();
		
		echo defaultpage();
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

elseif ($action == "ec") {
	// edit comment
  
	global $smarty, $baseurl;
    
	$comment = Comment::getById($_GET['id'], gGetDb());
    
	if ($comment == false) {
		// Only using die("Message"); for errors looks ugly.
		BootstrapSkin::displayAlertBox("Comment not found.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// Unauthorized if user is not an admin or the user who made the comment being edited.
	if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser() && $comment->getUser() != User::getCurrent()->getId()) {
		BootstrapSkin::displayAccessDenied();
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// get[id] is safe by this point.
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$database = gGetDb();
		$database->transactionally(function() use ($database, $comment, $baseurl)
		{
            
			$comment->setComment($_POST['newcomment']);
			$comment->setVisibility($_POST['visibility']);
        
			$comment->save();
        
			Logger::editComment($database, $comment);
        
			Notification::commentEdited($comment);
        
			SessionAlert::success("Comment has been saved successfully");
			header("Location: $baseurl/internal.php/viewRequest?id=" . $comment->getRequest());
		});
        
		die();    
	}
	else {
		$smarty->assign("comment", $comment);
		$smarty->display("edit-comment.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
}

elseif ($action == "sendtouser") {
	global $baseurl;
    
	$database = gGetDb();
    
	$requestObject = Request::getById($_POST['id'], $database);
	if ($requestObject == false) {
		BootstrapSkin::displayAlertBox("Request invalid", "alert-error", "Could not find request", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$request = $requestObject->getId();
    
	$user = User::getByUsername($_POST['user'], $database);
	$curuser = User::getCurrent()->getUsername();
    
	if ($user == false) {
		BootstrapSkin::displayAlertBox("We couldn't find the user you wanted to send the reservation to. Please check that this user exists and is an active user on the tool.", "alert-error", "Could not find user", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$database->transactionally(function() use ($database, $user, $request, $curuser)
	{
		$updateStatement = $database->prepare("UPDATE request SET reserved = :userid WHERE id = :request LIMIT 1;");
		$updateStatement->bindValue(":userid", $user->getId());
		$updateStatement->bindValue(":request", $request);
		if (!$updateStatement->execute()) {
			throw new TransactionException("Error updating reserved status of request.");   
		}
        
		Logger::sendReservation($database, Request::getById($request, $database), $user);
	});
    
	Notification::requestReservationSent($request, $user);
	SessionAlert::success("Reservation sent successfully");
	header("Location: $baseurl/internal.php/viewRequest?id=$request");
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
