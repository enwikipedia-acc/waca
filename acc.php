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
require_once 'includes/captcha.php';
require_once 'includes/database.php';
require_once 'includes/offlineMessage.php';
require_once 'includes/messages.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';

// Set the current version of the ACC.
$version = "0.9.7";

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
$captcha = new captcha();
$messages = new messages();
$skin     = new skin();
$accbotSend = new accbotSend();
$session = new session();

// Initialize the session data.
session_start();

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
			$skin->displayPfooter();
		}
		elseif (!isset($action)) {
			// When the action variable isn't set to anything,
			// the login page is displayed for the user to complete.
			echo showlogin();
			$skin->displayPfooter();
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
        $skin->displayIheader($_SESSION['user']);
        $session->checksecurity($_SESSION['user']);
		
		// ?
        $out = $messages->getMessage('20');
        $out .= "<div id=\"content\">";
        echo $out;
}

// When no action is specified the default Internal ACC are displayed.
// TODO: Improve way the method is called.
if ($action == '') {
	echo defaultpage();
	$skin->displayIfooter();
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
	foreach ( $acrnamebl as $wnbl => $nbl ) {
		$phail_test = @ preg_match( $nbl, $_POST['name'] );
		if ( $phail_test == TRUE ) {
			#$message = $messages->getMessage(15);
			echo "$message<br />\n";
			$target = "$wnbl";
			$host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
			$fp = fsockopen( "udp://127.0.0.1", 9001, $erno, $errstr, 30 );
			fwrite( $fp, "[Name-Bl-ACR] HIT: $wnbl - " . $_POST['name'] . " / " . $_POST['wname'] . " " . $_SERVER['REMOTE_ADDR'] . " ($host) " . $_POST['email'] . " " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" );
			fclose( $fp );
			echo "Account created! In order to complete the process, please make a confirmation edit to your user talk page. In this edit, note that you requested an account on the ACC account creation interface, and use a descriptive edit summary so that we can easily find this edit.  <b>Failure to do this will result in your request being declined.</b><br /><br />\n";
			die( );
		}
	}
	global $enableDnsblChecks;
	if( $enableDnsblChecks == 1) {
		$dnsblcheck = checkdnsbls( $_SERVER['REMOTE_ADDR'] );
		if ( $dnsblcheck['0'] == true ) {
			$cmt = "FROM $ip " . $dnsblcheck['1'];
			$fp = fsockopen( "udp://127.0.0.1", 9001, $erno, $errstr, 30 );
			fwrite( $fp, "[DNSBL-ACR] HIT: " . $_POST['name'] . " - " . $_POST['wname'] . " " . $_SERVER['REMOTE_ADDR'] . " " . $_POST['email'] . " " . $_SERVER['HTTP_USER_AGENT'] . " $cmt\r\n" );
			fclose( $fp );
			die( "Account not created, please see " . $dnsblcheck['1'] );
		}
	}
	$cu_name = urlencode( $_REQUEST['wname'] );
	$userblocked = file_get_contents( "http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$cu_name&format=php" );
	$ub = unserialize( $userblocked );
	if ( isset ( $ub['query']['blocks']['0']['id'] ) ) {
		$message = $messages->getMessage( '9' );
		$skin->displayRequestMsg("ERROR: You are presently blocked on the English Wikipedia<br />");
		$skin->displayPfooter();
		die();
	}
	$userexist = file_get_contents( "http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$cu_name&format=php" );
	$ue = unserialize( $userexist );
	foreach ( $ue['query']['users']['0'] as $oneue ) {
		if ( !isset($oneue['missing'])) {
			$skin->displayRequestMsg("Invalid On-Wiki username.<br />");
			$skin->displayPfooter();
			die();
		}
	}
	
	// check if the user is to new
	global $onRegistrationNewbieCheck;
	if( $onRegistrationNewbieCheck ) 
	{
		global $onRegistrationNewbieCheckEditCount, $onRegistrationNewbieCheckAge;
		$isNewbie = unserialize(file_get_contents( "http://en.wikipedia.org/w/api.php?action=query&list=allusers&format=php&auprop=editcount|registration&aulimit=1&aufrom=$cu_name" ));
		$time = $isNewbie['query']['allusers'][0]['registration'];
		$time2 = time() - strtotime($time);
		$editcount = $isNewbie['query']['allusers'][0]['editcount'];
		if (!($editcount > $onRegistrationNewbieCheckEditCount and $time2 > $onRegistrationNewbieCheckAge)) {
			$skin->displayRequestMsg("I'm sorry, you are too new to request an account at the moment.<br />");
			$skin->displayPfooter();
			die();
		}
	}
	// check if user checked the "I have read and understand the interface guidelines" checkbox
	if(!isset($_REQUEST['guidelines'])) {
		$skin->displayRequestMsg("I'm sorry, but you must read <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\">the interface guidelines</a> before your request may be submitted.<br />");
		$skin->displayPfooter();
		die();
	}
	
	$user = mysql_real_escape_string($_REQUEST['name']);
	if (stristr($user, "'") !== FALSE) {
		die("Username cannot contain the character '\n");
	}
	$wname = mysql_real_escape_string($_REQUEST['wname']);
	$pass = mysql_real_escape_string($_REQUEST['pass']);
	$pass2 = mysql_real_escape_string($_REQUEST['pass2']);
	$email = mysql_real_escape_string($_REQUEST['email']);
	$sig = mysql_real_escape_string($_REQUEST['sig']);
	$template = mysql_real_escape_string($_REQUEST['template']);
	if(isset($_REQUEST['secureenable']))
	{
		$secureenable = mysql_real_escape_string($_REQUEST['secureenable']);
	}
	else
	{
		$secureenable = false;
	}
	if(isset($_REQUEST['welcomeenable']))
	{
		$welcomeenable = mysql_real_escape_string($_REQUEST['welcomeenable']);	
	}
	else
	{
		$welcomeenable=false;
	}
	if ( !isset($user) || !isset($wname) || !isset($pass) || !isset($pass2) || !isset($email) || strlen($email) < 6) {
		echo "<h2>ERROR!</h2>Form data may not be blank.<br />\n";
		$skin->displayIfooter();
		die();
	}
	if (isset($_POST['debug']) && $_POST['debug'] == "on") {
		echo "<pre>\n";
		print_r($_REQUEST);
		echo "</pre>\n";
	}
	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$/i', $_REQUEST['email']);
	if ($mailisvalid == 0) {
		$skin->displayRequestMsg("ERROR: Invalid E-mail address.<br />");
		$skin->displayPfooter();
		die();
	}
	if ($pass != $pass2) {
		$skin->displayRequestMsg("Passwords did not match!<br />");
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_user WHERE user_name = '$user' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error() . " 132");
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		$skin->displayRequestMsg("I'm sorry, but that username is in use. Please choose another. <br />");
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_user WHERE user_email = '$email' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		$skin->displayRequestMsg( "I'm sorry, but that e-mail address is in use.<br />");
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_user WHERE user_onwikiname = '$wname' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		$skin->displayRequestMsg("I'm sorry, but $wname already has an account here.<br />");
		$skin->displayPfooter();
		die();
	}
	$query = "SELECT * FROM acc_pend WHERE pend_name = '$user' AND (pend_status = 'Open' OR pend_status = 'Admin' OR pend_status = 'Closed') AND DATE_SUB(CURDATE(),INTERVAL 7 DAY) <= pend_date LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	if (!empty($row['pend_name'])) {
		$skin->displayRequestMsg("I'm sorry, you are too new to request an account at the moment.<br />");
		$skin->displayPfooter();
		die();
	}
	if (!isset($fail) || $fail != 1) {
		if ($secureenable == "1") {
			$secure= 1;
		} else {
			$secure = 0;
		}
		if ($welcomeenable == "1") {
			$welcome = 1;
		} else {
			$welcome = 0;
		}
		$user_pass = md5($pass);
		$query = "INSERT INTO acc_user (user_name, user_email, user_pass, user_level, user_onwikiname, user_secure, user_welcome, user_welcome_sig, user_welcome_template) VALUES ('$user', '$email', '$user_pass', 'New', '$wname', '$secure', '$welcome', '$sig', '$template');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("New user: $user");
		$skin->displayRequestMsg("Account created! Your username is $user! In order to complete the process, please make a confirmation edit to your user talk page. In this edit, note that you requested an account on the ACC account creation interface, and use a descriptive edit summary so that we can easily find this edit.  <b>Failure to do this will result in your request being declined.</b><br /><br />");
	}
	$skin->displayPfooter();
	die();
}

elseif ($action == "register") {
	echo "<h2>Register!</h2>";
    echo "<span style=\"font-weight:bold;color:red;font-size:20px;\">This form is for requesting tool access. If you want to request an account for Wikipedia, then go to <a href=\"".$tsurl."\">".$tsurl."</a></span>";
	echo <<<HTML
	<form action="acc.php?action=sreg" method="post">
    <table cellpadding="1" cellspacing="0" border="0">
            <tr>
                <td>Desired Username:</td>
                <td><input type="text" name="name"></td>
            </tr>
            <tr>
                <td>E-mail Address:</td>
                <td><input type="text" name="email"></td>
            </tr>
            <tr>
                <td>Wikipedia username:</td>
                <td><input type="text" name="wname"></td>
            </tr>
            <tr>
                <td>Desired password (<strong>PLEASE DO NOT USE THE SAME PASSWORD AS ON WIKIPEDIA.</strong>):</td>
                <td><input type="password" name="pass"></td>
            </tr>
            <tr>
                <td>Desired password(again):</td>
                <td><input type="password" name="pass2"></td>
            </tr>
            <tr>
                <td>Enable use of the secure server:</td>
                <td><input type="checkbox" name="secureenable"></td>
            </tr>
            <tr>
                <td>Enable <a href="http://en.wikipedia.org/wiki/User:SQLBot-Hello">SQLBot-Hello</a> welcoming of the users I create:</td>
                <td><input type="checkbox" name="welcomeenable"></td>
            </tr>
            <tr>
                <td>Your signature (wikicode)<br /><i>This would be the same as ~~~ on-wiki. No date, please.  Not needed if you left the checkbox above unchecked.</i></td>
                <td><input type="text" name="sig" size ="40"></td>
            </tr>
            <tr>
                <td>Template you would like the bot to welcome with?<br /><i>If you'd like more templates added, please contact <a href="http://en.wikipedia.org/wiki/User_talk:SQL">SQL</a>, <a href="http://en.wikipedia.org/wiki/User_talk:Cobi">Cobi</a>, or <a href="http://en.wikipedia.org/wiki/User_talk:FastLizard4">FastLizard4</a>.</i>  Not needed if you left the checkbox above unchecked.</td>
                <td>
                	<select name="template" size="0">
                		<option value="welcome">{{welcome|user}} ~~~~</option>
                		<option value="welcomeg">{{welcomeg|user}} ~~~~</option>
                		<option value="welcome-personal">{{welcome-personal|user}} ~~~~</option>
                		<option value="werdan7">{{User:Werdan7/W}} ~~~~</option>
                		<option value="welcomemenu">{{WelcomeMenu|sig=~~~~}}</option>
                		<option value="welcomeicon">{{WelcomeIcon}} ~~~~</option>
                		<option value="welcomeshout">{{WelcomeShout|user}} ~~~~</option>
                		<option value="welcomesmall">{{WelcomeSmall|user}} ~~~~</option>
                		<option value="hopes">{{Hopes Welcome}} ~~~~</option>
	                	<option value="welcomeshort">{{Welcomeshort|user}} ~~~~</option>
						<option value="w-riana">{{User:Riana/Welcome|name=user|sig=~~~~}}</option>
						<option value="w-screen">{{w-screen|sig=~~~~}}</option>
						<option value="wodup">{{User:WODUP/Welcome}} ~~~~</option>
						<option value="williamh">{{User:WilliamH/Welcome|user}} ~~~~</option>
						<option value="malinaccier">{{User:Malinaccier/Welcome|~~~~}}</option>
						<option value="laquatique">{{User:L'Aquatique/welcome}} ~~~~</option>
						<option value="coffee">{{User:Coffee/welcome|user|||~~~~}}</option>
						<option value="matt-t">{{User:Matt.T/C}} ~~~~</option>
						<option value="roux">{{User:Roux/W}} ~~~~</option>
						<option value="staffwaterboy">{{User:Staffwaterboy/Welcome}} ~~~~</option>
						<option value="maedin">{{User:Maedin/Welcome}} ~~~~</option>
						<option value="chzz">{{User:Chzz/botwelcome|name=user|sig=~~~~}}</option>
						<option value="phantomsteve">{{User:Phantomsteve/bot welcome}} ~~~~</option>
					</select>
				</td>
            </tr>
			<tr>
				<td><b>I have read and understand the <a href="http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide">interface guidelines.</a><b></td>
				<td><input type="checkbox" name="guidelines"></td>
            <tr>
                <td></td>
                <td><input type="submit"><input type="reset"></td>
            </tr>
    </table>
    </form>
HTML;

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
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
			$hash = md5($hashme);
			if ($hash == $_GET['si']) {
				if ($_POST['pw'] == $_POST['pw2']) {
					$pw = md5($_POST['pw2']);
					$query = "UPDATE acc_user SET user_pass = '$pw' WHERE user_id = '$puser';";
					$result = mysql_query($query, $tsSQLlink);
					if (!$result) {
						Die("Query failed: $query ERROR: " . mysql_error());
					}
					echo "Password reset!\n<br />\nYou may now <a href=\"acc.php\">Login</a>";
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
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
		$hash = md5($hashme);
		if ($hash == $_GET['si']) {
			echo '<h2>Reset password for '. $row['user_name'].' ('.$row['user_email'].')</h2><form action="acc.php?action=forgotpw&amp;si='.$_GET['si'].'&amp;id='. $_GET['id'].'" method="post">';
			echo <<<HTML
			New Password: <input type="password" name="pw"><br />
            New Password (confirm): <input type="password" name="pw2"><br />
            <input type="submit"><input type="reset">
            </form><br />
            Return to <a href="acc.php">Login</a>
HTML;
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
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if (!isset($row['user_id'])) {
			echo "<h2>ERROR</h2>Missing or incorrect Username supplied..\n";
		}
		elseif (strtolower($_POST['email']) != strtolower($row['user_email'])) {
			echo "<h2>ERROR</h2>Missing or incorrect Email address supplied.\n";
		}
		else{
		$hashme = $puser . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
		$hash = md5($hashme);
		// re bug 29: please don't escape the url parameters here: it's a plain text email so no need to escape, or you break the link
		$mailtxt = "Hello! You, or a user from " . $_SERVER['REMOTE_ADDR'] . ", has requested a password reset for your account.\n\nPlease go to $tsurl/acc.php?action=forgotpw&si=$hash&id=" . $row['user_id'] . " to complete this request.\n\nIf you did not request this reset, please disregard this message.\n\n";
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($row['user_email'], "English Wikipedia Account Request System - Forgotten password", $mailtxt, $headers);
		echo "Your password reset request has been completed. Please check your e-mail.\n<br />";
		}
	}
	echo <<<HTML
	<form action="acc.php?action=forgotpw" method="post">
    Your username: <input type="text" name="username"><br />
    Your e-mail address: <input type="text" name="email"><br />
    <input type="submit"><input type="reset">
    </form><br />
    Return to <a href="acc.php">Login</a>
HTML;


	$skin->displayPfooter();
	die();
}
elseif ($action == "login") {
	if ($useCaptcha) {		
		if (isset($_POST['captcha'])) {
			if (!$captcha->verifyPasswd($_POST['captcha_id'],$_POST['captcha'])) {
				header("Location: $tsurl/acc.php?error=captchafail");
				die();
			}
		} else {
			// check if they were supposed to send a captcha but didn't
	    		if ($captcha->showCaptcha()) {
	    			header("Location: $tsurl/acc.php?error=captchamissing");
				die();
	    		}
		}
	}
	$puser = mysql_real_escape_string($_POST['username']);
	$ip = sanitize($_SERVER['REMOTE_ADDR']);
	$query = "SELECT * FROM acc_user WHERE user_name = \"$puser\";";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
        if ($row['user_forcelogout'] == 1)
        {
                mysql_query("UPDATE acc_user SET user_forcelogout = 0 WHERE user_name = \"" . $puser . "\"", $tsSQLlink);
        }
	
	// Checks whether the user is new to ACC with a pending account.
	if ($row['user_level'] == "New") {
		
		// Display the header of the interface.
		$skin->displayPheader();
		
		echo "<h2>Account Pending</h2>";
		echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
		echo "</pre><br />";
		
		// Display the footer of the interface.
		$skin->displayPfooter();
		die();
	}
	// Checks whether the user's account has been suspended.
	if ($row['user_level'] == "Suspended") {
		
		// Display the header of the interface.
		$skin->displayPheader();
		
		echo '<h2>Account Suspended</h2>';
		echo "I'm sorry, but, your account is presently suspended.<br />\n";
		
		// Checks whether there was a reason for the suspension.
		$reasonQuery = 'select log_cmt from acc_log where log_action = "Suspended" and log_pend = '.$row['user_id'].' order by log_time desc limit 1;';
		$reasonResult = mysql_query($reasonQuery, $tsSQLlink);
		$reasonRow = mysql_fetch_assoc($reasonResult);
		echo "The reason given is shown below:<br /><pre>";
		echo '<b>' . $reasonRow['log_cmt'] . "</b></pre><br />";
		
		// Display the footer of the interface.
		$skin->displayPfooter();
		die();
	}
	$calcpass = md5($_POST['password']);
	if ($row['user_pass'] == $calcpass)
	{
			if ($useCaptcha) {
				$captcha->clearFailedLogins();
			}
			
			// Assign values to certain Session variables.
			// The values are retrieved from the ACC database.
			$_SESSION['userID'] = $row['user_id'];
			$_SESSION['user'] = $row['user_name'];
			$_SESSION['ip'] = $ip;
			
			// Get data related to the current user.
			$result = mysql_query("SELECT user_lastip,user_lastactive FROM acc_user WHERE user_name ='" . $_SESSION['user'] . "';", $tsSQLlink) or sqlerror(mysql_error(),'Database error.');
			$row = mysql_fetch_assoc($result);
			
			// Assign values to the last login variables.
			$_SESSION['lastlogin_ip'] = $row['user_lastip'];
			$_SESSION['lastlogin_time'] = strtotime($row['user_lastactive']);		
			
			// Set the current IP as the last login IP.
			mysql_query("UPDATE acc_user SET user_lastip = '" . $_SESSION['ip'] . "' WHERE user_name = '" . $_SESSION['user'] . "';", $tsSQLlink);
			
			if ( isset( $_GET['newaction'] ) ) {
				$header = "Location: $tsurl/acc.php?action=".$_GET['newaction'];
				foreach ($_GET as $key => $get) {
					if ($key == "newaction" || $key == "nocheck" || $get == "login" ) {
					}
					else {
						$header .= "&$key=$get";
					}
				}
				header($header);
			}
			else {
				header("Location: $tsurl/acc.php");
			}
	}
	else
	{
		$now = date("Y-m-d H-i-s");
		if (!empty($row['user_email'])) {
			if ($useCaptcha) {
				$captcha->addFailedLogin();
			}
		}
		header("Location: $tsurl/acc.php?error=authfail");
		die();
	}
}
elseif ($action == "messagemgmt") {
	if (isset ($_GET['view'])) {
	if (!preg_match('/^[0-9]*$/',$_GET['view']))
		die('Invaild GET value passed.');
		
	$mid = sanitize($_GET['view']);
		/*
		OK, let's try and use acc_user for storing usernames. I've added a new column, rev_userid. Let's try and use that.
		
		I've put together a new query to aid the process:
		select r.rev_id, r.rev_msg, r.rev_timestamp, r.rev_userid, u.user_name, m.mail_id, m.mail_text, m.mail_count, m.mail_desc, m.mail_type from acc_rev r inner join acc_user u on r.rev_userid = u.user_id join acc_emails m on m.mail_id = r.rev_msg where m.mail_id = 1 order by r.rev_id desc limit 1;

		*/
		$query = "SELECT * FROM acc_emails WHERE mail_id = $mid ORDER BY mail_id DESC LIMIT 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$mailtext = htmlentities($row['mail_text']);
		echo "<h2>View message</h2><br />Message ID: " . $row['mail_id'] . "<br />\n";
		echo "Message count: " . $row['mail_count'] . "<br />\n";
		echo "Message title: " . $row['mail_desc'] . "<br />\n";
		echo "Message text: <br /><pre>$mailtext</pre><br />\n";
		$skin->displayIfooter();
		die();
	}
	if (isset ($_GET['edit'])) {
	if(!$session->hasright($_SESSION['user'], 'Admin')) {
			echo "I'm sorry, but, this page is restricted to administrators only.<br />\n";
			$skin->displayIfooter();
			die();
		}
		if (!preg_match('/^[0-9]*$/',$_GET['edit']))
			die('Invaild GET value passed.');		
		$mid = sanitize($_GET['edit']);
		if ( isset( $_GET['submit'] ) ) {
			$mtext = sanitize($_POST['mailtext']);
			$mtext = html_entity_decode($mtext);
			$mdesc = sanitize($_POST['maildesc']);
			$siuser = sanitize($_SESSION['user']);
			$query = "UPDATE acc_emails SET mail_desc = '$mdesc' WHERE mail_id = '$mid';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$query = "UPDATE acc_emails SET mail_text = '$mtext' WHERE mail_id = '$mid'";
			$result = mysql_query( $query, $tsSQLlink );
			if( !$result ) {
				sqlerror(mysql_error(),"Could not update message");
			}
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$mid', '$siuser', 'Edited', '$now');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$query = "SELECT mail_desc FROM acc_emails WHERE mail_id = $mid;";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$mailname = $row['mail_desc'];
			echo "Message $mailname ($mid) updated.<br />\n";
			$accbotSend->send("Message $mailname ($mid) edited by $siuser");
			$skin->displayIfooter();
			die();
		}
		$query = "SELECT * FROM acc_emails WHERE mail_id = $mid;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$mailtext = htmlentities($row['mail_text']);
		echo "<h2>Edit message</h2><strong>This is NOT a toy. If you can see this form, you can edit this message. <br />WARNING: MISUSE OF THIS FUNCTION WILL RESULT IN LOSS OF ACCESS.</strong><br />\n<form action=\"acc.php?action=messagemgmt&amp;edit=$mid&amp;submit=1\" method=\"post\"><br />\n";
		echo "<input type=\"text\" name=\"maildesc\" value=\"" . $row['mail_desc'] . "\"/><br />\n";
		echo "<textarea name=\"mailtext\" rows=\"20\" cols=\"60\">$mailtext</textarea><br />\n";
		echo "<input type=\"submit\"/><input type=\"reset\"/><br />\n";
		echo "</form>";
		$skin->displayIfooter();
		die();
	}
	$query = "SELECT mail_id, mail_count, mail_desc FROM acc_emails WHERE mail_type = 'Message';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Mail messages</h2>\n";
	echo "<ul>\n";
	while ( list( $mail_id, $mail_count, $mail_desc ) = mysql_fetch_row( $result ) ) {
		$out = "<li>$mail_id) <small>[ $mail_desc ] <a href=\"acc.php?action=messagemgmt&amp;edit=$mail_id\">Edit!</a> - <a href=\"acc.php?action=messagemgmt&amp;view=$mail_id\">View!</a></small></li>";
		$out2 = "<li>$mail_id) <small>[ $mail_desc ] <a href=\"acc.php?action=messagemgmt&amp;view=$mail_id\">View!</a></small></li>";
		if($session->hasright($_SESSION['user'], 'Admin')){
		echo "$out\n";
		}
		elseif(!$session->hasright($_SESSION['user'], 'Admin')){
		echo "$out2\n";
		}
	}
	echo "</ul>";
	$query = "SELECT * FROM acc_emails WHERE mail_type = 'Interface';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Public Interface messages</h2>\n";
	echo "<ul>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li>$mailn) <small>[ $maild ] <a href=\"acc.php?action=messagemgmt&amp;edit=$mailn\">Edit!</a> - <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		$out2 = "<li>$mailn) <small>[ $maild ] <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		if($session->hasright($_SESSION['user'], 'Admin')){
		echo "$out\n";
		}
		elseif(!$session->hasright($_SESSION['user'], 'Admin')){
		echo "$out2\n";
		}
	}
	echo "</ul>";
	$query = "SELECT * FROM acc_emails WHERE mail_type = 'Internal';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());

	echo "<h2>Internal Interface messages</h2>\n";
	echo "<ul>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li>$mailn) <small>[ $maild ] <a href=\"acc.php?action=messagemgmt&amp;edit=$mailn\">Edit!</a> - <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		$out2 = "<li>$mailn) <small>[ $maild ] <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		if($session->hasright($_SESSION['user'], 'Admin')){
		echo "$out\n";
		}
		elseif(!$session->hasright($_SESSION['user'], 'Admin')){
		echo "$out2\n";
		}
	}
	echo "</ul>";
	$skin->displayIfooter();
	die();
}
elseif ($action == "sban" && $_GET['user'] != "") {
	
	// Checks whether the current user is an admin.
	if(!$session->hasright($_SESSION['user'], "Admin")) {
		die("Only administrators may ban users");
	}
	
	// Checks whether there is a reason entered for ban.
	if (!isset($_POST['banreason']) || $_POST['banreason'] == "") {
		echo "<h2>ERROR</h2>\n<br />You must specify a ban reason.\n";
		$skin->displayIfooter();
		die();
	}
	
	// Checks whether there is a target entered to ban.
	if (!isset($_POST['target']) || $_POST['target'] == "") {
		echo "<h2>ERROR</h2>\n<br />You must specify a target to be blocked.\n";
		$skin->displayIfooter();
		die();
	}
	
	$duration = sanitize($_POST['duration']);
	if ($duration == "-1") {
		$duration = -1;
	} elseif ($duration == "other") {
		$duration = strtotime($_POST['otherduration']);
		if (!$duration) {
			echo "<h2>ERROR</h2>\n<br />Invalid ban time specified.\n";
			$skin->displayIfooter();
			die();
		} elseif (time() > $duration) {
			echo "<h2>ERROR</h2>\n<br />Invalid ban time specified (the ban would have already expired).\n";
			$skin->displayIfooter();
			die();
		}
	} else {
		$duration = $duration +time();
	}
	$reason = sanitize($_POST['banreason']);
	$siuser = sanitize($_GET['user']);
	$target = sanitize($_POST['target']);
	$type = sanitize($_POST['type']);
	$now = date("Y-m-d H-i-s");
	upcsum($target);
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$target', '$siuser', 'Banned', '$now');";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$query = "INSERT INTO acc_ban (ban_type, ban_target, ban_user, ban_reason, ban_date, ban_duration) VALUES ('$type', '$target', '$siuser', '$reason', '$now', $duration);";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "Banned " . htmlentities($_POST['target']) . " for $reason<br />\n";
	if ( !isset($duration) || $duration == "-1") {
		$until = "Indefinite";
	} else {
		$until = date("F j, Y, g:i a", $duration);
	}
	if ($until == 'Indefinite') {
		$accbotSend->send("$target banned by $siuser for " . $_POST['banreason'] . " indefinitely");
	} else {
		$accbotSend->send("$target banned by $siuser for " . $_POST['banreason'] . " until $until");
	}
	$skin->displayIfooter();
	die();
}
elseif ($action == "unban" && $_GET['id'] != "") 
{
	$siuser = sanitize($_SESSION['user']);

	if(!$session->hasright($_SESSION['user'], "Admin"))
	{
		die("Only administrators may unban users");
	}
	$bid = sanitize($_GET['id']);
	$query = "SELECT * FROM acc_ban WHERE ban_id = '$bid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
	{
		Die("Query failed: $query ERROR: " . mysql_error());
	}
	$row = mysql_fetch_assoc($result);
	$iTarget = $row['ban_target'];

	if( isset($_GET['confirmunban']) && $_GET['confirmunban']=="true" )
	{
		if (!isset($_POST['unbanreason']) || $_POST['unbanreason'] == "") 
		{
			echo "<h2>ERROR</h2><br />You must enter an unban reason.\n";
			$skin->displayIfooter();
			die;
		}
		else 
		{
			$unbanreason = sanitize($_POST['unbanreason']);
			$query = "DELETE FROM acc_ban WHERE ban_id = '$bid';";
			$result = $tsSQL->query($query);
			if (!$result)
			{
				die($tsSQL->showError(mysql_error(), "Database error"));
			}
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$bid', '$siuser', 'Unbanned', '$now', '$unbanreason');";
			$result = $tsSQL->query($query);
			if (!$result)
			{
				die($tsSQL->showError(mysql_error(), "Database error"));
			}
			echo "Unbanned ban #$bid<br />\n";
			$accbotSend->send("Ban #" . $bid . " ($iTarget) unbanned by " . $_SESSION['user']);
			$skin->displayIfooter();
			die();
		}
	}
	else
	{
		$confOut =  "Are you sure you wish to unban #".$bid.", targeted at ".$iTarget.", ";
		if ( !isset($row['ban_duration']) || $row['ban_duration'] == "-1") 
		{
			$confOut.= "not set to expire";
		} 
		else 
		{
			$confOut.= "set to expire " . date("F j, Y, g:i a", $row['ban_duration']);
		}
		$confOut .= ", and with the reason:<br />";
		echo $confOut;
		
		echo $row['ban_reason'] . "<br />";
		echo "What is your reason for unbanning this person?<br />";
		echo "<form METHOD=\"post\" ACTION=\"acc.php?action=unban&id=". $bid ."&confirmunban=true\">";
		echo "<input type=\"text\" name=\"unbanreason\"><input type=\"submit\"/></form><br />";
		echo "<a href=\"acc.php\">Cancel</a>";
		
	}
}
elseif ($action == "ban") {
	$siuser = sanitize($_SESSION['user']);
	if (isset ($_GET['ip']) || isset ($_GET['email']) || isset ($_GET['name'])) {
		if(!$session->hasright($_SESSION['user'], "Admin"))
			die("Only administrators may ban users");
		if (isset($_GET['ip'])) {
			$ip2 = sanitize($_GET['ip']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$ip2';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_ip'];
			$type = "IP";
		}
		elseif (isset($_GET['email'])) {
			$email2 = sanitize($_GET['email']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$email2';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_email'];
			$type = "EMail";
		}
		elseif (isset($_GET['name'])) {
			$name2 = sanitize($_GET['name']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$name2';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_name'];
			$type = "Name";
		}
		$target = sanitize($target);
		$query = "SELECT * FROM acc_ban WHERE ban_target = '$target';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['ban_id'] != "") {
			echo "<h2>ERROR</h2>\n<br />\nCould not ban. Already banned!<br />";
			$skin->displayIfooter();
			die();
		} else {
			echo "<h2>Ban an IP, Name or E-Mail</h2>\n";
			echo "<form action=\"acc.php?action=sban&amp;user=$siuser\" method=\"post\">";
			echo "Ban target: $target\n<br />\n";
			echo "<table><tr><td>Reason:</td><td><input type=\"text\" name=\"banreason\"></td></tr>\n";
			echo "<tr><td>Duration:</td><td>\n";
			echo "<SELECT NAME=\"duration\">\n";
			echo "<OPTION VALUE=\"-1\">Indefinite</OPTION>\n";
			echo "<OPTION VALUE=\"86400\">24 Hours</OPTION>\n";
			echo "<OPTION VALUE=\"604800\">One Week</OPTION>\n";
			echo "<OPTION VALUE=\"2629743\">One Month</OPTION>\n";
			echo "<OPTION VALUE=\"other\">Other</OPTION>\n";
			echo "</SELECT></td></tr>\n";
			/* TODO: Add some fancy javascript that hides this until the user selects other from the menu above */
			echo "<tr><td>Other:</td><td><input type=\"text\" name=\"otherduration\"></td></tr>";
			echo "</table><br />\n";
			echo "<input type=\"submit\"><input type=\"hidden\" name=\"target\" value=\"$target\" /><input type=\"hidden\" name=\"type\" value=\"$type\" /></form>\n";
		}
	} else {
		echo "<h2>Active Ban List</h2>\n<table border='1'>\n";
		echo "<tr><td>IP/Name/Email</td><td>Banned by</td><td>Reason</td><td>Time</td><td>Expiry</td>";
		$isAdmin = $session->hasright($_SESSION['user'], "Admin");
		if ($isAdmin) {
			echo "<td>Unban</td>";
		}
		echo "</tr>";
		$query = "SELECT * FROM acc_ban;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		while ($row = mysql_fetch_assoc($result)) {
			if ( !isset($row['ban_duration']) || $row['ban_duration'] == "-1") {
				$until = "Indefinite";
			} else {
				$until = date("F j, Y, g:i a", $row['ban_duration']);
			}
			echo "<tr>";
			switch($row['ban_type'])
			{
				case "IP":
					echo '<td><a href="search.php?term='.$row['ban_target'].'&amp;type=IP">'.$row['ban_target'].'</a></td>';
					break;
				case "EMail":
					echo '<td><a href="search.php?term='.$row['ban_target'].'&amp;type=email">'.$row['ban_target'].'</a></td>';
					break;
				case "Name";
					echo '<td><a href="search.php?term='.$row['ban_target'].'&amp;type=Request">'.$row['ban_target'].'</a></td>';
					break;
				default:
					echo '<td>'.$row['ban_target'].'</td>';
					break;
			}
			echo "<td>".$row['ban_user']."</td><td>".$row['ban_reason']."</td><td>".$row['ban_date']."</td><td>$until</td>";
			if ($isAdmin) {
				echo "<td><a href=\"acc.php?action=unban&amp;id=" . $row['ban_id'] . "\">Unban</a></td>";
			}
			echo "</tr>";
		}
		echo "</table>\n";
		if($isAdmin) {
			echo "<h2>Ban an IP, Name or E-Mail</h2>\n";
			echo "<form action=\"acc.php?action=sban&amp;user=$siuser\" method=\"post\">";
			echo "<table>";
			echo "<tr><td>Ban target:</td><td><input type=\"text\" name=\"target\" /></td></tr>\n";
			echo "<tr><td>Reason:</td><td><input type=\"text\" name=\"banreason\"></td></tr>\n";
			echo "<tr><td>Duration:</td><td>\n";
			echo "<SELECT NAME=\"duration\">\n";
			echo "<OPTION VALUE=\"-1\">Indefinite</OPTION>\n";
			echo "<OPTION VALUE=\"86400\">24 Hours</OPTION>\n";
			echo "<OPTION VALUE=\"604800\">One Week</OPTION>\n";
			echo "<OPTION VALUE=\"2629743\">One Month</OPTION>\n";
			echo "<OPTION VALUE=\"other\">Other</OPTION>\n";
			echo "</SELECT></td></tr>\n";
			/* TODO: Add some fancy javascript that hides this until the user selects other from the menu above */
			echo "<tr><td>Other:</td><td><input type=\"text\" name=\"otherduration\"></td></tr>";
			echo "<tr><td>Type:</td><td>\n";
 			echo "<select name=\"type\"><option value=\"IP\">IP</option><option value=\"Name\">Name</option><option value=\"EMail\">E-Mail</option></select>\n";
 			echo "</td></tr>\n";
			echo "</table><br />\n";
			echo "<input type=\"submit\"></form>\n";
		}
		$skin->displayIfooter();
		die();
	}
}
elseif ($action == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") {
	if ($_GET['target'] == "admins" || $_GET['target'] == "users") {
		if ($_GET['target'] == "admins") {
			$target = "Admin";
		} else {
			$target = "Open";
		}
		$gid = sanitize($_GET['id']);
		if (csvalid($gid, $_GET['sum']) != 1) {
			echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
			$skin->displayIfooter();
			die();
		}
		$sid = sanitize($_SESSION['user']);
		$query = "SELECT pend_status FROM acc_pend WHERE pend_id = '$gid';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['pend_status'] == $target) {
			echo "Cannot set status, target already deferred to $target<br />\n";
			$skin->displayIfooter();
			die();
		}
		$query = "UPDATE acc_pend SET pend_status = '$target', pend_reserved = '0' WHERE pend_id = '$gid';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		if ($_GET['target'] == "admins") {
			$deto = "admins";
		} else {
			$deto = "users";
		}
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Deferred to $deto', '$now');";
		upcsum($gid);
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("Request $gid deferred to $deto by $sid");
		$skin->displayRequestMsg("Request " . $_GET['id'] . " deferred to $deto.");
		echo defaultpage();
		$skin->displayIfooter();
		die();
	} else {
		echo "Target not specified.<br />\n";
	}
}
elseif ($action == "welcomeperf" || $action == "prefs") { //Welcomeperf is deprecated, but to avoid conflicts, include it still.
	if (isset ($_POST['sig'])) {
		$sig = sanitize($_POST['sig']);
		$template = sanitize($_POST['template']);
		$sid = sanitize($_SESSION['user']);
		if( isset( $_POST['welcomeenable'] ) ) {
			$welcomeon = 1;
		} else {
			$welcomeon = 0;
		}
		if( isset( $_POST['secureenable'] ) ) {
			$secureon = 1;
		} else {
			$secureon = 0;
		}
		$query = "UPDATE acc_user SET user_welcome = '$welcomeon', user_welcome_sig = '$sig', user_welcome_template = '$template', user_secure = '$secureon' WHERE user_name = '$sid'";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Preferences updated!<br />\n";
	}
	$sid = sanitize( $_SESSION['user'] );
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid'";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_welcome'] > 0) {
		$welcoming = " checked=\"checked\"";
	} else { $welcoming = ""; }
	if ($row['user_secure'] > 0) {
		$securepref = " checked=\"checked\"";
	} else { $securepref = ""; }
	$sig = " value=\"" . html_entity_decode($row['user_welcome_sig'],ENT_NOQUOTES) . "\"";
	$template = $row['user_welcome_template'];
	echo '<table>';
    echo '<tr><th>Table of Contents</th></tr>';
    echo '<tr><td><a href="#1">Welcome settings</a></td></tr>';
    echo '<tr><td><a href="#2">Change password</a></td></tr>';
    echo '</table>';
    echo '<a name="1"></a><h2>General settings</h2>';
    echo '<form action="acc.php?action=welcomeperf" method="post">';
    echo '<input type="checkbox" name="secureenable"'.$securepref.'/> Enable use of the secure server<br /><br />';
    echo '<input type="checkbox" name="welcomeenable"'.$welcoming.'/> Enable <a href="http://en.wikipedia.org/wiki/User:SQLBot-Hello">SQLBot-Hello</a> welcoming of the users I create<br /><br />';
    echo 'Your signature (wikicode) <input type="text" name="sig" size ="40"'. $sig.'/><br />';
    echo '<i>This would be the same as ~~~ on-wiki. No date, please.</i><br />';
    
    // TODO: clean up into nicer code, rather than coming out of php
    // TODO: Make the register and pref form use same welcome list
    ?>
    <select name="template" size="0">
    <option value="welcome"<?php if($template == "welcone") { echo " selected=\"selected\""; } ?>>{{welcome|user}} ~~~~</option>
    <option value="welcomeg"<?php if($template == "welcomeg") { echo " selected=\"selected\""; } ?>>{{welcomeg|user}} ~~~~</option>
    <option value="w-screen"<?php if($template == "w-screen") { echo " selected=\"selected\""; } ?>>{{w-screen|sig=~~~~}}</option>
    <option value="welcome-personal"<?php if($template == "welcome-personal") { echo " selected=\"selected\""; } ?>>{{welcome-personal|user}} ~~~~</option>
    <option value="werdan7"<?php if($template == "werdan7") { echo " selected=\"selected\""; } ?>>{{User:Werdan7/W}} ~~~~</option>
    <option value="welcomemenu"<?php if($template == "welcomemenu") { echo " selected=\"selected\""; } ?>>{{WelcomeMenu|sig=~~~~}}</option>
    <option value="welcomeicon"<?php if($template == "welcomeicon") { echo " selected=\"selected\""; } ?>>{{WelcomeIcon}} ~~~~</option>
    <option value="welcomeshout"<?php if($template == "welcomeshout") { echo " selected=\"selected\""; } ?>>{{WelcomeShout|user}} ~~~~</option>
    <option value="welcomesmall"<?php if($template == "welcomesmall") { echo " selected=\"selected\""; } ?>>{{WelcomeSmall|user}} ~~~~</option>
    <option value="hopes"<?php if($template == "hopes") { echo " selected=\"selected\""; } ?>>{{Hopes Welcome}} ~~~~</option>
    <option value="welcomeshort"<?php if($template == "welcomeshort") { echo " selected=\"selected\""; } ?>>{{Welcomeshort|user}} ~~~~</option>
    <option value="w-riana"<?php if($template == "w-riana") { echo " selected=\"selected\""; } ?>>{{User:Riana/Welcome|name=user|sig=~~~~}}</option>
    <option value="wodup"<?php if($template == "wodup") { echo " selected=\"selected\""; } ?>>{{User:WODUP/Welcome}} ~~~~</option>
    <option value="williamh"<?php if($template == "williamh") { echo " selected=\"selected\""; } ?>>{{User:WilliamH/Welcome|user}} ~~~~</option>
    <option value="malinaccier"<?php if($template == "malinaccier") { echo " selected=\"selected\""; } ?>>{{User:Malinaccier/Welcome|~~~~}}</option>
    <option value="welcome!"<?php if($template == "welcome!") { echo " selected=\"selected\""; } ?>>{{Welcome!|from=User|ps=~~~~}}</option>
    <option value="laquatique"<?php if($template == "laquatique") { echo " selected=\"selected\""; } ?>>{{User:L'Aquatique/welcome}} ~~~~</option>
    <option value="coffee"<?php if($template == "coffee") { echo " selected=\"selected\""; } ?>>{{User:Coffee/welcome|user|||~~~~}}</option>
	<option value="matt-t"<?php if($template == "matt-t") { echo " selected=\"selected\""; } ?>>{{User:Matt.T/C}} ~~~~</option>
	<option value="roux"<?php if($template == "roux") { echo " selected=\"selected\""; } ?>>{{User:Roux/W}} ~~~~</option>
	<option value="staffwaterboy"<?php if($template == "staffwaterboy") { echo " selected=\"selected\""; } ?>>{{User:Staffwaterboy/Welcome}} ~~~~</option>
	<option value="maedin"<?php if($template == "maedin") { echo " selected=\"selected\""; } ?>>{{User:Maedin/Welcome}} ~~~~</option>
	<option value="chzz"<?php if($template == "chzz") { echo " selected=\"selected\""; } ?>>{{User:Chzz/botwelcome|name=user|sig=~~~~}}</option>
	<option value="phantomsteve"<?php if($template == "phantomsteve") { echo " selected=\"selected\""; } ?>>{{User:Phantomsteve/bot welcome}} ~~~~~</option>
    </select><br /><?php
    echo '<i>If you\'d like more templates added, please <a href="https://jira.toolserver.org/browse/ACC">open a ticket</a>.</i><br />';

	echo <<<HTML
    <input type="submit"/><input type="reset"/>
    </form>
    <a name="2"></a><h2>Change your password</h2>
    <form action="acc.php?action=forgotpw" method="post">
    Your username: <input type="text" name="username"/><br />
    Your e-mail address: <input type="text" name="email"/><br />
    <input type="submit"/><input type="reset"/>
    </form><br />
HTML;


	$skin->displayIfooter();
	die();
}
elseif ($action == "done" && $_GET['id'] != "") {
	// check for valid close reasons
	global $messages, $skin;
	
	if (!isset($_GET['email']) | !($messages->isEmail($_GET['email'])) and $_GET['email'] != 'custom') {
		echo "Invalid close reason";
		$skin->displayIfooter();
		die();
	}
	
	// sanitise this input ready for inclusion in queries
	$gid = sanitize($_GET['id']);
	
	// check the checksum is valid
	if (csvalid($gid, $_GET['sum']) != 1) {
		echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
		$skin->displayIfooter();
		die();
	}
	
	
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	
	// check if an email has already been sent
	if ($row['pend_emailsent'] == "1" && !isset($_GET['override'])) {
		echo "<br />This request has already been closed in a manner that has generated an e-mail to the user, Proceed?<br />\n";
		echo "<a href=\"acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;override=yes&amp;email=" . $_GET['email'] . "\">Yes</a> / <a href=\"acc.php\">No</a><br />\n";
		$skin->displayIfooter();
		die();
	}
	
	global $enableReserving;
	if( $enableReserving ){
		// check the request is not reserved by someone else
		if( $row['pend_reserved'] != 0 && !isset($_GET['reserveoverride']) && $row['pend_reserved'] != $_SESSION['userID'])
		{
			echo "<br />This request is currently marked as being handled by ".$session->getUsernameFromUid($row['pend_reserved']).", Proceed?<br />\n";
			echo "<a href=\"acc.php?".$_SERVER["QUERY_STRING"]."&reserveoverride=yes\">Yes</a> / <a href=\"acc.php\">No</a><br />\n";
			$skin->displayIfooter();
			die();
		}
	}
	
	$gem = sanitize($_GET['email']);
	$sid = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result);
	$gus = sanitize($row2['pend_name']);
	if ($row2['pend_status'] == "Closed") {
		echo "<h2>ERROR</h2>Cannot close this request. Already closed.<br />\n";
		$skin->displayIfooter();
		die();
	}
	
	// custom close reasons
	if ($_GET['email'] == 'custom') {
		if (!isset($_POST['msgbody']) or empty($_POST['msgbody'])) {
			echo "<form action='?".$_SERVER["QUERY_STRING"]."' method='post'>\n";
			echo "<p>Message:</p>\n<textarea name='msgbody' cols='80' rows='25'></textarea>\n";
			echo "<p><input type='checkbox' name='ccmailist' />Cc to mailing list</p>\n";
			echo "<p><input type='submit' value='Close and send' /></p>\n";
			echo "</form>\n";
			$skin->displayIfooter();
			die();
		} else {
			
			$headers = 'From: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
			if (isset($_POST['ccmailist']) && $_POST['ccmailist'] == "on") {
				$headers .= 'Cc: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
			}
			$headers .= 'X-ACC-Request: ' . $gid . "\r\n";
			$headers .= 'X-ACC-UserID: ' . $_SESSION['userID'] . "\r\n";
			
			mail($row2['pend_email'], "RE: English Wikipedia Account Request", $_POST['msgbody'], $headers);
		}
	}
	
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_welcome'] > 0 && $gem == "1") {
		$sig = stripslashes($row['user_welcome_sig']);
		if (!isset($sig)) {
			$sig = "[[User:" . stripslashes($sid) . "|" . stripslashes($sid) . "]] ([[User_talk:" . stripslashes($sid) . "|talk]])";
		}
		$template = $row['user_welcome_template'];
		if (!isset($template)) {
			$template = "welcome";
		}
		$sig = sanitize($sig);
		$query = "INSERT INTO acc_welcome (welcome_uid, welcome_user, welcome_sig, welcome_status, welcome_pend, welcome_template) VALUES ('$sid', '$gus', '$sig', 'Open', '$gid', '$template');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
	}
	$query = "UPDATE acc_pend SET pend_status = 'Closed'";
	if( $enableReserving ){ $query .= ", `pend_reserved` = '0'"; }
	$query .= " WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Closed $gem', '$now');";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	switch ($gem) {
		case 0 :
			$crea = "Dropped";
			break;
		case 1 :
			$crea = "Created";
			break;
		case 2 :
			$crea = "Too Similar";
			break;
		case 3 :
			$crea = "Taken";
			break;
		case 4 :
			$crea = "Username vio";
			break;
		case 5 :
			$crea = "Impossible";
			break;
		case 26:
			$crea = "SUL Taken";
			break;
	}
	if ($gem == 'custom') {
		$crea = "Custom Close";
	}
	$now = explode("-", $now);
	$now = $now['0'] . "-" . $now['1'] . "-" . $now['2'] . ":" . $now['3'] . ":" . $now['4'];
	$accbotSend->send("Request " . $_GET['id'] . " (" . $row2['pend_name'] . ") Marked as 'Done' ($crea) by " . $_SESSION['user'] . " on $now");
	$skin->displayRequestMsg("Request " . $_GET['id'] . " ($gus) marked as 'Done'.<br />");
	$towhom = $row2['pend_email'];
	if ($gem != "0" and $gem != "custom") {
		sendemail($gem, $towhom);
		$query = "UPDATE acc_pend SET pend_emailsent = '1' WHERE pend_id = '" . $_GET['id'] . "';";
		$result = mysql_query($query, $tsSQLlink);
	}
	upcsum($_GET['id']);
	echo defaultpage();
	$skin->displayIfooter();
	die();
}
elseif ($action == "zoom") {
	if (!isset($_GET['id'])) {
		echo "No user specified!<br />\n";		
		$skin->displayIfooter();
		die();
	}
	echo zoomPage($_GET['id']);
	echo "</form>\n";
	$skin->displayIfooter();
	die();
}
elseif ($action == "logout") {
	echo showlogin();
	die("Logged out!\n");
}
elseif ($action == "logs") {
	if(isset($_GET['user'])){
		$filteruserl = " value=\"".$_GET['user']."\"";
		$filteruser = $_GET['user'];
	} else { $filteruserl = ""; $filteruser = "";}
	
	
	
	echo '<h2>Logs</h2>
	<form action="acc.php" method="get">
		<input type="hidden" name="action" value="logs" />
		<table>
			<tr><td>Filter by username:</td><td><input type="text" name="user"'.$filteruserl.' /></td></tr>
			<tr><td>Filter by log action:</td>
				<td>
					<select id="logActionSelect" name="logaction">';
	$logActions = array(
			//  log entry type => display name
				"" => "(All)",
				"Deferred to users" => "Defer to users", 
				"Deferred to admins" => "Defer to account creators", 
				"Suspended" => "User Suspension", 
				"Approved" => "User Approval", 
				"Promoted" => "User Promotion", 
				"Closed 1" => "Request creation", 
				"Closed 3" => "Request taken", 
				"Closed 2" => "Request similarity", 
				"Edited" => "Preference editing", 
				"Closed 5" => "Request marked as invalid", 
				"Closed 4" => "Request Username policy violation", 
				"Banned" => "Ban", 
				"Unbanned" => "Unban", 
				"Closed 0" => "Request Drop", 
				"Closed custom" => "Request Custom close",
				"Declined" => "User Declination", 
				"Blacklist Hit" => "Blacklist hit", 
				"DNSBL Hit" => "DNS Blacklist hit", 
				"Demoted" => "User Demotion", 
				"Renamed" => "User Rename", 
				"Prefchange" => "User Preferences change",
	);
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
	global $enableReserving, $allowDoubleReserving, $skin;
	
	// Check whether reserving is allowed.
	if( $enableReserving ) {	
		// Make sure there is no invlalid characters.
		if (!preg_match('/^[0-9]*$/',$_GET['resid'])) {
			// Notifies the user and stops the script.
			$skin->displayRequestMsg("The request ID supplied is invalid!");
			$skin->displayIfooter();
			die();
		}
		
		// Sanitises the resid for use.
		$request = sanitise($_GET['resid']);
		
		// Formulates and executes SQL query to check if the request exists.
		$query = "SELECT Count(*) FROM acc_pend WHERE pend_id = '$request';";
		$result = mysql_query($query, $tsSQLlink);
		$row = mysql_fetch_row($result);
		
		// The query counted the amount of records with the particular request ID.
		// When the value is zero it is an idication that that request doesnt exist.
		if($row[0]==="0") {
			// Notifies the user and stops the script.
			$skin->displayRequestMsg("The request ID supplied is invalid!");
			$skin->displayIfooter();
			die();
		}
		
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
		
		// Lock the tables to avoid a possible conflict.
		// See the following bug: https://jira.toolserver.org/browse/ACC-101
		mysql_query('LOCK TABLES pend_reserved,acc_pend WRITE;',$tsSQLlink);
		
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
							die('You already have reserved a request. Are you sure you wish to reserve another?<br /><ul><li><a href="'.$_SERVER["REQUEST_URI"].'&confdoublereserve=yes">Yes, reserve this request also</a></li><li><a href="acc.php">No, return to main request interface</a></li></ul>');
						}
						break;
					case "deny":
						// Prevent the user from continuing.
						die('You already have a request reserved!<br /><a href="acc.php">Return to main request interface</a>');
						break;
					case "inform":
						// Tell the user that they already have requests reserved, but let them through anyway..
						echo '<div id="doublereserve-warn">You have multiple requests reserved.</div>';
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
				Die('This request is currently closed. Are you sure you wish to reserve it?<br /><ul><li><a href="'.$_SERVER["REQUEST_URI"].'&confclosed=yes">Yes, reserve this closed request</a></li><li><a href="acc.php">No, return to main request interface</a></li></ul>');			
			}
		}	
		
		// No, lets reserve the request.
		$query = "UPDATE `acc_pend` SET `pend_reserved` = '".$_SESSION['userID']."' WHERE `acc_pend`.`pend_id` = $request LIMIT 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Error reserving request.");
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$request', '".sanitise($_SESSION['user'])."', 'Reserved', '$now');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("Request $request is being handled by " . $session->getUsernameFromUid($_SESSION['userID']));

		// Release the lock on the table.
		mysql_query('UNLOCK TABLES;',$tsSQLlink);
		
		// Decided to use the HTML redirect, because the PHP code results in an error.
		// I know that this breaks the Back button, but currently I dont have another solution.
		// As an alternative one could implement output buffering to solve this problem.
		echo "<meta http-equiv=\"Refresh\" Content=\"0; URL=$tsurl/acc.php?action=zoom&id=$request\">";
		die();
	}	
}
elseif ($action == "breakreserve") {
	global $enableReserving;
	if( $enableReserving ) {
		$request = sanitise($_GET['resid']);
		
		//check request is reserved
		$reservedBy = isReserved($request);
		if( $reservedBy != $_SESSION['userID'] )
			Die("You cannot break ".$session->getUsernameFromUid($reservedBy)."'s reservation");
		$query = "UPDATE `acc_pend` SET `pend_reserved` = '0' WHERE `acc_pend`.`pend_id` = $request LIMIT 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Error unreserving request.");
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$request', '".sanitise($_SESSION['user'])."', 'Unreserved', '$now');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$accbotSend->send("Request $request is no longer being handled.");
		echo defaultpage();
		$skin->displayIfooter();
		die();
	}	
}

elseif ($action == "comment") {
    if( isset($_GET['id']) ) {
        $id = $_GET['id'];
        echo "<h2>Comment on request <a href='acc.php?action=zoom&id=$id'>#$id</a></h2>";
    } else {
        $id = "";
         echo "<h2>Comment on a request</h2>";
    }
    echo "<form action='acc.php?action=comment-add' method='post'>
    Request ID: <input type='text' name='id' value='$id' /> <br />
    Comments:   <input type='text' name='comment' size='75'' /> <br />
    Visibility: <select name='visibility'><option>user</option><option>admin</option</select>
    <input type='submit' value='Submit' />
    </form>";
    $skin->displayIfooter();
	die();
}

elseif ($action == "comment-add") {
    echo "<h2>Adding comment to request " . $_POST['id'] . "...</h2><br />";
    if ((isset($_POST['id'])) && (isset($_POST['id'])) && (isset($_POST['visibility'])) && ($_POST['comment'] != "") && ($_POST['id'] != "")) {
        $id = sanitise($_POST['id']);
        $user = sanitise($_SESSION['user']);
        $comment = sanitise($_POST['comment']);
        $visibility = sanitise($_POST['visibility']);
        $now = date("Y-m-d H-i-s");
        
        // the mysql field name is actually cmt_visability. yes, it's a typo, but I cba to fix it
		$query = "INSERT INTO acc_cmt (cmt_time, cmt_user, cmt_comment, cmt_visability, pend_id) VALUES ('$now', '$user', '$comment', '$visibility', '$id');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result) {
            Die("Query failed: $query ERROR: " . mysql_error()); }
        echo " Comment added Successfully! <br />
        <a href='acc.php?action=zoom&id=$id'>Return to request #$id</a>";
        $botcomment_pvt =  ($visibility == "admin") ? "private " : "";
        $botcomment = $user . " posted a " . $botcomment_pvt . "comment on request " . $id;
        if($visibility != 'admin')
        {
        	$botcomment .= ': ' . $comment;
        }
        $accbotSend->send($botcomment);
    } else {
        echo "ERROR: A required input is missing <br />
        <a href='acc.php'>Return to main</a>";
    }
 $skin->displayIfooter();
 die();
}

elseif ($action == "comment-quick") {
    if ((isset($_POST['id'])) && (isset($_POST['id'])) && (isset($_POST['visibility'])) && ($_POST['comment'] != "") && ($_POST['id'] != "")) {
        $id = sanitise($_POST['id']);
        $user = sanitise($_SESSION['user']);
        $comment = sanitise($_POST['comment']);
        $visibility = sanitise($_POST['visibility']);
        $now = date("Y-m-d H-i-s");

        $query = "INSERT INTO acc_cmt (cmt_time, cmt_user, cmt_comment, cmt_visability, pend_id) VALUES ('$now', '$user', '$comment', '$visibility', '$id');";
        $result = mysql_query($query, $tsSQLlink);
        if (!$result) {
            Die("Query failed: $query ERROR: " . mysql_error());
        }
        $botcomment = $user . " posted a comment on request " . $id . ': ' . $comment;
        $accbotSend->send($botcomment);
        echo zoomPage($id);
        $skin->displayIfooter();
		die();
    }
}
?>