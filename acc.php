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

// Get all the classes.
require_once 'functions.php';
initialiseSession();
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php'; // this needs to be high up, but below config, functions, database and session init
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

// Clear session before banner and logged in as message is generated on logout attempt - Prom3th3an
if ($action == "logout") {
	session_unset();
    
	BootstrapSkin::displayInternalHeader();
	echo showlogin();
	BootstrapSkin::displayInternalFooter();
	die();
}

// Checks whether the user is set - the user should first login.
if (!isset($_SESSION['user'])) {
	$suser = '';
	BootstrapSkin::displayInternalHeader();

	// Checks whether the user want to reset his password or register a new account.
	// Performs the clause when the action is not one of the above options.
	if ($action != 'register' && $action != 'forgotpw' && $action != 'sreg' && $action != "registercomplete" && $action != "login") {
		echo showlogin();
		BootstrapSkin::displayInternalFooter();
		die();
	}
	else {
		// A content block is created if the action is none of the above.
		// This block would later be used to keep all the HTML except the header and footer.
		$out = "<div id=\"content\">";
		echo $out;
	}
}

// Forces the current user to logout if necessary.
if (isset($_SESSION['userID'])) {
	$session->forceLogout($_SESSION['userID']);
}

BootstrapSkin::displayInternalHeader();
$session->checksecurity();


// When no action is specified the default Internal ACC are displayed.
// TODO: Improve way the method is called.
if ($action == '') {
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
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
elseif ($action == "forgotpw") {
	global $baseurl, $smarty;
    
	if (isset ($_GET['si']) && isset ($_GET['id'])) {
		$user = User::getById($_GET['id'], gGetDb());
        
		if ($user === false) {
			BootstrapSkin::displayAlertBox("User not found.", "alert-error");
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		if (isset ($_POST['pw']) && isset ($_POST['pw2'])) {
			$hash = $user->getForgottenPasswordHash();
            
			if ($hash == $_GET['si']) {
				if ($_POST['pw'] == $_POST['pw2']) {
					$user->setPassword($_POST['pw2']);
					$user->save();
                    
					BootstrapSkin::displayAlertBox(
						"You may now <a href=\"$baseurl/acc.php\">Login</a>", 
						"alert-error", 
						"Password reset!", 
						true, 
						false);
                    
					BootstrapSkin::displayInternalFooter();
					die();
				}
				else {
					BootstrapSkin::displayAlertBox("Passwords did not match!", "alert-error", "Error", true, false);
					BootstrapSkin::displayInternalFooter();
					die();
				}
			}
			else {
				BootstrapSkin::displayAlertBox("Invalid request<!-- 1 -->", "alert-error", "Error", true, false);
				BootstrapSkin::displayInternalFooter();
				die();
			}
		}
        
		$hash = $user->getForgottenPasswordHash();
        
		if ($hash == $_GET['si']) {
			$smarty->assign('user', $user);
			$smarty->assign('si', $_GET['si']);
			$smarty->assign('id', $_GET['id']);
			$smarty->display('forgot-password/forgotpwreset.tpl');
		}
		else {
			BootstrapSkin::displayAlertBox(
				"The hash supplied in the link did not match the hash in the database!", 
				"alert-error", 
				"Invalid request", 
				true, 
				false);
		}
        
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (isset ($_POST['username'])) {
		$user = User::getByUsername($_POST['username'], gGetDb());

		if ($user == false) {
			BootstrapSkin::displayAlertBox(
				"Could not find user with that username and email address!", 
				"alert-error", 
				"Error", 
				true, 
				false);
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
		elseif (strtolower($_POST['email']) != strtolower($user->getEmail())) {
			BootstrapSkin::displayAlertBox("Could not find user with that username and email address!", 
				"alert-error", 
				"Error", 
				true, 
				false);
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
		else {
			$hash = $user->getForgottenPasswordHash();
                       
			$smarty->assign("user", $user);
			$smarty->assign("hash", $hash);
			$smarty->assign("remoteAddress", $_SERVER['REMOTE_ADDR']);
            
			$mailtxt = $smarty->fetch("forgot-password/reset-mail.tpl");
			$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
            
			mail(
				$user->getEmail(), 
				"English Wikipedia Account Request System - Forgotten password", 
				$mailtxt, 
				$headers);
            
			BootstrapSkin::displayAlertBox(
				"<strong>Your password reset request has been completed.</strong> Please check your e-mail.", 
				"alert-success", 
				"", 
				false, 
				false);
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
	}
    
	$smarty->display('forgot-password/forgotpw.tpl');

	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "login") {
	global $baseurl, $smarty;
    
	if (!isset($_POST['username'])) {
		header("Location: $baseurl/acc.php?error=authfail&tplUsername=");
		die();
	}

	$user = User::getByUsername($_POST['username'], gGetDb());
    
	if ($user == false || !$user->authenticate($_POST['password'])) {
		header("Location: $baseurl/acc.php?error=authfail&tplUsername=" . urlencode($_POST['username']));
		die();
	}
    
	if ($user->getStoredOnWikiName() == "##OAUTH##" && $user->getOAuthAccessToken() == null) {
		reattachOAuthAccount($user);   
	}
    
	if ($user->isOAuthLinked()) {
		try {
			// test retrieval of the identity
			$user->getOAuthIdentity();
		}
		catch (TransactionException $ex) {
			$user->setOAuthAccessToken(null);
			$user->setOAuthAccessSecret(null);
			$user->save();
            
			reattachOAuthAccount($user);
		}
	}
	else {
		global $enforceOAuth;
        
		if ($enforceOAuth) {
			reattachOAuthAccount($user);
		}
	}
    
	// At this point, the user has successfully authenticated themselves.
	// We now proceed to perform login-specific actions, and check the user actually has
	// the correct permissions to continue with the login.
    
	if ($user->getForcelogout()) {
		$user->setForcelogout(false);
		$user->save();
	}
    
	if ($user->isNew()) {
		header("Location: $baseurl/acc.php?error=newacct");
		die();
	}
    
	$database = gGetDb();
    
	$sqlText = <<<SQL
SELECT comment FROM log
WHERE action = :action AND objectid = :userid AND objecttype = 'User'
ORDER BY timestamp DESC LIMIT 1;
SQL;
    
	$suspendstatement = $database->prepare($sqlText);
    
	if ($user->isDeclined()) {
		$suspendAction = "Declined";
		$userid = $user->getId();
		$suspendstatement->bindValue(":action", $suspendAction);
		$suspendstatement->bindValue(":userid", $userid);
		$suspendstatement->execute();
        
		$suspendreason = $suspendstatement->fetchColumn();
        
		$suspendstatement->closeCursor();
        
		BootstrapSkin::displayInternalHeader();
		$smarty->assign("suspendreason", $suspendreason);
		$smarty->display("login/declined.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if ($user->isSuspended()) {
		$suspendAction = "Suspended";
		$userid = $user->getId();
		$suspendstatement->bindValue(":action", $suspendAction);
		$suspendstatement->bindValue(":userid", $userid);
		$suspendstatement->execute();
        
		$suspendreason = $suspendstatement->fetchColumn();
        
		$suspendstatement->closeCursor();
        
		BootstrapSkin::displayInternalHeader();
		$smarty->assign("suspendreason", $suspendreason);
		$smarty->display("login/suspended.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (!$user->isIdentified() && $forceIdentification == 1) {
		header("Location: $baseurl/acc.php?error=noid");
		die();
	}
    
	// At this point, we've tested that the user is OK, so we set the login cookies.
    
	$_SESSION['user'] = $user->getUsername();
	$_SESSION['userID'] = $user->getId();
    
	if ($user->getOAuthAccessToken() == null && $user->getStoredOnWikiName() == "##OAUTH##") {
		reattachOAuthAccount($user);
	}
    
	header("Location: $baseurl/acc.php");
}
elseif ($action == "messagemgmt") {
	global $smarty;
    
	if (isset($_GET['view'])) {
		$message = InterfaceMessage::getById($_GET['view'], gGetDb());
                
		if ($message == false) {
			BootstrapSkin::displayAlertBox("Unable to find specified message", "alert-error", "Error", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		$smarty->assign("message", $message);
		$smarty->assign("readonly", true);
		$smarty->display("message-management/editform.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	if (isset($_GET['edit'])) {
		if (!(User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser())) {
			BootstrapSkin::displayAccessDenied();
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		$database = gGetDb();
        
		$database->transactionally(function() use ($database)
		{
			global $smarty;
            
			$message = InterfaceMessage::getById($_GET['edit'], $database);
            
			if ($message == false) {
				throw new TransactionException("Unable to find specified message", "Error");
			}
            
			if (isset($_GET['submit'])) {
				$message->setContent($_POST['mailtext']);
				$message->setDescription($_POST['maildesc']);
				$message->save();
            
				Logger::interfaceMessageEdited(gGetDb(), $message);
              
				$smarty->assign("message", $message);
				$smarty->display("message-management/alert-editsuccess.tpl");
                
				Notification::interfaceMessageEdited($message);
                
				BootstrapSkin::displayInternalFooter();
				return;
			}
            
			$smarty->assign("message", $message);
			$smarty->assign("readonly", false);
			$smarty->display("message-management/editform.tpl");
        
			BootstrapSkin::displayInternalFooter();
		});
        
		die();
	}
    
	$sqlText = <<<SQL
        SELECT * 
        FROM interfacemessage 
        WHERE type = :type 
            AND description NOT LIKE '%[deprecated]';
SQL;
    
	$fetchStatement = gGetDb()->prepare($sqlText);
	$data = array();
        
	//$fetchStatement->execute(array(":type" => "Interface"));
	//$data['Public Interface messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
	$fetchStatement->execute(array(":type" => "Internal"));
	$data['Internal Interface messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
	$smarty->assign("data", $data);
	$smarty->display('message-management/view.tpl');
   
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "templatemgmt") {
	global $baseurl, $smarty;
    
	if (isset($_GET['view'])) {
		$template = WelcomeTemplate::getById($_GET['view'], gGetDb());
        
		if ($template === false) {
			SessionAlert::success("Something went wrong, we can't find the template you asked for! Please try again.");
			header("Location: {$baseurl}/acc.php?action=templatemgmt");
			die();
		}
        
		$smarty->assign("template", $template);
		$smarty->display("welcometemplate/view.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (isset($_GET['add'])) {
		if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) {
			BootstrapSkin::displayAccessDenied();
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		if (isset($_POST['submit'])) {
			global $baseurl;
            
			$database = gGetDb();
            
			$database->transactionally(function() use ($database, $baseurl)
			{
				$template = new WelcomeTemplate();
				$template->setDatabase($database);
				$template->setUserCode($_POST['usercode']);
				$template->setBotCode($_POST['botcode']);
				$template->save();
            
				Logger::welcomeTemplateCreated($database, $template);
                            
				Notification::welcomeTemplateCreated($template);
            
				SessionAlert::success("Template successfully created.");
				header("Location: $baseurl/acc.php?action=templatemgmt");
			});
		}
		else {
			
			if (isset($_POST['preview'])) {
				$usercode = $_POST['usercode'];
				$botcode = $_POST['botcode'];
				echo displayPreview($usercode);
			}
			else {
				$usercode = '';
				$botcode = '';
			}

			$smarty->assign("usercode", $usercode);
			$smarty->assign("botcode", $botcode);
            
			$smarty->display("welcometemplate/add.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		die();
	}
    
	if (isset($_GET['select'])) {
		$user = User::getCurrent();
        
		if ($_GET['select'] == 0) {
			$user->setWelcomeTemplate(null);
			$user->save();
            
			SessionAlert::success("Disabled automatic user welcoming.");
			header("Location: {$baseurl}/acc.php?action=templatemgmt");
			die();
		}
		else {
			$template = WelcomeTemplate::getById($_GET['select'], gGetDb());
			if ($template !== false) {
				$user->setWelcomeTemplate($template->getId());
				$user->save();
                
				SessionAlert::success("Updated selected welcome template for automatic welcoming.");
				header("Location: {$baseurl}/acc.php?action=templatemgmt");
				die();
			}
			else {
				SessionAlert::error("Something went wrong, we can't find the template you asked for!");
				header("Location: {$baseurl}/acc.php?action=templatemgmt");
				die();
			}
		}
	}
    
	if (isset($_GET['del'])) {
		global $baseurl;
        
		if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) {
			BootstrapSkin::displayAccessDenied();
			BootstrapSkin::displayInternalFooter();
			die();
		}

		$database = gGetDb();
        
		$template = WelcomeTemplate::getById($_GET['del'], $database);
		if ($template == false) {
			SessionAlert::error("Something went wrong, we can't find the template you asked for!");
			header("Location: {$baseurl}/acc.php?action=templatemgmt");
			die();
		}
        
		$database->transactionally(function() use($database, $template)
		{
			$tid = $template->getId();
            
			$database
				->prepare("UPDATE user SET welcome_template = NULL WHERE welcome_template = :id;")
				->execute(array(":id" => $tid));
            
			Logger::welcomeTemplateDeleted($database, $template);
            
			$template->delete();
            
			SessionAlert::success("Template deleted. Any users who were using this template have had automatic welcoming disabled.");
			Notification::welcomeTemplateDeleted($tid);
		});
        
		header("Location: $baseurl/acc.php?action=templatemgmt");
		die();			
	}
    
	if (isset($_GET['edit'])) {
		if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) {
			BootstrapSkin::displayAccessDenied();
			BootstrapSkin::displayInternalFooter();
			die();
		}

		$database = gGetDb();
        
		$template = WelcomeTemplate::getById($_GET['edit'], $database);
		if ($template == false) {
			SessionAlert::success("Something went wrong, we can't find the template you asked for! Please try again.");
			header("Location: {$baseurl}/acc.php?action=templatemgmt");
			die();
		}

		if (isset($_POST['submit'])) {
			$database->transactionally(function() use($database, $template)
			{
				$template->setUserCode($_POST['usercode']);
				$template->setBotCode($_POST['botcode']);
				$template->save();
			
				Logger::welcomeTemplateEdited($database, $template);
                
				SessionAlert::success("Template updated.");
				Notification::welcomeTemplateEdited($template);
			});
            
			header("Location: $baseurl/acc.php?action=templatemgmt");
			die();
		}
		else {
			$smarty->assign("template", $template);
			$smarty->display("welcometemplate/edit.tpl");
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
	}
    
	$templateList = WelcomeTemplate::getAll();
    
	$smarty->assign("templatelist", $templateList);
	$smarty->display("welcometemplate/list.tpl");
    
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "sban") {
	global $smarty;
    
	// Checks whether the current user is an admin.
	if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) {
		BootstrapSkin::displayAccessDenied();
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// Checks whether there is a reason entered for ban.
	if (!isset($_POST['banreason']) || $_POST['banreason'] == "") {
		BootstrapSkin::displayAlertBox("You must specify a ban reason", "alert-error", "", false, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// Checks whether there is a target entered to ban.
	if (!isset($_POST['target']) || $_POST['target'] == "") {
		BootstrapSkin::displayAlertBox("You must specify a target to be banned", "alert-error", "", false, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	$duration = $_POST['duration'];
    
	if ($duration == "-1") {
		$duration = -1;
	}
	elseif ($duration == "other") {
		$duration = strtotime($_POST['otherduration']);
		if (!$duration) {
			BootstrapSkin::displayAlertBox("Invalid ban time", "alert-error", "", false, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
		elseif (time() > $duration) {
			BootstrapSkin::displayAlertBox("Ban time has already expired!", "alert-error", "", false, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
	}
	else {
		$duration = $duration + time();
	}
    
	switch ($_POST['type']) {
		case 'IP':
			if (filter_var($_POST['target'], FILTER_VALIDATE_IP) === false) {
				BootstrapSkin::displayAlertBox("Invalid target - IP address expected.", "alert-error", "", false, false);
				BootstrapSkin::displayInternalFooter();
				die();
			}
            
			global $squidIpList;
			if (in_array($_POST['target'], $squidIpList)) {
				BootstrapSkin::displayAlertBox(
					"This IP address is on the protected list of proxies, and cannot be banned.", 
					"alert-error", 
					"", 
					false, 
					false);
				BootstrapSkin::displayInternalFooter();
				die();
			}
			break;
		case 'Name':
			break;
		case 'EMail':
			// TODO: cut this down to a bare-bones implementation so we don't accidentally reject a valid address.
			if (!preg_match(';^(?:[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[A-Za-z0-9-]*[A-Za-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$;', $_POST['target'])) {
				BootstrapSkin::displayAlertBox(
					"Invalid target - email address expected.", 
					"alert-error", 
					"", 
					false, 
					false);
                
				BootstrapSkin::displayInternalFooter();
				die();
			}
			break;
		default:
			BootstrapSkin::displayAlertBox("I don't know what type of target you want to ban! You'll need to choose from email address, IP, or requested name.", "alert-error", "", false, false);
			BootstrapSkin::displayInternalFooter();
			die();
	}
        
	if (count(Ban::getActiveBans($_POST['target'])) > 0) {
		BootstrapSkin::displayAlertBox("This target is already banned!", "alert-error", "", false, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$database = gGetDb();
    
	$ban = new Ban();
    
	$currentUsername = User::getCurrent()->getUsername();
    
	$database->transactionally(function() use ($database, $ban, $duration, $currentUsername)
	{
		$ban->setDatabase($database);
		$ban->setActive(1);
		$ban->setType($_POST['type']);
		$ban->setTarget($_POST['target']);
		$ban->setUser($currentUsername);
		$ban->setReason($_POST['banreason']);
		$ban->setDuration($duration);
    
		$ban->save();
        
		Logger::banned($database, $ban, $_POST['banreason']);
	});
    
	$smarty->assign("ban", $ban);
	BootstrapSkin::displayAlertBox($smarty->fetch("bans/bancomplete.tpl"), "alert-info", "", false, false);
        
	Notification::banned($ban);
    
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "unban") {
	global $smarty;
    
	if (!isset($_GET['id']) || $_GET['id'] == "") {
		BootstrapSkin::displayAlertBox(
			"The ID parameter appears to be missing! This is probably a bug.", 
			"alert-error", 
			"Ahoy There! Something's not right...", 
			true, 
			false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) {
		BootstrapSkin::displayAccessDenied();
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$ban = Ban::getActiveId($_GET['id']);
        
	if ($ban == false) {
		BootstrapSkin::displayAlertBox(
			"The specified ban ID is not currently active or doesn't exist!", 
			"alert-error", 
			"", 
			false, 
			false);
        
		BootstrapSkin::displayInternalFooter();
		die();
	}

	if (isset($_GET['confirmunban']) && $_GET['confirmunban'] == "true") {
		if (!isset($_POST['unbanreason']) || $_POST['unbanreason'] == "") {
			BootstrapSkin::displayAlertBox("You must enter an unban reason!", "alert-error", "", false, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
		else {
			$database = gGetDb();
            
			$database->transactionally(function() use ($database, $ban)
			{
				$ban->setActive(0);
				$ban->save();
                
				$banId = $ban->getId();
				$currentUser = User::getCurrent()->getUsername();
                
				Logger::unbanned($database, $ban, $_POST['unbanreason']);
			});
        
			BootstrapSkin::displayAlertBox("Unbanned " . $ban->getTarget(), "alert-info", "", false, false);
			BootstrapSkin::displayInternalFooter();
			Notification::unbanned($ban, $_POST['unbanreason']);
			die();
		}
	}
	else {
		$smarty->assign("ban", $ban);
		$smarty->display("bans/unban.tpl");
        
		BootstrapSkin::displayInternalFooter();
	}
}
elseif ($action == "ban") {
	global $smarty;
    
	if (isset ($_GET['ip']) || isset ($_GET['email']) || isset ($_GET['name'])) {
		if (!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) {
			BootstrapSkin::displayAlertBox("Only administrators or checkusers may ban users", "alert-error");
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		$database = gGetDb();
		// TODO: rewrite me!
		if (isset($_GET['ip'])) {
			$query = "SELECT ip, forwardedip FROM request WHERE id = :ip;";
			$statement = $database->prepare($query);
			$statement->bindValue(":ip", $_GET['ip']);
			$statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = getTrustedClientIP($row['ip'], $row['forwardedip']);
			$type = "IP";
		}
		elseif (isset($_GET['email'])) {
			$query = "SELECT email FROM request WHERE id = :ip;";
			$statement = $database->prepare($query);
			$statement->bindValue(":ip", $_GET['email']);
			$statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = $row['email'];
			$type = "EMail";
		}
		elseif (isset($_GET['name'])) {
			$query = "SELECT name FROM request WHERE id = :ip;";
			$statement = $database->prepare($query);
			$statement->bindValue(":ip", $_GET['name']);
			$statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = $row['name'];
			$type = "Name";
		}
		else {
			BootstrapSkin::displayAlertBox("Unknown ban type.", "alert-error");
			BootstrapSkin::displayInternalFooter();
			die();    
		}
        
		if (count(Ban::getActiveBans($target))) {
			BootstrapSkin::displayAlertBox("This target is already banned!", "alert-error");
			BootstrapSkin::displayInternalFooter();
			die();
		} 
        
		$smarty->assign("bantype", $type);
		$smarty->assign("bantarget", trim($target));
		$smarty->display("bans/banform.tpl");
	}
	else {
		$bans = Ban::getActiveBans();
  
		$smarty->assign("activebans", $bans);
		$smarty->display("bans/banlist.tpl");
	}
    
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") {
	global $availableRequestStates;
	
	if (array_key_exists($_GET['target'], $availableRequestStates)) {
		$request = Request::getById($_GET['id'], gGetDb());
		
		if ($request == false) {
			BootstrapSkin::displayAlertBox(
				"Could not find the specified request!", 
				"alert-error", 
				"Error!", 
				true, 
				false);
            
			BootstrapSkin::displayInternalFooter();
			die();
		}
		
		if ($request->getChecksum() != $_GET['sum']) {
			SessionAlert::error(
				"This is similar to an edit conflict on Wikipedia; it means that you have tried to perform an action "
				. "on a request that someone else has performed an action on since you loaded the page",
				"Invalid checksum");
            
			header("Location: acc.php?action=zoom&id={$request->getId()}");
			die();
		}
        
		$sqlText = <<<SQL
SELECT timestamp FROM log
WHERE objectid = :request and objecttype = 'Request' AND action LIKE 'Closed%'
ORDER BY timestamp DESC LIMIT 1;
SQL;
        
		$statement = gGetDb()->prepare($sqlText);
		$statement->execute(array(":request" => $request->getId()));
		$logTime = $statement->fetchColumn();
		$statement->closeCursor();
        
		$date = new DateTime();
		$date->modify("-7 days");
		$oneweek = $date->format("Y-m-d H:i:s");
        
		if ($request->getStatus() == "Closed" 
			&& $logTime < $oneweek 
			&& !User::getCurrent()->isAdmin() 
			&& !User::getCurrent()->isCheckuser()) {
			SessionAlert::error("Only administrators and checkusers can reopen a request that has been closed for over a week.");
			header("Location: acc.php?action=zoom&id={$request->getId()}");
			die();
		}
        
		if ($request->getStatus() == $_GET['target']) {
			SessionAlert::error(
				"Cannot set status, target already deferred to " . htmlentities($_GET['target']), 
				"Error");
			header("Location: acc.php?action=zoom&id={$request->getId()}");
			die();
		}
        
		$database = gGetDb();
		$database->transactionally(function() use ($database, $request)
		{
			global $availableRequestStates;
                
			$request->setReserved(0);
			$request->setStatus($_GET['target']);
			$request->updateChecksum();
			$request->save();
            
			$deto = $availableRequestStates[$_GET['target']]['deferto'];
			$detolog = $availableRequestStates[$_GET['target']]['defertolog'];
            
			Logger::deferRequest($database, $request, $detolog);
        
			Notification::requestDeferred($request);
			SessionAlert::success("Request {$request->getId()} deferred to $deto");
			header("Location: acc.php");
		});
        
		die();
	}
	else {
		BootstrapSkin::displayAlertBox("Defer target not valid.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
}
elseif ($action == "prefs") {
	global $smarty, $enforceOAuth;
    
	if (isset ($_POST['sig'])) {
		$user = User::getCurrent();
		$user->setWelcomeSig($_POST['sig']);
		$user->setEmailSig($_POST['emailsig']);
		$user->setAbortPref(isset($_POST['abortpref']) ? 1 : 0);
        
		if (isset($_POST['email'])) {
			$mailisvalid = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            
			if ($mailisvalid === false) {
				BootstrapSkin::displayAlertBox("Invalid email address", "alert-error", "Error!");
			}
			else {
				$user->setEmail(trim($_POST['email']));
			}
		}

		try {
			$user->save();
		}
		catch (PDOException $ex) {
			BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error saving Preferences", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		BootstrapSkin::displayAlertBox("Preferences updated!", "alert-info");
	}
    
	$smarty->assign("enforceOAuth", $enforceOAuth);
	$smarty->display("prefs.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
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

	/** @var EmailTemplate $emailTemplate */
	$emailTemplate = EmailTemplate::getById($gem, gGetDb());
	if ($emailTemplate instanceof EmailTemplate) {
		$isForCreated = $emailTemplate->getDefaultAction() === EmailTemplate::CREATED;
	} else {
		$isForCreated = false;
	}

	// check if a request being created does not already exist.
	if ($isForCreated && !$exists && !isset($_GET['createoverride'])) {
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

		// CC mailing list option
		if (User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser()) {
			// these people get the choice
			if (isset($_POST['ccmailist']) && $_POST['ccmailist'] == "on") {
				$headers .= 'Cc: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
			}
		} else {
			// these people do not.
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
elseif ($action == "zoom") {
	if (!isset($_GET['id'])) {
		BootstrapSkin::displayAlertBox("No request specified!", "alert-error", "Error!", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (isset($_GET['hash'])) {
		$urlhash = $_GET['hash'];
	}
	else {
		$urlhash = "";
	}
	echo zoomPage($_GET['id'], $urlhash);

	$tailscript = getTypeaheadSource(User::getAllUsernames(gGetDb()));
	BootstrapSkin::displayInternalFooter($tailscript);
	die();
}
elseif ($action == "logs") {
	global $baseurl;
	
	$filterUser = isset($_GET['filterUser']) && $_GET['filterUser'] != "" ? $_GET['filterUser'] : false;
	$filterAction = isset($_GET['filterAction']) && $_GET['filterAction'] != "" ? $_GET['filterAction'] : false;
	
	$limit = 100;
	if (isset($_GET['limit'])) {
		$limit = (int)$_GET['limit'];
	}
	
	$offset = 0;
	$page = 1;
	if (isset($_GET['page'])) {
		$page = (int)$_GET['page'];
		$offset = ($page - 1) * $limit;
	}
	
	$logs = Logger::getLogs($filterUser, $filterAction, $limit, $offset);
	if ($logs === false) {
		$smarty->assign("logs", array());
		$smarty->display("logs/main.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	$count = $logs['count'];
	unset($logs['count']);
	
	// The number of pages on the pager to show. Must be odd
	$pageLimit = 9;
	
	$pageData = array( 
		'canprev' => $page != 1,
		'cannext' => ($page * $limit) < $count,
		'maxpage' => ceil($count / $limit),
		'pagelimit' => $pageLimit,
	);
	
	$pageMargin = (($pageLimit - 1) / 2);
	$pageData['lowpage'] = max(1, $page - $pageMargin);
	$pageData['hipage'] = min($pageData['maxpage'], $page + $pageMargin);
	
	$pageCount = ($pageData['hipage'] - $pageData['lowpage']) + 1;
	
	if ($pageCount < $pageLimit) {
		if ($pageData['lowpage'] == 1 && $pageData['hipage'] == $pageData['maxpage']) {
			// nothing to do, we're already at max range.	
		}
		elseif ($pageData['lowpage'] == 1 && $pageData['hipage'] < $pageData['maxpage']) {
			$pageData['hipage'] = min($pageLimit, $pageData['maxpage']);
		}
		elseif ($pageData['lowpage'] > 1 && $pageData['hipage'] == $pageData['maxpage']) {
			$pageData['lowpage'] = max(1, $pageData['maxpage'] - $pageLimit + 1);
		}
	}
	
	$pageData['pages'] = range($pageData['lowpage'], $pageData['hipage']);
		
	$smarty->assign("pagedata", $pageData);
	
	$smarty->assign("limit", $limit);
	$smarty->assign("page", $page);

	$smarty->assign("logs", $logs);
	
	
	$smarty->assign("filterUser", $filterUser);
	$smarty->assign("filterAction", $filterAction);
	$smarty->display("logs/main.tpl");

	$tailscript = getTypeaheadSource(User::getAllUsernames(gGetDb(), true));
	
	BootstrapSkin::displayInternalFooter($tailscript);
	die();
}
elseif ($action == "reserve") {
	$database = gGetDb();
    
	$database->transactionally(function() use ($database)
	{
		$request = Request::getById($_GET['resid'], $database);
        
		if ($request == false) {
			throw new TransactionException("Request not found", "Error");
		}
        
		global $enableEmailConfirm, $baseurl;
		if ($enableEmailConfirm == 1) {
			if ($request->getEmailConfirm() != "Confirmed") {
				throw new TransactionException("Email address not yet confirmed for this request.", "Error");
			}
		}

		$logQuery = $database->prepare(<<<SQL
SELECT timestamp FROM log
WHERE objectid = :request AND objecttype = 'Request' AND action LIKE 'Closed%'
ORDER BY timestamp DESC LIMIT 1;
SQL
		);
		$logQuery->bindValue(":request", $request->getId());
		$logQuery->execute();
		$logTime = $logQuery->fetchColumn();
		$logQuery->closeCursor();
        
		$date = new DateTime();
		$date->modify("-7 days");
		$oneweek = $date->format("Y-m-d H:i:s");
        
		if ($request->getStatus() == "Closed" && $logTime < $oneweek && !User::getCurrent($database)->isAdmin()) {
			throw new TransactionException("Only administrators and checkusers can reserve a request that has been closed for over a week.", "Error");
		}
        
	   	if ($request->getReserved() != 0 && $request->getReserved() != User::getCurrent($database)->getId()) {
			throw new TransactionException("Request is already reserved by {$request->getReservedObject()->getUsername()}.", "Error");
		}
           
		if ($request->getReserved() == 0) {
			// Check the number of requests a user has reserved already
			$doubleReserveCountQuery = $database->prepare("SELECT COUNT(*) FROM request WHERE reserved = :userid;");
			$doubleReserveCountQuery->bindValue(":userid", User::getCurrent($database)->getId());
			$doubleReserveCountQuery->execute();
			$doubleReserveCount = $doubleReserveCountQuery->fetchColumn();
			$doubleReserveCountQuery->closeCursor();

			// User already has at least one reserved. 
			if ($doubleReserveCount != 0) {
				SessionAlert::warning("You have multiple requests reserved!");
			}

			// Is the request closed?
			if (!isset($_GET['confclosed'])) {
				if ($request->getStatus() == "Closed") {
					// FIXME: bootstrappify properly
					throw new TransactionException('This request is currently closed. Are you sure you wish to reserve it?<br /><ul><li><a href="' . $_SERVER["REQUEST_URI"] . '&confclosed=yes">Yes, reserve this closed request</a></li><li><a href="' . $baseurl . '/acc.php">No, return to main request interface</a></li></ul>', "Request closed", "alert-info");
				}
			}	
        
			$request->setReserved(User::getCurrent($database)->getId());
			$request->save();
	
			Logger::reserve($database, $request);
                
			Notification::requestReserved($request);
                
			SessionAlert::success("Reserved request {$request->getId()}.");
		}
        
		header("Location: $baseurl/acc.php?action=zoom&id={$request->getId()}");
	});
	    
	die();	
}
elseif ($action == "breakreserve") {
	global $smarty;
    
	$database = gGetDb();
    
	$request = Request::getById($_GET['resid'], $database);
        
	if ($request == false) {
		BootstrapSkin::displayAlertBox("Could not find request.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if ($request->getReserved() == 0) {
		BootstrapSkin::displayAlertBox("Request is not reserved.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$reservedUser = $request->getReservedObject();
    
	if ($reservedUser == false) {
		BootstrapSkin::displayAlertBox("Could not find user who reserved the request (!!).", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if ($reservedUser->getId() != User::getCurrent()->getId()) {
		if (User::getCurrent()->isAdmin()) {
			if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
				$database->transactionally(function() use($database, $request)
				{
					$request->setReserved(0);
					$request->save();

					Logger::breakReserve($database, $request);
                
					Notification::requestReserveBroken($request);
					header("Location: acc.php");
				});
                
				die();
			}
			else {
				global $baseurl;
				$smarty->assign("reservedUser", $reservedUser);
				$smarty->assign("request", $request);
                
				$smarty->display("confirmations/breakreserve.tpl");
			}
		}
		else {
			echo "You cannot break " . htmlentities($reservedUser->getUsername()) . "'s reservation";
		}
	}
	else {
		$database->transactionally(function() use ($database, $request)
		{
			$request->setReserved(0);
			$request->save();

			Logger::unreserve($database, $request);
        
			Notification::requestUnreserved($request);
			header("Location: acc.php");
		});
        
		die();
	}
    
	BootstrapSkin::displayInternalFooter();
	die();		
}
elseif ($action == "comment") {
	global $smarty;
    
	$request = Request::getById($_GET['id'], gGetDb());
	$smarty->assign("request", $request);
	$smarty->display("commentform.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "comment-add") {
	global $baseurl, $smarty;
    
	$request = Request::getById($_POST['id'], gGetDb());
	if ($request == false) {
		BootstrapSkin::displayAlertBox("Could not find request!", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (!isset($_POST['comment']) || $_POST['comment'] == "") {
		BootstrapSkin::displayAlertBox("Comment must be supplied!", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die(); 
	}
    
	$visibility = 'user';
	if (isset($_POST['visibility'])) {
		// sanity check
		$visibility = $_POST['visibility'] == 'user' ? 'user' : 'admin';
	}
    
	//Look for and detect IPv4/IPv6 addresses in comment text, and warn the commenter.
	if ((preg_match('/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/', $_POST['comment']) || preg_match('/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/', $_POST['comment'])) && $_POST['privpol-check-override'] != "override") {
			BootstrapSkin::displayAlertBox("IP address detected in comment text.  Warning acknowledgement checkbox must be checked.", "alert-error", "Error", true, false);
			$smarty->assign("request", $request);
			$smarty->assign("comment", $_POST['comment']);
			$smarty->assign("actionLocation", "comment-add");
			$smarty->display("privpol-warning.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		}
    
	$comment = new Comment();
	$comment->setDatabase(gGetDb());
    
	$comment->setRequest($request->getId());
	$comment->setVisibility($visibility);
	$comment->setUser(User::getCurrent()->getId());
	$comment->setComment($_POST['comment']);
    
	$comment->save();
    
	if (isset($_GET['hash'])) {
		$urlhash = urlencode(htmlentities($_GET['hash']));
	}
	else {
		$urlhash = "";
	}

	BootstrapSkin::displayAlertBox(
		"<a href='$baseurl/acc.php?action=zoom&amp;id={$request->getId()}&amp;hash=$urlhash'>Return to request #{$request->getId()}</a>",
		"alert-success",
		"Comment added Successfully!",
		true, false);
        
	Notification::commentCreated($comment);
        
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "comment-quick") {
	$request = Request::getById($_POST['id'], gGetDb());
	if ($request == false) {
		BootstrapSkin::displayAlertBox("Could not find request!", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (!isset($_POST['comment']) || $_POST['comment'] == "") {
		header("Location: acc.php?action=zoom&id=" . $request->getId());
		die(); 
	}
    
	$visibility = 'user';
	if (isset($_POST['visibility'])) {
		// sanity check
		$visibility = $_POST['visibility'] == 'user' ? 'user' : 'admin';
	}

	//Look for and detect IPv4/IPv6 addresses in comment text, and warn the commenter.
	if ((preg_match('/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/', $_POST['comment']) || preg_match('/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))/', $_POST['comment'])) && $_POST['privpol-check-override'] != "override") {
			BootstrapSkin::displayAlertBox("IP address detected in comment text.  Warning acknowledgement checkbox must be checked.", "alert-error", "Error", true, false);
			$smarty->assign("request", $request);
			$smarty->assign("comment", $_POST['comment']);
			$smarty->assign("actionLocation", "comment-quick");
			$smarty->display("privpol-warning.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		}
    
	$comment = new Comment();
	$comment->setDatabase(gGetDb());
    
	$comment->setRequest($request->getId());
	$comment->setVisibility($visibility);
	$comment->setUser(User::getCurrent()->getId());
	$comment->setComment($_POST['comment']);
    
	$comment->save();
    
	Notification::commentCreated($comment);
    
	header("Location: acc.php?action=zoom&id=" . $request->getId());
}
elseif ($action == "changepassword") {
	if ((!isset($_POST['oldpassword'])) || $_POST['oldpassword'] == "") {
		//Throw an error if old password is not specified.
		BootstrapSkin::displayAlertBox("You did not enter your old password.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	if ((!isset($_POST['newpassword'])) || $_POST['newpassword'] == "") {
		//Throw an error if new password is not specified.
		BootstrapSkin::displayAlertBox("You did not enter your new password.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	if ($_POST['newpassword'] != $_POST['newpasswordconfirm']) {
		//Throw an error if new password does not match what is in the confirmation box.
		BootstrapSkin::displayAlertBox("The 2 new passwords you entered do not match.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$user = User::getCurrent();
	   
	if (!$user->authenticate($_POST['oldpassword'])) {
		//Throw an error if the old password field's value does not match the user's current password.
		BootstrapSkin::displayAlertBox("The old password you entered is not correct.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$user->setPassword($_POST['newpassword']);
	$user->save();
    
	BootstrapSkin::displayAlertBox("Password successfully changed!", "alert-success", "", false, false);
	BootstrapSkin::displayInternalFooter();
	die();
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
			header("Location: $baseurl/acc.php?action=zoom&id=" . $comment->getRequest());
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

	/** @var Request $requestObject */
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
    
	$database->transactionally(function() use ($database, $user, $requestObject, $curuser)
	{
		$updateStatement = $database->prepare("UPDATE request SET reserved = :userid WHERE id = :request LIMIT 1;");
		$updateStatement->bindValue(":userid", $user->getId());
		$updateStatement->bindValue(":request", $requestObject->getId());
		if (!$updateStatement->execute()) {
			throw new TransactionException("Error updating reserved status of request.");   
		}
        
		Logger::sendReservation($database, $requestObject, $user);
	});
    
	Notification::requestReservationSent($requestObject, $user);
	SessionAlert::success("Reservation sent successfully");
	header("Location: $baseurl/acc.php?action=zoom&id=$request");
}
elseif ($action == "emailmgmt") {
	global $smarty, $createdid, $availableRequestStates;
    
	/* New page for managing Emails, since I would rather not be handling editing
	interface messages (such as the Sitenotice) and the new Emails in the same place. */
	if (isset($_GET['create'])) {
		if (!User::getCurrent()->isAdmin()) {
			BootstrapSkin::displayAccessDenied();
			BootstrapSkin::displayInternalFooter();
			die();
		}
		if (isset($_POST['submit'])) {
			$database = gGetDb();
			$database->transactionally(function() use ($database)
			{
				global $baseurl;
                
				$emailTemplate = new EmailTemplate();
				$emailTemplate->setDatabase($database);
            
				$emailTemplate->setName($_POST['name']);
				$emailTemplate->setText($_POST['text']);
				$emailTemplate->setJsquestion($_POST['jsquestion']);
				$emailTemplate->setDefaultAction($_POST['defaultaction']);
				$emailTemplate->setActive(isset($_POST['active']));

				// Check if the entered name already exists (since these names are going to be used as the labels for buttons on the zoom page).
				// getByName(...) returns false on no records found.
				if (EmailTemplate::getByName($_POST['name'], $database)) {
					throw new TransactionException("That Email template name is already being used. Please choose another.");
				}
			
				$emailTemplate->save();
                
				Logger::createEmail($database, $emailTemplate);
                
				Notification::emailCreated($emailTemplate);
                
				SessionAlert::success("Email template has been saved successfully.");
				header("Location: $baseurl/acc.php?action=emailmgmt");
			});
            
			die();
		}
        
		$smarty->assign('id', null);
		$smarty->assign('createdid', $createdid);
		$smarty->assign('requeststates', $availableRequestStates);
		$smarty->assign('emailTemplate', new EmailTemplate());
		$smarty->assign('emailmgmtpage', 'Create'); //Use a variable so we don't need two Smarty templates for creating and editing.
		$smarty->display("email-management/edit.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	if (isset($_GET['edit'])) {
		global $createdid;
        
		$database = gGetDb();
        
		if (isset($_POST['submit'])) {
			$emailTemplate = EmailTemplate::getById($_GET['edit'], $database);
			// Allow the user to see the edit form (with read only fields) but not POST anything.
			if (!User::getCurrent()->isAdmin()) {
				BootstrapSkin::displayAccessDenied();
				BootstrapSkin::displayInternalFooter();
				die();
			}
            
			$emailTemplate->setName($_POST['name']);
			$emailTemplate->setText($_POST['text']);
			$emailTemplate->setJsquestion($_POST['jsquestion']);
			
			if ($_GET['edit'] == $createdid) {
				// Both checkboxes on the main created message should always be enabled.
				$emailTemplate->setDefaultAction(EmailTemplate::CREATED);
				$emailTemplate->setActive(1);
				$emailTemplate->setPreloadOnly(0);
			}
			else {
				$emailTemplate->setDefaultAction($_POST['defaultaction']);
				$emailTemplate->setActive(isset($_POST['active']));
				$emailTemplate->setPreloadOnly(isset($_POST['preloadonly']));
			}
				
			// Check if the entered name already exists (since these names are going to be used as the labels for buttons on the zoom page).
			$nameCheck = EmailTemplate::getByName($_POST['name'], gGetDb());
			if ($nameCheck != false && $nameCheck->getId() != $_GET['edit']) {
				BootstrapSkin::displayAlertBox("That Email template name is already being used. Please choose another.");
				BootstrapSkin::displayInternalFooter();
				die();
			}

			$database->transactionally(function() use ($database, $emailTemplate)
			{
				$emailTemplate->save();
                
				Logger::editedEmail($database, $emailTemplate);
            
				global $baseurl;
                
				Notification::emailEdited($emailTemplate);
				SessionAlert::success("Email template has been saved successfully.");
				header("Location: $baseurl/acc.php?action=emailmgmt");
			});
            
			die();
		}
        
		$emailTemplate = EmailTemplate::getById($_GET['edit'], gGetDb());
		$smarty->assign('id', $emailTemplate->getId());
		$smarty->assign('emailTemplate', $emailTemplate);
		$smarty->assign('createdid', $createdid);
		$smarty->assign('requeststates', $availableRequestStates);
		$smarty->assign('emailmgmtpage', 'Edit'); // Use a variable so we don't need two Smarty templates for creating and editing.
		$smarty->display("email-management/edit.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$query = "SELECT * FROM emailtemplate WHERE active = 1";
	$statement = gGetDb()->prepare($query);
	$statement->execute();
	$rows = $statement->fetchAll(PDO::FETCH_CLASS, "EmailTemplate");
	$smarty->assign('activeemails', $rows);
        
	$query = "SELECT * FROM emailtemplate WHERE active = 0";
	$statement = gGetDb()->prepare($query);
	$statement->execute();
	$inactiverows = $statement->fetchAll(PDO::FETCH_CLASS, "EmailTemplate");
	$smarty->assign('inactiveemails', $inactiverows);
 
	if (count($inactiverows) > 0) {
		$smarty->assign('displayinactive', true);
	}
	else {
		$smarty->assign('displayinactive', false);
	}
    
	$smarty->display("email-management/main.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
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
elseif ($action == "listall") {
    global $availableRequestStates, $enableEmailConfirm;

	if (isset($_GET['status']) && isset($availableRequestStates[$_GET['status']])) {
		$type = $_GET['status']; // safe, we've verified it's sane in the above if statement.

	    $database = gGetDb();

	    if ($enableEmailConfirm == 1) {
	        $query = "SELECT * FROM request WHERE status = :type AND emailconfirm = 'Confirmed';";
	    } else {
	        $query = "SELECT * FROM request WHERE status = :type;";
	    }

	    $statement = $database->prepare($query);

        $statement->bindValue(":type", $type);
        $statement->execute();

        $requests = $statement->fetchAll(PDO::FETCH_CLASS, "Request");
        foreach ($requests as $req) {
        	/** @var Request $req */
            $req->setDatabase($database);
        }

        global $smarty;
        $smarty->assign("requests", $requests);
        $smarty->assign("showStatus", false);
        $html = $smarty->fetch("mainpage/requesttable.tpl");
        echo $html;
    } else {
        echo defaultpage();
    }

    BootstrapSkin::displayInternalFooter();
    die();
}
# If the action specified does not exist, goto the default page.
else {
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
	die();
}
