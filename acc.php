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


// Get all the classes.
require_once 'config.inc.php';
require_once 'devlist.php';
require_once 'LogClass.php';
require_once 'functions.php';
require_once 'includes/database.php';
require_once 'includes/offlineMessage.php';
require_once 'includes/messages.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';
require_once 'includes/http.php';

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
$messages = new messages();
$skin     = new skin();
$accbotSend = new accbotSend();
$session = new session();
$date = new DateTime();

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

		// show the sitenotice
        $out = $messages->getSitenotice();

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
			$accbotSend->send( "[Name-Bl-ACR] HIT: $wnbl - " . sanitize($_POST['name']) . " / " . sanitize($_POST['wname']) . " " . sanitize($_SERVER['REMOTE_ADDR']) . " ($host) " . sanitize($_POST['email']) . " " . sanitize($_SERVER['HTTP_USER_AGENT']));
			echo "Unable to create account. Your request has triggered our spam blacklists, please email the mailing list instead.";
			echo "</div>";
			$skin->displayPfooter();
			die( );
		}
	}
	global $enableDnsblChecks;
	if( $enableDnsblChecks == 1) {
		$dnsblcheck = checkdnsbls( $_SERVER['REMOTE_ADDR'] );
		if ( $dnsblcheck['0'] == true ) {
			$cmt = "FROM $ip " . $dnsblcheck['1'];
			$accbotSend->send("[DNSBL-ACR] HIT: " . sanitize($_POST['name']) . " - " . sanitize($_POST['wname']) . " " . sanitize($_SERVER['REMOTE_ADDR']) . " " . sanitize($_POST['email']) . " " . $_SERVER['HTTP_USER_AGENT'] . " $cmt");
			echo "Account not created, please see " . $dnsblcheck['1'];
			echo "</div>";
			$skin->displayPfooter();
			die(  );
		}
	}
	$sregHttpClient = new http();
	$cu_name = rawurlencode( $_REQUEST['wname'] );
	$userblocked = $sregHttpClient->get( "http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$cu_name&format=php" );
	$ub = unserialize( $userblocked );
	if ( isset ( $ub['query']['blocks']['0']['id'] ) ) {
		$message = $messages->getMessage( '9' );
		$skin->displayRequestMsg("ERROR: You are presently blocked on the English Wikipedia<br />");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	$userexist = $sregHttpClient->get( "http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$cu_name&format=php" );
	$ue = unserialize( $userexist );
	foreach ( $ue['query']['users'] as $oneue ) {
		if ( isset($oneue['missing'])) {
			$skin->displayRequestMsg("Invalid On-Wiki username.<br />");
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
			$skin->displayRequestMsg("I'm sorry, you are too new to request an account at the moment.<br />");
			echo "</div>";
			$skin->displayPfooter();
			die();
		}
	}
	// check if user checked the "I have read and understand the interface guidelines" checkbox
	if(!isset($_REQUEST['guidelines'])) {
		$skin->displayRequestMsg("I'm sorry, but you must read <a href=\"http://en.wikipedia.org/wiki/Wikipedia:Request_an_account/Guide\">the interface guidelines</a> before your request may be submitted.<br />");
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
	if ( !isset($user) || !isset($wname) || !isset($pass) || !isset($pass2) || !isset($email) || !isset($conf_revid)|| strlen($email) < 6) {
		echo "<h2>ERROR!</h2>Form data may not be blank.<br />\n";
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
		$skin->displayRequestMsg("ERROR: Invalid E-mail address.<br />");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	if ($_REQUEST['pass'] !== $_REQUEST['pass2']) { // comparing pre-filtered values here, secure as it's just a comparison.
		$skin->displayRequestMsg("Please re-enter passwords. Passwords did not match!<br />");
		echo "</div>";
		$skin->displayPfooter();
		die();
	}
	if(!((string)(int)$conf_revid === (string)$conf_revid)||$conf_revid==""){
		$skin->displayRequestMsg("Please enter the revision id of your confirmation edit in the \"Confirmation diff\" field. The revid is the number after the &diff= part of the URL of a diff. <br />");
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
		$skin->displayRequestMsg("I'm sorry, but that username is in use. Please choose another. <br />");
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
		if ($secureenable == "1") {
			$secure= 1;
		} else {
			$secure = 0;
		}
		$user_pass = authutils::encryptPassword($_REQUEST['pass']); // again, using unfiltered as data processing is done here.
		$query = "INSERT INTO acc_user (user_name, user_email, user_pass, user_level, user_onwikiname, user_secure,user_confirmationdiff) VALUES ('$user', '$email', '$user_pass', 'New', '$wname', '$secure','$conf_revid');";
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
	echo $messages->getMessage(29);
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
	echo '<form action="'.$tsurl.'/acc.php?action=forgotpw" method="post">';
	echo <<<HTML
    Your username: <input type="text" name="username" /><br />
    Your e-mail address: <input type="text" name="email" /><br />
    <input type="submit" /><input type="reset" />
    </form><br />
HTML;
    echo 'Return to <a href="'.$tsurl.'/acc.php">Login</a></div>';

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
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$mailtext = htmlentities($row['mail_text'],ENT_COMPAT,'UTF-8');
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
			$query = "UPDATE acc_emails SET mail_text = '$mtext', mail_desc = '$mdesc', mail_count = mail_count + 1 WHERE mail_id = '$mid';";
			$result = mysql_query( $query, $tsSQLlink );
			if( !$result ) {
				sqlerror(mysql_error(),"Could not update message");
			}
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$mid', '$siuser', 'Edited', '$now');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
			$query = "SELECT mail_desc FROM acc_emails WHERE mail_id = $mid;";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
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
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$mailtext = htmlentities($row['mail_text'],ENT_COMPAT,'UTF-8');
		echo "<h2>Edit message</h2><strong>This is NOT a toy. If you can see this form, you can edit this message. <br />WARNING: MISUSE OF THIS FUNCTION WILL RESULT IN LOSS OF ACCESS.</strong><br />\n<form action=\"$tsurl/acc.php?action=messagemgmt&amp;edit=$mid&amp;submit=1\" method=\"post\"><br />\n";
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
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Mail messages</h2>\n";
	echo "<ul>\n";
	while ( list( $mail_id, $mail_count, $mail_desc ) = mysql_fetch_row( $result ) ) {
		$out = "<li>$mail_id) <small>[ $mail_desc ] <a href=\"$tsurl/acc.php?action=messagemgmt&amp;edit=$mail_id\">Edit!</a> - <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$mail_id\">View!</a></small></li>";
		$out2 = "<li>$mail_id) <small>[ $mail_desc ] <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$mail_id\">View!</a></small></li>";
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
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Public Interface messages</h2>\n";
	echo "<ul>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li>$mailn) <small>[ $maild ] <a href=\"$tsurl/acc.php?action=messagemgmt&amp;edit=$mailn\">Edit!</a> - <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		$out2 = "<li>$mailn) <small>[ $maild ] <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
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
		sqlerror("Query failed: $query ERROR: " . mysql_error());

	echo "<h2>Internal Interface messages</h2>\n";
	echo "<ul>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li>$mailn) <small>[ $maild ] <a href=\"$tsurl/acc.php?action=messagemgmt&amp;edit=$mailn\">Edit!</a> - <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		$out2 = "<li>$mailn) <small>[ $maild ] <a href=\"$tsurl/acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
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
elseif ($action == "sban") {
	
	// Checks whether the current user is an admin.
	if(!$session->hasright($_SESSION['user'], "Admin")) {
		die("Only administrators or checkusers may ban users");
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
	switch( $_POST[ 'type' ] ) {
		case 'IP':
			if( ip2long( $_POST[ 'target' ] ) === false ) {
				echo '<h2>ERROR</h2><br />Invalid target specified.  Expecting IP address.';
				$skin->displayIfooter();
				die();
			}
			global $squidIpList;
			if( in_array( $_POST[ 'target' ], $squidIpList ) ) {
				echo '<h2>ERROR</h2><br />Invalid target specified. You\'re trying to block the toolserver!';
				$skin->displayIfooter();
				die();
			}
			break;

		case 'Name':
			if( preg_match( '/[\#\/\|\[\]\{\}\@\%\:\<\>]/', $_POST[ 'target' ] ) ) {
				echo '<h2>ERROR</h2><br />Invalid target specified.  Expecting user name.';
				$skin->displayIfooter();
				die();
			}
			break;

		case 'EMail':
			if( !preg_match( ';^(?:[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[A-Za-z0-9-]*[A-Za-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$;', $_POST['target'] ) ) {
				echo '<h2>ERROR</h2><br />Invalid target specified.  Expecting E-mail address.';
				$skin->displayIfooter();
				die();
			}
			break;

		default:
			echo '<h2>ERROR</h2><br />Invalid type specified.  Expecting IP, Name, or EMail.';
			$skin->displayIfooter();
			die();
	}
	$reason = sanitize($_POST['banreason']);
	$siuser = sanitize($_SESSION['user']); 
	$target = sanitize($_POST['target']);
	$type = sanitize($_POST['type']);
	$now = date("Y-m-d H-i-s");
	upcsum($target);
	$query = "SELECT * FROM acc_ban WHERE ban_type = '$type' AND ban_target = '$target' AND (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if($row['ban_id'] != "") {
		$skin->displayRequestMsg("The specified target is already banned!");
		$skin->displayIfooter();
		die();
	}
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Banned', '$now', '$reason');";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$query = "INSERT INTO acc_ban (ban_type, ban_target, ban_user, ban_reason, ban_date, ban_duration) VALUES ('$type', '$target', '$siuser', '$reason', '$now', $duration);";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	echo "Banned " . htmlentities($_POST['target'],ENT_COMPAT,'UTF-8') . " for $reason<br />\n";
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
		die("Only administrators or checkusers may unban users");
	}
	$bid = sanitize($_GET['id']);
	$query = "SELECT * FROM acc_ban WHERE ban_id = '$bid' AND (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
	{
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	}
	$row = mysql_fetch_assoc($result);
	if( $row['ban_id'] == "") {
		$skin->displayRequestMsg("The specified target is not banned!");
		$skin->displayIfooter();
		die();
	}
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
			$query = "UPDATE acc_ban SET ban_active = 0 WHERE ban_id = '$bid'";
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
			$accbotSend->send("Ban #" . $bid . " ($iTarget) unbanned by " . $_SESSION['user']. " ($unbanreason)");
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
		echo "<form METHOD=\"post\" ACTION=\"$tsurl/acc.php?action=unban&id=". $bid ."&confirmunban=true\">";
		echo "<input type=\"text\" name=\"unbanreason\"/><input type=\"submit\"/></form><br />";
		echo "<a href=\"$tsurl/acc.php\">Cancel</a>";
		
	}
}
elseif ($action == "ban") {
	$siuser = sanitize($_SESSION['user']);
	if (isset ($_GET['ip']) || isset ($_GET['email']) || isset ($_GET['name'])) {
		if(!$session->hasright($_SESSION['user'], "Admin"))
			die("Only administrators or checkusers may ban users");
		if (isset($_GET['ip'])) {
			$ip2 = sanitize($_GET['ip']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$ip2';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_ip'];
			$type = "IP";
		}
		elseif (isset($_GET['email'])) {
			$email2 = sanitize($_GET['email']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$email2';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_email'];
			$type = "EMail";
		}
		elseif (isset($_GET['name'])) {
			$name2 = sanitize($_GET['name']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$name2';";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				sqlerror("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_name'];
			$type = "Name";
		}
		$target = sanitize($target);
		$query = "SELECT * FROM acc_ban WHERE ban_target = '$target' AND (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['ban_id'] != "") {
			echo "<h2>ERROR</h2>\n<br />\nCould not ban. Already banned!<br />";
			$skin->displayIfooter();
			die();
		} else {
			echo "<h2>Ban an IP, Name or E-Mail</h2>\n";
			echo "<form action=\"$tsurl/acc.php?action=sban&amp;user=$siuser\" method=\"post\">";
			echo "Ban target: $target\n<br />\n";
			echo "<table><tr><td>Reason:</td><td><input type=\"text\" name=\"banreason\" /></td></tr>\n";
			echo "<tr><td>Duration:</td><td>\n";
			echo "<select name=\"duration\">\n";
			echo "<option value=\"-1\">Indefinite</option>\n";
			echo "<option value=\"86400\">24 Hours</option>\n";
			echo "<option value=\"604800\">One Week</option>\n";
			echo "<option value=\"2629743\">One Month</option>\n";
			echo "<option value=\"other\">Other</option>\n";
			echo "</select></td></tr>\n";
			/* TODO: Add some fancy javascript that hides this until the user selects other from the menu above */
			echo "<tr><td>Other:</td><td><input type=\"text\" name=\"otherduration\" /></td></tr>";
			echo "</table><br />\n";
			echo "<input type=\"submit\" /><input type=\"hidden\" name=\"target\" value=\"$target\" /><input type=\"hidden\" name=\"type\" value=\"$type\" /></form>\n";
			$skin->displayIfooter();
			die();
		}
	} else {
		echo "<h2>Active Ban List</h2>\n<table border='1'>\n";
		echo "<tr><th>Type</th><th>IP/Name/Email</th><th>Banned by</th><th>Reason</th><th>Time</th><th>Expiry</th>";
		$isAdmin = $session->hasright($_SESSION['user'], "Admin");
		if ($isAdmin) {
			echo "<td>Unban</td>";
		}
		echo "</tr>";
		$query = "SELECT * FROM acc_ban WHERE (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		while ($row = mysql_fetch_assoc($result)) {
			if ( !isset($row['ban_duration']) || $row['ban_duration'] == "-1") {
				$until = "Indefinite";
			} else {
				$until = date("F j, Y, g:i a", $row['ban_duration']);
			}
			echo "<tr><td>" . htmlentities($row['ban_type'],ENT_COMPAT,'UTF-8') . '</td>';
			switch($row['ban_type'])
			{
				case "IP":
					echo '<td>';
					if ($isAdmin) { 
						echo '<a href="' . $tsurl . '/search.php?term='.$row['ban_target'].'&amp;type=IP">';
					}
					echo $row['ban_target'];
					if ($isAdmin) { 
						echo '</a>';
					}
					echo '</td>';
					break;
				case "EMail":
					echo '<td>';
					echo '<a href="' . $tsurl . '/search.php?term='.$row['ban_target'].'&amp;type=email">';
					echo $row['ban_target'];
					echo '</a>';
					echo '</td>';
					break;
				case "Name";
					echo '<td><a href="' . $tsurl . '/search.php?term='.$row['ban_target'].'&amp;type=Request">'.$row['ban_target'].'</a></td>';
					break;
				default:
					echo '<td>'.$row['ban_target'].'</td>';
					break;
			}
			echo "<td>".$row['ban_user']."</td><td>".$row['ban_reason']."</td><td>".$row['ban_date']."</td><td>$until</td>";
			if ($isAdmin) {
				echo "<td><a href=\"$tsurl/acc.php?action=unban&amp;id=" . $row['ban_id'] . "\">Unban</a></td>";
			}
			echo "</tr>";
		}
		echo "</table>\n";
		if ($isAdmin) {
			echo "<h2>Ban an IP, Name or E-Mail</h2>\n";
			echo "<form action=\"$tsurl/acc.php?action=sban\" method=\"post\">";
			echo "<table>";
			echo "<tr><td>Ban target:</td><td><input type=\"text\" name=\"target\" /></td></tr>\n";
			echo "<tr><td>Reason:</td><td><input type=\"text\" name=\"banreason\" /></td></tr>\n";
			echo "<tr><td>Duration:</td><td>\n";
			echo "<select name=\"duration\">\n";
			echo "<option value=\"-1\">Indefinite</option>\n";
			echo "<option value=\"86400\">24 Hours</option>\n";
			echo "<option value=\"604800\">One Week</option>\n";
			echo "<option value=\"2629743\">One Month</option>\n";
			echo "<option value=\"other\">Other</option>\n";
			echo "</select></td></tr>\n";
			/* TODO: Add some fancy javascript that hides this until the user selects other from the menu above */
			echo "<tr><td>Other:</td><td><input type=\"text\" name=\"otherduration\"/></td></tr>";
			echo "<tr><td>Type:</td><td>\n";
 			echo "<select name=\"type\"><option value=\"IP\">IP</option><option value=\"Name\">Name</option><option value=\"EMail\">E-Mail</option></select>\n";
 			echo "</td></tr>\n";
			echo "</table><br />\n";
			echo "<input type=\"submit\"/></form>\n";
		}
		$skin->displayIfooter();
		die();
	}
}
elseif ($action == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") {
	global $availableRequestStates;
	$target = sanitize($_GET['target']);
	
	if (array_key_exists($target, $availableRequestStates)) {
			
		
			
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
elseif ($action == "welcomeperf" || $action == "prefs") { //Welcomeperf is deprecated, but to avoid conflicts, include it still.
	if (isset ($_POST['sig'])) {
		$sig = sanitize($_POST['sig']);
		$sid = sanitize($_SESSION['user']);
		if( isset( $_POST['secureenable'] ) ) {
			$secureon = 1;
		} else {
			$secureon = 0;
		}
		if( isset( $_POST['abortpref'] ) ) {
			$abortprefOld = 1;
		} else {
			$abortprefOld= 0;
		}
		if( isset( $_POST['email'] ) ) {
			$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$/i', $_POST['email']);
			if ($mailisvalid == 0) {
				$skin->displayRequestMsg("ERROR: Invalid E-mail address.<br />");
			}
			else {
				$newemail = sanitize($_POST['email']);
			}
		}
		if (isset($newemail)) {
		$query = "UPDATE acc_user SET user_welcome_sig = '$sig', user_secure = '$secureon', user_abortpref = '$abortprefOld', user_email = '$newemail' WHERE user_name = '$sid'";
		}
		else {
			$query = "UPDATE acc_user SET user_welcome_sig = '$sig', user_secure = '$secureon', user_abortpref = '$abortprefOld' WHERE user_name = '$sid'";
		}
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error());
		echo "Preferences updated!<br />\n";
	}
	$sid = sanitize( $_SESSION['user'] );
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid'";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_secure'] > 0) {
		$securepref = " checked=\"checked\"";
	} else { $securepref = ""; }
	$sig = " value=\"" . $row['user_welcome_sig'] . "\"";
	$abortpref= " checked=\"checked\"";
	if(array_key_exists('user_abortpref',$row)){
		if($row['user_abortpref']==0){
			$abortpref= "";
		}
	}
	$useremail = " value=\"" . htmlentities($row['user_email']) . "\"";
    echo '<h2>General settings</h2>';
    echo '<form action="'.$tsurl.'/acc.php?action=welcomeperf" method="post">';
    echo '<input type="checkbox" name="secureenable"'.$securepref.'/> Enable use of the secure server<br /><br />';
    echo 'Your signature (wikicode).<input type="text" name="sig" size ="40"'. $sig.'/>';
    echo '<i>This would be the same as ~~~ on-wiki. No date, please.</i><br /><br />';
	//Preference used in functions.php:
    echo '<input type="checkbox" name="abortpref"'.$abortpref.'/> Don\'t ask to double check before closing requests (requires Javascript)<br /><br />';
    echo 'Your Email address: <input type="text" name="email" size ="40"'. $useremail .'/><br />';
    echo 'Your on-wiki username: ' . htmlentities($row['user_onwikiname']) . '<br />' ;
    // TODO: clean up into nicer code, rather than coming out of php
	echo <<<HTML
    <input type="submit"/><input type="reset"/>
    </form>
    <h2>Change your password</h2>
HTML;
    echo '<form action="'.$tsurl.'/acc.php?action=changepassword" method="post">';
	echo <<<HTML
    Your old password: <input type="password" name="oldpassword"/><br />
    Your new password: <input type="password" name="newpassword"/><br />
    Confirm new password: <input type="password" name="newpasswordconfirm"/><br />
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
	$gem = sanitize($_GET['email']);
	
	// check the checksum is valid
	if (csvalid($gid, $_GET['sum']) != 1) {
		echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
		$skin->displayIfooter();
		die();
	}
	
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	$rows = mysql_num_rows($result);
	
	if ($rows < 1) {
		$skin->displayRequestMsg("Invalid Request ID.");
		$skin->displayIfooter();
		die();
	}
	
	// check if an email has already been sent
	if ($row['pend_emailsent'] == "1" && !isset($_GET['override']) && $gem != 0) {
		echo "<br />This request has already been closed in a manner that has generated an e-mail to the user, Proceed?<br />\n";
		echo "<a href=\"$tsurl/acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;override=yes&amp;email=" . $_GET['email'] . "\">Yes</a> / <a href=\"$tsurl/acc.php\">No</a><br />\n";
		$skin->displayIfooter();
		die();
	}
	
	
	
	// check the request is not reserved by someone else
	if( $row['pend_reserved'] != 0 && !isset($_GET['reserveoverride']) && $row['pend_reserved'] != $_SESSION['userID'])
	{
		echo "<br />This request is currently marked as being handled by ".$session->getUsernameFromUid($row['pend_reserved']).", Proceed?<br />\n";
		echo "<a href=\"$tsurl/acc.php?".$_SERVER["QUERY_STRING"]."&reserveoverride=yes\">Yes</a> / <a href=\"$tsurl/acc.php\">No</a><br />\n";
		$skin->displayIfooter();
		die();
	}
	
	
	$sid = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result);
	$gus = sanitize($row2['pend_name']);
	if ($row2['pend_status'] == "Closed") {
		echo "<h2>ERROR</h2>Cannot close this request. Already closed.<br />\n";
		$skin->displayIfooter();
		die();
	}
	
	// Checks whether the username is already in use on Wikipedia.
	$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=" . urlencode($pend2['user_name']) . "&format=php");
	$ue = unserialize($userexist);
	if (!isset ($ue['query']['users']['0']['missing'])) {
		$exists = 1;
	}
	else {
		$exists = 0;
	}
	
	// check if a request being created does not already exist. 
	if ($gem == 1 && $exists == 0 && !isset($_GET['createoverride'])) {
		echo "<br />You have chosen to mark this request as \"created\", but the account does not exist on the English Wikipedia, proceed?  <br />\n";
		echo "<a href=\"$tsurl/acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;createoverride=yes&amp;email=" . $_GET['email'] . "\">Yes</a> / <a href=\"$tsurl/acc.php\">No</a><br />\n";
		$skin->displayIfooter();
		die();
	}
	
	// custom close reasons
	if ($gem  == 'custom') {
		if (!isset($_POST['msgbody']) or empty($_POST['msgbody'])) {
			$querystring = htmlspecialchars($_SERVER["QUERY_STRING"],ENT_COMPAT,'UTF-8'); //Send it through htmlspecialchars so HTML validators don't complain. 
			echo "<form action='?".$querystring."' method='post'>\n";
			echo "<p>Please enter your message to the user below.</p>";
			echo "<p><strong>Please note that this content will be sent as the entire body of an email to the user, so remember to close the email properly with a signature (not ~~~~ either).</strong></p>";
			echo "\n<textarea name='msgbody' cols='80' rows='25'></textarea>\n";
			echo "<p><input type='checkbox' name='created' />Account created</p>\n";
			echo "<p><input type='checkbox' name='ccmailist' checked='checked'";
			if (!($session->hasright($_SESSION['user'], "Admin")))
				echo " DISABLED";
			echo "/>Cc to mailing list</p>\n";
			echo "<p><input type='submit' value='Close and send' /></p>\n";
			echo "</form>\n";
			$skin->displayIfooter();
			die();
		} else {
			
			$headers = 'From: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
			if (!($session->hasright($_SESSION['user'], "Admin")) || isset($_POST['ccmailist']) && $_POST['ccmailist'] == "on")
				$headers .= 'Cc: accounts-enwiki-l@lists.wikimedia.org' . "\r\n";
			$headers .= 'X-ACC-Request: ' . $gid . "\r\n";
			$headers .= 'X-ACC-UserID: ' . $_SESSION['userID'] . "\r\n";
			
			mail($row2['pend_email'], "RE: [ACC #$gid] English Wikipedia Account Request", $_POST['msgbody'], $headers);
			
			$query = "UPDATE acc_pend SET pend_emailsent = '1' WHERE pend_id = '" . $gid . "';";
			$result = mysql_query($query, $tsSQLlink);
		
			if (isset($_POST['created']) && $_POST['created'] == "on") {
				$gem  = 'custom-y';
			} else {
				$gem  = 'custom-n';
			}
		}
	}
	
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
	$query = "UPDATE acc_pend SET pend_status = 'Closed'";
	$query .= ", `pend_reserved` = '0'";
	$query .= " WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$gid', '$sid', 'Closed $gem', '$now', " . (isset($_POST['msgbody']) ? ("'" . sanitize($_POST['msgbody']) . "'") : "''") . ");";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error());
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
		case 30:
			$crea = "Password Reset";
			break;
	}
	if ($gem == 'custom') {
		$crea = "Custom";
	} else if ($gem == 'custom-y') {
		$crea = "Custom, Created";
	} else if ($gem == 'custom-n') {
		$crea = "Custom, Not Created";
	}
	$now = explode("-", $now);
	$now = $now['0'] . "-" . $now['1'] . "-" . $now['2'] . ":" . $now['3'] . ":" . $now['4'];
	$accbotSend->send("Request " . $_GET['id'] . " (" . $row2['pend_name'] . ") Marked as 'Done' ($crea) by " . $_SESSION['user'] . " on $now");
	$skin->displayRequestMsg("Request " . $_GET['id'] . " (" . htmlentities($row2['pend_name'],ENT_COMPAT,'UTF-8') . ") marked as 'Done'.<br />");
	$towhom = $row2['pend_email'];
	if ($gem != "0" && $gem != 'custom' && $gem != 'custom-y' && $gem != 'custom-n') {
		sendemail($gem, $towhom, $gid);
		$query = "UPDATE acc_pend SET pend_emailsent = '1' WHERE pend_id = '" . $gid . "';";
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
	if (isset($_GET['hash'])) {
		$urlhash = $_GET['hash'];
	}
	else {
		$urlhash = "";
	}
	echo zoomPage($_GET['id'],$urlhash);
	$skin->displayIfooter();
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
				"Closed 1" => "Request creation",
				"Closed 3" => "Request taken",
				"Closed 2" => "Request similarity",
				"Closed 5" => "Request marked as invalid",
				"Closed 4" => "Request Username policy violation",
				"Closed 0" => "Request drop",
				"Closed 26" => "Request taken in SUL",
				"Closed 30" => "Request closed, password reset",
				"Closed custom" => "Request custom close",
				"Closed custom-y" => "Request custom close, created",
				"Closed custom-n" => "Request custom close, not created",
				"Email Confirmed" => "Email confirmed reservation",
				"Blacklist Hit" => "Blacklist hit", 
				"DNSBL Hit" => "DNS Blacklist hit",
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
				"Prefchange" => "User preferences change"
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
	global $allowDoubleReserving, $skin;
	
	
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
	// See the following bug: https://jira.toolserver.org/browse/ACC-101
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

	$request = sanitise($_GET['resid']);
	
	//check request is reserved
	$reservedBy = isReserved($request);
	if( $reservedBy == "" ) {
		$skin->displayRequestMsg("Request is not reserved, or request ID is invalid.");
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
        $id = sanitize($_GET['id']);
        echo "<h2>Comment on request <a href='$tsurl/acc.php?action=zoom&amp;id=$id&amp;hash=$urlhash'>#$id</a></h2>
              <form action='$tsurl/acc.php?action=comment-add&amp;hash=$urlhash' method='post'>";
    } else {
        $id = "";
        echo "<h2>Comment on a request</h2>
              <form action='$tsurl/acc.php?action=comment-add' method='post'>";
    }
    echo "
    Request ID: <input type='text' name='id' value='$id' /> <br />
    Comments:   <input type='text' name='comment' size='75' /> <br />
    Visibility: <select name='visibility'><option>user</option><option>admin</option></select>
    <input type='submit' value='Submit' />
    </form>";
    $skin->displayIfooter();
	die();
}

elseif ($action == "comment-add") {
	$id = sanitise($_POST['id']); //TODO: We need to do better than just sanitise it, we also need to check that the request id is actually valid. 
    echo "<h2>Adding comment to request " . $id . "...</h2><br />";
    if ((isset($_POST['id'])) && (isset($_POST['id'])) && (isset($_POST['visibility'])) && ($_POST['comment'] != "") && ($_POST['id'] != "")) {
        $user = sanitise($_SESSION['user']);
        $comment = sanitise($_POST['comment']);
        $visibility = sanitise($_POST['visibility']);
        $now = date("Y-m-d H-i-s");
        
        if (isset($_GET['hash'])) {
		$urlhash = sanitise($_GET['hash']);
	    }
	    else {
		$urlhash = "";
	    }
        
        // the mysql field name is actually cmt_visability. yes, it's a typo, but I cba to fix it
		$query = "INSERT INTO acc_cmt (cmt_time, cmt_user, cmt_comment, cmt_visability, pend_id) VALUES ('$now', '$user', '$comment', '$visibility', '$id');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result) {
            sqlerror("Query failed: $query ERROR: " . mysql_error()); }
        echo " Comment added Successfully! <br />
        <a href='$tsurl/acc.php?action=zoom&amp;id=$id&amp;hash=$urlhash'>Return to request #$id</a>";
        $botcomment_pvt =  ($visibility == "admin") ? "private " : "";
        $botcomment = $user . " posted a " . $botcomment_pvt . "comment on request " . $id;

        $accbotSend->send($botcomment);
    } else {
        echo "ERROR: A required input is missing <br />
        <a href='$tsurl/acc.php'>Return to main</a>";
    }
 $skin->displayIfooter();
 die();
}

elseif ($action == "comment-quick") {
    if ((isset($_POST['id'])) && (isset($_POST['id'])) && (isset($_POST['visibility'])) && ($_POST['id'] != "")) {

        $id = sanitise($_POST['id']);
        $user = sanitise($_SESSION['user']);
        $comment = sanitise($_POST['comment']);
        $visibility = sanitise($_POST['visibility']);
        $now = date("Y-m-d H-i-s");
	if($_POST['comment'] == ""){
		header("Location: acc.php?action=zoom&id=".$id);
		die();
	}
        $query = "INSERT INTO acc_cmt (cmt_time, cmt_user, cmt_comment, cmt_visability, pend_id) VALUES ('$now', '$user', '$comment', '$visibility', '$id');";
        $result = mysql_query($query, $tsSQLlink);
        if (!$result) {
            sqlerror("Query failed: $query ERROR: " . mysql_error());
        }
        $botcomment = $user . " posted a comment on request " . $id;
        $accbotSend->send($botcomment);

	header("Location: acc.php?action=zoom&id=".$id);

	die();
    }
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
elseif ($action == "ec") { // edit comment
	if(!isset($_GET['id']) || !( !is_int($_GET['id']) ? (ctype_digit($_GET['id'])) : true ) ) {
		// Only using die("Message"); for errors looks ugly.
		$skin->displayRequestMsg("No comment found.");
		$skin->displayIfooter();
		die();
	}
	
	$result = mysql_query("SELECT * FROM acc_cmt WHERE cmt_id = '" . sanitize($_GET['id']) . "';");
	$row = mysql_fetch_assoc($result);
	
	if($row==false) {
		$skin->displayRequestMsg("No comment found.");
		$skin->displayIfooter();
		die();
	}
	
	// Unauthorized if user is not an admin or the user who made the comment being edited.
	if(!$session->hasright($_SESSION['user'], "Admin") && $row['cmt_user'] != $_SESSION['user']) { 
		$skin->displayRequestMsg("Unauthorized.");
		$skin->displayIfooter();
		die();
	}
	
	// get[id] is safe by this point.
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		mysql_query("UPDATE acc_cmt SET cmt_comment = \"".mysql_real_escape_string($_POST['newcomment'],$tsSQLlink)."\", cmt_visability = \"".mysql_real_escape_string($_POST['visability'],$tsSQLlink)."\" WHERE cmt_id = \"".sanitize($_GET['id'])."\" LIMIT 1;");
		$now = date("Y-m-d H-i-s");
		mysql_query("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('".sanitize($_GET['id'])."', '".sanitize($_SESSION['user'])."', 'EditComment-c', '$now');");
		mysql_query("INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('".sanitize($row["pend_id"])."', '".sanitize($_SESSION['user'])."', 'EditComment-r', '$now');");
		$accbotSend->send("Comment " . $_GET['id'] . " edited by " . sanitize($_SESSION['user']));
		//Show user confirmation that the edit has been saved, and redirect them to the request after 5 seconds.
		header("Refresh:5;URL=$tsurl/acc.php?action=zoom&id=".$row['pend_id']);
		$skin->displayRequestMsg("Comment has been saved successfully. You will be redirected to the request in 5 seconds.<br /><br />\n
		Click <a href=\"".$tsurl."/acc.php?action=zoom&id=".$row['pend_id']."\">here</a> if you are not redirected.");
		$skin->displayIfooter();
		die();
	}
	else {	
		echo "<h2>Edit comment #".$_GET['id']."</h2>"; 
		global $tsurl;
		echo "<form method=\"post\">\n";
		echo "<strong>Time:</strong>&nbsp;" . $row['cmt_time'] . "<br />\n";
		echo "<strong>Author:</strong>&nbsp;" . $row['cmt_user'] . "<br />\n";
		echo "<strong>Security:</strong>&nbsp;<select name = \"visability\">\n";
	    if ( $row['cmt_visability'] == "user") {
	    	echo "<option value=\"user\" selected>User</option>\n";
	    	echo "<option value = \"admin\">Admin</option>\n";
	    }
	    else {
	    	echo "<option value = \"user\">User</option>\n";
	    	echo "<option value = \"admin\" selected>Admin</option>\n";
	    }
	    echo "</select><br />\n";
		echo "<strong>Request:</strong>&nbsp;<a href=\"".$tsurl."/acc.php?action=zoom&id=".$row['pend_id']."\">#" . $row['pend_id'] . "</a><br />";
		
		echo "<strong>Old text:</strong><pre>".$row['cmt_comment']."</pre>";
		
		echo "<input type=\"text\" size=\"100\" name=\"newcomment\" value=\"".htmlentities($row['cmt_comment'],ENT_COMPAT,'UTF-8')."\" />";
		echo "<input type=\"submit\" />";
		echo "</form>";
			
		$skin->displayIfooter();
		die();
	}
}

/*
 * Commented out by stw:
 *  a) wrong. Check the code in the bot to figure out what will actually happen.
 *  b) this will likely increase the amount of time taken for decent investigation to start.
 *  
 *  Please see note on JIRA: ACC-161
 * ******************************************
 * //Silence the bot when it gets annoying
 * elseif ($action == "silence") { 
 *	$accbotSend->send("Bot inactivity warning silenced by " . $session->getUsernameFromUid($_SESSION['userID']));
 *	echo '<p>The bot has been sent a message that will silence it for one hour.</p>';
 *	$skin->displayIfooter();
 *	die();
 *}
 */
?>
