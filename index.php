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
**                                                           **
**************************************************************/

require_once ('config.inc.php');
require_once ('functions.php');

$fail = 0;

// check to see if the database is unavailable
global $dontUseDb;
if ($dontUseDb) {
	require_once('offline-messages.php');
	showExternalOfflineMessage();
	die();
}

global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
global $antispoof_host, $antispoof_db, $antispoof_table, $antispoof_password;
global $tsSQLlink, $asSQLlink;
list($tsSQLlink, $asSQLlink) = getDBconnections();

function confirmEmail( $id ) {
	/*
	* Confirms either a new users e-mail, or a requestor's e-mail.
	* $id will be acc_pend.pend_id
	*/
	global $toolserver_database, $tsSQLlink, $asSQLlink;
	global $tsurl;
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$pid = sanitize($id);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$pid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$row = mysql_fetch_assoc($result);
	if ($row['pend_id'] == "") {
		echo "<h2>ERROR</h2>Missing or invalid information supplied.\n";
		die();
	}
	$seed = microtime(true);
	usleep( rand(0,3000) );
	$seed = $seed +  microtime( true );
	usleep( rand(0,300) );
	$seed = $seed +  microtime( true );
	usleep( rand(0,300) );
	$seed = $seed -  microtime( true );
	mt_srand( $seed );
	$salt = mt_rand( );
	$hash = md5( $id . $salt );
	$mailtxt = "Hello! You, or a user from " . $_SERVER['REMOTE_ADDR'] . ", has requested an account on the English Wikipedia ( http://en.wikipedia.org ).\n\nPlease go to $tsurl/index.php?action=confirm&si=$hash&id=" . $row['pend_id'] . "&nocheck=1 in order to complete this request.\n\nIf you did not make this request, please disregard this message.\n\n";
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($row['pend_email'], "English Wikipedia Account Request", $mailtxt, $headers);
	$query = "UPDATE acc_pend SET pend_mailconfirm = '$hash' WHERE pend_id = '$pid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
}

function checkSpoofs( $username ) {
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		global $antispoof_equivset, $antispoof_db, $antispoof_table, $asSQLlink;
		require_once($antispoof_equivset);
		@ mysql_select_db($antispoof_db, $asSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
		$fone = strtr($username,$equivset);
		//$fone = mysql_real_escape_string( $fone );
		$query = "SELECT * FROM $antispoof_table WHERE su_normalized = 'v2:$fone';";
		$result = mysql_query($query, $asSQLlink);
		if(!$result) sqlerror("ERROR: No result returned. - ".mysql_error(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		$numSpoof = 0;
		while ($row = mysql_fetch_assoc($result)) {
		        if( isset( $row['su_name'] ) ) { $numSpoof++; }
		}
		mysql_close( $spooflink );
		if( $numSpoof == 0 ) {
		        return( FALSE );
		} else {
			return( TRUE );
		}
	}
	else { return FALSE; }
}

function checktor($addr) {
	/*
	* Check if the supplied host is a TOR node
	*/
	$flags = array ();
	$flags['tor'] = "no";
	$p = explode(".", $addr);
	if(strpos($addr,':') != -1 ) {
		//IPv6 addy
		return $flags;
	}
	$ahbladdr = $p['3'] . "." . $p['2'] . "." . $p['1'] . "." . $p['0'] . "." . "tor.ahbl.org";
	;
	$ahbl = gethostbyname($ahbladdr);
	if ($ahbl == "127.0.0.2") {
		$flags['transit'] = "yes";
		"yes";
		$flags['tor'] = "yes";
	}
	if ($ahbl == "127.0.0.3") {
		$flags['exit'] = "yes";
		"yes";
		$flags['tor'] = "yes";
	}
	return ($flags);
}

function emailvalid($email) {
	if (!strpos($email, '@')) {
		return false;
	}
	$parts = explode("@", $email);
	$username = isset($parts[0]) ? $parts[0] : '';
	$domain = isset($parts[1]) ? $parts[1] : '';
	if (function_exists('checkdnsrr')) {
		getmxrr($domain, $mxhosts, $mxweight);
		if (count($mxhosts) > 0) {
			for ($i = 0; $i < count($mxhosts); $i++) {
				$mxs[$mxhosts[$i]] = $mxweight[$i];
			}
			$mailers = array_keys($mxs);
		}
		elseif (checkdnsrr($domain, 'A')) {
			$mailers['0'] = gethostbyname($domain);
		} else {
			$mailers = array ();
		}
		if (count($mailers) > 0) {
			return true;
		} else {
			return false;
		}
	} else {
		return true;
	}
}

function displayform() {
	/*
	* Display Request form via MySQL
	*/
	global $toolserver_database, $tsSQLlink;
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$query = "SELECT * FROM acc_emails WHERE mail_id = '6' ORDER BY mail_id DESC LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row['mail_text'];
}

function clearOldUnconfirmed( ) {
	global $toolserver_database, $tsSQLlink;
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$ntime = mktime(
        	date("H"),
        	date("i"),
        	date("s"),
        	date("m"),
        	date("d") - 2,
        	date("Y")
        );
	$expiry =  date("Y-m-d H:i:s", $ntime);
	$query = "DELETE FROM acc_pend WHERE pend_date < '$expiry' AND pend_mailconfirm != 'Confirmed' AND pend_mailconfirm != '';";
	$result = mysql_query($query, $tsSQLlink);
}

if ($enableEmailConfirm == 1) {
	clearOldUnconfirmed( );
}


displayheader();

$action = '';
if( isset( $_GET['action'] ) ) {
	$action = $_GET['action'];
}

if ($enableEmailConfirm == 1) {

if ( $action == "confirm" && isset($_GET['id']) && isset($_GET['si']) ) {
	@ mysql_select_db( $toolserver_database ) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$pid = mysql_real_escape_string( $_GET['id'] );
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$pid';";
	$result = mysql_query( $query, $tsSQLlink );
	if ( !$result )
		sqlerror("Query failed: $query ERROR: ".mysql_error(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$row = mysql_fetch_assoc( $result );
	if( $row['pend_mailconfirm'] == $_GET['si'] ) {
                $successmessage = showmessage(24);
		echo "$successmessage <br />\n";
		$query = "UPDATE acc_pend SET pend_mailconfirm = 'Confirmed' WHERE pend_id = '$pid';";
		$result = mysql_query($query, $tsSQLlink);
		if ( !$result )
			sqlerror("Query failed: $query ERROR: ".mysql_error(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>."); 
		$user = $row['pend_name'];
		if( checkSpoofs( $user ) ) { $uLevel = "Admin"; } else { $uLevel = "Open"; }
		if( $uLevel == "Open" ) { $what = ""; } else { $what = "<Account Creator Needed!> "; }
		$comments = html_entity_decode(stripslashes($row['pend_cmt']));
			sendtobot("\00314[[\00303acc:\00307$pid\00314]]\0034 N\00310 \00302$tsurl/acc.php?action=zoom&id=$pid\003 \0035*\003 \00303$user\003 \0035*\003 \00310$what\003" . substr(str_replace(array (
			"\n",
			"\r"
			), array (
			' ',
			' '
			), $comments), 0, 200) . ((strlen($comments) > 200) ? '...' : ''));
	} elseif( $row['pend_mailconfirm'] == "Confirmed" ) {
		echo "Your e-mail address has already been confirmed!\n";
	} else {
		echo "E-mail confirmation failed!<br />\n";
	}
	displayfooter();
	die();
} elseif ( $action == "confirm" ) {
	echo "Invalid Parameters. Please be sure you copied the URL correctly<br />\n";
	displayfooter();
	die();
}
}

if (isset ($_POST['name']) && isset ($_POST['email'])) {
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$_POST['name'] = str_replace(" ", "_", $_POST['name']);
	$_POST['name'] = ltrim( rtrim ( ucfirst($_POST['name'] ) ) );
	
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		@ mysql_select_db("enwiki_p", $asSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
		$query = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
		$result = mysql_query($query, $asSQLlink);
		if (!$result)
			Die("ERROR: No result returned.");
	}	
		
	$ip = $_SERVER['REMOTE_ADDR'];
	$ip2 = $_SERVER['REMOTE_ADDR'];
	$ip = mysql_real_escape_string($ip);

	if( !$dontUseWikiDb ) {
		@ mysql_select_db("enwiki_p", $asSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
		$query = 'SELECT * FROM ipblocks WHERE ipb_user = \''.$ip.'\';';
		$result = mysql_query($query, $asSQLlink);
		if( !$result && !isOnWhitelist( $ip ) ) {
			$message = showmessage(9);
			echo "$message<br />\n";
			$fail = 1;
		}
	}	
	
	$email = $_POST['email'];
	$email = ltrim($email);
	$email = rtrim($email);
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	foreach ($uablacklist as $wubl => $ubl) {
		$phail_test = @ preg_match($ubl, $_SERVER['HTTP_USER_AGENT']);
		if ($phail_test == TRUE) {
			$now = date("Y-m-d H-i-s");
			$target = "$wubl";
			$siuser = mysql_real_escape_string($_POST['name']);
			$cmt = mysql_real_escape_string("FROM $ip $email");
			sendtobot("[Grawp-Bl] HIT: $wubl - " . $_POST['name'] . " $ip2 $email " . $_SERVER['HTTP_USER_AGENT']);
			//$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
			//$result = mysql_query($query);
			//if(!$result) Die("ERROR: No result returned.");
			//$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\''.$ip.'\',\'ClueBot\',\''.mysql_real_escape_string('Blacklist Hit: '.$wnbl.' - '.$_POST['name'].' '.$ip2.' '.$email.' '.$_SERVER['HTTP_USER_AGENT']).'\',\''.$now.'\',\''.(time() + 172800).'\');';
			//mysql_query($query);
			die();
		}
	}
	foreach ($nameblacklist as $wnbl => $nbl) {
		$phail_test = @ preg_match($nbl, $_POST['name']);
		if ($phail_test == TRUE) {
			$message = showmessage(15);
			echo "$message<br />\n";
			$now = date("Y-m-d H-i-s");
			$target = "$wnbl";
			$siuser = mysql_real_escape_string($_POST['name']);
			$cmt = mysql_real_escape_string("FROM $ip $email");
			sendtobot("[Name-Bl] HIT: $wnbl - " . $_POST['name'] . " $ip2 $email " . $_SERVER['HTTP_USER_AGENT']);
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("ERROR: No result returned.");
			$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $ip . '\',\'ClueBot\',\'' . mysql_real_escape_string('Blacklist Hit: ' . $wnbl . ' - ' . $_POST['name'] . ' ' . $ip2 . ' ' . $email . ' ' . $_SERVER['HTTP_USER_AGENT']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
			mysql_query($query, $tsSQLlink);
			die();
		}
	}
	foreach ($emailblacklist as $wnbl => $nbl) {
		$phail_test = @ preg_match($nbl, $_POST['email']);
		if ($phail_test == TRUE) {
			$message = showmessage(15);
			echo "$message<br />\n";
			$now = date("Y-m-d H-i-s");
			$target = "$wnbl";
			$siuser = mysql_real_escape_string($_POST['name']);
			$cmt = mysql_real_escape_string("FROM $ip $email");
			sendtobot("[Email-Bl] HIT: $wnbl - " . $_POST['name'] . " $ip2 $email " . $_SERVER['HTTP_USER_AGENT']);
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
			$result = mysql_query($query, $tsSQLlink);
			if (!$result)
				Die("ERROR: No result returned.");
			$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $ip . '\',\'ClueBot\',\'' . mysql_real_escape_string('Blacklist Hit: ' . $wnbl . ' - ' . $_POST['name'] . ' ' . $ip2 . ' ' . $email . ' ' . $_SERVER['HTTP_USER_AGENT']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
			mysql_query($query, $tsSQLlink);
			die();
		}
	}
	global $enableDnsblChecks;
	if( $enableDnsblChecks == 1 ){
		$dnsblcheck = checkdnsbls($ip2);
		if ($dnsblcheck['0'] == true) {
			$toruser = checktor($ip2);
			if ($toruser['tor'] == "yes") {
				$tor = " (TOR node)";
			} else {
				$tor = "";
			}
			$now = date("Y-m-d H-i-s");
			$siuser = mysql_real_escape_string($_POST['name']);
			$cmt = mysql_real_escape_string("FROM $ip $email<br />" . $dnsblcheck['1']);
			sendtobot("[DNSBL]$tor HIT: " . $_POST['name'] . " $ip2 $email " . $_SERVER['HTTP_USER_AGENT']);
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('DNSBL', '$siuser', 'DNSBL Hit', '$now', '$cmt');";
			if ($enableSQLError) 
				echo '<!-- Query: ' . $query . ' -->';
			mysql_query($query, $tsSQLlink);
			if ($enableSQLError)
				echo '<!-- Error: ' . mysql_error() . ' -->';
			$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $ip . '\',\'ClueBot\',\'' . mysql_real_escape_string("DNSBL Hit:<br />\n" . $dnsblcheck['1']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
			if ($enableSQLError)
				echo '<!-- Query: ' . $query . ' -->';
			mysql_query($query, $tsSQLlink);
			if ($enableSQLError)
				echo '<!-- Error: ' . mysql_error() . ' -->';
		}
	}
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		
		@ mysql_select_db("enwiki_p", $asSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	}
	$user = $_POST['name'];
	$user = ltrim($user);
	$user = rtrim($user);
	$user = mysql_real_escape_string($user);
	$email = $_POST['email'];
	$email = ltrim($email);
	$email = rtrim($email);
	$email = mysql_real_escape_string($email);
	$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=" . $_POST['name'] . "&format=php");
	$ue = unserialize($userexist);
	foreach ($ue['query']['users'] as $oneue) {
		if (!isset ($oneue['missing'])) {
			$message = showmessage(10);
			echo "$message<br />\n";
			$fail = 1;
		}
	}
	$nums = preg_match("/^[0-9]+$/", $_POST['name']);
	if ($nums > 0) {
		$message = showmessage(11);
		echo "$message<br />\n";
		$fail = 1;
	}
	$unameismail = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['name']);
	if ($unameismail > 0) {
		$message = showmessage(12);
		echo "$message<br />\n";
		$fail = 1;
	}
	$unameisinvalidchar = preg_match('/[\#\/\|\[\]\{\}\@\%\:\<\>]/', $_POST['name']);
	if ($unameisinvalidchar > 0 || ltrim( rtrim( $_POST['name'] == "" ) ) ) {
		$message = showmessage(13);
		echo "$message<br />\n";
		$fail = 1;
	}
	if (!emailvalid($_POST['email'])) {
		$message = showmessage(14);
		echo "$message<br />\n";
		$fail = 1;
	}

	$mailiswmf = preg_match('/.*wiki(m.dia|p.dia).*/i', $email);
	if ($mailiswmf != 0) {
		$message = showmessage(14);
		echo "$message<br />\n";
		$fail = 1;
	}

	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_name = '$user'";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	if ($row['pend_id'] != "") {
		$message = showmessage(17);
		echo "$message<br />\n";
		$fail = 1;
	}
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_email = '$email'";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	if ($row['pend_id'] != "") {
		$message = showmessage(18);
		echo "$message<br />\n";
		$fail = 1;
	}
	mysql_query('DELETE FROM `acc_ban` WHERE `ban_duration` < UNIX_TIMESTAMP()', $tsSQLlink);
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'IP' AND ban_target = '$ip'";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row['ban_duration'];
	$toruser = checktor($ip2);
	if ($row['ban_id'] != "" || $toruser['tor'] == "yes") {
		if ($dbanned < 0 || $dbanned == "") {
			$dbanned = time() + 100;
		}
		if ($toruser['tor'] == "yes") {
			$row[ban_reason] = "<a href=\"http://en.wikipedia.org/wiki/Tor_%28anonymity_network%29\">TOR</a> nodes are not permitted to use this tool, due to abuse.";
		}
		if ($dbanned < time()) {
			//Not banned!
		} else { //Still banned!
			$message = showmessage(19);
			echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
			$fail = 1;
			displayfooter();
			die();
		}
	}
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'Name' AND ban_target = '$user'";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row['ban_duration'];
	if ($row['ban_id'] != "") {
		if ($dbanned < 0 || $dbanned == "") {
			$dbanned = time() + 100;
		}

		if ($dbanned < time()) {
			//Not banned!
		} else { //Still banned!
			$message = showmessage(19);
			echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
			$fail = 1;
			displayfooter();
			die();
		}
	}
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'EMail' AND ban_target = '$email'";
	$result = mysql_query($query, $tsSQLlink);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row['ban_duration'];
	if ($row['ban_id'] != "") {
		if ($dbanned < 0 || $dbanned == "") {
			$dbanned = time() + 100;
		}

		if ($dbanned < time()) {
			//Not banned!
		} else { //Still banned!
			$message = showmessage(19);
			echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
			$fail = 1;
			displayfooter();
			die();
		}
	}

	if ($fail != 1) {
		if( $enableEmailConfirm == 1 )
		{$message = showmessage(15);} else {$message = showmessage(24);}
		echo "$message<br />\n";
	} else {
		$message = showmessage(16);
		echo "$message<br />\n";
	}
	if ($fail == 1) {
		displayform();
		displayfooter();
		die();
	}
	$comments = sanitize($_POST['comments']);
	$comments = htmlentities($comments); //Escape injections.
	$dnow = date("Y-m-d H-i-s");
	if( checkSpoofs( $user ) ) { $uLevel = "Admin"; } else { $uLevel = "Open"; }
	$query = "INSERT INTO acc_pend (pend_id , pend_email , pend_ip , pend_name , pend_cmt , pend_status , pend_date ) VALUES ( NULL , '$email', '$ip', '$user', '$comments', '$uLevel' , '$dnow' );";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("ERROR: No result returned. (acc_pend)");
	$q2 = $query;
	$query = "SELECT pend_id,pend_email FROM acc_pend WHERE pend_name = '$user' ORDER BY pend_id DESC LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);	
	if (!$result)
		Die("ERROR: No result returned. (select)");
	$row = mysql_fetch_assoc($result);
	$pid = $row['pend_id'];
	if ($pid != 0 || $pid != "") {
		upcsum($pid);
	}
        if ($enableEmailConfirm == 1) {	
        confirmEmail( $pid );
        }
} else {
	displayform();
	displayfooter();
	die();
}
?>
