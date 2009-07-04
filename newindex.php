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
**Chris G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**                                                           **
**************************************************************/

require_once 'config.inc.php';
require_once 'AntiSpoof.php';

$fail = 0;


// TODO: Move all these classes into their own files

// Offline messages class
class offlineMessage {
	private $dontUseDb;
	
	public function __construct() {
		global $dontUseDb;
		$this->dontUseDb = $dontUseDb;
	}
	
	private function showOfflineMessageHeader() {
		echo <<<HTML
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
		<head>
			<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
			<title>Account Creation Assistance for the English Wikipedia - http://en.wikipedia.org/wiki/Wikipedia:Request an account</title>
			<style type="text/css" media="screen">
				@import "style.css";
			</style>
		</head>

		<body id="body">
			<div id="header">
				<div id="header-title">
					Account Creation Assistance
				</div>
			</div>
			<div id="navigation">
				<a href="http://en.wikipedia.org">English Wikipedia</a> 
			</div>

			<div id="content">
HTML;
	}

	private function showOfflineMessageFooter() {
		echo <<<HTML
			</div>
			<div id="footer">
				Account Creation Assistance Manager by <a href="http://stable.toolserver.org/acc/team.php">The ACC dev team</a>. <a href="https://jira.toolserver.org/browse/ACC">Bugs?</a><br />

				Designed by <a href="http://charlie.mudoo.net/">Charlie Melbye</a>
			</div>
		</body>
	</html>
HTML;
	}

	private function showExternalOfflineMessage() {
		echo <<<HTML
		<h1>Request an account on the English Wikipedia</h1> 		
		<h2>Our apologies!</h2>
		<p>We’re very sorry, but the account creation request tool is currently offline while critical maintenance is performed. We will restore normal operations as soon as possible.</p>
		<p>However, you can still request an account by emailing <a href="mailto:accounts-enwiki-l@lists.wikimedia.org">accounts-enwiki-l@lists.wikimedia.org</a>, with the username that you would like. We’ll take care of your request as soon as possible.</p>
		<p>Thanks for your interest in joining Wikipedia.</p>
HTML;
	}
	
	private function showInternalOfflineMessage() {
		global $offlineProblem, $offlineCulprit;
		echo <<<HTML
		<h2>Whoops!</h2>
		<p>After much experimentation, someone finally managed to kill ACC. So, the tool is currently offline while our resident developers pound their skulls against the furniture.</p> 
		<p>Apparently, this is supposed to fix it.</p>
		<p>Once the nature of the problem is known, we will insert it here: <b>$offlineProblem</b></p>
		<p>Once the identity of the culprit(s) is known, trout should be applied here: <b>$offlineCulprit</b></p>
		<p>Although the tool is dead and the Bot is sleeping, email still works fine. So, we expect a swarm of irate potential editors to bury us in requests shortly. Please keep an eye on the mailing list. Remember to 'cc' or 'bcc' accounts-enwiki when you reply to let others know you have replied.</p> 
		<p>For more information, <a href="irc://irc.freenode.net/#wikipedia-en-accounts">join IRC</a>, check the mailing list (<a href="https://lists.wikimedia.org/mailman/listinfo/accounts-enwiki-l">sign up if you need to</a>) or just light candles – they may help too.</p>
HTML;
}
	
	public function check() {
		if ($this->dontUseDb) {
			$this->showOfflineMessageHeader();
			if ($this->isExternal) {
				$this->showExternalOfflineMessage();
			} else {
				$this->showInternalOfflineMessage();
			}
			$this->showOfflineMessageFooter();
			die();
		}
	}
}

// database class
class database {
	private $dbLink;
	
	public function __construct($host,$username,$password) {
		$this->dbLink = mysql_pconnect($host, $username, $password) or $this->showError("Error connecting to database $host: ".$this->getError(),'Error connecting to database');
	}
	
	public function selectDb($database) {
		// TODO: Improve error msg and handling
		mysql_select_db($database,$this->dbLink) or $this->showError('Error selecting database.');
	}
	
	public function query($query) {
		return mysql_query($query,$this->dbLink);
	}
	
	public function escape($string) {
		// WARNING: This does not escape against XSS, this is intentional to avoid double escape etc
		// please escape user input seperately using htmlentities()
		return mysql_real_escape_string($string,$this->dbLink);
	}
	
	public function showError($sql_error,$generic_error=null) {
		global $enableSQLError;
		if ($generic_error==null) {
			$generic_error = $sql_error;
		}
		if ($enableSQLError) {
			die($sql_error);
		} else {
			die($generic_error);
		}
	}
	
	public function getError() {
		return mysql_error($this->dbLink);
	}
	
	public function __destruct() {
		mysql_close($this->dbLink);
	}
}

// request class
class accRequest {
	private $id;
	
	public function __construct () {
		global $enableEmailConfirm;
		if ($enableEmailConfirm == 1) {
			$this->clearOldUnconfirmed();
		}
	}
	
	private function clearOldUnconfirmed() {
		global $tsSQL;
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
		$tsSQL->query($query);
	}
	
	public function setID($id) {
		if (preg_match('/^[0-9]*$/',$id)) {
			$this->id = $id;
			return true;
		}
		die("Invalid request id.");
	}
	
	// TODO: Setting most of these functions to public to be safe,
	// however some of them could be moved over to private
	
	public function confirmEmail($id=null) {
		/*
		* Confirms either a new users e-mail, or a requestor's e-mail.
		* $id will be acc_pend.pend_id
		*/
		global $tsSQL, $tsurl;
		if ($id==null) {
			$id = $this->id;
		}
		$pid = $tsSQL->escape($id);
		$query = "SELECT * FROM acc_pend WHERE pend_id = '$pid';";
		$result = $tsSQL->query($query);
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
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
		$result = $tsSQL->query($query);
		if (!$result) {
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		}
	}
	
	public function checkConfirmEmail() {
		global $tsSQL, $enableEmailConfirm, $messages, $action, $accbot, $tsurl;
		if ($enableEmailConfirm == 1) {
			if ( $action == "confirm" && isset($_GET['id']) && isset($_GET['si']) ) {
				$pid = $tsSQL->escape($_GET['id']);
				$query = "SELECT * FROM acc_pend WHERE pend_id = '$pid';";
				$result = $tsSQL->query($query);
				if ( !$result )
					$tsSQL->showError("Query failed: $query ERROR: ".$tsSQL->getError(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
				$row = mysql_fetch_assoc( $result );
				if( $row['pend_mailconfirm'] == $_GET['si'] ) {
					$successmessage = $messages->getMessage(24);
					echo "$successmessage <br />\n";
					$query = "UPDATE acc_pend SET pend_mailconfirm = 'Confirmed' WHERE pend_id = '$pid';";
					$result = $tsSQL->query($query);
					if ( !$result )
						$tsSQL->showError("Query failed: $query ERROR: ".$tsSQL->getError(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>."); 
					$user = $row['pend_name'];
					$spoofs = $this->getSpoofs($user);
					if( $spoofs === FALSE ) {
						$uLevel = "Open";
						$what = "";
					} else {
						$uLevel = "Admin";
						$what = "<Account Creator Needed!> ";
					}
					$comments = html_entity_decode(stripslashes($row['pend_cmt']));
						$accbot->send("\00314[[\00303acc:\00307$pid\00314]]\0034 N\00310 \00302$tsurl/acc.php?action=zoom&id=$pid\003 \0035*\003 \00303$user\003 \0035*\003 \00310$what\003" . substr(str_replace(array (
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
				echo $messages->getMessage(22);
				die();
			} elseif ( $action == "confirm" ) {
				echo "Invalid Parameters. Please be sure you copied the URL correctly<br />\n";
				echo $messages->getMessage(22);
				die();
			}
		}
	}
	
	public function checktor($addr) {
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
	
	public function emailvalid($email) {
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
	
	public function displayform() {
		/*
		* Display Request form via MySQL
		*/
		global $tsSQL;
		$query = "SELECT * FROM acc_emails WHERE mail_id = '6' ORDER BY mail_id DESC LIMIT 1;";
		$result = $tsSQL->query($query);
		if (!$result)
			Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		echo $row['mail_text'];
	}
	
	public function getSpoofs( $username ) {
		global $dontUseWikiDb, $asSQL, $antispoof_table;
		if( !$dontUseWikiDb ) {
			$return = AntiSpoof::checkUnicodeString( $username );
			if($return[0] == 'OK' ) {		
				$sanitized = $asSQL->escape($return[1]);
				$query = "SELECT su_name FROM ".$antispoof_table." WHERE su_normalized = '$sanitized';";
				$result = $asSQL->query($query);
				if(!$result) $asSQL->showError("Database error.");
				$numSpoof = 0;
				$reSpoofs = array();
				while ( list( $su_name ) = mysql_fetch_row( $result ) ) {
					if( isset( $su_name ) ) { $numSpoof++; }
					array_push( $reSpoofs, $su_name );
				}
				if( $numSpoof == 0 ) {
					return( FALSE );
				} else {
					return( $reSpoofs );
				}
			} else {
				return ( $return[1] );
			}
		} else { return FALSE; }
	}
	
	public function checkdnsbls($addr) {
		global $dnsbls;

		$dnsblip = implode('.', array_reverse(explode('.', $addr)));
		$dnsbldata = '<ul>';
		$banned = false;

		foreach ($dnsbls as $dnsblname => $dnsbl) {
			echo '<!-- Checking ' . $dnsblname . ' ... ';
			$tmpdnsblresult = gethostbyname($dnsblip . '.' . $dnsbl['zone']);
			echo $tmpdnsblresult . ' -->';
			if (long2ip(ip2long($tmpdnsblresult)) != $tmpdnsblresult) {
				$tmpdnsblresult = 'Nothing.';
				continue;
			}
			//		if (!isset($dnsbl['ret'][$lastdigit]) and ($dnsbl['bunk'] == false)) { $tmpdnsblresult = 'Nothing.'; continue; }
			$dnsbldata .= '<li> ' . $dnsblip . '.' . $dnsbl['zone'] . ' (' . $dnsblname . ') = ' . $tmpdnsblresult;
			$lastdigit = explode('.', $tmpdnsblresult);
			$lastdigit = $lastdigit['3'];
			if (isset ($dnsbl['ret'][$lastdigit])) {
				$dnsbldata .= ' (' . $dnsbl['ret'][$lastdigit] . ')';
				$banned = true;
			} else {
				$dnsbldata .= ' (unknown)';
				if ($dnsbl['bunk'])
					$banned = true;
			}
			$dnsbldata .= ' &mdash;  <a href="' . str_replace('%i', $addr, $dnsbl['url']) . "\"> more information</a>.\n";
		}
		unset ($dnsblip, $dnsblname, $dnsbl, $tmpdnsblresult, $lastdigit);

		$dnsbldata .= '</ul>';
		echo '<!-- ' . $dnsbldata . ' -->';
		return array (
			$banned,
			$dnsbldata
		);
	}
	
	public function checkBlacklist($blacklist,$check,$email,$ircblname) {
		global $tsSQL, $accbot, $messages;
		$ip = $_SERVER['REMOTE_ADDR'];
		foreach ($blacklist as $blname => $regex) {
			$phail_test = @ preg_match($regex,$check);
			if ($phail_test == TRUE) {
				$message = $messages->getMessage(15);
				echo "$message<br />\n";
				$now = date("Y-m-d H-i-s");
				$target = "$blname";
				$siuser = $tsSQL->escape($check);
				$cmt = $tsSQL->escape("FROM $ip $email");
				$accbot->send("[$ircblname] HIT: $blname - " . $check . " $ip $email " . $_SERVER['HTTP_USER_AGENT']);
				$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
				$result = $tsSQL->query($query);
				if (!$result)
					die("ERROR: No result returned.");
				$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $tsSQL->escape($ip) . '\',\'ClueBot\',\'' . $tsSQL->escape('Blacklist Hit: ' . $blname . ' - ' . $check . ' ' . $ip . ' ' . $email . ' ' . $_SERVER['HTTP_USER_AGENT']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
				$tsSQL->query($query);
				die();
			}
		}
	}
	
	public function upcsum($id) {
		/*
		* Updates the entries checksum (on each load of that entry, to prevent dupes)
		*/
		global $tsSQL;
		$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
		$result = $tsSQL->query($query);
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"Database query error.");
		$pend = mysql_fetch_assoc($result);
		$hash = md5($pend['pend_id'] . $pend['pend_name'] . $pend['pend_email'] . microtime());
		$query = "UPDATE acc_pend SET pend_checksum = '$hash' WHERE pend_id = '$id';";
		$result = $tsSQL->query($query);
	}
}

// the skin
class skin {
	public function displayheader() {
		global $tsSQL;
		$result = $tsSQL->query("SELECT * FROM acc_emails WHERE mail_id = '8';");
		if (!$result) {
			// TODO: Nice error message
			die("ERROR: No result returned.");
		}
		$row = mysql_fetch_assoc($result);
		echo $row['mail_text'];
	}
}

// messages class
class messages {
	public function getMessage ($messageno) {
		global $tsSQL;
		$messageno = $tsSQL->escape($messageno);
		$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
		$result = $tsSQL->query($query);
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"Database query error.");
		$row = mysql_fetch_assoc($result);
		return $row['mail_text'];
	}
}

// accbot class
class accbotSend {
	public function send($message) {
		/*
		* Send to the IRC bot via UDP
		*/
		global $whichami;
		sleep(3);
		$fp = fsockopen("udp://91.198.174.202", 9001, $erno, $errstr, 30);
		if (!$fp) {
			echo "SOCKET ERROR: $errstr ($errno)<br />\n";
		}
		fwrite($fp, $this->formatForBot( chr(2)."[$whichami]".chr(2).": $message" ) );
		fclose($fp);
	}
	
	private function formatForBot( $data ) { 		
		global $key; 		
		$pData[0] = $key; 		
		$pData[1] = $data; 		
		$sData = serialize( $pData ); 		
		return $sData; 		
	}
}

// check to see if the database is unavailable
$offlineMessage = new offlineMessage(true);
$offlineMessage->check();

// connect to the TS database and the antispoof database
global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
global $antispoof_host, $antispoof_db, $antispoof_table, $antispoof_password;
$tsSQL = new database($toolserver_host,$toolserver_username,$toolserver_password);
$asSQL = new database($antispoof_host,$toolserver_username,$antispoof_password);
$tsSQL->selectDb($toolserver_database);
$asSQL->selectDb($antispoof_db); 

$request = new accRequest();
$messages = new messages();
$accbot = new accbotSend();

$skin = new skin();
$skin->displayheader();

$action = '';
if( isset( $_GET['action'] ) ) {
	$action = $_GET['action'];
}
if( isset( $_GET['id'] ) ) {
	$request->setID($_GET['id']);
}

$request->checkConfirmEmail();

if (isset ($_POST['name']) && isset ($_POST['email'])) {
	$_POST['name'] = str_replace(" ", "_", $_POST['name']);
	$_POST['name'] = ltrim( rtrim ( ucfirst($_POST['name'] ) ) );
	
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		@ $asSQL->selectDb('enwiki_p');
		$query = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
		$result = $asSQL->query($query);
		if (!$result) {
			die("ERROR: No result returned.");
		}
	}	

	// Initialize Variables		
	$ip = $_SERVER['REMOTE_ADDR'];
	$ip2 = $_SERVER['REMOTE_ADDR'];
	$ip = $asSQL->escape($ip);

	$user = $_POST['name'];
	$user = $asSQL->escape(trim($user));

	$email = $_POST['email'];
	$email = $asSQL->escape(trim($email));
	
	//Delete old bans
	$tsSQL->query('DELETE FROM `acc_ban` WHERE `ban_duration` < UNIX_TIMESTAMP() AND ban_duration != -1');

	//Check for bans
	// TODO: This should be moved into its own class as well
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'IP' AND ban_target = '$ip'";
	$result = $tsSQL->query($query);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row['ban_duration'];
	$toruser = $request->checktor($ip2);
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
			$message = $messages->getMessage(19);
			echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
			$fail = 1;
			echo $messages->getMessage(22);
			die();
		}
	}
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'Name' AND ban_target = '$user'";
	$result = $tsSQL->query($query);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row['ban_duration'];
	if ($row['ban_id'] != "") {
		if ($dbanned < 0 || $dbanned == "") {
			$dbanned = time() + 100;
		}

		if ($dbanned < time()) {
			//Not banned!
		} else { //Still banned!
			$message = $messages->getMessage(19);
			echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
			$fail = 1;
			echo $messages->getMessage(22);
			die();
		}
	}
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'EMail' AND ban_target = '$email'";
	$result = $tsSQL->query($query);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row['ban_duration'];
	if ($row['ban_id'] != "") {
		if ($dbanned < 0 || $dbanned == "") {
			$dbanned = time() + 100;
		}

		if ($dbanned < time()) {
			//Not banned!
		} else { //Still banned!
			$message = $messages->getMessage(19);
			echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
			$fail = 1;
			echo $messages->getMessage(22);
			die();
		}
	}

	if( !$dontUseWikiDb ) {
		$query = 'SELECT * FROM ipblocks WHERE ipb_address = \''.$ip.'\';';
		$result = $asSQL->query($query);
		$rows = mysql_num_rows( $result );
		if( ($rows > 0) && !isOnWhitelist( $ip ) ) {
			$message = $messages->getMessage(9);
			echo "$message<br />\n";
			$fail = 1;
		}
	}	
	
	// Commenting out the whole block, currently the code just dies
	// without giving the user an error or logging it anywhere -- Chris G 7/4/09 (aus: 4/7/09)
	//foreach ($uablacklist as $wubl => $ubl) {
		//$phail_test = @ preg_match($ubl, $_SERVER['HTTP_USER_AGENT']);
		//if ($phail_test == TRUE) {
			//$now = date("Y-m-d H-i-s");
			//$target = "$wubl";
			//$siuser = mysql_real_escape_string($_POST['name']);
			//$cmt = mysql_real_escape_string("FROM $ip $email");
			
			//sendtobot("[Grawp-Bl] HIT: $wubl - " . $_POST['name'] . " $ip2 $email " . $_SERVER['HTTP_USER_AGENT']);
			//$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
			//$result = mysql_query($query);
			//if(!$result) Die("ERROR: No result returned.");
			//$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\''.$ip.'\',\'ClueBot\',\''.mysql_real_escape_string('Blacklist Hit: '.$wnbl.' - '.$_POST['name'].' '.$ip2.' '.$email.' '.$_SERVER['HTTP_USER_AGENT']).'\',\''.$now.'\',\''.(time() + 172800).'\');';
			//mysql_query($query);
			//die();
		//}
	//}
	
	// Check the blacklists
	$request->checkBlacklist($emailblacklist,$_POST['email'],$_POST['email'],'Email-Bl');
	$request->checkBlacklist($nameblacklist,$_POST['name'],$_POST['email'],'Name-Bl');
	
		
	global $enableDnsblChecks;
	if( $enableDnsblChecks == 1 ){
		$dnsblcheck = $request->checkdnsbls($ip2);
		if ($dnsblcheck['0'] == true) {
			$toruser = $request->checktor($ip2);
			if ($toruser['tor'] == "yes") {
				$tor = " (TOR node)";
			} else {
				$tor = "";
			}
			$now = date("Y-m-d H-i-s");
			$siuser = $tsSQL->escape($_POST['name']);
			$cmt = $tsSQL->escape("FROM $ip $email<br />" . $dnsblcheck['1']);
			$accbot->send("[DNSBL]$tor HIT: " . $_POST['name'] . " $ip2 $email " . $_SERVER['HTTP_USER_AGENT']);
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('DNSBL', '$siuser', 'DNSBL Hit', '$now', '$cmt');";
			if ($enableSQLError) 
				echo '<!-- Query: ' . $query . ' -->';
			$tsSQL->query($query);
			if ($enableSQLError)
				echo '<!-- Error: ' . $tsSQL->showError() . ' -->';
			$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $ip . '\',\'ClueBot\',\'' . $tsSQL->escape("DNSBL Hit:<br />\n" . $dnsblcheck['1']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
			if ($enableSQLError)
				echo '<!-- Query: ' . $query . ' -->';
			$tsSQL->query($query);
			if ($enableSQLError)
				echo '<!-- Error: ' . $tsSQL->showError() . ' -->';
		}
	}

	$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=" . $_POST['name'] . "&format=php");
	$ue = unserialize($userexist);
	if (!isset ($ue['query']['users']['0']['missing'])) {
		$message = $messages->getMessage(10);
		echo "$message<br />\n";
		$fail = 1;
	}
	$nums = preg_match("/^[0-9]+$/", $_POST['name']);
	if ($nums > 0) {
		$message = $messages->getMessage(11);
		echo "$message<br />\n";
		$fail = 1;
	}
	$unameismail = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['name']);
	if ($unameismail > 0) {
		$message = $messages->getMessage(12);
		echo "$message<br />\n";
		$fail = 1;
	}
	$unameisinvalidchar = preg_match('/[\#\/\|\[\]\{\}\@\%\:\<\>]/', $_POST['name']);
	if ($unameisinvalidchar > 0 || ltrim( rtrim( $_POST['name'] == "" ) ) ) {
		$message = $messages->getMessage(13);
		echo "$message<br />\n";
		$fail = 1;
	}
	if (!$request->emailvalid($_POST['email'])) {
		$message = $messages->getMessage(14);
		echo "$message<br />\n";
		$fail = 1;
	}

	$mailiswmf = preg_match('/.*wiki(m.dia|p.dia).*/i', $email);
	if ($mailiswmf != 0) {
		$message = $messages->getMessage(14);
		echo "$message<br />\n";
		$fail = 1;
	}

	// (JIRA) ACC-55
	$trailingspace = substr($_POST['name'], strlen($_POST['name']) - 1);
	if ($trailingspace == " " || $trailingspace == "_"  ) {
		$message = $messages->getMessage(25);
		echo "$message<br />\n";
		$fail = 1;
	}

	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_name = '$user'";
	$result = $tsSQL->query($query);
	$row = mysql_fetch_assoc($result);
	if ($row['pend_id'] != "") {
		$message = $messages->getMessage(17);
		echo "$message<br />\n";
		$fail = 1;
	}
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_email = '$email'";
	$result = $tsSQL->query($query);
	$row = mysql_fetch_assoc($result);
	if ($row['pend_id'] != "") {
		$message = $messages->getMessage(18);
		echo "$message<br />\n";
		$fail = 1;
	}

	if ($fail != 1) {
		if( $enableEmailConfirm == 1 )
		{$message = $messages->getMessage(15);} else {$message = $messages->getMessage(24);}
		echo "$message<br />\n";
		echo $messages->getMessage(22);
	} else {
		$message = $messages->getMessage(16);
		echo "$message<br />\n";
	}
	if ($fail == 1) {
		$request->displayform();
		echo $messages->getMessage(22);
		die();
	}

	$comments = $tsSQL->escape($_POST['comments']);
	$dnow = date("Y-m-d H-i-s");
	if( $request->getSpoofs( $user ) ) { $uLevel = "Admin"; } else { $uLevel = "Open"; }
	$query = "INSERT INTO acc_pend (pend_id , pend_email , pend_ip , pend_name , pend_cmt , pend_status , pend_date ) VALUES ( NULL , '$email', '$ip', '$user', '$comments', '$uLevel' , '$dnow' );";
	$result = $tsSQL->query($query);
	if (!$result)
		die("ERROR: No result returned. (acc_pend)");
	$q2 = $query;
	$query = "SELECT pend_id,pend_email FROM acc_pend WHERE pend_name = '$user' ORDER BY pend_id DESC LIMIT 1;";
	$result = $tsSQL->query($query);	
	if (!$result)
		die("ERROR: No result returned. (select)");
	$row = mysql_fetch_assoc($result);
	$pid = $row['pend_id'];
	if ($pid != 0 || $pid != "") {
		$request->upcsum($pid);
	}
	if ($enableEmailConfirm == 1) {	
		$request->confirmEmail( $pid );
	}
} else {
	$request->displayform();
	echo $messages->getMessage(22);
	die();
}
?>
