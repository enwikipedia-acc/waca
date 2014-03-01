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
require_once 'devlist.php';
require_once 'LogClass.php';
require_once 'functions.php';
require_once 'includes/PdoDatabase.php';
require_once 'includes/SmartyInit.php'; // this needs to be high up, but below config, functions, and database
require_once 'includes/database.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';
require_once 'includes/http.php';

// Set the current version of the ACC.
$version = "0.9.7";

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
Offline::check(false);

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

// Initialize the class objects.
$skin     = new skin();
$accbotSend = new accbotSend();
$session = new session();
$date = new DateTime();

$locationProvider = new $locationProviderClass(gGetDb('acc'), $locationProviderApiKey);
$rdnsProvider = new $rdnsProviderClass(gGetDb('acc'));


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
}

// Checks whether the user and nocheck variable is set.
// When none of these are set, the user should first login.
if (!isset($_SESSION['user']) && !isset($_GET['nocheck'])) {
//if (!isset($_SESSION['user']) && !($action=='login' && isset($_POST['username']))) {
	// Sets the parameter to blank, this way the correct options would be displayed.
	// It would tell the user now that he or she should log in or create an account.
	$suser = '';
	$skin->displayIheader($suser);

	// Checks whether the user want to reset his password or register a new account.
	// Performs the clause when the action is not one of the above options.
	if ($action != 'register' && $action != 'forgotpw' && $action != 'sreg') {
		if (isset($action)) {
			// Display the login form and the rest of the page coding.
			// The data in the current $GET varianle would be send as parameter.
			// There it would be used to possibly fill some of the form's fields.
			echo showlogin($action, $_GET);
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
        $session->checksecurity($_SESSION['user']);
}

// When no action is specified the default Internal ACC are displayed.
// TODO: Improve way the method is called.
if ($action == '') {
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
	die();
}

elseif ($action == "sreg") {
	if(isset($_SESSION['user']))
	{
		$suser = sanitize($_SESSION['user']);
	}
	else
	{
		$suser = '';
	}

    $sregHttpClient = new http();
	$cu_name = rawurlencode( $_REQUEST['wname'] );
	$userblocked = $sregHttpClient->get( "http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$cu_name&format=php" );
	$ub = unserialize( $userblocked );
	if ( isset ( $ub['query']['blocks']['0']['id'] ) ) {
		$message = InterfaceMessage::get(InterfaceMessage::DECL_BLOCKED);
		BootstrapSkin::displayAlertBox("You are presently blocked on the English Wikipedia", "alert-error", "Error");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	$userexist = $sregHttpClient->get( "http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$cu_name&format=php" );
	$ue = unserialize( $userexist );
	foreach ( $ue['query']['users'] as $oneue ) {
		if ( isset($oneue['missing'])) {
			BootstrapSkin::displayAlertBox("Invalid on-wiki username", "alert-error", "Error");
			echo "</div>";
			$skin->displayPfooter();
			die();
		}
	}

	// check if the user is to new
	global $onRegistrationNewbieCheck;
	if( $onRegistrationNewbieCheck ) 
	{
		global $onRegistrationNewbieCheckEditCount, $onRegistrationNewbieCheckAge;
		$isNewbie = unserialize($sregHttpClient->get( "http://en.wikipedia.org/w/api.php?action=query&list=allusers&format=php&auprop=editcount|registration&aulimit=1&aufrom=$cu_name" ));
		$time = $isNewbie['query']['allusers'][0]['registration'];
		$time2 = time() - strtotime($time);
		$editcount = $isNewbie['query']['allusers'][0]['editcount'];
		if (!($editcount > $onRegistrationNewbieCheckEditCount and $time2 > $onRegistrationNewbieCheckAge)) {
            BootstrapSkin::displayAlertBox("You are too new to request an account at the moment.", "alert-info", "Sorry!", false);
			echo "</div>";
			$skin->displayPfooter();
			die();
		}
	}
	// check if user checked the "I have read and understand the interface guidelines" checkbox
	if(!isset($_REQUEST['guidelines'])) {
        BootstrapSkin::displayAlertBox("You must read <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\">the interface guidelines</a> before your request may be submitted.", "alert-info", "Sorry!", false);
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	
	$user = sanitize($_REQUEST['name']);
	$wname = mysql_real_escape_string($_REQUEST['wname']);
	$pass = mysql_real_escape_string($_REQUEST['pass']);
	$pass2 = mysql_real_escape_string($_REQUEST['pass2']);
	$email = mysql_real_escape_string($_REQUEST['email']);
	$sig = mysql_real_escape_string($_REQUEST['sig']);
	$conf_revid=mysql_real_escape_string($_REQUEST['conf_revid']);
    
	if(isset($_REQUEST['welcomeenable']))
	{
		$welcomeenable = mysql_real_escape_string($_REQUEST['welcomeenable']);	
	}
	else
	{
		$welcomeenable=false;
	}
	if ( !isset($user) || !isset($wname) || !isset($pass) || !isset($pass2) || !isset($email) || !isset($conf_revid)|| strlen($email) < 6) {
        BootstrapSkin::displayAlertBox("Form data may not be blank.", "alert-error", "Error!", false);
		
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	if (isset($_POST['debug']) && $_POST['debug'] == "on") {
		echo "<pre>\n";
		print_r($_REQUEST);
		echo "</pre>\n";
	}
	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$/i', $_REQUEST['email']);
	if ($mailisvalid == 0) {
		BootstrapSkin::displayAlertBox("Invalid email address", "alert-error", "Error!", false);
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	if ($_REQUEST['pass'] !== $_REQUEST['pass2']) { // comparing pre-filtered values here, secure as it's just a comparison.
        BootstrapSkin::displayAlertBox("Your passwords did not match, please try again.", "alert-error", "Error!", false);
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	if(!((string)(int)$conf_revid === (string)$conf_revid)||$conf_revid==""){
		BootstrapSkin::displayAlertBox("Please enter the revision id of your confirmation edit in the \"Confirmation diff\" field. The revid is the number after the &diff= part of the URL of a diff.", "alert-error", "Error!", false);
		echo "</div>";
		$skin->displayPfooter();
		die();		
	
	}
	$query = "SELECT * FROM acc_user WHERE user_name = '$user' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
        BootstrapSkin::displayAlertBox("Sorry, but that username is in use. Please choose another.", "alert-error", "Error!", false);
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_user WHERE user_email = '$email' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		$skin->displayRequestMsg( "I'm sorry, but that e-mail address is in use.<br />");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_user WHERE user_onwikiname = '$wname' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		$skin->displayRequestMsg("I'm sorry, but $wname already has an account here.<br />");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_pend WHERE pend_name = '$user' AND (pend_status = 'Open' OR pend_status = 'Admin' OR pend_status = 'Closed') AND DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= pend_date LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	if (!empty($row['pend_name'])) {
		$skin->displayRequestMsg("I'm sorry, you are too new to request an account at the moment.<br />");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	if (!isset($fail) || $fail != 1) {
		$user_pass = authutils::encryptPassword($_REQUEST['pass']); // again, using unfiltered as data processing is done here.
		$query = "INSERT INTO acc_user (user_name, user_email, user_pass, user_level, user_onwikiname, user_confirmationdiff) VALUES ('$user', '$email', '$user_pass', 'New', '$wname', '$conf_revid');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("New user: $user");
		$skin->displayRequestMsg("Account requested! Your username is $user! Your request will be reviewed soon.</b><br /><br />");
	}
	echo "</div>";
	$skin->displayPfooter();
	die();
}

elseif ($action == "register") {
	global $smarty;
    $smarty->display("register.tpl");
	$skin->displayPfooter();
	die();
}
elseif ($action == "forgotpw") {

	if (isset ($_GET['si']) && isset ($_GET['id'])) {
		if (isset ($_POST['pw']) && isset ($_POST['pw2'])) {
			$puser = sanitize($_GET['id']);
			$query = "SELECT * FROM acc_user WHERE user_id = '$puser';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_templateid'] . $row['user_id'] . $row['user_pass'];
			$hash = md5($hashme);
			if ($hash == $_GET['si']) {
				if ($_POST['pw'] == $_POST['pw2']) {
					$pw = authutils::encryptPassword($_POST['pw2']);
					$query = "UPDATE acc_user SET user_pass = '$pw' WHERE user_id = '$puser';";
					$result = mysql_query($query, $tsSQLlink);
					if (!$result) {
						sqlerror("Query failed: $query ERROR: " . mysql_error());
					}
					echo "Password reset!\n<br />\nYou may now <a href=\"$tsurl/acc.php\">Login</a>";
					} else {
						echo "<h2>ERROR</h2>Passwords did not match!<br />\n";
					}
			} else {
				echo "<h2>ERROR</h2>\nInvalid request.1<br />";
			}
			$skin->displayPfooter();
			die();
		}
		$puser = sanitize($_GET['id']);
		$query = "SELECT * FROM acc_user WHERE user_id = '$puser';";
		$result = mysql_query($query, $tsSQLlink );
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_templateid'] . $row['user_id'] . $row['user_pass'];
		$hash = md5($hashme);
		if ($hash == $_GET['si']) {
			echo '<h2>Reset password for '. htmlentities($row['user_name']).' ('.htmlentities($row['user_email']).')</h2><form action="'.$tsurl.'/acc.php?action=forgotpw&amp;si='.htmlentities($_GET['si']).'&amp;id='. htmlentities($_GET['id']).'" method="post">';
			echo <<<HTML
			New Password: <input type="password" name="pw"><br />
            New Password (confirm): <input type="password" name="pw2"><br />
            <input type="submit"><input type="reset">
            </form><br />
HTML;
            echo "Return to <a href=\"$tsurl/acc.php\">Login</a>";
		} else {
			echo "<h2>ERROR</h2>\nInvalid request. The HASH supplied in the link did not match the HASH in the database!<br />";
		}
		$skin->displayPfooter();
		die();
	}
	if (isset ($_POST['username'])) {
		$puser = mysql_real_escape_string($_POST['username']);
		$query = "SELECT * FROM acc_user WHERE user_name = '$puser';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if (!isset($row['user_id'])) {
			echo "<h2>ERROR</h2>Missing or incorrect Username supplied.\n";
		}
		elseif (strtolower($_POST['email']) != strtolower($row['user_email'])) {
			echo "<h2>ERROR</h2>Missing or incorrect Email address supplied.\n";
		}
		else{
		$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_templateid'] . $row['user_id'] . $row['user_pass'];
		$hash = md5($hashme);
		// re bug 29: please don't escape the url parameters here: it's a plain text email so no need to escape, or you break the link
		$mailtxt = "Hello! You, or a user from " . $_SERVER['REMOTE_ADDR'] . ", has requested a password reset for your account.\n\nPlease go to $tsurl/acc.php?action=forgotpw&si=$hash&id=" . $row['user_id'] . " to complete this request.\n\nIf you did not request this reset, please disregard this message.\n\n";
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($row['user_email'], "English Wikipedia Account Request System - Forgotten password", $mailtxt, $headers);
		echo "Your password reset request has been completed. Please check your e-mail.\n<br />";
		}
	}
$smarty->display('forgotpw.tpl');

	$skin->displayPfooter();
	die();
}
elseif ($action == "login") {
	$puser = sanitize($_POST['username']);
	$ip = sanitize($_SERVER['REMOTE_ADDR']);
	$password = $_POST['password'];
	$newaction = $_GET['newaction'];
	$internalInterface -> login($puser, $ip, $password, $newaction);
}
elseif ($action == "messagemgmt") 
{
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
        
        $message = InterfaceMessage::getById($_GET['edit'], gGetDb());
        
	    if ($message == false)
        {
            BootstrapSkin::displayAlertBox("Unable to find specified message", "alert-error", "Error", true, false);
            BootstrapSkin::displayInternalFooter();
		    die();
        }
        
		if ( isset( $_GET['submit'] ) ) 
        {
            $message->setContent($_POST['mailtext']);
            $message->setDescription($_POST['maildesc']);
            $message->save();
            
            $logStatement = gGetDb()->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES (:message, :user, 'Edited', CURRENT_TIMESTAMP());");
            $logStatement->bindParam(":message", $message->getId());
            $logStatement->bindParam(":user", User::getCurrent()->getUsername());
            $logStatement->execute();
            
			$mailname = $message->getDescription();
            
            BootstrapSkin::displayAlertBox("Message $mailname ({$message->getId()}) updated.", "alert-success", "Saved!", true, false);
			$accbotSend->send("Message $mailname ({$message->getId()}) edited by " . User::getCurrent()->getUsername());
			BootstrapSkin::displayInternalFooter();
			die();
		}
        
        $smarty->assign("message", $message);
        $smarty->assign("readonly", false);
        $smarty->display("message-management/editform.tpl");
        
		BootstrapSkin::displayInternalFooter();
		die();
	}
    
    $fetchStatement = gGetDb()->prepare("SELECT * FROM interfacemessage WHERE type = :type");
    $data = array();
    
    $fetchStatement->execute(array(":type" => "Message"));
    $data['Email messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
    $fetchStatement->execute(array(":type" => "Interface"));
    $data['Public Interface messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
    $fetchStatement->execute(array(":type" => "Internal"));
    $data['Internal Interface messages'] = $fetchStatement->fetchAll(PDO::FETCH_CLASS, 'InterfaceMessage');
    
    $smarty->assign("data", $data);
    $smarty->display('message-management/view.tpl');
   
	BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "templatemgmt") {
	if (isset($_GET['view'])) {
		if (!preg_match('/^[0-9]*$/',$_GET['view']))
			die('Invaild GET value passed.');
	
		$tid = sanitize($_GET['view']);
		$query = "SELECT * FROM acc_template WHERE template_id = '$tid' LIMIT 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror(mysql_error());
		$row = mysql_fetch_assoc($result);
		echo "<h2>View template</h2><br />Template ID: ".$row['template_id']."<br />\n";
		echo "Display code: ".$row['template_usercode']."<br />\n";
		echo "Bot code: ".str_replace("\n", '\n', $row['template_botcode'])."<br />\n";
		echo displayPreview($row['template_usercode']);
		echo "<br /><a href='$tsurl/acc.php?action=templatemgmt'>Back</a>";
		$skin->displayIfooter();
		die();
	}
	if (isset($_GET['add'])) {
		if(!$session->hasright($_SESSION['user'], 'Admin')) {
			echo "I'm sorry, but you do not have permission to access this page.<br />\n";
			$skin->displayIfooter();
			die();
		}
		if (isset($_POST['submit'])) {
			$usercode = sanitize($_POST['usercode']);
			$usercode = str_replace('\n', "\n", $usercode);
			$botcode = sanitize($_POST['botcode']);
			$botcode = str_replace('\n', "\n", $botcode);
			$siuser = sanitize($_SESSION['user']);
			$query = "INSERT INTO acc_template (template_usercode, template_botcode) VALUES ('$usercode', '$botcode');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror(mysql_error());
			$now = date("Y-m-d H-i-s");
			$tid = mysql_insert_id();
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$tid', '$siuser', 'CreatedTemplate', '$now');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror(mysql_error());
			echo "Template $tid created.";
			$accbotSend->send("New template $tid ($usercode) created by $siuser.");
		} else {
			echo "<h2>Create template</h2><strong>This is NOT a toy. If you can see this form, you can create a new template.</strong><br />The display code will be displayed as it is to users when choosing template.<br />In the bot code, \$username will be replaced with the account creator's username, and \$signature with his signature, including a timestamp.<br />Please remember that these two variables should be used, and not ~~~~ as this will use the bot's signature.\n";
			if (isset($_POST['preview'])) {
				$usercode = $_POST['usercode'];
				$botcode = $_POST['botcode'];
				echo displayPreview($usercode);
			} else {
				$usercode = '';
				$botcode = '';
			}
			echo "<form action=\"$tsurl/acc.php?action=templatemgmt&amp;add=yes\" method=\"post\">\n";
			echo "Display code: <input type=\"text\" name=\"usercode\" value=\"$usercode\" size=\"40\"/><br />\n";
			echo "Bot code: <input type=\"text\" name=\"botcode\" value=\"$botcode\" size=\"40\"/><br />\n";
			echo "<input type=\"submit\" name=\"submit\" value=\"Create!\"/><input type=\"submit\" name=\"preview\" value=\"Preview\"/><br />\n";
			echo "</form>";
			$skin->displayIfooter();
			die();
		}
	}
	if (isset($_GET['del'])) {
		if(!$session->hasright($_SESSION['user'], 'Admin') || $_GET['del'] == '1') {
			echo "I'm sorry, but you do not have permission to access this page.<br />\n";
			$skin->displayIfooter();
			die();
		}
		if (!preg_match('/^[0-9]*$/', $_GET['del']))
			die('Invaild GET value passed.');
		$tid = sanitize($_GET['del']);
		$siuser = sanitize($_SESSION['user']);
		$query = "SELECT user_id, user_name FROM acc_user WHERE user_welcome_templateid = '$tid';";
		$usersaffected = mysql_query($query, $tsSQLlink);
		if (!$usersaffected)
			sqlerror(mysql_error());
		$query = "UPDATE acc_user SET user_welcome_templateid = '1' WHERE user_welcome_templateid = '$tid';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror(mysql_error());
		$query = "DELETE FROM acc_template WHERE template_id = '$tid';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror(mysql_error());
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$tid', '$siuser', 'DeletedTemplate', '$now');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror(mysql_error());
		echo "Template $tid deleted.<br />";
		if (mysql_num_rows($usersaffected)) {
			echo "The following users were using the template, and have been switched to the default one:\n";
			echo "<ul>\n";
			while (list($affected_id, $affected_name) = mysql_fetch_row($usersaffected)) {
				echo "<li><a href=$tsurl/statistics.php?page=Users&user=$affected_id>$affected_name</a></li>\n";
			}
			echo "</ul>\nPlease try inform these users that their template has been changed.";
		} else {
			echo "No users were using the template.";
		}
			
		$accbotSend->send("Template $tid deleted by $siuser.");
	}
	if (isset($_GET['edit'])) {
		if(!$session->hasright($_SESSION['user'], 'Admin') || $_GET['edit'] == '1') {
			echo "I'm sorry, but you do not have permission to access this page.<br />\n";
			$skin->displayIfooter();
			die();
		}
		if (!preg_match('/^[0-9]*$/', $_GET['edit']))
			die('Invaild GET value passed.');
		$tid = sanitize($_GET['edit']);
		if (isset($_POST['submit'])) {
			$usercode = sanitize($_POST['usercode']);
			$usercode = str_replace('\n', "\n", $usercode);
			$botcode = sanitize($_POST['botcode']);
			$botcode = str_replace('\n', "\n", $botcode);
			$siuser = sanitize($_SESSION['user']);
			$query = "UPDATE acc_template SET template_usercode = '$usercode', template_botcode = '$botcode' WHERE template_id = '$tid';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror(mysql_error());
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$tid', '$siuser', 'EditedTemplate', '$now');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror(mysql_error());
			echo "Template $tid ($usercode) updated.<br />\n";
			$accbotSend->send("Template $tid ($usercode) edited by $siuser.");
		} else {
			echo "<h2>Edit template</h2><strong>This is NOT a toy. If you can see this form, you can edit this template.</strong><br />The display code will be displayed as it is to users when choosing template.<br />In the bot code, \$username will be replaced with the account creator's username, and \$signature with his signature, including a timestamp.<br />Please remember that these two variables should be used, and not ~~~~ as this will use the bot's signature.\n";
			if (isset($_POST['preview'])) {
				$usercode = $_POST['usercode'];
				$botcode = $_POST['botcode'];
				echo displayPreview($usercode);
			} else {
				$query = "SELECT * FROM acc_template WHERE template_id = '$tid';";
				$result = mysql_query($query, $tsSQLlink);
				if (!$result)
					sqlerror(mysql_error());
				$row = mysql_fetch_assoc($result);
				$usercode = str_replace("\n", '\n', $row['template_usercode']);
				$botcode = str_replace("\n", '\n', $row['template_botcode']);
			}
			echo "<form action=\"$tsurl/acc.php?action=templatemgmt&amp;edit=$tid\" method=\"post\">\n";
			echo "Display code: <input type=\"text\" name=\"usercode\" size=\"40\" value=\"$usercode\"/><br />\n";
			echo "Bot code: <input type=\"text\" name=\"botcode\" size=\"40\" value=\"$botcode\"/><br />\n";
			echo "<input type=\"submit\" name=\"submit\" value=\"Edit!\"/><input type=\"submit\" name=\"preview\" value=\"Preview\"/><br />\n";
			echo "</form>";
			$skin->displayIfooter();
			die();
		}
	}
	$sid = sanitize( $_SESSION['user'] );
	if (isset($_GET['set'])) {
		$selected = sanitize($_POST['selectedtemplate']);
		$query = "SELECT * FROM acc_template WHERE template_id = $selected;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror(mysql_error());
		if (mysql_num_rows($result) || $selected == '0') {
			$query = "UPDATE acc_user SET user_welcome_templateid = $selected WHERE user_name = '$sid';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror(mysql_error());
			echo "Template choice saved.";
		} else {
			echo "Invalid selection.";
		}
	}
	$query = "SELECT user_welcome_templateid FROM acc_user WHERE user_name = '$sid'";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror(mysql_error());
	$userinfo = mysql_fetch_assoc($result);
	$query = "SELECT template_id, template_usercode FROM acc_template;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror(mysql_error());
	echo "<h2>Welcome templates</h2>\n";
	echo "<form action=\"$tsurl/acc.php?action=templatemgmt&amp;set=yes\" method=\"post\" name=\"templateselection\">";
	echo "<table><tbody>\n";
	$current = 0;
	while ( list($template_id, $usercode) = mysql_fetch_row($result) ) {
		$currentrow = $current;
		$current += 1;
		echo "<tr";
		if ($current % 2 == 0)
			echo ' class="alternate"';
		echo '>';
		echo "<td><input type=\"radio\" name=\"selectedtemplate\" value=\"$template_id\"";
		if ($userinfo['user_welcome_templateid'] == $template_id)
			echo " checked=\"checked\"";
		echo " /></td>";
		echo "<td onclick=\"document.templateselection.selectedtemplate[$currentrow].checked = true;\">&nbsp;<small>$usercode</small>&nbsp;</td>";
		if($session->hasright($_SESSION['user'], 'Admin')) {
			if ($template_id != 1) {
				echo "<td><a href=\"$tsurl/acc.php?action=templatemgmt&amp;edit=$template_id\">Edit!</a>&nbsp;<a href=\"$tsurl/acc.php?action=templatemgmt&amp;del=$template_id\" onclick=\"javascript:return confirm('Are you sure you wish to delete template $template_id?')\">Delete!</a>&nbsp;</td>";
			} else {
				echo "<td></td>";
			}
		}
		echo "<td><a href=\"$tsurl/acc.php?action=templatemgmt&amp;view=$template_id\">View!</a></td></tr>";
	}
	echo "<tr><td><input type=\"radio\" name=\"selectedtemplate\" value=\"0\"";
	if ($userinfo['user_welcome_templateid'] == 0)
		echo " checked=\"checked\"";
	echo " /></td><td onclick=\"document.templateselection.selectedtemplate[$current].checked = true;\">&nbsp;&nbsp;Disable automatic welcoming.</td><td></td>";
	if ($session->hasright($_SESSION['user'], 'Admin'))
		echo "<td></td>";
	echo "</tr>";
	echo "</tbody></table><br />";
	echo "<input type=\"submit\" value=\"Update template choice\" />";
	echo "</form>";
	if ($session->hasright($_SESSION['user'], 'Admin')) {
		echo "<form action=\"$tsurl/acc.php?action=templatemgmt&amp;add=yes\" method=\"post\">";
		echo "<input type=\"submit\" value=\"Add new\" />";
		echo "</form>";
	}
	$skin->displayIfooter();
	die();
}
elseif ($action == "sban") 
{	
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
    
    if(!$database->beginTransaction())
    {
        BootstrapSkin::displayAlertBox("Error initiating database transaction", "alert-error", "", false, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $currentUsername = User::getCurrent()->getUsername();
    $ban = new Ban();
            
    $ban->setDatabase($database);
    $ban->setActive(1);
    $ban->setType($_POST['type']);
    $ban->setTarget($_POST['target']);
    $ban->setUser($currentUsername);
    $ban->setReason($_POST['banreason']);
    $ban->setDuration($duration);
    
    try
    {
        $ban->save();
        
        $logQuery = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:banid, :siuser, 'Banned', CURRENT_TIMESTAMP(), :reason);";
        $logStatement = $database->prepare($logQuery);
        $banid = $ban->getId();
        $logStatement->bindParam(":banid", $banid);
        $logStatement->bindParam(":siuser", $currentUsername);
        $logStatement->bindParam(":reason", $_POST['banreason']);
        if(!$logStatement->execute())
        {
            throw new Exception("Error saving log entry.");   
        }
    }
    catch(Exception $ex)
    {
        $database->rollBack();
        BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error in transaction", false, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    $database->commit();
    
    $smarty->assign("ban", $ban);
    BootstrapSkin::displayAlertBox($smarty->fetch("bans/bancomplete.tpl"), "alert-info","", false, false);
    
	if ( !isset($duration) || $duration == "-1") 
    {
		$until = "Indefinite";
	} 
    else 
    {
		$until = date("F j, Y, g:i a", $duration);
	}
    
	if ($until == 'Indefinite') 
    {
		$accbotSend->send($ban->getTarget() . " banned by $currentUsername for " . $ban->getReason() . " indefinitely");
	} 
    else 
    {
		$accbotSend->send($ban->getTarget() . " banned by $currentUsername for " . $ban->getReason() . " until $until");
	}
    
    BootstrapSkin::displayInternalFooter();
	die();
}
elseif ($action == "unban") 
{
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
        BootstrapSkin::displayAlertBox("The specified ban ID is not currently active!", "alert-error", "", false, false);
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
            
            if(!$database->beginTransaction())
            {
                BootstrapSkin::displayAlertBox("Error initiating database transaction", "alert-error", "", false, false);
                BootstrapSkin::displayInternalFooter();
                die();
            }
            
            try
            {
                $ban->setActive(0);
                $ban->save();
                
                $banId = $ban->getId();
                $currentUser = User::getCurrent()->getUsername();
                
                $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:id, :user, 'Unbanned', CURRENT_TIMESTAMP(), :reason);";
                $statement = $database->prepare($query);
                $statement->bindParam(":id", $banId);
                $statement->bindParam(":user", $currentUser);
                $statement->bindParam(":reason", $_POST['unbanreason']);
                
                if(!$statement->execute())
                {
                    throw new Exception("Error saving log entry.");   
                }
            }
            catch(Exception $ex)
            {
                $database->rollBack();
                BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Error in transaction", false, false);
                BootstrapSkin::displayInternalFooter();
                die();
            }
            
            $database->commit();
		
            BootstrapSkin::displayAlertBox("Unbanned " . $ban->getTarget(), "alert-info", "", false, false);
            BootstrapSkin::displayInternalFooter();
			$accbotSend->send($ban->getTarget() . " unbanned by " . User::getCurrent()->getUsername() . " ({$_POST['unbanreason']}))");
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
            $statement->bindParam(":ip", $_GET['ip']);
            $statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = getTrustedClientIP($row['pend_ip'], $row['pend_proxyip']);
			$type = "IP";
		}
		elseif (isset($_GET['email'])) {
            $query = "SELECT pend_email FROM acc_pend WHERE pend_id = :ip;";
            $statement = $database->prepare($query);
            $statement->bindParam(":ip", $_GET['email']);
            $statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = $row['pend_email'];
			$type = "EMail";
		}
		elseif (isset($_GET['name'])) {
            $query = "SELECT pend_name FROM acc_pend WHERE pend_id = :ip;";
            $statement = $database->prepare($query);
            $statement->bindParam(":ip", $_GET['name']);
            $statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			$target = $row['pend_name'];
			$type = "Name";
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
elseif ($action == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") {
	global $availableRequestStates;
	$target = sanitize($_GET['target']);
	
	if (array_key_exists($target, $availableRequestStates)) {
			
		
			
		$gid = $internalInterface->checkreqid($_GET['id']);
		if (csvalid($gid, $_GET['sum']) != 1) {
			echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
			$skin->displayIfooter();
			die();
		}
		$sid = sanitize($_SESSION['user']);
		$query = "SELECT pend_status FROM acc_pend WHERE pend_id = '$gid';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$query2 = "SELECT log_time FROM acc_log WHERE log_pend = '$gid' AND log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 1;";
		$result2 = mysql_query($query2, $tsSQLlink);
		if (!$result2)
			sqlerror("Query failed: $query2 ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$date->modify("-7 days");
		$oneweek = $date->format("Y-m-d H:i:s");
		if ($row['pend_status'] == "Closed" && $row2['log_time'] < $oneweek && ! ($session->hasright($_SESSION['user'], "Admin"))) {
			$skin->displayRequestMsg("Only administrators and checkusers can reopen a request that has been closed for over a week.");	
			$skin->displayIfooter();
			die();
		}
		if ($row['pend_status'] == $target) {
			echo "Cannot set status, target already deferred to $target<br />\n";
			$skin->displayIfooter();
			die();
		}
		$query = "UPDATE acc_pend SET pend_status = '$target', pend_reserved = '0' WHERE pend_id = '$gid';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());

		$deto = $availableRequestStates[$target]['deferto'];
		$detolog = $availableRequestStates[$target]['defertolog'];


		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Deferred to $detolog', '$now');";
		upcsum($gid);
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("Request $gid deferred to $deto by $sid");
		$skin->displayRequestMsg("Request " . sanitize($_GET['id']) . " deferred to $deto.");
		header("Location: acc.php");
		die();
	} else {
		echo "Defer target not valid.<br />\n";
	}
}
elseif ($action == "prefs") 
{
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
	global $messages, $skin;
	
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
    
	$gid = $internalInterface->checkreqid($_GET['id']);
	$gem = sanitize($_GET['email']);
	
	// check the checksum is valid
	if (csvalid($gid, $_GET['sum']) != 1) 
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
        $alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$tsurl/acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;override=yes&amp;email=" . $_GET['email'] . "\">Yes</a>";
        $alertContent .= "<a class=\"btn btn-danger span3\" href=\"$tsurl/acc.php\">No</a>";
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
        $alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$tsurl/acc.php?".$_SERVER["QUERY_STRING"]."&reserveoverride=yes\">Yes</a>";
        $alertContent .= "<a class=\"btn btn-danger span3\" href=\"$tsurl/acc.php\">No</a>";
        $alertContent .= "</div>";
        
        BootstrapSkin::displayAlertBox($alertContent, "alert-info", "Warning!", true, false, false, true);
        BootstrapSkin::displayInternalFooter();
		die();
	}
	
	$sid = sanitize($_SESSION['user']);
    $gus = sanitize($request->getName());
    
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
        $alertContent .= "<a class=\"btn btn-success offset3 span3\"  href=\"$tsurl/acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;createoverride=yes&amp;email=" . $_GET['email'] . "\">Yes</a>";
        $alertContent .= "<a class=\"btn btn-danger span3\" href=\"$tsurl/acc.php\">No</a>";
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
	
    /*
    // Commented out cos it's causing a load of issues, and theoretically should never work?
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_welcome_templateid'] > 0 && ($gem == "1" || $gem == "custom-y")) {
		$query = "INSERT INTO acc_welcome (welcome_uid, welcome_user, welcome_status) VALUES ('$sid', '$gus', 'Open');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
	}
    */
    
    $request->setStatus('Closed');
    $request->setReserved(0);
    
    $request->save();
    
    $closeaction = "Closed $gem";
    $messagebody = isset($_POST['msgbody']) ? $_POST['msgbody'] : '';
    $statement = gGetDb()->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:request, :user, :closeaction, CURRENT_TIMESTAMP(), :msgbody);");
    $statement->bindParam(':request', $request->getId());
    $statement->bindParam(':user', User::getCurrent()->getUsername());
    $statement->bindParam(':closeaction', $closeaction);
    $statement->bindParam(':msgbody', $messagebody);
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

	$now = new DateTime();
    $now = $now->format("Y-m-d H:i:s");
	$accbotSend->send("Request " . $request->getId() . " (" . $request->getName() . ") Marked as 'Done' ($crea) by " . User::getCurrent()->getUsername() . " on $now");
	$skin->displayRequestMsg("Request " . $request->getId() . " (" . htmlentities($request->getName(),ENT_COMPAT,'UTF-8') . ") marked as 'Done'.<br />");
	$towhom = $request->getEmail();
	if ($gem != "0" && $gem != 'custom' && $gem != 'custom-y' && $gem != 'custom-n') {
		sendemail($gem, $towhom, $gid);
        $request->setEmailSent(1);
	}
    $request->save();
    
	upcsum($_GET['id']);
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
elseif ($action == "logout") {
	echo showlogin();
	die("Logged out!\n");
}
elseif ($action == "logs") {
	if(isset($_GET['user'])){
		$filteruser = $_GET['user'];
		$filteruserl = xss($filteruser); 
		$filteruserl = " value=\"".$filteruserl."\"";
	} else { $filteruserl = ""; $filteruser = "";}
	
	echo '<h2>Logs</h2>
	<form action="'.$tsurl.'/acc.php" method="get">
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
		$logPage->filterUser = sanitise($_GET['user']);
	}
	if (isset ($_GET['limit'])) {
		$limit = sanitise($_GET['limit']);
	}
	if (isset ($_GET['from'])) {
		$offset = $_GET['from'];	
	}
	
	if(isset($_GET['logaction']))
	{
		$logPage->filterAction=sanitise($_GET['logaction']);
	}

	echo $logPage->showListLog(isset($offset) ? $offset : 0 ,isset($limit) ? $limit : 100);
	$skin->displayIfooter();
	die();
}
elseif ($action == "reserve") {
	// Gets the global variables.
	global $allowDoubleReserving, $skin;
	
	// Sanitises the resid for use and checks its validity.
	$request = $internalInterface->checkreqid($_GET['resid']);

	global $enableEmailConfirm;
	if($enableEmailConfirm == 1){
		// check the request is email-confirmed to prevent jumping the gun (ACC-122)
		$mcresult = mysql_query('SELECT pend_mailconfirm FROM acc_pend WHERE pend_id = ' . $request . ';', $tsSQLlink) or die(sqlerror(mysql_error(),"error"));
		$mcrow = mysql_fetch_row($mcresult);
		if($mcrow[0] != "Confirmed")
		{
			$skin->displayRequestMsg("This request is not yet email-confirmed!");
			die();
		}
	}
	
	$query = "SELECT pend_status FROM acc_pend WHERE pend_id = '$request';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	$query2 = "SELECT log_time FROM acc_log WHERE log_pend = '$request' AND log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 1;";
	$result2 = mysql_query($query2, $tsSQLlink);
	if (!$result2)
		sqlerror("Query failed: $query2 ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$date->modify("-7 days");
	$oneweek = $date->format("Y-m-d H:i:s");
	if ($row['pend_status'] == "Closed" && $row2['log_time'] < $oneweek && !($session->hasright($_SESSION['user'], "Admin"))) {
		$skin->displayRequestMsg("Only administrators and checkusers can reserve a request that has been closed for over a week.");	
		$skin->displayIfooter();
		die();
	}
	
	// Lock the tables to avoid a possible conflict.
	// See the following bug: http://www.webcitation.org/6MjKF433v (ACC-101)
	mysql_query('LOCK TABLES acc_pend,acc_log WRITE;',$tsSQLlink);
	
	// Check if the request is not reserved.
	$reservedBy = isReserved($request);
	if($reservedBy != false)
	{
		// Notify the user that the request is already reserved.
		die("Request already reserved by ".$session->getUsernameFromUid($reservedBy));
	}
	
	if(isset($allowDoubleReserving)){
		// Check the number of requests a user has reserved already
		$doubleReserveCountQuery = "SELECT COUNT(*) FROM acc_pend WHERE pend_reserved = ".$_SESSION['userID'].';';
		$doubleReserveCountResult = mysql_query($doubleReserveCountQuery, $tsSQLlink);
		if (!$doubleReserveCountResult)	Die("Error in determining other reserved requests.");
		$doubleReserveCountRow = mysql_fetch_assoc($doubleReserveCountResult);
		$doubleReserveCount = $doubleReserveCountRow['COUNT(*)'];
		
		// User already has at least one reserved. 
		if( $doubleReserveCount != 0) 
		{
			switch($allowDoubleReserving)
			{
				case "warn":
					// Alert the user.
					if(isset($_GET['confdoublereserve']) && $_GET['confdoublereserve'] == "yes")
					{
						// The user confirms they wish to reserve another request.
					}
					else
					{
						//Unlock tables first!
						mysql_query("UNLOCK TABLES;", $tsSQLlink);
						die('You already have reserved a request. Are you sure you wish to reserve another?<br /><ul><li><a href="'.$_SERVER["REQUEST_URI"].'&confdoublereserve=yes">Yes, reserve this request also</a></li><li><a href="' . $tsurl . '/acc.php">No, return to main request interface</a></li></ul>');
					}
					break;
				case "deny":
					//Unlock tables first!
					mysql_query("UNLOCK TABLES;", $tsSQLlink);
					// Prevent the user from continuing.
					die('You already have a request reserved!<br />Your request to reserve an additional request has been denied.<br /><a href="' . $tsurl . '/acc.php">Return to main request interface</a>');
					break;
				case "inform":
					// Tell the user that they already have requests reserved, but let them through anyway..
					echo '<div id="doublereserve-warn">WARNING: You have multiple requests reserved.</div>';
					break;
				case "ignore":
					// Do sod all.
					break;
				default:
					// Do sod all.
					break;
			}
		}
	}
	
	// Is the request closed?
	if(! isset($_GET['confclosed']) )
	{
		$query = "select pend_status from acc_pend where pend_id = '".$request."';";
		$result = mysql_query($query,$tsSQLlink);
		$row = mysql_fetch_assoc($result);
		if($row['pend_status']=="Closed")
		{		
			//Unlock tables first!
			mysql_query("UNLOCK TABLES;", $tsSQLlink);
			Die('This request is currently closed. Are you sure you wish to reserve it?<br /><ul><li><a href="'.$_SERVER["REQUEST_URI"].'&confclosed=yes">Yes, reserve this closed request</a></li><li><a href="' . $tsurl . '/acc.php">No, return to main request interface</a></li></ul>');			
		}
	}	
	
	// No, lets reserve the request.
	$query = "UPDATE `acc_pend` SET `pend_reserved` = '".$_SESSION['userID']."' WHERE `acc_pend`.`pend_id` = $request LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result){		//Unlock tables first!
		mysql_query("UNLOCK TABLES;", $tsSQLlink);
		Die("Error reserving request.");
	}
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$request', '".sanitise($_SESSION['user'])."', 'Reserved', '$now');";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$accbotSend->send("Request $request is being handled by " . $session->getUsernameFromUid($_SESSION['userID']));

	// Release the lock on the table.
	mysql_query('UNLOCK TABLES;',$tsSQLlink);
	
	if($doubleReserveCount) {
		//Autorefresh is probably not a good idea when warnings are displayed, as there is no guarantee that the user has acknowledged the warning.  Disabling.  This also resolves Issue #3 on GitHub.  --FastLizard4
		echo "<p><a href=\"{$tsurl}/acc.php?action=zoom&id={$request}\">Acknowledge, return to request page</a></p>\n";
	} else {
		// Decided to use the HTML redirect, because the PHP code results in an error.
		// I know that this breaks the Back button, but currently I dont have another solution.
		// As an alternative one could implement output buffering to solve this problem.
		echo "<meta http-equiv=\"Refresh\" Content=\"0; URL=$tsurl/acc.php?action=zoom&id=$request\">";
}
	die();
		
}
elseif ($action == "breakreserve") {

	$request = $internalInterface->checkreqid($_GET['resid']);
	
	//check request is reserved
	$reservedBy = isReserved($request);
	if( $reservedBy == "" ) {
		$skin->displayRequestMsg("Request is not reserved.");
		$skin->displayIfooter();
		die();
	}
	if( $reservedBy != $_SESSION['userID'] )
	{
		global $enableAdminBreakReserve;
		if($enableAdminBreakReserve && ($session->hasright($_SESSION['user'], "Admin"))) {
			if(isset($_GET['confirm']) && $_GET['confirm'] == 1)	
			{
				$query = "UPDATE `acc_pend` SET `pend_reserved` = '0' WHERE `acc_pend`.`pend_id` = $request LIMIT 1;";
				$result = mysql_query($query, $tsSQLlink);
				if (!$result)
					Die("Error unreserving request.");
				$now = date("Y-m-d H-i-s");
				$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$request', '".sanitise($_SESSION['user'])."', 'BreakReserve', '$now');";
				$result = mysql_query($query, $tsSQLlink);
				if (!$result)
					sqlerror("Query failed: $query ERROR: " . mysql_error());
				$accbotSend->send("Reservation on Request $request broken by " . $session->getUsernameFromUid($_SESSION['userID']));
				header("Location: acc.php");
				die();
			}
			else
			{
				global $tsurl;
				echo "Are you sure you wish to break " . $session->getUsernameFromUid($reservedBy) .
					"'s reservation?<br />" . 
					"<a href=\"$tsurl/acc.php?action=breakreserve&resid=$request&confirm=1\">Yes</a> / " . 
					"<a href=\"$tsurl/acc.php?action=zoom&id=$request\">No</a>";
			}
			}
			else {
				echo "You cannot break ".$session->getUsernameFromUid($reservedBy)."'s reservation";
			}
		}
	else
	{
		$query = "UPDATE `acc_pend` SET `pend_reserved` = '0' WHERE `acc_pend`.`pend_id` = $request LIMIT 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
		{
			die("Error unreserving request.");
		}
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$request', '".sanitise($_SESSION['user'])."', 'Unreserved', '$now');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("Request $request is no longer being handled.");
		header("Location: acc.php");
		die();
	}
	$skin->displayIfooter();
	die();		
}

elseif ($action == "comment") {
	if (isset($_GET['hash'])) {
		$urlhash = sanitise($_GET['hash']);
	} else {
	    $urlhash = "";
	}
       
    if( isset($_GET['id']) ) {
        $id = $internalInterface->checkreqid($_GET['id']);
        echo "<h2>Comment on request <a href='$tsurl/acc.php?action=zoom&amp;id=$id&amp;hash=$urlhash'>#$id</a></h2>
              <form action='$tsurl/acc.php?action=comment-add&amp;hash=$urlhash' method='post' class='form-horizontal span8'>";
    } else {
        $id = "";
        echo "<h2>Comment on a request</h2>
              <div class='row-fluid'>
              <form action='$tsurl/acc.php?action=comment-add' method='post' class='form-horizontal span8'>";
    }
    echo "
	<div class='control-group'>
		<label for='id' class='control-label'>Request ID:</label> 
		<div class='controls'><input type='text' name='id' value='$id'></div>
	</div>
	<div class='control-group'>
		<label for='visibility' class='control-label'>Visibility:</label> 
		<div class='controls'>
			<select name='visibility'><option>user</option><option>admin</option></select>
		</div>
	</div>
	<div class='control-group'>
		<label for='comment' class='control-label'>Comments:</label> 
		<div class='controls'><textarea name='comment' class='input-xxlarge' rows='6'></textarea></div>
	</div>
	<div class='control-group'>
		<div class='controls'><button type='submit' class='btn btn-default'>Submit</button></div>
	</div>
    </div>
    </form>";
    $skin->displayIfooter();
	die();
}

elseif ($action == "comment-add") 
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
        "<a href='$tsurl/acc.php?action=zoom&amp;id={$request->getId()}&amp;hash=$urlhash'>Return to request #{$request->getId()}</a>",
        "alert-success",
        "Comment added Successfully!",
        true, false);
        
    $botcomment_pvt =  ($visibility == "admin") ? "private " : "";
    $botcomment = User::getCurrent()->getUsername() . " posted a " . $botcomment_pvt . "comment on request " . $request->getId();

    $accbotSend->send($botcomment);
        
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
    
    $botcomment_pvt =  ($visibility == "admin") ? "private " : "";
    $botcomment = User::getCurrent()->getUsername() . " posted a " . $botcomment_pvt . "comment on request " . $request->getId();

    $accbotSend->send($botcomment);
    
    header("Location: acc.php?action=zoom&id=" . $request->getId());
}

elseif ($action == "changepassword") {
	$oldpassword = sanitize($_POST['oldpassword']); //Sanitize the values for SQL queries. 
	$newpassword = sanitize($_POST['newpassword']);
	$newpasswordconfirm = sanitize($_POST['newpasswordconfirm']);
	$sessionuser = sanitize($_SESSION['user']);
	
	if ((!isset($_POST['oldpassword'])) || $_POST['oldpassword'] == "" ) { //Throw an error if old password is not specified.
		$skin->displayRequestMsg("You did not enter your old password.<br />\n");	
		$skin->displayIfooter();
		die();
	}
	
	if ((!isset($_POST['newpassword'])) || $_POST['newpassword'] == "" ) { //Throw an error if new password is not specified.
		$skin->displayRequestMsg("You did not enter your new password.<br />\n");	
		$skin->displayIfooter();
		die();
	}
	
	if ($_POST['newpassword'] != $_POST['newpasswordconfirm']) { //Throw an error if new password does not match what is in the confirmation box.
		$skin->displayRequestMsg("The 2 new passwords you entered do not match.<br />\n");	
		$skin->displayIfooter();
		die();
	}
	
	$query = "SELECT * FROM acc_user WHERE user_name = '$sessionuser';"; //Run a query to get information about the logged in user.
	$result = mysql_query($query, $tsSQLlink);
    if (!$result) {
    	sqlerror("Query failed: $query ERROR: " . mysql_error());
    }
    $row = mysql_fetch_assoc($result);
    
   
    if ( ! authutils::testCredentials( $_POST['oldpassword'], $row['user_pass'] ) ) { //Throw an error if the old password field's value does not match the user's current password.
    	$skin->displayRequestMsg("The old password you entered is not correct.<br />\n");	
		$skin->displayIfooter();
		die();
    }
    
    $user_pass = authutils::encryptPassword($newpassword); //Encrypt the new password before entering it into the database.
    
    $query2 = "UPDATE acc_user SET user_pass = '$user_pass' WHERE user_name = '$sessionuser';"; //Update the password in the database.
    $result2 = mysql_query($query2, $tsSQLlink);
    if (!$result2) {
    	sqlerror("Query failed: $query2 ERROR: " . mysql_error());
    }
    
    $skin->displayRequestMsg("Password successfully changed!<br />\n");	//Output a success message if we got this far.
	$skin->displayIfooter();
	die();
}
elseif ($action == "ec")
{ 
    // edit comment
    
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
        $comment->setComment($_POST['newcomment']);
        $comment->setVisibility($_POST['visibility']);
        
        $comment->save();
        
        $logQuery = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ( :id, :user, :action, CURRENT_TIMESTAMP() );";
        $database = gGetDb();
        $logStatement = $database->prepare($logQuery);
        
        $logAction = "EditComment-c";
        $logStatement->bindParam(":user", User::getCurrent()->getUsername());
        $logStatement->bindParam(":id", $comment->getId());
        $logStatement->bindParam(":action", $logAction);
        $logStatement->execute();
        
        $logAction = "EditComment-r";    
        $logStatement->bindParam(":id", $comment->getRequest());
        $logStatement->execute();
        
        $accbotSend->send("Comment " . $comment->getId() . " edited by " . User::getCurrent()->getUsername());
        
		//Show user confirmation that the edit has been saved, and redirect them to the request after 5 seconds.
		header("Refresh:5;URL=$tsurl/acc.php?action=zoom&id=" . $comment->getRequest());
        
		BootstrapSkin::displayAlertBox("You will be redirected to the request in 5 seconds Click <a href=\"" . $tsurl . "/acc.php?action=zoom&id=" . $comment->getRequest() . "\">here</a> if you are not redirected.", "alert-success", "Comment has been saved successfully.", true, false);
        BootstrapSkin::displayInternalFooter();
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
    
    // Sanitises the resid for use and checks its validity.    
	$request = $internalInterface->checkreqid($_POST['id']);
    
    $database = gGetDb();
	
    $user = User::getByUsername($_POST['user'], $database);
    $curuser = User::getCurrent()->getUsername();
    
    if($user == false)
    {
        BootstrapSkin::displayAlertBox("We couldn't find the user you wanted to send the reservation to. Please check that this user exists and is an active user on the tool.", "alert-error", "Could not find user", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
    if($database->beginTransaction())
    {
        try
        {
            $updateStatement = $database->prepare("UPDATE acc_pend SET pend_reserved = :userid WHERE pend_id = :request LIMIT 1;");
            $updateStatement->bindParam(":userid", $user->getId());
            $updateStatement->bindParam(":request", $request);
            if(!$updateStatement->execute())
            {
                throw new Exception("Error updating reserved status of request.");   
            }
            
            $logStatement = $database->prepare("INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES (:request, :user, :action, CURRENT_TIMESTAMP(), '');");
            $action = "SendReserved";
            $logStatement->bindParam(":user", $curuser);
            $logStatement->bindParam(":request", $request);
            $logStatement->bindParam(":action", $action);
            if(!$logStatement->execute())
            {
                throw new Exception("Error inserting send log entry.");   
            }
            
            $logStatement->bindParam(":user", $user->getUsername());
            $action = "ReceiveReserved";
            if(!$logStatement->execute())
            {
                throw new Exception("Error inserting send log entry.");   
            }
            
            $database->commit();
        }
        catch(Exception $ex)
        {
            $database->rollBack();          
            BootstrapSkin::displayAlertBox($ex->getMessage(), "alert-error", "Query Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die(); 
        }
        catch(PDOException $ex)
        {
            $database->rollBack();          
            BootstrapSkin::displayAlertBox("An error was encountered during the transaction, and the transaction has been rolled back. <br />" . $ex->getMessage(), "alert-error", "Database Error", true, false);
            BootstrapSkin::displayInternalFooter();
            die(); 
        }
    }
    else
    {
        BootstrapSkin::displayAlertBox("Could not start database transaction.", "alert-error", "Database Error", true, false);
        BootstrapSkin::displayInternalFooter();
        die();
    }
    
	//$accbotSend->send("Request $request is being handled by " . $_POST['user']);

    // redirect to zoom page
    echo "<meta http-equiv=\"Refresh\" Content=\"0; URL=$tsurl/acc.php?action=zoom&id=$request\">";
}
elseif ($action == "emailmgmt") { 
	/* New page for managing Emails, since I would rather not be handling editing
	interface messages (such as the Sitenotice) and the new Emails in the same place. */
	if(isset($_GET['create'])) {
		if(!User::getCurrent()->isAdmin()) {
			BootstrapSkin::displayAlertBox("I'm sorry, but you must be an administrator to access this page.");
			BootstrapSkin::displayInternalFooter();
			die();
		}
		if(isset($_POST['submit'])) {
			$emailTemplate = new EmailTemplate();
            $emailTemplate->setDatabase(gGetDb());
            
			$name = $_POST['name'];
            
			$emailTemplate->setName($name);
			$emailTemplate->setText($_POST['text']);
			$emailTemplate->setJsquestion($_POST['jsquestion']);
			$emailTemplate->setOncreated(isset($_POST['oncreated']));
			$emailTemplate->setActive(isset($_POST['active']));
			$siuser = sanitize($_SESSION['user']);
			
			// Check if the entered name already exists (since these names are going to be used as the labels for buttons on the zoom page).
			$nameCheck = EmailTemplate::getByName($name, gGetDb());
			if ($nameCheck) {
				BootStrap::displayAlertBox("That Email template name is already being used. Please choose another.");
				BootstrapSkin::displayInternalFooter();
				die();
			}
			
			$emailTemplate->save();
			$id = $emailTemplate->getId();
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$id', '$siuser', 'CreatedEmail', CURRENT_TIMESTAMP());";
			$result = $tsSQL->query($query);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
		    header("Refresh:5;URL=$tsurl/acc.php?action=emailmgmt");
			BootstrapSkin::displayAlertBox("Email template has been saved successfully. You will be returned to Email Management in 5 seconds.<br /><br />\n
			Click <a href=\"".$tsurl."/acc.php?action=emailmgmt\">here</a> if you are not redirected.");
			$accbotSend->send("Email $name ($id) created by $siuser");
			BootstrapSkin::displayInternalFooter();
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
		$gid = sanitize($_GET['edit']);
        
		if(isset($_POST['submit'])) 
        {
			$emailTemplate = EmailTemplate::getById($gid, gGetDb());
			// Allow the user to see the edit form (with read only fields) but not POST anything.
			if(!User::getCurrent()->isAdmin()) {
				BootstrapSkin::displayAlertBox("I'm sorry, but you must be an administrator to access this page.");
				BootstrapSkin::displayInternalFooter();
				die();
			}
			$name = $_POST['name'];
			$emailTemplate->setName($name);
			$emailTemplate->setText($_POST['text']);
			$emailTemplate->setJsquestion($_POST['jsquestion']);
			
			if ($gid == $createdid) { // Both checkboxes on the main created message should always be enabled.
				$emailTemplate->setOncreated(1);
				$emailTemplate->setActive(1);
			}
			else {
				$emailTemplate->setOncreated(isset($_POST['oncreated']));
				$emailTemplate->setActive(isset($_POST['active']));
			}
			$siuser = sanitize($_SESSION['user']);
				
			// Check if the entered name already exists (since these names are going to be used as the labels for buttons on the zoom page).
			$nameCheck = EmailTemplate::getByName($name, gGetDb());
			if ($nameCheck != false && $nameCheck->getId() != $gid) {
				BootstrapSkin::displayAlertBox("That Email template name is already being used. Please choose another.");
				BootstrapSkin::displayInternalFooter();
				die();
			}

			$emailTemplate->save();
            
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$siuser', 'EditedEmail', CURRENT_TIMESTAMP())";
			$result = $tsSQL->query($query);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
			header("Refresh:5;URL=$tsurl/acc.php?action=emailmgmt");
			BootstrapSkin::displayAlertBox("Email template has been saved successfully. You will be returned to Email Management in 5 seconds.<br /><br />\n
			Click <a href=\"".$tsurl."/acc.php?action=emailmgmt\">here</a> if you are not redirected.");
			$accbotSend->send("Email $name ($gid) edited by $siuser");
            
			BootstrapSkin::displayInternalFooter();
			die();
		} // /if was submitted
        
		$emailTemplate = EmailTemplate::getById($_GET['edit'], gGetDb());
		$smarty->assign('id', $gid);
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
# If the action specified does not exist, goto the default page.
else {
	echo defaultpage();
	BootstrapSkin::displayInternalFooter();
	die();
}
?>
