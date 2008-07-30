<?php


/**************************************************************
** English Wikipedia Account Request Interface               **
** Wikipedia Account Request Graphic Design by               **
** Charles Melbye is licensed under a Creative               **
** Commons Attribution-Noncommercial-Share Alike             **
** 3.0 United States License. All other code                 **
** released under Public Domain by the ACC                   **
** Development Team.                                         **
**             Developers:                                   **
**  SQL ( http://en.wikipedia.org/User:SQL )                 **
**  Cobi ( http://en.wikipedia.org/User:Cobi )               **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye )          **
**FastLizard4 ( http://en.wikipedia.org/User:FastLizard4 )   **
**Stwalkerster ( http://en.wikipedia.org/User:Stwalkerster ) **
**Soxred93 ( http://en.wikipedia.org/User:Soxred93)          **
**Alexfusco5 ( http://en.wikipedia.org/User:Alexfusco5)      **
**OverlordQ ( http://en.wikipedia.org/wiki/User:OverlordQ )  **
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**			                                           **
**************************************************************/

require_once ( 'config.inc.php' );
require_once ( 'devlist.php' );
require_once ( 'functions.php' );
ini_set( 'session.cookie_path', $cookiepath );
ini_set( 'session.name', $sessionname );
$version = "0.9.7";

$link = mysql_connect( $toolserver_host, $toolserver_username, $toolserver_password );
if ( !$link ) {
	die( 'Could not connect: ' . mysql_error( ) );
}
@ mysql_select_db( $toolserver_database ) or print mysql_error( );
session_start( );

$action = '';
if ( isset ( $_GET['action'] ) ) {
	$action = $_GET['action'];
}

if ( !isset ( $_SESSION['user'] ) && !isset ( $_GET['nocheck'] ) ) {
	$suser = '';
	echo makehead( $suser );
	if ( $action != 'register' && $action != 'forgotpw' && $action != 'sreg' ) {
		echo showlogin( );
		die( );
	}
}
elseif ( !isset ( $_GET['nocheck'] ) ) {
	echo makehead( $_SESSION['user'] );
	checksecurity( $_SESSION['user'] );
	$out = showmessage( '20' );
	$out .= "<div id=\"content\">";
	echo $out;
}

if ( $action == '' ) {
	echo defaultpage( );
}
elseif ( $action == "sreg" ) {
	$suser = sanitize( $_SESSION['user'] );
	foreach ( $acrnamebl as $wnbl => $nbl ) {
		$phail_test = @ preg_match( $nbl, $_POST['name'] );
		if ( $phail_test == TRUE ) {
			#$message = showmessage(15);
			echo "$message<br />\n";
			$target = "$wnbl";
			$host = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
			$fp = fsockopen( "udp://127.0.0.1", 9001, $erno, $errstr, 30 );
			fwrite( $fp, "[Name-Bl-ACR] HIT: $wnbl - " . $_POST['name'] . " / " . $_POST['wname'] . " " . $_SERVER['REMOTE_ADDR'] . " ($host) " . $_POST['email'] . " " . $_SERVER['HTTP_USER_AGENT'] . "\r\n" );
			fclose( $fp );
			echo "Account created!<br /><br />\n";
			die( );
		}
	}
	$dnsblcheck = checkdnsbls( $_SERVER['REMOTE_ADDRR'] );
	if ( $dnsblcheck['0'] == true ) {
		$cmt = "FROM $ip " . $dnsblcheck['1'];
		$fp = fsockopen( "udp://127.0.0.1", 9001, $erno, $errstr, 30 );
		fwrite( $fp, "[DNSBL-ACR] HIT: " . $_POST['name'] . " - " . $_POST['wname'] . " " . $_SERVER['REMOTE_ADDR'] . " " . $_POST['email'] . " " . $_SERVER['HTTP_USER_AGENT'] . " $cmt\r\n" );
		fclose( $fp );
		die( "Account not created, please see " . $dnsblcheck['1'] );
	}
	$cu_name = urlencode( $_REQUEST['wname'] );
	$userblocked = file_get_contents( "http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$cu_name&format=php" );
	$ub = unserialize( $userblocked );
	if ( isset ( $ub['query']['blocks']['0']['id'] ) ) {
		$message = showmessage( '9' );
		echo "ERROR: You are presently blocked on the English Wikipedia<br />\n";
		$fail = 1;
	}
	$userexist = file_get_contents( "http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$cu_name&format=php" );
	$ue = unserialize( $userexist );
	foreach ( $ue['query']['users']['0'] as $oneue ) {
		if ( $oneue['missing'] == "" ) {
			echo "Invalid On-Wiki username.<br />\n";
			$fail = 1;
		}
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
	$welcomeenable = mysql_real_escape_string($_REQUEST['welcomeenable']);
	if ($user == "" || $wname == "" || $pass == "" || $pass2 == "" || $email == "" || strlen($email) < 6) {
		echo "<h2>ERROR!</h2>Form data may not be blank.<br />\n";
		echo showfooter();
		die();
	}
	if ($_POST['debug'] == "on") {
		echo "<pre>\n";
		print_r($_REQUEST);
		echo "</pre>\n";
	}
	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$/i', $_REQUEST['email']);
	if ($mailisvalid == 0) {
		echo "ERROR: Invalid E-mail address.<br />\n";
		$fail = 1;
	}
	if ($pass != $pass2) {
		echo "Passwords did not match!<br />\n";
		$fail = 1;
	}
	$query = "SELECT * FROM acc_user WHERE user_name = '$user' LIMIT 1;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		echo "I'm sorry, but that username is in use. Please choose another. <br />\n";
		$fail = 1;
	}
	$query = "SELECT * FROM acc_user WHERE user_email = '$email' LIMIT 1;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		echo "I'm sorry, but that e-mail address is in use.<br />\n";
		$fail = 1;
	}
	$query = "SELECT * FROM acc_user WHERE user_onwikiname = '$wname' LIMIT 1;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] != "") {
		echo "I'm sorry, but $wname already has an account here.<br />\n";
		$fail = 1;
	}
	if ($fail != 1) {
		if ($welcomeenable == "1") {
			$welcome = 1;
		} else {
			$welcome = 0;
		}
		$user_pass = md5($pass);
		$query = "INSERT INTO acc_user (user_name, user_email, user_pass, user_level, user_onwikiname, user_welcome, user_welcome_sig, user_welcome_template) VALUES ('$user', '$email', '$user_pass', 'New', '$wname', '$welcome', '$sig', '$template');";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		sendtobot("New user: $user");
		echo "Account created!<br /><br />\n";
		echo showlogin();
	}
	echo showfootern();
	die();
}
elseif ($action == "register") {
?>
    <h2>Register!</h2>
    <strong><strong>PLEASE DO NOT USE THE SAME PASSWORD AS ON WIKIPEDIA.</strong><br />
    <form action="acc.php?action=sreg" method="post">
    <table cellpadding="1" cellspacing="0" border="0">
    <tr>
        <td>
            <tr>
                <td>Desired Username:</td>
                <td><input type="text" name="name"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>E-mail Address:</td>
                <td><input type="text" name="email"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>Wikipedia username:</td>
                <td><input type="text" name="wname"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>Desired password:</td>
                <td><input type="password" name="pass"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>Desired password(again):</td>
                <td><input type="password" name="pass2"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>Enable <a href="http://en.wikipedia.org/wiki/User:SQLBot-Hello">SQLBot-Hello</a> welcoming of the users I create:</td>
                <td><input type="checkbox" name="welcomeenable"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>Your signature (wikicode)<br /><i>This would be the same as ~~~ on-wiki. No date, please.  Not needed if you left the checkbox above unchecked.</i></td>
                <td><input type="text" name="sig" size ="40"></td>
            </tr>
        </td>
        <td>
            <tr>
                <td>Template you would like the bot to welcome with?<br /><i>If you'd like more templates added, please contact <a href="http://en.wikipedia.org/wiki/User_talk:SQL">SQL</a>, <a href="http://en.wikipedia.org/wiki/User_talk:Cobi">Cobi</a>, or <a href="http://en.wikipedia.org/wiki/User_talk:FastLizard4">FastLizard4</a>.</i>  Not needed if you left the checkbox above unchecked.</td>
                <td><select name="template" size="0"><option value="welcome">{{welcome|user}} ~~~~</option><option 
value="welcomeg">{{welcomeg|user}} ~~~~</option><option value="welcome-personal">{{welcome-personal|user}} ~~~~</option><option 
value="werdan7">{{User:Werdan7/W}} ~~~~</option>    <option value="welcomemenu">{{WelcomeMenu|sig=~~~~}}</option><option 
value="welcomeicon">{{WelcomeIcon}} ~~~~</option>    <option value="welcomeshout">{{WelcomeShout|user}} ~~~~</option><option 
value="welcomesmall">{{WelcomeSmall|user}} ~~~~</option><option value="hopes">{{Hopes Welcome}} ~~~~</option><option 
value="welcomeshort">{{Welcomeshort|user}} ~~~~</option>
<option value="w-riana">{{User:Riana/Welcome|name=user|sig=~~~~}}</option>
<option value="w-kk">{{User:KrakatoaKatie/Welcome1}} ~~~~</option>
<option value="w-screen">{{w-screen|sig=~~~~}}</option>
<option value="wodup">{{User:WODUP/Welcome}} ~~~~</option>
<option value="williamh">{{User:WilliamH/Welcome|user}} ~~~~</option></select>
<option value="malinaccier">{{User:Malinaccier/Welcome|~~~~}}</option></select></td>
            </tr>
        </td>
        <td>
            <tr>
                <td></td>
                <td><input type="submit"><input type="reset"></td>
            </tr>
        </td>

    </tr>
    </table>
    </form>
<?php


	echo showfootern();
	die();
}
elseif ($action == "forgotpw") {

	if (isset ($_GET['si']) && isset ($_GET['id'])) {
		if (isset ($_POST['pw']) && isset ($_POST['pw2'])) {
			$puser = sanitize($_GET['id']);
			$query = "SELECT * FROM acc_user WHERE user_id = '$puser';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
			$hash = md5($hashme);
			if ($hash == $_GET['si']) {
				if ($_POST['pw'] == $_POST['pw2']) {
					$pw = md5($_POST['pw2']);
					$query = "UPDATE acc_user SET user_pass = '$pw' WHERE user_id = '$puser';";
					$result = mysql_query($query);
					if (!$result)
						Die("Query failed: $query ERROR: " . mysql_error());
					echo "Password reset!\n<br />\nYou may now <a href=\"acc.php\">Login</a>";
				} else {
					echo "<h2>ERROR</h2>Passwords did not match!<br />\n";
				}
			} else {
				echo "<h2>ERROR</h2>\nInvalid request.1<br />";
			}
			echo showfootern();
			die();
		}
		$puser = sanitize($_GET['id']);
		$query = "SELECT * FROM acc_user WHERE user_id = '$puser';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
		$hash = md5($hashme);
		if ($hash == $_GET['si']) {
?><h2>Reset password for <?php echo $row['user_name']." (".$row['user_email'].")";?></h2>
            <form action="acc.php?action=forgotpw&amp;si=<?php echo $_GET['si']; ?>&amp;id=<?php echo $_GET['id']; ?>" method="post">
            New Password: <input type="password" name="pw"><br />
            New Password (confirm): <input type="password" name="pw2"><br />
            <input type="submit"><input type="reset">
            </form><br />
            Return to <a href="acc.php">Login</a>
            <?php


		} else {
			echo "<h2>ERROR</h2>\nInvalid request.2<br />";
		}
		echo showfootern();
		die();
	}
	if (isset ($_POST['username'])) {
		$puser = sanitize($_POST['username']);
		$query = "SELECT * FROM acc_user WHERE user_name = '$puser';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['user_id'] == "") {
			echo "<h2>ERROR</h2>Missing or invalid information supplied.\n";
			die();
		}
		if (strtolower($_POST['email']) != strtolower($row['user_email'])) {
			echo "<h2>ERROR</h2>Missing or invalid information supplied (ERR 2).\n";
			echo showfootern();
			die();
		}
		$hashme = $puser . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
		$hash = md5($hashme);
		$mailtxt = "Hello! You, or a user from " . $_SERVER['REMOTE_ADDR'] . ", has requested a password reset for your account.\n\nPlease go to $tsurl/acc.php?action=forgotpw&amp;si=$hash&amp;id=" . $row['user_id'] . " to complete this request.\n\nIf you did not request this reset, please disregard this message.\n\n";
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($row['user_email'], "English Wikipedia Account Request System - Forgotten password", $mailtxt, $headers);
		echo "Your password reset request has been completed. Please check your e-mail.\n<br />";
		echo showfootern();
		die();
	}
?>
    <form action="acc.php?action=forgotpw" method="post">
    Your username: <input type="text" name="username"><br />
    Your e-mail address: <input type="text" name="email"><br />
    <input type="submit"><input type="reset">
    </form><br />
    Return to <a href="acc.php">Login</a>
    <?php


	echo showfootern();
	die();
}
elseif ($action == "login") {
	$puser = sanitize($_POST['username']);
	$query = "SELECT * FROM acc_user WHERE user_name = \"$puser\";";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_level'] == "New") {
		echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
		echo showfootern();
		die();
	}
	if ($row['user_level'] == "Suspended" && $_SESSION['user'] != "SQL") {
		echo "I'm sorry, but, your account is presently suspended.<br />\n";
		echo showfootern();
		die();
	}
	$calcpass = md5($_POST['password']);
	if ($row['user_pass'] == $calcpass) {
		$_SESSION['user'] = $row['user_name'];
		header("Location: $tsurl/acc.php");
	} else {
		echo "<h2>ERROR</h2>\n";
		echo "Username and/or password incorrect.<br />\n";
	}
}
elseif ($action == "messagemgmt") {
	if (isset ($_GET['view'])) {
		$mid = sanitize($_GET['view']);
		$query = "SELECT * FROM acc_emails WHERE mail_id = $mid;";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$mailtext = htmlentities($row['mail_text']);
		echo "<h2>View message</h2><br />Message ID: " . $row['mail_id'] . "<br />\n";
		echo "Message count: " . $row['mail_count'] . "<br />\n";
		echo "Message title: " . $row['mail_desc'] . "<br />\n";
		echo "Message text: <br /><pre>$mailtext</pre><br />\n";
		echo showfooter();
		die();
	}
	if (isset ($_GET['edit'])) {
		$siuser = sanitize($_SESSION['user']);
		$query = "SELECT * FROM acc_user WHERE user_name = '$siuser';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['user_level'] != "Admin" && $_SESSION['user'] != "SQL") {
			echo "I'm sorry, but, this page is restricted to administrators only.<br />\n";
			echo showfooter();
			die();
		}
		$mid = sanitize($_GET['edit']);
		if ($_GET['submit'] == "1") {
			$mtext = html_entity_decode($mtext);
			$mtext = sanitize($_POST['mailtext']);
			$mdesc = sanitize($_POST['maildesc']);
			$siuser = sanitize($_SESSION['user']);
			$query = "UPDATE acc_emails SET mail_desc = '$mdesc' WHERE mail_id = '$mid';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$query = "UPDATE acc_emails SET mail_text = '$mtext' WHERE mail_id = '$mid';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$mid', '$siuser', 'Edited', '$now');";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			echo "Message $mid updated.<br />\n";
			sendtobot("Message $mid edited by $siuser");
			echo showfooter();
			die();
		}
		$query = "SELECT * FROM acc_emails WHERE mail_id = $mid;";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$mailtext = htmlentities($row['mail_text']);
		echo "<h2>Edit message</h2><strong>This is NOT a toy. If you can see this form, you can edit this message. <br />WARNING: MISUSE OF THIS FUNCTION WILL RESULT IN LOSS OF ACCESS.</strong><br />\n<form action=\"acc.php?action=messagemgmt&amp;edit=$mid&amp;submit=1\" method=\"post\"><br />\n";
		echo "<input type=\"text\" name=\"maildesc\" value=\"" . $row['mail_desc'] . "\"/><br />\n";
		echo "<textarea name=\"mailtext\" rows=\"20\" cols=\"60\">$mailtext</textarea><br />\n";
		echo "<input type=\"submit\"/><input type=\"reset\"/><br />\n";
		echo "</form>";
		echo showfooter();
		die();
	}
	$query = "SELECT * FROM acc_emails WHERE mail_type = 'Message';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Mail messages</h2>\n";
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li><small>[ $maild - $mailc ] <a href=\"acc.php?action=messagemgmt&amp;edit=$mailn\">Edit! (admin only)</a> - <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		echo "$out\n";
	}
	echo "</ol><br />\n";
	$query = "SELECT * FROM acc_emails WHERE mail_type = 'Interface';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Public Interface messages</h2>\n";
	echo "<ol start=\"6\">\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li><small>[ $maild ] <a href=\"acc.php?action=messagemgmt&amp;edit=$mailn\">Edit! (admin only)</a> - <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		echo "$out\n";
	}
	echo "</ol><br />\n";
	$query = "SELECT * FROM acc_emails WHERE mail_type = 'Internal';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Internal Interface messages</h2>\n";
	echo "<ol start=\"20\">\n";
	while ($row = mysql_fetch_assoc($result)) {
		$mailn = $row['mail_id'];
		$mailc = $row['mail_count'];
		$maild = $row['mail_desc'];
		$out = "<li><small>[ $maild ] <a href=\"acc.php?action=messagemgmt&amp;edit=$mailn\">Edit! (admin only)</a> - <a href=\"acc.php?action=messagemgmt&amp;view=$mailn\">View!</a></small></li>";
		echo "$out\n";
	}
	echo "</ol><br />\n";
	echo showfooter();
	die();
}
elseif ($action == "sban" && $_GET['user'] != "") {
	if ($_POST['banreason'] == "") {
		echo "<h2>ERROR</h2>\n<br />You must specify a ban reason.\n";
		echo showfooter();
		die();
	}
	$duration = sanitize($_POST['duration']);
	if ($duration == "-1") {
		$duration = -1;
	} else {
		$duration = $duration +time();
	}
	$reason = sanitize($_POST['banreason']);
	$siuser = sanitize($_GET['user']);
	$target = sanitize($_GET['target']);
	$type = sanitize($_GET['type']);
	$now = date("Y-m-d H-i-s");
	upcsum($target);
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$target', '$siuser', 'Banned', '$now');";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$query = "INSERT INTO acc_ban (ban_type, ban_target, ban_user, ban_reason, ban_date, ban_duration) VALUES ('$type', '$target', '$siuser', '$reason', '$now', $duration);";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "Banned $target for $reason<br />\n";
	if ($duration == "" || $duration == "-1") {
		$until = "Forever";
	} else {
		$until = date("F j, Y, g:i a", $duration);
	}
	sendtobot("$target banned by $siuser for " . $_POST['banreason'] . " until $until");
	echo showfooter();
	die();
}
elseif ($action == "unban" && $_GET['id'] != "") {
	$siuser = sanitize($_SESSION['user']);
	$bid = sanitize($_GET['id']);
	$query = "DELETE FROM acc_ban WHERE ban_id = '$bid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$bid', '$siuser', 'Unbanned', '$now');";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "Unbanned ban #$bid<br />\n";
	sendtobot("$target unbanned by " . $_SESSION['user']);
	echo showfooter();
	die();
}
elseif ($action == "ban") {
	$siuser = sanitize($_SESSION['user']);
	if (isset ($_GET['ip']) || isset ($_GET['email']) || isset ($_GET['name'])) {
		if ($_GET['ip'] != "") {
			$ip2 = sanitize($_GET['ip']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$ip2';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_ip'];
			$type = "IP";
		}
		elseif ($_GET['email'] != "") {
			$email2 = sanitize($_GET['email']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$email2';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_email'];
			$type = "EMail";
		}
		elseif ($_GET['name'] != "") {
			$name2 = sanitize($_GET['name']);
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$name2';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row = mysql_fetch_assoc($result);
			$target = $row['pend_name'];
			$type = "Name";
		}
		$target = sanitize($target);
		$query = "SELECT * FROM acc_ban WHERE ban_target = '$target';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['ban_id'] != "") {
			echo "<h2>ERROR</h2>\n<br />\nCould not ban. Already banned!<br />";
			echo showfooter();
			die();
		} else {
			echo "<h2>Ban an IP, Name or E-Mail</h2>\n<form action=\"acc.php?action=sban&amp;user=$siuser&amp;target=$target&amp;type=$type\" method=\"post\">Ban target: $target\n<br />Reason: <input type=\"text\" name=\"banreason\">\n<br />Duration: <SELECT NAME=\"duration\"><OPTION VALUE=\"-1\">Forever<OPTION VALUE=\"86400\">24 Hours<OPTION VALUE=\"604800\">One Week<OPTION VALUE=\"2629743\">One Month</SELECT><br /><input type=\"submit\"></form>\n";
		}
	}
	echo "<h2>Active Ban List</h2>\n<ol>\n";
	$query = "SELECT * FROM acc_ban;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	while ($row = mysql_fetch_assoc($result)) {
		if ($row['ban_duration'] == "" || $row['ban_duration'] == "-1") {
			$until = "Forever";
		} else {
			$until = date("F j, Y, g:i a", $row['ban_duration']);
		}
		echo "<li><small><strong>" . $row['ban_target'] . "</strong> - Banned by: <strong>" . $row['ban_user'] . "</strong> for <strong>" . htmlentities($row['ban_reason']) . "</strong> at <strong>" . $row['ban_date'] . "</strong> Until <strong>$until</strong>. (<a href=\"acc.php?action=unban&amp;id=" . $row['ban_id'] . "\">UNBAN</a>)</small></li>";
	}
	echo "</ol>\n";
	echo showfooter();
	die();
}
elseif ($action == "usermgmt") {
	$siuser = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_user WHERE user_name = '$siuser';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_level'] != "Admin" && $_SESSION['user'] != "SQL") {
		echo "I'm sorry, but, this page is restricted to administrators only.<br />\n";
		echo showfooter();
		die();
	}
	if (isset ($_GET['approve'])) {
		$aid = sanitize($_GET['approve']);
		$siuser = sanitize($_SESSION['user']);
		$query = "SELECT * FROM acc_user WHERE user_id = '$aid';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['user_level'] == "Admin") {
			echo "Sorry, the user you are trying to approve has Administrator access. Please use the <a href=\"acc.php?action=usermgmt&amp;demote=$aid\">demote function</a> instead.<br />\n";
			echo showfooter();
			die();
		}		
		$query = "UPDATE acc_user SET user_level = 'User' WHERE user_id = '$aid';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$aid', '$siuser', 'Approved', '$now');";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changed User #" . $_GET['approve'] . " access to 'User'<br />\n";
		$uid = $aid;
		$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
		$result2 = mysql_query($query2);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		sendtobot("User $aid (" . $row2['user_name'] . ") approved by $siuser");
	}
	if (isset ($_GET['demote'])) {
		$did = sanitize($_GET['demote']);
		$siuser = sanitize($_SESSION['user']);
		if ($_POST['demotereason'] == "") {
			echo "<h2>Demote Reason</h2><strong>The reason you enter here will be shown in the log. Please keep this in mind.</strong><br />\n<form action=\"acc.php?action=usermgmt&amp;demote=$did\" method=\"post\"><br />\n";
			echo "<textarea name=\"demotereason\" rows=\"20\" cols=\"60\"></textarea><br />\n";
			echo "<input type=\"submit\"><input type=\"reset\"/><br />\n";
			echo "</form>";
			echo showfooter();
			die();
		} else {
			$demotersn = sanitize($_POST['demotereason']);
			$query = "UPDATE acc_user SET user_level = 'User' WHERE user_id = '$did';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Demoted', '$now', '$demotersn');";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			echo "Changed User #" . $_GET['demote'] . " access to 'Demoted'<br />\n";
			$uid = $did;
			$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
			$result2 = mysql_query($query2);
			if (!$result2)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			sendtobot("User $did (" . $row2['user_name'] . ") demoted by $siuser because: \"" . $_POST['demotereason'] . "\"");
			echo showfooter();
			die();
		}

	}
	if (isset ($_GET['suspend'])) {
		$did = sanitize($_GET['suspend']);
		$siuser = sanitize($_SESSION['user']);
		if ($_POST['suspendreason'] == "") {
			echo "<h2>Suspend Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"acc.php?action=usermgmt&amp;suspend=$did\" method=\"post\"><br />\n";
			echo "<textarea name=\"suspendreason\" rows=\"20\" cols=\"60\"></textarea><br />\n";
			echo "<input type=\"submit\"><input type=\"reset\"/><br />\n";
			echo "</form>";
			echo showfooter();
			die();
		} else {
			$suspendrsn = sanitize($_POST['suspendreason']);
			$query = "UPDATE acc_user SET user_level = 'Suspended' WHERE user_id = '$did';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Suspended', '$now', '$suspendrsn');";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			echo "Changed User #" . $_GET['suspend'] . " access to 'Suspended'<br />\n";
			$uid = $did;
			$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
			$result2 = mysql_query($query2);
			if (!$result2)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			sendtobot("User $did (" . $row2['user_name'] . ") suspended access by $siuser because: \"" . $_POST['suspendreason'] . "\"");
			echo showfooter();
			die();
		}

	}
	if (isset ($_GET['promote'])) {
		$aid = sanitize($_GET['promote']);
		$siuser = sanitize($_SESSION['user']);
		$query = "UPDATE acc_user SET user_level = 'Admin' WHERE user_id = '$aid';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$aid', '$siuser', 'Promoted', '$now');";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changed User #" . $_GET['promote'] . " access to 'Admin'<br />\n";
		$uid = $aid;
		$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
		$result2 = mysql_query($query2);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		sendtobot("User $aid (" . $row2['user_name'] . ") promoted to admin by $siuser");
	}
	if (isset ($_GET['decline'])) {
		$did = sanitize($_GET['decline']);
		$siuser = sanitize($_SESSION['user']);
		$query = "SELECT * FROM acc_user WHERE user_id = '$did';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['user_level'] != "New") {
			echo "You cannot decline this user because the user is not a New user.<br />\n";
			echo showfooter();
			die();
		}
		if ($_POST['declinereason'] == "") {
			echo "<h2>Decline Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"acc.php?action=usermgmt&amp;decline=$did\" method=\"post\"><br />\n";
			echo "<textarea name=\"declinereason\" rows=\"20\" cols=\"60\"></textarea><br />\n";
			echo "<input type=\"submit\"><input type=\"reset\"/><br />\n";
			echo "</form>";
			echo showfooter();
			die();
		} else {
			$declinersn = sanitize($_POST['declinereason']);
			$query = "UPDATE acc_user SET user_level = 'Declined' WHERE user_id = '$did';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$now = date("Y-m-d H-i-s");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Declined', '$now', '$declinersn');";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			echo "Changed User #" . $_GET['decline'] . " access to 'Declined'<br />\n";
			$uid = $did;
			$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
			$result2 = mysql_query($query2);
			if (!$result2)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			sendtobot("User $did (" . $row2['user_name'] . ") declined access by $siuser because: \"" . $_POST['declinereason'] . "\"");
			echo showfooter();
			die();
		}
	}
	
        	if ( isset ($_GET['rename']) && $enableRenames == 1 ) {
		$siuser = sanitize($_SESSION['user']);
		$newname == "";
		if ($_POST['newname'] == "") {
						$result = mysql_query("SELECT user_name FROM acc_user WHERE user_id = '{$_GET['rename']}';");
						if (!$result)
							Die("Query failed: $query ERROR: " . mysql_error());
						$oldname = mysql_fetch_assoc($result);
                        echo "<form action=\"acc.php?action=usermgmt&amp;rename=" . $_GET['rename'] . "\" method=\"post\">";						
                        echo "<div class=\"required\">";
                        echo "<label for=\"oldname\">Old Username:</label>";
                        echo "<input id=\"oldname\" type=\"text\" name=\"oldname\" readonly=\"readonly\" value=\"" . stripslashes($oldname['user_name']) . "\"/>";
                        echo "</div>";
                        echo "<div class=\"required\">";
                        echo "<label for=\"newname\">New Username:</label>";
                        echo "<input id=\"newname\" type=\"text\" name=\"newname\"/>";
                        echo "</div>";
                        echo "<div class=\"submit\">";
                        echo "<input type=\"submit\"/>";
                        echo "</div>";
                        echo "</form>";
			echo showfooter();
			die();
		} else {
			if ( hasright($_SESSION['user'], "Admin") != TRUE )
				Die("You don't have the right, and I am too tired to make it fail properly");
			$oldname = sanitize($_POST['oldname']);
			$newname = sanitize($_POST['newname']);
			$userid = sanitize($_GET['rename']);
			$result = mysql_query("SELECT user_name FROM acc_user WHERE user_id = '$userid';");
				if (!$result)
					Die("Query failed: $query ERROR: " . mysql_error());
			$checkname = mysql_fetch_assoc($result);	
				if ($checkname['user_name'] != $oldname)
					Die("Rename form corrupted");
			if(mysql_num_rows(mysql_query("SELECT * FROM acc_user WHERE user_name = '$oldname';")) != 1 || mysql_num_rows(mysql_query("SELECT * FROM acc_user WHERE user_name = '$newname';")) != 0)
				die("Target username in use, or current user does not exist.");
			$query = "UPDATE acc_user SET user_name = '$newname' WHERE user_id = '$userid';";
			$result = mysql_query($query);
			$tgtmessage = "User " . $_GET['rename'] . " (" . $oldname . ")";
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());						
			$query = "UPDATE acc_log SET log_pend = '$newname' WHERE log_pend = '$tgtmessage' AND log_action != 'Renamed';";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());				
            $query = "UPDATE acc_log SET log_user = '$newname' WHERE log_user = '$oldname'";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			$now = date("Y-m-d H-i-s");
			$logentry = $oldname . " to " . $newname;
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$logentry', '$siuser', 'Renamed', '$now', '');";
			$result = mysql_query($query);
			if (!$result)
				Die("Query failed: $query ERROR: " . mysql_error());
			echo "Changed User " . $oldname . " name to ". $newname . "<br />\n";
			$query2 = "SELECT * FROM acc_user WHERE user_name = '$oldname';";
			$result2 = mysql_query($query2);
			if (!$result2)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			sendtobot("User $siuser changed $oldname's username to $newname");
			echo showfooter();
			die();
		}

	}
?>
    <h1>User Management</h1>
    <strong>This interface isn't a toy. If it says you can do it, you can do it.<br />Please use this responsibly.</strong>
    <h2>Open requests</h2>
    <?php


	$query = "SELECT * FROM acc_user WHERE user_level = 'New';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	if (mysql_num_rows($result) != 0){
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];
		$out = "<li><small>[ $uname / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ] <a href=\"acc.php?action=usermgmt&amp;approve=$userid\">Approve!</a> - <a href=\"acc.php?action=usermgmt&amp;decline=$userid\">Decline</a> - <a href=\"http://toolserver.org/~sql/sqlbot.php?user=$uoname\">Count!</a></small></li>";
		echo "$out\n";
	}
	echo "</ol>\n";
	}

?>
	<div id="usermgmt-users">
    <h2>Users</h2>
    <?php


	$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Approved') WHERE user_level = 'User' GROUP BY log_pend ORDER BY log_pend DESC;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];

		$out = "<li><small>[ <a href=\"users.php?viewuser=$userid\">$uname</a> / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
		if( $enableRenames == 1 ) {
			$out .= " <a href=\"acc.php?action=usermgmt&amp;rename=$userid\">Rename!</a> -";
		}
		$out .= " <a href=\"acc.php?action=usermgmt&amp;suspend=$userid\">Suspend!</a> - <a href=\"acc.php?action=usermgmt&amp;promote=$userid\">Promote!</a> (Approved by $row[log_user])</small></li>";
		echo "$out\n";
	}
?>
    </ol>
	</div>
	<div id="usermgmt-admins">
    <h2>Admins</h2>
    <?php


	$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Promoted') WHERE user_level = 'Admin' GROUP BY log_pend ORDER BY log_time ASC;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];
		$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Suspended';";
		$result2 = mysql_query($query);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$suspended = $row2['COUNT(*)'];

		$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Promoted';";
		$result2 = mysql_query($query);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$promoted = $row2['COUNT(*)'];

		$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Approved';";
		$result2 = mysql_query($query);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$approved = $row2['COUNT(*)'];

$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Demoted';";
		$result2 = mysql_query($query);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$demoted = $row2['COUNT(*)'];

$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Declined';";
		$result2 = mysql_query($query);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$declined = $row2['COUNT(*)'];

		$out = "<li><small>[ <a href=\"users.php?viewuser=$userid\">$uname</a> / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
		if( $enableRenames == 1 ) {
			$out .= " <a href=\"acc.php?action=usermgmt&amp;rename=$userid\">Rename!</a> -";
		}
		$out .= " <a href=\"acc.php?action=usermgmt&amp;suspend=$userid\">Suspend!</a> - <a href=\"acc.php?action=usermgmt&amp;demote=$userid\">Demote!</a> (Promoted by $row[log_user] [P:$promoted|S:$suspended|A:$approved|Dm:$demoted|D:$declined])</small></li>";
		echo "$out\n";
	}
?>
    </ol>
	</div>
    <h2>Suspended accounts</h2>
	<div class="showhide" id="showhide-suspended-link" onclick="showhide('showhide-suspended');">[show]</div>
	<div id="showhide-suspended" style="display: none;">
    <?php


	$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Suspended') WHERE user_level = 'Suspended' GROUP BY log_pend ORDER BY log_id DESC;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];
		$out = "<li><small>[ <a href=\"users.php?viewuser=$userid\">$uname</a> / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
		if( $enableRenames == 1 ) {
			$out .= " <a href=\"acc.php?action=usermgmt&amp;rename=$userid\">Rename!</a> -";
		}
		out .= " <a href=\"acc.php?action=usermgmt&amp;approve=$userid\">Unsuspend!</a> (Suspended by " . $row['log_user'] . " because \"" . $row['log_cmt'] . "\")</small></li>";
		echo "$out\n";
	}
?>
    </ol>
	</div>
    <h2>Declined accounts</h2>
	<div class="showhide" id="showhide-declined-link" onclick="showhide('showhide-declined');">[show]</div>
	<div id="showhide-declined" style="display: none;">
    <?php


	$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Declined') WHERE user_level = 'Declined' GROUP BY log_pend ORDER BY log_id DESC;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];
		$out = "<li><small>[ $uname / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
		if( $enableRenames == 1 ) {
		$out .= " <a href=\"acc.php?action=usermgmt&amp;rename=$userid\">Rename!</a> -";
		}
		$out .= " <a href=\"acc.php?action=usermgmt&amp;approve=$userid\">Approve!</a> (Declined by " . $row['log_user'] . " because \"" . $row['log_cmt'] . "\")</small></li>";
		echo "$out\n";
	}
?>
    </ol>
	</div>
    <?php


	echo showfooter();
	die();
}
elseif ($action == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") {
	if ($_GET['target'] == "admin" || $_GET['target'] == "user") {
		if ($_GET['target'] == "admin") {
			$target = "Admin";
		} else {
			$target = "Open";
		}
		$gid = sanitize($_GET['id']);
		if (csvalid($gid, $_GET['sum']) != 1) {
			echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
			echo showfooter();
			die();
		}
		$sid = sanitize($_SESSION['user']);
		$query = "SELECT pend_status FROM acc_pend WHERE pend_id = '$gid';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		if ($row['pend_status'] == $target) {
			echo "Cannot set status, target already deferred to $target<br />\n";
			echo showfooter();
			die();
		}
		$query = "UPDATE acc_pend SET pend_status = '$target' WHERE pend_id = '$gid';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		if ($_GET['target'] == "admin") {
			$deto = "admins";
		} else {
			$deto = "users";
		}
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Deferred to $deto', '$now');";
		upcsum($gid);
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		sendtobot("Request $gid deferred to $deto by $sid");
		echo "Request " . $_GET['id'] . " deferred to $deto.<br />";
		echo defaultpage();
	} else {
		echo "Target not specified.<br />\n";
	}
}
elseif ($action == "welcomeperf" || $action == "prefs") { //Welcomeperf is deprecated, but to avoid conflicts, include it still.
	if (isset ($_POST['sig'])) {
		$sig = sanitize($_POST['sig']);
		$template = sanitize($_POST['template']);
		$sid = $_SESSION['user'];
		if ($_POST['welcomeenable'] == "on") {
			$welcomeon = 1;
		} else {
			$welcomeon = 0;
		}
		$query = "UPDATE acc_user SET user_welcome = '$welcomeon' WHERE user_name = '$sid'";
		$query2 = "UPDATE acc_user SET user_welcome_sig = '$sig' WHERE user_name = '$sid'";
		$query3 = "UPDATE acc_user SET user_welcome_template = '$template' WHERE user_name = '$sid'";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$result = mysql_query($query2);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$result = mysql_query($query3);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Preferences updated!<br />\n";
	}
	$sid = $_SESSION['user'];
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid'";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_welcome'] > 0) {
		$welcoming = " checked=\"checked\"";
	}
	$sig = " value=\"" . htmlentities($row['user_welcome_sig']) . "\"";
	$template = $row['user_welcome_template'];
?>
    <table>
    <tr><th>Table of Contents</th></tr>
    <tr><td><a href="#1">Welcome settings</a></td></tr>
    <tr><td><a href="#2">Change password</a></td></tr>
    </table>
    <a name="1"></a><h2>Welcome settings</h2>
    <form action="acc.php?action=welcomeperf" method="post">
    <input type="checkbox" name="welcomeenable"<?php echo $welcoming ?>/> Enable <a href="http://en.wikipedia.org/wiki/User:SQLBot-Hello">SQLBot-Hello</a> welcoming of the users I create<br />
    Your signature (wikicode) <input type="text" name="sig" size ="40"<?php echo $sig; ?>/><br />
    <i>This would be the same as ~~~ on-wiki. No date, please.</i><br />
    <select name="template" size="0">
    <option value="welcome"<?php if($template == "welcone") { echo " selected=\"selected\""; } ?>>{{welcome|user}} ~~~~</option>
    <option value="welcomeg"<?php if($template == "welcomeg") { echo " selected=\"selected\""; } ?>>{{welcomeg|user}} ~~~~</option>
    <option value="w-screen"<?php if($template == "w-screen") { echo " selected=\"selected\""; } ?>>{{w-screen|sig=~~~~}}</option>
    <option value="welcome-personal"<?php if($template == "welcome-personal") { echo " selected=\"selected\""; } ?>>{{welcome-personal|user}} ~~~~</option>
    <option value="w-kk"<?php if($template == "w-kk") { echo " selected=\"selected\""; } ?>>{{User:KrakatoaKatie/Welcome1}} ~~~~</option>
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
    </select><br />
    <i>If you'd like more templates added, please contact <a href="http://en.wikipedia.org/wiki/User_talk:SQL">SQL</a>.</i><br />


    <input type="submit"/><input type="reset"/>
    </form>
    <a name="2"></a><h2>Change your password</h2>
    <form action="acc.php?action=forgotpw" method="post">
    Your username: <input type="text" name="username"/><br />
    Your e-mail address: <input type="text" name="email"/><br />
    <input type="submit"/><input type="reset"/>
    </form><br />
    <?php


	echo showfooter();
	die();
}
elseif ($action == "done" && $_GET['id'] != "") {
	if ($_GET['email'] == "" | $_GET['email'] >= 6) {
		echo "Invalid close reason";
		echo showfooter();
		die();
	}
	$gid = sanitize($_GET['id']);
	if (csvalid($gid, $_GET['sum']) != 1) {
		echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
		echo showfooter();
		die();
	}
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['pend_emailsent'] == "1" && $_GET['override'] != "yes") {
		echo "<br />This request has already been closed in a manner that has generated an e-mail to the user, Proceed?<br />\n";
		echo "<a href=\"acc.php?sum=" . $_GET['sum'] . "&amp;action=done&amp;id=" . $_GET['id'] . "&amp;override=yes&amp;email=" . $_GET['email'] . "\">Yes</a> / <a href=\"acc.php\">No</a><br />\n";
		echo showfooter();
		die();
	}
	$gem = sanitize($_GET['email']);
	$sid = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result);
	$gus = sanitize($row2['pend_name']);
	if ($row2['pend_status'] == "Closed") {
		echo "<h2>ERROR</h2>Cannot close this request. Already closed.<br />\n";
		echo showfooter();
		die();
	}
	$query = "SELECT * FROM acc_user WHERE user_name = '$sid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_welcome'] > 0 && $gem == "1") {
		$sig = $row['user_welcome_sig'];
		if ($sig == "") {
			$sig = "[[User:$sid|$sid]] ([[User_talk:$sid|talk]])";
		}
		$template = $row['user_welcome_template'];
		$sig = sanitize($sig);
		if ($template == "") {
			$template = "welcome";
		}
		$query = "INSERT INTO acc_welcome (welcome_uid, welcome_user, welcome_sig, welcome_status, welcome_pend, welcome_template) VALUES ('$sid', '$gus', '$sig', 'Open', '$gid', '$template');";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
	}
	$query = "UPDATE acc_pend SET pend_status = 'Closed' WHERE pend_id = '$gid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Closed $gem', '$now');";
	$result = mysql_query($query);
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
	}
	$now = explode("-", $now);
	$now = $now['0'] . "-" . $now['1'] . "-" . $now['2'] . ":" . $now['3'] . ":" . $now['4'];
	sendtobot("Request " . $_GET['id'] . " ($gus) Marked as 'Done' ($crea) by $sid on $now");
	echo "Request " . $_GET['id'] . " ($gus) marked as 'Done'.<br />";
	$towhom = $row2['pend_email'];
	if ($gem != "0") {
		sendemail($gem, $towhom);
		$query = "UPDATE acc_pend SET pend_emailsent = '1' WHERE pend_id = '" . $_GET['id'] . "';";
		$result = mysql_query($query);
	}
	upcsum($_GET['id']);
	echo defaultpage();
}
elseif ($action == "zoom") {
	if ($_GET['id'] == "") {
		echo "No user specified!<br />\n";
		echo showfooter();
		die();
	}
	$gid = sanitize($_GET['id']);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	echo "<h2>Details for Request #" . $_GET['id'] . ":</h2>";
	$uname = urlencode($row['pend_name']);
	$thisip = $row['pend_ip'];
	$thisid = $row['pend_id'];
	$thisemail = $row['pend_email'];
	if ($row['pend_date'] == "0000-00-00 00:00:00") {
		$row['pend_date'] = "Date Unknown";
	}
	$sUser = $row['pend_name'];
	$requesttable = listrequests($thisid);
	echo $requesttable;

	//$row['pend_cmt'] = preg_replace('/\<\/?(div|span|script|\?php|\?|img)\s?(.*)\s?\>/i', '', $row['pend_cmt']);
	$row['pend_cmt'] = htmlentities( $row['pend_cmt'], ENT_QUOTES );
	//Escape injections.
	echo "<br /><strong>Comment</strong>: " . $row['pend_cmt'] . "<br />\n";
	$query = "SELECT * FROM acc_log WHERE log_pend = '$gid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<h2>Possibly conflicting usernames</h2>\n";
	$spoofs = getSpoofs( $sUser );
	if( !$spoofs ) {
		echo "<i>None detected</i><br />\n";
	} else {
		echo "<ul>\n";
		foreach( $spoofs as $oSpoof ) {
			$oS = htmlentities($oSpoof);
			echo "<li><a href=\"http://en.wikipedia.org/wiki/User:$oS\">$oSpoof</a> (<a href=\"http://en.wikipedia.org/wiki/Special:Contributions/$oS\">contribs</a> | <a href=\"http://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A$oS\">Logs</a>)</li>\n";
		}
		echo "</ul>\n";
	}
	mysql_close();
	mysql_connect( $toolserver_host, $toolserver_username, $toolserver_password );
	@ mysql_select_db( $toolserver_database ) or print mysql_error( );
	echo "<h2>Logs for Request #" . $_GET['id'] . ":</h2>";
	 if (mysql_num_rows($result) != 0){
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$rlu = $row['log_user'];
		$rla = $row['log_action'];
		$rlp = $row['log_pend'];
		$rlt = $row['log_time'];
		if ($rla == "Deferred to admins" || $rla == "Deferred to users") {
			echo "<li>$rlu $rla, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed") {
			echo "<li>$rlu $rla, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed 0") {
			echo "<li>$rlu Dropped, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed 1") {
			echo "<li>$rlu Closed (Account created), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed 2") {
			echo "<li>$rlu Closed (Too Similar), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed 3") {
			echo "<li>$rlu Closed (Taken), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed 4") {
			echo "<li>$rlu Closed (Username vio), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Closed 5") {
			echo "<li>$rlu Closed (Technical Impossibility), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";

		}
		if ($rla == "Closed 6") {
			echo "<li>$rlu Closed (Custom reason), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($rla == "Blacklist Hit") {
			echo "<li>$rlu Rejected by Blacklist $rlp, " . $row['log_cmt'] . " at $rlt.</li>\n";
		}
		if ($rla == "Renamed") {
			echo "<li>$rlu renamed user $rlp at $rlt.</li>\n";
		}
	}

	echo "</ol>\n";
        }

	echo "<h2>Other requests from $thisip:</h2>\n";
	$query = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_id != '$thisid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$numip = 0;
 	while ($row = mysql_fetch_assoc($result)) {
	if ($numip == 0) { echo "<ol>\n"; }
		echo "<li><a href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></li>";
		$numip++;
	}
	if ($numip == 0) {
		echo "<i>None.</i>\n";
	}
	else {echo "</ol>\n";}
	echo "<h2>Other requests from $thisemail:</h2>\n";
	$query = "SELECT * FROM acc_pend WHERE pend_email = '$thisemail' AND pend_id != '$thisid';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$numem = 0;
		while ($row = mysql_fetch_assoc($result)) {
		if ($numem == 0) { echo "<ol>\n"; }
		echo "<li><a href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></li>";
		$numem++;
	}
	if ($numem == 0) {
		echo "<i>None.</i>\n";
	}
	else {echo "</ol>\n";}
	echo showfooter();
	die();
}
elseif ($action == "logout") {
	session_unset();
	echo showlogin();
	die("Logged out!\n");
}
elseif ($action == "logs") {
	if (isset ($_GET['limit'])) {
		$limit = $_GET['limit'];
		$limit = sanitize($limit);
	} else {
		$limit = 100;
	}
	if (isset ($_GET['from'])) {
		$from = sanitize($_GET['from']);
		$query = "SELECT * FROM acc_log ORDER BY log_time DESC LIMIT $limit OFFSET $from;";
	} else {
		$query = "SELECT * FROM acc_log ORDER BY log_time DESC LIMIT $limit;";
		$from = 0;
	}
	$next = $from +100;
	$prev = $from -100;
	if ($from > 0) {
		$n1 = "<h4><a href=\"acc.php?action=logs&amp;from=$prev\">Previous 100</a> <a href=\"acc.php?action=logs&amp;from=$next\">Next 100</a></h4>\n";
		echo $n1;
	} else {
		$n1 = "<h4><a href=\"acc.php?action=logs&amp;from=$next\">Next 100</a></h4>\n";
		echo $n1;
	}
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$rlu = $row['log_user'];
		$rla = $row['log_action'];
		$rlp = $row['log_pend'];
		$rlt = $row['log_time'];
		$rlc = $row['log_cmt'];
		if ($row['log_time'] == "0000-00-00 00:00:00") {
			$row['log_time'] = "Date Unknown";
		}
		if ($row['log_action'] == "Deferred to admins" || $rla == "Deferred to users") {

			echo "<li>$rlu $rla, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed") {
			echo "<li>$rlu $rla, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 0") {
			echo "<li>$rlu Dropped, <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 1") {
			echo "<li>$rlu Closed (Account created), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 2") {
			echo "<li>$rlu Closed (Too Similar), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 3") {
			echo "<li>$rlu Closed (Taken), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 4") {
			echo "<li>$rlu Closed (Username vio), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 5") {
			echo "<li>$rlu Closed (Technical Impossibility), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Closed 6") {
			echo "<li>$rlu Closed (Custom reason), <a href=\"acc.php?action=zoom&amp;id=$rlp\">Request $rlp</a> at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Blacklist Hit") {
			echo "<li>$rlu <strong>Rejected by Blacklist</strong> $rlp, $rlc at $rlt.</li>\n";
		}
		if ($row['log_action'] == "Unbanned") {
			echo "<li>$rlu Unbanned $rlp at $rlt</li>\n";
		}
		if ($row['log_action'] == "Banned") {
			$mid = sanitize($row['log_pend']);
			$query3 = "SELECT * FROM acc_ban WHERE ban_target = '$mid';";
			$result3 = mysql_query($query3);
			if (!$result3)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row3 = mysql_fetch_assoc($result3);
			echo "<li>$rlu Banned " . $row3['log_pend'] . " #" . $row3['ban_id'] . " (" . _utf8_decode($row3['ban_target']) . "), Reason: " . $row3['ban_reason'] . ", at $rlt.</li>\n";
		}

		if ($rla == "Edited") {
			$mid = $rlp;
			$query3 = "SELECT * FROM acc_emails WHERE mail_id = '$mid';";
			$result3 = mysql_query($query3);
			if (!$result3)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row3 = mysql_fetch_assoc($result3);
			echo "<li>$rlu Edited Message <a href=\"acc.php?action=messagemgmt&amp;view=$rlp\">$rlp (" . $row3['mail_desc'] . ")</a>, at $rlt.</li>\n";
		}
		if ($rla == "Promoted" || $rla == "Approved" || $rla == "Suspended" || $rla == "Declined") {
			$uid = $rlp;
			$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
			$result2 = mysql_query($query2);
			if (!$result2)
				Die("Query failed: $query ERROR: " . mysql_error());
			$row2 = mysql_fetch_assoc($result2);
			$moreinfo = "";
			if ($rla == "Declined") {
				$moreinfo = " because \"$rlc\"";
			}
			echo "<li>$rlu $rla, User $rlp (" . $row2['user_name'] . ") at $rlt$moreinfo.</li>\n";
		}
		if ($rla == "Renamed") {
			echo "<li>$rlu renamed user $rlp at $rlt.</li>\n";
		}
	}
	echo "</ol>\n";
	echo $n1;
	echo showfooter();
	die();
}
?>
