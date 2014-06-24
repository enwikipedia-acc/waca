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
require_once 'LogClass.php';
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php'; // this needs to be high up, but below config, functions, and database
require_once 'includes/database.php';
require_once 'includes/session.php';
require_once 'lib/mediawiki-extensions-OAuth/lib/OAuth.php';
require_once 'lib/mediawiki-extensions-OAuth/lib/JWT.php';
require_once 'oauth/OAuthUtility.php';

// Set the current version of the ACC.
$version = "0.9.7";

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
if(Offline::isOffline())
{
    echo Offline::getOfflineMessage(false);
    die();
}

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

// Initialize the class objects.
$session = new session();
$date = new DateTime();

// initialise providers
global $squidIpList;
$locationProvider = new $locationProviderClass(gGetDb('acc'), $locationProviderApiKey);
$rdnsProvider = new $rdnsProviderClass(gGetDb('acc'));
$antispoofProvider = new $antispoofProviderClass();
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

// Checks whether the user and nocheck variable is set.
// When none of these are set, the user should first login.
if (!isset($_SESSION['user']) && !isset($_GET['nocheck'])) {
//if (!isset($_SESSION['user']) && !($action=='login' && isset($_POST['username']))) {
	// Sets the parameter to blank, this way the correct options would be displayed.
	// It would tell the user now that he or she should log in or create an account.
	$suser = '';
	BootstrapSkin::displayInternalHeader();

	// Checks whether the user want to reset his password or register a new account.
	// Performs the clause when the action is not one of the above options.
	if ($action != 'register' && $action != 'forgotpw' && $action != 'sreg' && $action != "registercomplete") {
		if (isset($action)) {
			// Display the login form and the rest of the page coding.
			// The data in the current $GET varianle would be send as parameter.
			// There it would be used to possibly fill some of the form's fields.
			echo showlogin();
			BootstrapSkin::displayInternalFooter();
		}
		elseif (!isset($action)) {
			// When the action variable isn't set to anything,
			// the login page is displayed for the user to complete.
			echo showlogin();
			BootstrapSkin::displayInternalFooter();
		}
		// All the needed HTML code is displayed for the user.
		// The script is thus terminated.
		die();
	} else {
	    // A content block is created if the action is none of the above.
		// This block would later be used to keep all the HTML except the header and footer.
		$out = "<div id=\"content\">";
		echo $out;
	}
}
// Executes if the user variable is set, but not the nocheck.
// This ussually happens when an user account has been renamed.
// LouriePieterse: I cant figure out for what reason this is used.
elseif (!isset($_GET['nocheck']))
{
		// Forces the current user to logout.
        $session->forceLogout($_SESSION['userID']);

		// ?
        BootstrapSkin::displayInternalHeader();
        $session->checksecurity();
}

// When no action is specified the default Internal ACC are displayed.
// TODO: Improve way the method is called.
if ($action == '') {
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
	die();
}

elseif ($action == "sreg")
{
    global $useOauthSignup;
        
    // TODO: check blocked
    // TODO: check age.
    
	// check if user checked the "I have read and understand the interface guidelines" checkbox
	if(!isset($_REQUEST['guidelines'])) {
        BootstrapSkin::displayAlertBox("You must read <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\">the interface guidelines</a> before your request may be submitted.", "alert-info", "Sorry!", false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
		BootstrapSkin::displayAlertBox("Invalid email address", "alert-error", "Error!", false);
        BootstrapSkin::displayInternalFooter();
        die();
	}
    
	if ($_REQUEST['pass'] !== $_REQUEST['pass2']) 
    { 
        BootstrapSkin::displayAlertBox("Your passwords did not match, please try again.", "alert-error", "Error!", false);
        BootstrapSkin::displayInternalFooter();
		die();
	}
    
    if(!$useOauthSignup)
    {
        if(!((string)(int)$_REQUEST['conf_revid'] === (string)$_REQUEST['conf_revid']) || $_REQUEST['conf_revid'] == "")
        {
            BootstrapSkin::displayAlertBox("Please enter the revision id of your confirmation edit in the \"Confirmation diff\" field. The revid is the number after the &diff= part of the URL of a diff.", "alert-error", "Error!", false);
            BootstrapSkin::displayInternalFooter();
            die();		
        }
    }
    
	if (User::getByUsername($_REQUEST['name'], gGetDb()) != false) {
        BootstrapSkin::displayAlertBox("Sorry, but that username is in use. Please choose another.", "alert-error", "Error!", false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	$query = gGetDb()->prepare("SELECT * FROM user WHERE email = :email LIMIT 1;");
    $query->execute(array(":email" => $_REQUEST['email']));
    if($query->fetchObject("User") != false)
    {
        BootstrapSkin::displayAlertBox("I'm sorry, but that e-mail address is in use.", "alert-error", "Error!", false);
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
        
        if(!$useOauthSignup)
        {
            $newUser->setOnWikiName($_REQUEST['wname']);
            $newUser->setConfirmationDiff($_REQUEST['conf_revid']);
        }
        
        $newUser->save();
    
        global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal, $useOauthSignup;
    
        if($useOauthSignup)
        {
            try
            {
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
            catch(Exception $ex)
            {
                throw new TransactionException($ex->getMessage(), "Connection to Wikipedia failed.", "alert-error", 0, $ex);
            }
        }
        else
        {
            global $baseurl;
            Notification::userNew($newUser);
            header("Location: {$baseurl}acc.php?action=registercomplete");
        }
    });
    
	die();
}

elseif ($action == "register") 
{
    global $useOauthSignup, $smarty;
    $smarty->assign("useOauthSignup", $useOauthSignup);
    $smarty->display("register.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "registercomplete")
{
    BootstrapSkin::displayAlertBox("Your request will be reviewed soon by a tool administrator, and you'll get an email informing you of the decision.", "alert-success", "Account requested!", false);
    BootstrapSkin::displayInternalFooter();
}
elseif ($action == "forgotpw")
{
    global $baseurl, $smarty;
    
	if (isset ($_GET['si']) && isset ($_GET['id'])) 
    {
        $user = User::getById($_GET['id'], gGetDb());
        
        if($user === false)
        {
            BootstrapSkin::displayAlertBox("User not found.", "alert-error");
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
		if (isset ($_POST['pw']) && isset ($_POST['pw2'])) 
        {
			$hash = $user->getForgottenPasswordHash();
            
			if ($hash == $_GET['si']) 
            {
				if ($_POST['pw'] == $_POST['pw2']) 
                {
                    $user->setPassword($_POST['pw2']);
                    $user->save();
                    
                    BootstrapSkin::displayAlertBox("You may now <a href=\"$baseurl/acc.php\">Login</a>", "alert-error", "Password reset!", true, false);
                    BootstrapSkin::displayInternalFooter();
                    die();
				} 
                else 
                {
                    BootstrapSkin::displayAlertBox("Passwords did not match!", "alert-error", "Error", true, false);
                    BootstrapSkin::displayInternalFooter();
                    die();
				}
			} 
            else 
            {
                BootstrapSkin::displayAlertBox("Invalid request<!-- 1 -->", "alert-error", "Error", true, false);
                BootstrapSkin::displayInternalFooter();
                die();
			}
		}
        
		$hash = $user->getForgottenPasswordHash();
        
		if ($hash == $_GET['si']) 
        {
			$smarty->assign('user', $user);
			$smarty->assign('si',$_GET['si']);
			$smarty->assign('id',$_GET['id']);
			$smarty->display('forgot-password/forgotpwreset.tpl');
		} 
        else 
        {
            BootstrapSkin::displayAlertBox("The hash supplied in the link did not match the hash in the database!", "alert-error", "Invalid request", true, false);
		}
        
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (isset ($_POST['username'])) 
    {
        $user = User::getByUsername($_POST['username'], gGetDb());

		if ($user == false) 
        {
            BootstrapSkin::displayAlertBox("Could not find user with that username and email address!", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
		}
		elseif (strtolower($_POST['email']) != strtolower($user->getEmail())) 
        {
            BootstrapSkin::displayAlertBox("Could not find user with that username and email address!", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
		}
		else
        {
		    $hash = $user->getForgottenPasswordHash();
		    // re bug 29: please don't escape the url parameters here: it's a plain text email so no need to escape, or you break the link
		    $mailtxt = "Hello! You, or a user from " . $_SERVER['REMOTE_ADDR'] . ", has requested a password reset for your account.\n\nPlease go to $baseurl/acc.php?action=forgotpw&si=$hash&id=" . $user->getId() . " to complete this request.\n\nIf you did not request this reset, please disregard this message.\n\n";
		    $headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		    mail($user->getEmail(), "English Wikipedia Account Request System - Forgotten password", $mailtxt, $headers);
            BootstrapSkin::displayAlertBox("<strong>Your password reset request has been completed.</strong> Please check your e-mail.", "alert-success", "", false, false);
            BootstrapSkin::displayInternalFooter();
            die();
		}
	}
    
    $smarty->display('forgot-password/forgotpw.tpl');

    BootstrapSkin::displayInternalFooter();
    die();
}
elseif ($action == "login") 
{
    global $baseurl, $smarty;
    
    $user = User::getByUsername($_POST['username'], gGetDb());
    
    if($user == false || !$user->authenticate($_POST['password']) )
    {
        header("Location: $baseurl/acc.php?error=authfail&tplUsername=" . urlencode($_POST['username']));
        die();
    }
    
    // At this point, the user has successfully authenticated themselves.
    // We now proceed to perform login-specific actions, and check the user actually has
    // the correct permissions to continue with the login.
    
    if($user->getForcelogout())
    {
        $user->setForcelogout(false);
        $user->save();
    }
    
    if($user->isNew()) 
    {
        header("Location: $baseurl/acc.php?error=newacct");
        die();
    }
    
    $database = gGetDb();
    $suspendstatement = $database->prepare("SELECT log_cmt FROM acc_log WHERE log_action = :action AND log_pend = :userid ORDER BY log_time DESC LIMIT 1;");
    
    if($user->isDeclined()) 
    {
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
    
    if($user->isSuspended()) 
    {
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
    
    if(!$user->isIdentified() && $forceIdentification == 1) 
    {
        header("Location: $baseurl/acc.php?error=noid");
        die();
    }
    
    // At this point, we've tested that the user is OK, so we set the login cookies.
    
    $_SESSION['user'] = $user->getUsername();
    $_SESSION['userID'] = $user->getId();
    
    if( $user->getOAuthAccessToken() == null && $user->getStoredOnWikiName() == "##OAUTH##")
    {
        global $oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal;

        try
        {
            // Get a request token for OAuth
            $util = new OAuthUtility($oauthConsumerToken, $oauthSecretToken, $oauthBaseUrl, $oauthBaseUrlInternal);
            $requestToken = $util->getRequestToken();

            // save the request token for later
            $user->setOAuthRequestToken($requestToken->key);
            $user->setOAuthRequestSecret($requestToken->secret);
            $user->save();
            
            $redirectUrl = $util->getAuthoriseUrl($requestToken);
            
            header("Location: {$redirectUrl}");
            die();
        }
        catch(Exception $ex)
        {
            throw new TransactionException($ex->getMessage(), "Connection to Wikipedia failed.", "alert-error", 0, $ex);
        }        
    }
    
    header("Location: $baseurl/acc.php");
}
elseif ($action == "messagemgmt") 
{
    global $smarty;
    
	if (isset($_GET['view'])) 
    {
        $message = InterfaceMessage::getById($_GET['view'], gGetDb());
                
	    if ($message == false)
        {
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
	if (isset($_GET['edit'])) 
    {
	    if(!(User::getCurrent()->isAdmin() || User::getCurrent()->isCheckuser()))
        {
            BootstrapSkin::displayAlertBox("I'm sorry, but, this page is restricted to administrators only.", "alert-error", "Access Denied", true, false);
            BootstrapSkin::displayInternalFooter();
			die();
		}
        
        $database = gGetDb();
        
        $database->transactionally(function() use ($database)
        {
            global $smarty;
            
            $message = InterfaceMessage::getById($_GET['edit'], $database);
            
            if ($message == false)
            {
                throw new TransactionException("Unable to find specified message", "Error");
            }
            
            if ( isset( $_GET['submit'] ) ) 
            {   
                $message->setContent($_POST['mailtext']);
                $message->setDescription($_POST['maildesc']);
                $message->save();
            
                $logStatement = gGetDb()->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:message, :user, 'Edited', CURRENT_TIMESTAMP());");
                $logStatement->bindValue(":message", $message->getId());
                $logStatement->bindValue(":user", User::getCurrent()->getUsername());
                $logStatement->execute();
            
                BootstrapSkin::displayAlertBox("Message {$message->getDescription()} ({$message->getId()}) updated.", "alert-success", "Saved!", true, false);
                
                Notification::interfaceMessageEdited($message);
                
			    BootstrapSkin::displayInternalFooter();
		    }
            
            $smarty->assign("message", $message);
            $smarty->assign("readonly", false);
            $smarty->display("message-management/editform.tpl");
        
		    BootstrapSkin::displayInternalFooter();
        });
        
		die();
	}
    
    $fetchStatement = gGetDb()->prepare("SELECT * FROM interfacemessage WHERE type = :type AND description NOT LIKE '%[deprecated]';");
    $data = array();
    
    // hide from display, these are all deprecated now. --stw 17-MAR-2014
    // $fetchStatement->execute(array(":type" => "Message"));
    // $data['Email messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
    $fetchStatement->execute(array(":type" => "Interface"));
    $data['Public Interface messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
    $fetchStatement->execute(array(":type" => "Internal"));
    $data['Internal Interface messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
    $smarty->assign("data", $data);
    $smarty->display('message-management/view.tpl');
   
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "templatemgmt") 
{
    global $baseurl, $smarty;
    
	if (isset($_GET['view'])) 
    {
        $template = WelcomeTemplate::getById($_GET['view'], gGetDb());
        
        if($template === false)
        {
            SessionAlert::success("Something went wrong, we can't find the template you asked for! Please try again.");
            header("Location: {$baseurl}/acc.php?action=templatemgmt");
            die();
        }
        
        $smarty->assign("template", $template);
        $smarty->display("welcometemplate/view.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (isset($_GET['add'])) 
    {
		if(!User::getCurrent()->isAdmin() || !User::getCurrent()->isCheckuser()) 
        {
            BootstrapSkin::displayAlertBox("I'm sorry, but you do not have permission to access this page.", "alert-error", "Access Denied", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
		if (isset($_POST['submit'])) 
        {
            global $baseurl;
            
            $database = gGetDb();
            
            $database->transactionally(function() use ($database, $baseurl)
            {
                $template = new WelcomeTemplate();
                $template->setDatabase($database);
                $template->setUserCode($_POST['usercode']);
                $template->setBotCode($_POST['botcode']);
                $template->save();
            
                $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:templateid, :username, 'CreatedTemplate', CURRENT_TIMESTAMP());";
                $statement = $database->prepare($query);
                $statement->execute(
                    array(
                        ":templateid" => $template->getId(), 
                        ":username" => User::getCurrent()->getUsername()
                        )
                    );
            
                Notification::welcomeTemplateCreated($template);
            
                SessionAlert::success("Template successfully created.");
                header("Location: $baseurl/acc.php?action=templatemgmt");
            });
		} 
        else 
        {
			
			if (isset($_POST['preview'])) {
				$usercode = $_POST['usercode'];
				$botcode = $_POST['botcode'];
				echo displayPreview($usercode);
			} else {
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
    
    if(isset($_GET['select']))
    {
        $user = User::getCurrent();
        
        if($_GET['select'] == 0)
        {
            $user->setWelcomeTemplate(null);
            $user->save();
            
            SessionAlert::success("Disabled automatic user welcoming.");
            header("Location: {$baseurl}/acc.php?action=templatemgmt");
            die();
        }
        else
        {
            $template = WelcomeTemplate::getById($_GET['select'], gGetDb());
            if($template !== false)
            {
                $user->setWelcomeTemplate($template->getId());
                $user->save();
                
                SessionAlert::success("Updated selected welcome template for automatic welcoming.");
                header("Location: {$baseurl}/acc.php?action=templatemgmt");
                die();
            }
            else
            {
                SessionAlert::success("Something went wrong, we can't find the template you asked for! Please try again.");
                header("Location: {$baseurl}/acc.php?action=templatemgmt");
                die();
            }
        }
    }
    
	if (isset($_GET['del'])) 
    {
        global $baseurl;
        
		if(!User::getCurrent()->isAdmin() || !User::getCurrent()->isCheckuser()) 
        {
            BootstrapSkin::displayAlertBox("I'm sorry, but you do not have permission to access this page.", "alert-error", "Access Denied", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

        $database = gGetDb();
        
        $template = WelcomeTemplate::getById($_GET['del'], $database);
        if($template == false)
        {
            SessionAlert::success("Something went wrong, we can't find the template you asked for! Please try again.");
            header("Location: {$baseurl}/acc.php?action=templatemgmt");
            die();
        }
        
        $database->transactionally(function() use($database, $template)
        {
            $tid = $template->getId();
            
            $database
                ->prepare("UPDATE user SET welcome_template = NULL WHERE welcome_template = :id;")
                ->execute(array(":id" => $tid));
            
            $database
                ->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:tid, :user, 'DeletedTemplate', CURRENT_TIMESTAMP());")
                ->execute(array(":tid" => $tid, ":user" => User::getCurrent()->getUsername()));
            
            $template->delete();
            
            SessionAlert::success("Template deleted. Any users who were using this template have had automatic welcoming disabled.");
		    Notification::welcomeTemplateDeleted($tid);
        });
        
        header("Location: $baseurl/acc.php?action=templatemgmt");
	    die();			
	}
    
	if (isset($_GET['edit'])) 
    {
		if(!User::getCurrent()->isAdmin() || !User::getCurrent()->isCheckuser()) 
        {
            BootstrapSkin::displayAlertBox("I'm sorry, but you do not have permission to access this page.", "alert-error", "Access Denied", true, false);
			BootstrapSkin::displayInternalFooter();
			die();
		}

        $database = gGetDb();
        
        $template = WelcomeTemplate::getById($_GET['edit'], $database);
        if($template == false)
        {
            SessionAlert::success("Something went wrong, we can't find the template you asked for! Please try again.");
            header("Location: {$baseurl}/acc.php?action=templatemgmt");
            die();
        }

		if (isset($_POST['submit'])) 
        {
            $database->transactionally(function() use($database, $template)
            {
                $template->setUserCode($_POST['usercode']);
                $template->setBotCode($_POST['botcode']);
                $template->save();
			
                $statement = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:id, :user, 'EditedTemplate', CURRENT_TIMESTAMP());");
                $statement->execute(array(":id" => $template->getId(), ":user" => User::getCurrent()->getUsername()));
            
                SessionAlert::success("Template updated.");
                Notification::welcomeTemplateEdited($template);
            });
            
            header("Location: $baseurl/acc.php?action=templatemgmt");
            die();
		} 
        else 
        {
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
elseif ($action == "sban") 
{	
    global $smarty;
    
	// Checks whether the current user is an admin.
	if(!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser()) 
    {
        BootstrapSkin::displayAlertBox("Only administrators or checkusers may unban users", "alert-error", "", false, false);
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
    
	if ($duration == "-1")
    {
		$duration = -1;
	}
    elseif ($duration == "other") 
    {
		$duration = strtotime($_POST['otherduration']);
		if (!$duration) 
        {
            BootstrapSkin::displayAlertBox("Invalid ban time", "alert-error", "", false, false);
            BootstrapSkin::displayInternalFooter();
			die();
		} 
        elseif (time() > $duration) 
        {
            BootstrapSkin::displayAlertBox("Invalid ban time - it would have already expired!", "alert-error", "", false, false);
            BootstrapSkin::displayInternalFooter();
			die();
		}
	} 
    else 
    {
		$duration = $duration +time();
	}
    
	switch( $_POST[ 'type' ] ) 
    {
		case 'IP':
			if( filter_var( $_POST[ 'target' ], FILTER_VALIDATE_IP ) === false ) {
                BootstrapSkin::displayAlertBox("Invalid target - I'm expecting an IP address.", "alert-error", "", false, false);
                BootstrapSkin::displayInternalFooter();
				die();
			}
            
			global $squidIpList;
			if( in_array( $_POST[ 'target' ], $squidIpList ) ) {
                BootstrapSkin::displayAlertBox("This IP address is on the protected list of proxies, and cannot be banned.", "alert-error", "", false, false);
                BootstrapSkin::displayInternalFooter();
				die();
			}
			break;
		case 'Name':
			break;
		case 'EMail':
            // TODO: cut this down to a bare-bones implementation so we don't accidentally reject a valid address.
			if( !preg_match( ';^(?:[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[A-Za-z0-9-]*[A-Za-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$;', $_POST['target'] ) ) {
                BootstrapSkin::displayAlertBox("Invalid target - I'm expecting an email address.", "alert-error", "", false, false);
                BootstrapSkin::displayInternalFooter();
				die();
			}
			break;
		default:
            BootstrapSkin::displayAlertBox("I don't know what type of target you want me to ban! You'll need to choose from email address, IP, or requested name.", "alert-error", "", false, false);
            BootstrapSkin::displayInternalFooter();
			die();
	}
        
    if(count(Ban::getActiveBans($_POST['target'])) > 0)
    {
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
        
        $logQuery = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:banid, :siuser, 'Banned', CURRENT_TIMESTAMP(), :reason);";
        $logStatement = $database->prepare($logQuery);
        $banid = $ban->getId();
        $logStatement->bindValue(":banid", $banid);
        $logStatement->bindValue(":siuser", $currentUsername);
        $logStatement->bindValue(":reason", $_POST['banreason']);
        
        if(!$logStatement->execute())
        {
            throw new TransactionException("Error saving log entry.");   
        }
    });
    
    $smarty->assign("ban", $ban);
    BootstrapSkin::displayAlertBox($smarty->fetch("bans/bancomplete.tpl"), "alert-info","", false, false);
        
	Notification::banned($ban);
    
    BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "unban") 
{
    global $smarty;
    
    if(!isset($_GET['id']) || $_GET['id'] == "")
    {
        BootstrapSkin::displayAlertBox("The ID parameter appears to be missing! This is probably a bug.", "alert-error", "Ahoy There! Something's not right...", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if(!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser())
    {
        BootstrapSkin::displayAlertBox("Only administrators or checkusers may unban users", "alert-error", "", false, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $ban = Ban::getActiveId($_GET['id']);
        
    if($ban == false)
    {
        BootstrapSkin::displayAlertBox("The specified ban ID is not currently active or doesn't exist!", "alert-error", "", false, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }

	if( isset($_GET['confirmunban']) && $_GET['confirmunban'] == "true" )
	{
		if (!isset($_POST['unbanreason']) || $_POST['unbanreason'] == "") 
		{
            BootstrapSkin::displayAlertBox("You must enter an unban reason!", "alert-error", "", false, false);
            BootstrapSkin::displayInternalFooter();
            die();
		}
		else 
		{
            $database = gGetDb();
            
            $database->transactionally(function() use ($database, $ban) 
            {
                $ban->setActive(0);
                $ban->save();
                
                $banId = $ban->getId();
                $currentUser = User::getCurrent()->getUsername();
                
                $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:id, :user, 'Unbanned', CURRENT_TIMESTAMP(), :reason);";
                $statement = $database->prepare($query);
                $statement->bindValue(":id", $banId);
                $statement->bindValue(":user", $currentUser);
                $statement->bindValue(":reason", $_POST['unbanreason']);
                
                if(!$statement->execute())
                {
                    throw new TransactionException("Error saving log entry.");   
                }
            });
        
            BootstrapSkin::displayAlertBox("Unbanned " . $ban->getTarget(), "alert-info", "", false, false);
            BootstrapSkin::displayInternalFooter();
            Notification::unbanned($ban, $_POST['unbanreason']);
			die();
		}
	}
	else
	{
        $smarty->assign("ban", $ban);
        $smarty->display("bans/unban.tpl");
        
		BootstrapSkin::displayInternalFooter();
	}
}
elseif ($action == "ban") {    
    global $smarty;
    
	if (isset ($_GET['ip']) || isset ($_GET['email']) || isset ($_GET['name']))
    {
		if(!$session->hasright($_SESSION['user'], "Admin"))
        {
		    BootstrapSkin::displayAlertBox("Only administrators or checkusers may ban users", "alert-error");
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        $database = gGetDb();
        // TODO: rewrite me!
		if (isset($_GET['ip'])) {
			$query = "SELECT pend_ip, pend_proxyip FROM acc_pend WHERE pend_id = :ip;";
            $statement = $database->prepare($query);
            $statement->bindValue(":ip", $_GET['ip']);
            $statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = getTrustedClientIP($row['pend_ip'], $row['pend_proxyip']);
			$type = "IP";
		}
		elseif (isset($_GET['email'])) {
            $query = "SELECT pend_email FROM acc_pend WHERE pend_id = :ip;";
            $statement = $database->prepare($query);
            $statement->bindValue(":ip", $_GET['email']);
            $statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = $row['pend_email'];
			$type = "EMail";
		}
		elseif (isset($_GET['name'])) {
            $query = "SELECT pend_name FROM acc_pend WHERE pend_id = :ip;";
            $statement = $database->prepare($query);
            $statement->bindValue(":ip", $_GET['name']);
            $statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = $row['pend_name'];
			$type = "Name";
		}
        else
        {
            BootstrapSkin::displayAlertBox("Unknown ban type.", "alert-error");
            BootstrapSkin::displayInternalFooter();
			die();    
        }
        
		if (count(Ban::getActiveBans($target))) 
        {
			BootstrapSkin::displayAlertBox("This target is already banned!", "alert-error");
            BootstrapSkin::displayInternalFooter();
			die();
		} 
        
        $smarty->assign("bantype", $type);
        $smarty->assign("bantarget", trim($target));
        $smarty->display("bans/banform.tpl");
	} 
    else 
    {
        $bans = Ban::getActiveBans();
  
        $smarty->assign("activebans", $bans);
        $smarty->display("bans/banlist.tpl");
	}
    
    BootstrapSkin::displayInternalFooter();
    die();
}
elseif ($action == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") 
{
	global $availableRequestStates;
	
	if (array_key_exists($_GET['target'], $availableRequestStates)) 
    {
		$request = Request::getById($_GET['id'], gGetDb());
		
        if($request == false)
        {
            BootstrapSkin::displayAlertBox("Could not find the specified request!", "alert-error", "Error!", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
		
        if( $request->getChecksum() != $_GET['sum'] )
        {
            SessionAlert::error("This is similar to an edit conflict on Wikipedia; it means that you have tried to perform an action on a request that someone else has performed an action on since you loaded the page", "Invalid checksum");
            header("Location: acc.php?action=zoom&id={$request->getId()}");
            die();
        }
        
		$statement = gGetDb()->prepare("SELECT log_time FROM acc_log WHERE log_pend = :request AND log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 1;");
        $statement->execute(array(":request" => $request->getId()));
        $logTime = $statement->fetchColumn();
        $statement->closeCursor();
        
        $date = new DateTime();
		$date->modify("-7 days");
		$oneweek = $date->format("Y-m-d H:i:s");
        
		if ($request->getStatus() == "Closed" && $logTime < $oneweek && ! User::getCurrent()->isAdmin() && ! User::getCurrent()->isCheckuser()) 
        {
			SessionAlert::error("Only administrators and checkusers can reopen a request that has been closed for over a week.");
            header("Location: acc.php?action=zoom&id={$request->getId()}");
			die();
		}
        
		if ($request->getStatus() == $_GET['target']) 
        {
            SessionAlert::error("Cannot set status, target already deferred to " . htmlentities($_GET['target']), "Error");
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
            
            $statement2 = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:request, :user, :deferlog, CURRENT_TIMESTAMP());");
            $statement2->bindValue(":deferlog", "Deferred to $detolog");
            $statement2->bindValue(":request", $request->getId());
            $statement2->bindValue(":user", User::getCurrent()->getUsername());
            
            if(!$statement2->execute()) 
            {
                throw new TransactionException("Error occurred saving log entry");    
            }
        
		    Notification::requestDeferred($request);
            SessionAlert::success("Request {$request->getId()} deferred to $deto");
		    header("Location: acc.php");
        });
        
        die();
	} 
    else 
    {
        BootstrapSkin::displayAlertBox("Defer target not valid.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
	}
}
elseif ($action == "prefs") 
{
    global $smarty;
    
	if (isset ($_POST['sig'])) 
    {
        $user = User::getCurrent();
        $user->setWelcomeSig($_POST['sig']);
        $user->setEmailSig($_POST['emailsig']);
        $user->setAbortPref(isset( $_POST['abortpref'] ) ? 1 : 0);
        
		if( isset( $_POST['email'] ) ) 
        {
			$mailisvalid = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            
			if ($mailisvalid === false) 
            {
                BootstrapSkin::displayAlertBox("Invalid email address", "alert-error", "Error!");
			}
			else 
            {
                $user->setEmail(trim($_POST['email']));
			}
		}

        try 
        {
            $user->save();
        }
        catch(PDOException $ex)
        {
            BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error saving Preferences", true, false);
            BootstrapSkin::displayInternalFooter();
            die();
        }
        
        BootstrapSkin::displayAlertBox("Preferences updated!", "alert-info");
	}
    
	$smarty->display("prefs.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "done" && $_GET['id'] != "") {
	// check for valid close reasons
	global $messages, $baseurl, $smarty;
	
    if(isset($_GET['email'])) 
    {
        if($_GET['email'] == 0 || $_GET['email'] == "custom")
        {
            $validEmail = true;
        }
        else
        {
            $validEmail = EmailTemplate::getById($_GET['email'], gGetDb()) != false;
        }
    }
    else
    {
        $validEmail = false;
    }
    
	if ($validEmail == false) 
    {
        BootstrapSkin::displayAlertBox("Invalid close reason", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// sanitise this input ready for inclusion in queries
    $request = Request::getById($_GET['id'], gGetDb());
    
    if ($request == false) {
        // Notifies the user and stops the script.
        BootstrapSkin::displayAlertBox("The request ID supplied is invalid!", "alert-error","Error",true,false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	$gem = sanitize($_GET['email']);
	
	// check the checksum is valid
	if ($request->getChecksum() != $_GET['sum']) 
    {
        BootstrapSkin::displayAlertBox("This is similar to an edit conflict on Wikipedia; it means that you have tried to perform an action on a request that someone else has performed an action on since you loaded the page.", "alert-error", "Invalid Checksum", true, false);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// check if an email has already been sent
	if ($request->getEmailSent() == "1" && !isset($_GET['override']) && $gem != 0) 
    {
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
	if( $request->getReserved() != 0 && !isset($_GET['reserveoverride']) && $request->getReserved() != User::getCurrent()->getId())
	{
        $alertContent = "<p>This request is currently marked as being handled by " . $request->getReservedObject()->getUsername() . ", Proceed?</p><br />";
        $alertContent .= "<div class=\"row-fluid\">";
        $alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$baseurl/acc.php?".$_SERVER["QUERY_STRING"]."&reserveoverride=yes\">Yes</a>";
        $alertContent .= "<a class=\"btn btn-danger span3\" href=\"$baseurl/acc.php\">No</a>";
        $alertContent .= "</div>";
        
        BootstrapSkin::displayAlertBox($alertContent, "alert-info", "Warning!", true, false, false, true);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	    
	if ($request->getStatus() == "Closed") 
    {
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
        $alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$baseurl/acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;createoverride=yes&amp;email=" . $_GET['email'] . "\">Yes</a>";
        $alertContent .= "<a class=\"btn btn-danger span3\" href=\"$baseurl/acc.php\">No</a>";
        $alertContent .= "</div>";
        
        BootstrapSkin::displayAlertBox($alertContent, "alert-info", "Warning!", true, false, false, true);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// custom close reasons
    if ($gem  == 'custom') 
    {
		if (!isset($_POST['msgbody']) or empty($_POST['msgbody'])) 
        {
            // Send it through htmlspecialchars so HTML validators don't complain. 
			$querystring = htmlspecialchars($_SERVER["QUERY_STRING"],ENT_COMPAT,'UTF-8'); 
            
            $template = false;
            if(isset($_GET['preload']))
            {
                $template = EmailTemplate::getById($_GET['preload'], gGetDb());
            }
            
            if($template != false)
            {
                $preloadTitle = $template->getName();
                $preloadText = $template->getText();
                $forcreated = $template->getOncreated();
            }
            else
            {
                $preloadText = "";
                $preloadTitle = "";
                $forcreated = false;
            }
            
            $smarty->assign("preloadtext", $preloadText);
            $smarty->assign("preloadtitle", $preloadTitle);
            $smarty->assign("forcreated", $forcreated);
            $smarty->assign("querystring", $querystring);
            $smarty->assign("request", $request);
            $smarty->assign("iplocation", $locationProvider->getIpLocation($request->getTrustedIp()));
            $smarty->display("custom-close.tpl");
			BootstrapSkin::displayInternalFooter();
			die();
		} 
        else 
        {			
			$headers = 'From: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
			if (! User::getCurrent()->isAdmin() || isset($_POST['ccmailist']) && $_POST['ccmailist'] == "on")
            {
				$headers .= 'Cc: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
            }
            
			$headers .= 'X-ACC-Request: ' . $request->getId() . "\r\n";
			$headers .= 'X-ACC-UserID: ' . User::getCurrent()->getId() . "\r\n";
			
			// Get the closing user's Email signature and append it to the Email.            
			if(User::getCurrent()->getEmailSig() != "") {
				$emailsig = html_entity_decode(User::getCurrent()->getEmailSig(), ENT_QUOTES, "UTF-8");
				mail($request->getEmail(), "RE: [ACC #{$request->getId()}] English Wikipedia Account Request", $_POST['msgbody'] . "\n\n". $emailsig, $headers);
			}
			else {
				mail($request->getEmail(), "RE: [ACC #{$request->getId()}] English Wikipedia Account Request", $_POST['msgbody'], $headers);
			}
			
            $request->setEmailSent(1);
            
			if (isset($_POST['created']) && $_POST['created'] == "on") {
				$gem  = 'custom-y';
			} else {
				$gem  = 'custom-n';
			}
		}
	}
    
    $request->setStatus('Closed');
    $request->setReserved(0);
    
    // TODO: make this transactional
    $request->save();
    
    $closeaction = "Closed $gem";
    $messagebody = isset($_POST['msgbody']) ? $_POST['msgbody'] : '';
    $statement = gGetDb()->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:request, :user, :closeaction, CURRENT_TIMESTAMP(), :msgbody);");
    $statement->bindValue(':request', $request->getId());
    $statement->bindValue(':user', User::getCurrent()->getUsername());
    $statement->bindValue(':closeaction', $closeaction);
    $statement->bindValue(':msgbody', $messagebody);
    $statement->execute();
    
	if ($gem == '0') {
		$crea = "Dropped";
	} else if ($gem == 'custom') {
		$crea = "Custom";
	} else if ($gem == 'custom-y') {
		$crea = "Custom, Created";
	} else if ($gem == 'custom-n') {
		$crea = "Custom, Not Created";
	} else {
		$template = EmailTemplate::getById($gem, gGetDb());
		$crea = $template->getName();
	}

    Notification::requestClosed($request, $crea);
	
    BootstrapSkin::displayAlertBox("Request " . $request->getId() . " (" . htmlentities($request->getName(),ENT_COMPAT,'UTF-8') . ") marked as 'Done'.", "alert-success");
    
	$towhom = $request->getEmail();
	if ($gem != "0" && $gem != 'custom' && $gem != 'custom-y' && $gem != 'custom-n') {
		sendemail($gem, $towhom, $request->getId());
        $request->setEmailSent(1);
	}
    
    $request->updateChecksum();
    $request->save();
    
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "zoom") 
{
	if (!isset($_GET['id'])) 
    {
        BootstrapSkin::displayAlertBox("No request specified!", "alert-error", "Error!", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
	if (isset($_GET['hash'])) 
    {
		$urlhash = $_GET['hash'];
	}
	else 
    {
		$urlhash = "";
	}
	echo zoomPage($_GET['id'],$urlhash);
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "logs") 
{
    global $baseurl;
    
	if(isset($_GET['user']))
    {
		$filteruser = $_GET['user'];
		$filteruserl = " value=\"" . htmlentities($filteruser, ENT_QUOTES, 'UTF-8') . "\"";
	}
    else
    { 
        $filteruserl = ""; 
        $filteruser = "";
    }
	
	echo '<h2>Logs</h2>
	<form action="'.$baseurl.'/acc.php" method="get">
		<input type="hidden" name="action" value="logs" />
		<table>
			<tr><td>Filter by username:</td><td><input type="text" name="user"'.$filteruserl.' /></td></tr>
			<tr><td>Filter by log action:</td>
				<td>
					<select id="logActionSelect" name="logaction">';
	$logActions = array(
			//  log entry type => display name
				"" => "(All)",
				"Reserved" => "Request reservation",
				"Unreserved" => "Request unreservation",
				"BreakReserve" => "Break reservation",
				"Deferred%" => "Deferred request",
				"Email Confirmed" => "Email confirmed reservation",
				"Banned" => "Ban",
				"Unbanned" => "Unban",
				"Edited" => "Message editing",
				"CreatedTemplate" => "Template creation",
				"EditedTemplate" => "Template editing",
				"DeletedTemplate" => "Template deletion",
				"Declined" => "User declination",
				"Suspended" => "User suspension",
				"Demoted" => "User demotion",
				"Renamed" => "User rename",
				"Approved" => "User approval",
				"Promoted" => "User promotion",
				"Prefchange" => "User preferences change",
                		"SendReserved" => "Reservation sending",
        		        "ReceiveReserved" => "Reservation recieving",
				"Closed 0" => "Request drop",
				"Closed custom" => "Request custom close",
				"Closed custom-y" => "Request custom close, created",
				"Closed custom-n" => "Request custom close, not created"
	);
	// Add entries for every Email template, including inactive ones.
	$query = "SELECT id, name FROM emailtemplate ORDER BY id";
	$statement = gGetDb()->prepare($query);
	$statement->execute();
	while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
		$logAction = "Closed " . $row['id'];
		$logActions[$logAction] = "Request " . $row['name'];
	}
	
	foreach($logActions as $key => $value)
	{
		echo "<option value=\"".$key."\"";
		if(isset($_GET['logaction'])) { if($key == $_GET['logaction']) echo " selected=\"selected\""; }
		echo ">".$value."</option>";
		
	}
	echo '			</select>
				</td>
			</tr>
		</table>
	<input type="submit" /></form>';
	
	
	$logPage = new LogPage();

	if( isset($_GET['user']) ){
		$logPage->filterUser = sanitize($_GET['user']);
	}
	if (isset ($_GET['limit'])) {
		$limit = sanitize($_GET['limit']);
	}
	if (isset ($_GET['from'])) {
		$offset = $_GET['from'];	
	}
	
	if(isset($_GET['logaction']))
	{
		$logPage->filterAction=sanitize($_GET['logaction']);
	}

	echo $logPage->showListLog(isset($offset) ? $offset : 0 ,isset($limit) ? $limit : 100);
    
	BootstrapSkin::displayInternalFooter();
    
	die();
}
elseif ($action == "reserve") 
{    
    $database = gGetDb();
    
    $database->transactionally(function() use ($database)
    {
        $request = Request::getById($_GET['resid'], $database);
        
        if($request == false)
        {
            throw new TransactionException("Request not found", "Error");
        }
        
        global $enableEmailConfirm, $allowDoubleReserving, $baseurl;
	    if($enableEmailConfirm == 1)
        {
            if($request->getEmailConfirm() != "Confirmed")
		    {
                throw new TransactionException("Email address not yet confirmed for this request.", "Error");
		    }
	    }

        $logQuery = $database->prepare("SELECT log_time FROM acc_log WHERE log_pend = :request AND log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 1;");
        $logQuery->bindValue(":request", $request->getId());
        $logQuery->execute();
        $logTime = $logQuery->fetchColumn();
        $logQuery->closeCursor();
        
        $date = new DateTime();
        $date->modify("-7 days");
	    $oneweek = $date->format("Y-m-d H:i:s");
        
	    if ($request->getStatus() == "Closed" && $logTime < $oneweek && !User::getCurrent($database)->isAdmin()) 
        {
            throw new TransactionException("Only administrators and checkusers can reserve a request that has been closed for over a week.", "Error");
	    }
        
       	if($request->getReserved() != 0 && $request->getReserved() != User::getCurrent($database)->getId())
        {
            throw new TransactionException("Request is already reserved by {$request->getReservedObject()->getUsername()}.", "Error");
        }
           
        if($request->getReserved() == 0)
        {
            if(isset($allowDoubleReserving))
            {
		        // Check the number of requests a user has reserved already
        
		        $doubleReserveCountQuery = $database->prepare("SELECT COUNT(*) FROM request WHERE reserved = :userid;");
                $doubleReserveCountQuery->bindValue(":userid", User::getCurrent($database)->getId());
                $doubleReserveCountQuery->execute();
		        $doubleReserveCount = $doubleReserveCountQuery->fetchColumn();
                $doubleReserveCountQuery->closeCursor();
		
		        // User already has at least one reserved. 
		        if( $doubleReserveCount != 0) 
		        {
                    SessionAlert::warning("You have multiple requests reserved!");
		        }
	        }
	
	        // Is the request closed?
	        if(! isset($_GET['confclosed']) )
	        {
		        if($request->getStatus() == "Closed")
		        {		
                    // FIXME: bootstrappify properly
			        throw new TransactionException('This request is currently closed. Are you sure you wish to reserve it?<br /><ul><li><a href="'.$_SERVER["REQUEST_URI"].'&confclosed=yes">Yes, reserve this closed request</a></li><li><a href="' . $baseurl . '/acc.php">No, return to main request interface</a></li></ul>', "Request closed", "alert-info");
		        }
	        }	
        
            $request->setReserved(User::getCurrent($database)->getId());
            $request->save();
	
            $query = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:request, :user, 'Reserved', CURRENT_TIMESTAMP);");
            $query->bindValue(":user", User::getCurrent($database)->getUsername());
            $query->bindValue(":request", $request->getId());
            $query->execute();
    
            Notification::requestReserved($request);
                
            SessionAlert::success("Reserved request {$request->getId()}.");
        }
        
        header("Location: $baseurl/acc.php?action=zoom&id={$request->getId()}");
    });
	    
	die();	
}
elseif ($action == "breakreserve") 
{
    global $smarty;
    
    $database = gGetDb();
    
    $request = Request::getById($_GET['resid'], $database);
        
    if($request == false)
    {
        BootstrapSkin::displayAlertBox("Could not find request.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if($request->getReserved() == 0)
    {
        BootstrapSkin::displayAlertBox("Request is not reserved.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $reservedUser = $request->getReservedObject();
    
    if($reservedUser == false)
    {
        BootstrapSkin::displayAlertBox("Could not find user who reserved the request (!!).", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	if( $reservedUser->getId() != User::getCurrent()->getId() )
	{
		global $enableAdminBreakReserve;
		if($enableAdminBreakReserve && User::getCurrent()->isAdmin()) 
        {
			if(isset($_GET['confirm']) && $_GET['confirm'] == 1)	
			{
                $database->transactionally(function() use($database, $request)
                {
                    $request->setReserved(0);
                    $request->save();

				    $query = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:request, :username, 'BreakReserve', CURRENT_TIMESTAMP());");
                    $query->bindValue(":request", $request->getId());
                    $query->bindValue(":username", User::getCurrent()->getUsername());
                    
                    if(!$query->execute())
                    {
                        throw new TransactionException("Error creating log entry.");
                    }
                
                    Notification::requestReserveBroken($request);
                    header("Location: acc.php");
                });
                
                die();
			}
			else
			{
				global $baseurl;
                $smarty->assign("reservedUser", $reservedUser);
                $smarty->assign("request", $request);
                
                $smarty->display("confirmations/breakreserve.tpl");
			}
		}
		else 
        {
			echo "You cannot break " . $reservedUser->getUsername() . "'s reservation";
		}
	}
	else
	{
        $database->transactionally(function() use ($database, $request)
        {
            $request->setReserved(0);
            $request->save();

            $query = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:request, :username, 'Unreserved', CURRENT_TIMESTAMP());");
            $query->bindValue(":request", $request->getId());
            $query->bindValue(":username", User::getCurrent()->getUsername());
            
            if(!$query->execute()) 
            {
                throw new TransactionException("Error creating log entry");
            }
        
            Notification::requestUnreserved($request);
		    header("Location: acc.php");
        });
        
        die();
	}
    
	BootstrapSkin::displayInternalFooter();
	die();		
}

elseif ($action == "comment") 
{
    global $smarty;
    
    $request = Request::getById($_GET['id'], gGetDb());
    $smarty->assign("request", $request);
    $smarty->display("commentform.tpl");
    BootstrapSkin::displayInternalFooter();
    die();
}

elseif ($action == "comment-add") 
{
    global $baseurl;
    
    $request = Request::getById($_POST['id'], gGetDb());
    if($request == false)
    {
        BootstrapSkin::displayAlertBox("Could not find request!", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if(!isset($_POST['comment']) || $_POST['comment'] == "")
    {
        BootstrapSkin::displayAlertBox("Comment must be supplied!", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die(); 
    }
    
    $visibility = 'user';
    if( isset($_POST['visibility']) )
    {
        // sanity check
        $visibility = $_POST['visibility'] == 'user' ? 'user' : 'admin';
    }
    
    $comment = new Comment();
    $comment->setDatabase(gGetDb());
    
    $comment->setRequest($request->getId());
    $comment->setVisibility($visibility);
    $comment->setUser(User::getCurrent()->getId());
    $comment->setComment($_POST['comment']);
    
    $comment->save();
    
    if (isset($_GET['hash'])) 
    {
        $urlhash = urlencode(htmlentities($_GET['hash']));
    }
    else 
    {
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

elseif ($action == "comment-quick") 
{
    $request = Request::getById($_POST['id'], gGetDb());
    if($request == false)
    {
        BootstrapSkin::displayAlertBox("Could not find request!", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if(!isset($_POST['comment']) || $_POST['comment'] == "")
    {
		header("Location: acc.php?action=zoom&id=" . $request->getId());
        die(); 
    }
    
    $visibility = 'user';
    if( isset($_POST['visibility']) )
    {
        // sanity check
        $visibility = $_POST['visibility'] == 'user' ? 'user' : 'admin';
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

elseif ($action == "changepassword")
{	
	if ((!isset($_POST['oldpassword'])) || $_POST['oldpassword'] == "" ) 
    { 
        //Throw an error if old password is not specified.
        BootstrapSkin::displayAlertBox("You did not enter your old password.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
	
	if ((!isset($_POST['newpassword'])) || $_POST['newpassword'] == "" ) 
    { 
        //Throw an error if new password is not specified.
        BootstrapSkin::displayAlertBox("You did not enter your new password.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
	}
	
	if ($_POST['newpassword'] != $_POST['newpasswordconfirm']) 
    { 
        //Throw an error if new password does not match what is in the confirmation box.
        BootstrapSkin::displayAlertBox("The 2 new passwords you entered do not match.", "alert-error", "Error", true, false);
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
    $user = User::getCurrent();
	   
    if ( ! $user->authenticate($_POST['oldpassword']) ) 
    { 
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
elseif ($action == "ec")
{ 
    // edit comment
  
    global $smarty, $baseurl;
    
    $comment = Comment::getById($_GET['id'], gGetDb());
    
    if($comment == false) 
    {
        // Only using die("Message"); for errors looks ugly.
        BootstrapSkin::displayAlertBox("Comment not found.", "alert-error", "Error", true, false);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// Unauthorized if user is not an admin or the user who made the comment being edited.
	if(!User::getCurrent()->isAdmin() && !User::getCurrent()->isCheckuser() && $comment->getUser() != User::getCurrent()->getId())
    { 
        BootstrapSkin::displayAlertBox("Access denied.", "alert-error", "", false, false);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	
	// get[id] is safe by this point.
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $database = gGetDb();
        $database->transactionally(function() use ($database, $comment, $baseurl)
        {
            
            $comment->setComment($_POST['newcomment']);
            $comment->setVisibility($_POST['visibility']);
        
            $comment->save();
        
            $logQuery = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ( :id, :user, :action, CURRENT_TIMESTAMP() );";
        
            $logStatement = $database->prepare($logQuery);
        
            $logStatement->bindValue(":user", User::getCurrent()->getUsername());
            $logStatement->bindValue(":id", $comment->getId());
            $logStatement->bindValue(":action", "EditComment-c");
            $logStatement->execute();
        
            $logStatement->bindValue(":id", $comment->getRequest());
            $logStatement->bindValue(":action", "EditComment-r");
            $logStatement->execute();
        
            Notification::commentEdited($comment);
        
            SessionAlert::success("Comment has been saved successfully");
		    header("Location: $baseurl/acc.php?action=zoom&id=" . $comment->getRequest());
        });
        
        die();    
	}
	else 
    {	
        $smarty->assign("comment", $comment);
        $smarty->display("edit-comment.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
}
elseif ($action == "sendtouser") 
{ 
    global $baseurl;
    
    $database = gGetDb();
    
    $requestObject = Request::getById($_POST['id'], $database);
    if($requestObject == false)
    {
        BootstrapSkin::displayAlertBox("Request invalid", "alert-error", "Could not find request", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $request = $requestObject->getId();
    
    $user = User::getByUsername($_POST['user'], $database);
    $curuser = User::getCurrent()->getUsername();
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("We couldn't find the user you wanted to send the reservation to. Please check that this user exists and is an active user on the tool.", "alert-error", "Could not find user", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $database->transactionally(function() use ($database, $user, $request, $curuser)
    {
        $updateStatement = $database->prepare("UPDATE acc_pend SET pend_reserved = :userid WHERE pend_id = :request LIMIT 1;");
        $updateStatement->bindValue(":userid", $user->getId());
        $updateStatement->bindValue(":request", $request);
        if(!$updateStatement->execute())
        {
            throw new TransactionException("Error updating reserved status of request.");   
        }
            
        $logStatement = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:request, :user, :action, CURRENT_TIMESTAMP(), '');");
        
        $logStatement->bindValue(":user", $curuser);
        $logStatement->bindValue(":request", $request);
        $logStatement->bindValue(":action", "SendReserved");
        if(!$logStatement->execute())
        {
            throw new TransactionException("Error inserting send log entry.");   
        }
            
        $logStatement->bindValue(":user", $user->getUsername());
        $logStatement->bindValue(":action", "ReceiveReserved");
        if(!$logStatement->execute())
        {
            throw new TransactionException("Error inserting receive log entry.");   
        }
    });
    
    SessionAlert::success("Reservation sent successfully");
    header("Location: $baseurl/acc.php?action=zoom&id=$request");
}
elseif ($action == "emailmgmt") 
{
    global $smarty, $createdid;
    
	/* New page for managing Emails, since I would rather not be handling editing
	interface messages (such as the Sitenotice) and the new Emails in the same place. */
	if(isset($_GET['create'])) {
		if(!User::getCurrent()->isAdmin()) {
			BootstrapSkin::displayAlertBox("I'm sorry, but you must be an administrator to access this page.");
			BootstrapSkin::displayInternalFooter();
			die();
		}
		if(isset($_POST['submit'])) 
        {
            $database = gGetDb();
            $database->transactionally(function() use ($database)
            {
                global $baseurl;
                
                $emailTemplate = new EmailTemplate();
                $emailTemplate->setDatabase($database);
            
        	    $emailTemplate->setName($_POST['name']);
			    $emailTemplate->setText($_POST['text']);
			    $emailTemplate->setJsquestion($_POST['jsquestion']);
			    $emailTemplate->setOncreated(isset($_POST['oncreated']));
			    $emailTemplate->setActive(isset($_POST['active']));
			    
			    // Check if the entered name already exists (since these names are going to be used as the labels for buttons on the zoom page).
                // getByName(...) returns false on no records found.
                if (EmailTemplate::getByName($_POST['name'], $database)) 
                {
				    throw new TransactionException("That Email template name is already being used. Please choose another.");
			    }
			
			    $emailTemplate->save();
                
			    $query = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:id, :user, 'CreatedEmail', CURRENT_TIMESTAMP());");
                if(!$query->execute(array(":id" => $emailTemplate->getId(), ":user" => User::getCurrent()->getUsername())))
                {
                    throw new TransactionException("Error creating log entry.");    
                }
                
                Notification::emailCreated($emailTemplate);
                
                SessionAlert::success("Email template has been saved successfully.");
                header("Location: $baseurl/acc.php?action=emailmgmt");
            });
            
            die();    
		}
        
		$smarty->assign('id', null);
		$smarty->assign('createdid', $createdid);
        $smarty->assign('emailTemplate', new EmailTemplate());
        $smarty->assign('emailmgmtpage', 'Create'); //Use a variable so we don't need two Smarty templates for creating and editing.
		$smarty->display("email-management/edit.tpl");
		BootstrapSkin::displayInternalFooter();
		die();
	}
	if(isset($_GET['edit'])) {
		global $createdid;
        
        $database = gGetDb();
        
		if(isset($_POST['submit'])) 
        {
			$emailTemplate = EmailTemplate::getById($_GET['edit'], $database);
			// Allow the user to see the edit form (with read only fields) but not POST anything.
			if(!User::getCurrent()->isAdmin()) {
				BootstrapSkin::displayAlertBox("I'm sorry, but you must be an administrator to access this page.");
				BootstrapSkin::displayInternalFooter();
				die();
			}
            
			$emailTemplate->setName($_POST['name']);
			$emailTemplate->setText($_POST['text']);
			$emailTemplate->setJsquestion($_POST['jsquestion']);
			
			if ($_GET['edit'] == $createdid) { // Both checkboxes on the main created message should always be enabled.
				$emailTemplate->setOncreated(1);
				$emailTemplate->setActive(1);
                $emailTemplate->setPreloadOnly(0);
			}
			else {
				$emailTemplate->setOncreated(isset($_POST['oncreated']));
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
                
                $logQuery = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:id, :username, 'EditedEmail', CURRENT_TIMESTAMP())");
                $logQuery->execute(array(":id" => $_GET['edit'], ":username" => User::getCurrent()->getUsername()));
            
			
			    if (! $logQuery->execute(array(":id" => $_GET['edit'], ":username" => User::getCurrent()->getUsername())))
			    {
                    throw new TransactionException("Error saving log entry");
                }
            
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
 
	if (count($inactiverows) > 0) 
    {
		$smarty->assign('displayinactive', true);
	}
	else 
    {
		$smarty->assign('displayinactive', false);
	}
    
	$smarty->display("email-management/main.tpl");
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "oauthdetach")
{ 
    global $baseurl;
    
    $currentUser = User::getCurrent();
    $currentUser->setOAuthAccessSecret(null);
    $currentUser->setOAuthAccessToken(null);
    $currentUser->setOAuthRequestSecret(null);
    $currentUser->setOAuthRequestToken(null);
        
    $currentUser->save();
    
    header("Location: {$baseurl}/acc.php?action=logout");
}
elseif ($action == "oauthattach")
{
    $database = gGetDb();
    $database->transactionally(function() use ($database)
    {
        try
        {
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
        catch(Exception $ex)
        {
            throw new TransactionException($ex->getMessage(), "Connection to Wikipedia failed.", "alert-error", 0, $ex);
        }
    });
}
# If the action specified does not exist, goto the default page.
else {
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
	die();
}
