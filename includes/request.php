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

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

class accRequest {
	private $id;
	
	public function __construct () {
		// Get global variable from configuration file.
		global $enableEmailConfirm;
		
		// Checks whether email confirmation is activated.
		// Clears the old unconfirmed requests if so.
		if ($enableEmailConfirm == 1) {
			$this->clearOldUnconfirmed();
		}
	}
	
	private function clearOldUnconfirmed() {
		// Get global variables from configuration file.
		global $tsSQL, $emailConfirmationExpiryDays;
		
		// Determine which requests are old enough to be cleared.
		// The amount of expiry days are subtracted from the current date and time.
		$ntime = mktime(
	        	date("H"),
	        	date("i"),
	        	date("s"),
	        	date("m"),
	        	date("d") -  $emailConfirmationExpiryDays,
	        	date("Y"));
		
		// Converts the UNIX timestamp into a usuable date format.
		$expiry =  date("Y-m-d H:i:s", $ntime);
		
		// Formulates and executes the SQL query to delete requests that are older than the determined date.
		$query = "DELETE FROM acc_pend WHERE pend_date < '$expiry' AND pend_mailconfirm != 'Confirmed' AND pend_mailconfirm != '';";
		$tsSQL->query($query);
	}
	
	public function setID($id) {
		// Checks whether the ID complies to the guidelines.
		if (preg_match('/^[0-9]*$/',$id)) {
			// Assigns the ID to this class' private ID variable.
			$this->id = $id;
			return true;
		}
		// If the ID doesnt comply the the current script are terminated.
		die("Invalid request id.");
	}
	
	public function isTOR() {
		// Get messages object from index file.
		global $messages;
		
		// Checks whether the IP is of the TOR network.
		$toruser = $this->checktor($_SERVER['REMOTE_ADDR']);
		
		// Checks whether the tor field in the array is said to yes.
		if ($toruser['tor'] == "yes") {
			// Gets message to display to the user.
			$message = $messages->getMessage(19);
			
			// Displays the appropiate message to the user.
			echo "$message<strong><a href=\"http://en.wikipedia.org/wiki/Tor_%28anonymity_network%29\">TOR</a> nodes are not permitted to use this tool, due to abuse.</strong><br />\n";
			
			// Display the footer of the interface.
			$skin->displayfooter();
			
			// Terminates the current script, as the user is banned.
			// This is done because the requesting process should be stopped. 
			die();
		}
	}
	
	/**
	 * Checks for various types of bans.
	 * @param $type Which type of ban to check for. { "IP" | "Name" | "EMail" }
 	 * @param $target The data to validate.
	 */
	public function checkBan($type,$target) {
		// Get requered objects from index file.
		global $messages, $tsSQL;
		
		// Formulates and executes the SQL query to check for the ban.
		$query = "SELECT * FROM acc_ban WHERE ban_type = '".$tsSQL->escape($type)."' AND ban_target = '".$tsSQL->escape($target)."'";
		$result = $tsSQL->query($query);
		
		// Fetch the result row as an array.
		$row = mysql_fetch_assoc($result);
		
		// Gets the ban duration.
		$dbanned = $row['ban_duration'];
		
		// When there is no ban_id it means there is no ban, so the checks are skipped.
		if ($row['ban_id'] != "") {
			// TO-DO: Is the needed? Why would the duration be less than 0 or empty?
			// Wouldnt these records be deleted by the index file?
			// Checks whether the ban duration is less than zero or if it has an empty value.
			if ($dbanned < 0 || $dbanned == "") {
				// Adds to the current Unix timestamp and assigns it to the variable.
				$dbanned = time() + 100;
			}
			
			// Checks whether the ban duration is less than the current timestamp.
			if ($dbanned < time()) {
				// User not banned anymore.
			} else {
				// User is still banned.
				// Gets message to display to the user.
				$message = $messages->getMessage(19);
				
				// Displays the appropiate message to the user and the retrieved reason.
				echo "$message<strong>" . $row['ban_reason'] . "</strong><br />\n";
				
				// Display the footer of the interface.
				$skin->displayfooter();
			
				// Terminates the current script, as the user is still banned.
				// This is done because the requesting process should be stopped. 
				die();
			}
		}
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
		// Get global variables from configuration file.
		global $enableEmailConfirm, $tsurl;
		
		// Get variables and objects from index file.
		global $tsSQL, $messages, $action, $accbot;
		
		// Checks whether email confirmation is activated.
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
					$now = date("Y-m-d H-i-s");
					$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ($pid, '$user', 'Email Confirmed', '$now')";
					$result = $tsSQL->query($query);
					if ( !$result )
						$tsSQL->showError("Query failed: $query ERROR: ".$tsSQL->getError(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
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
				
				// Display the footer of the interface.
				$skin->displayfooter();
			
				// Terminates the current script, as the parameters are incorrect.
				die();
			}
		}
	}
	
	/*
	* Check if the supplied host is a TOR node.
	*/
	public function checktor($addr) {
		// Creates empty array.
		$flags = array ();
		
		// Sets tor variable to no.
		$flags['tor'] = "no";
		
		// Breaks the IP string up into an array.
		$p = explode(".", $addr);
		
		// Checks whether the user uses the IPv6 addy.
		// Returns the flags array with the false variable.
		if(strpos($addr,':') != -1 ) {
			return $flags;
		}
		
		// Generates a new host name by means of the IP array and TOR string.
		$ahbladdr = $p['3'] . "." . $p['2'] . "." . $p['1'] . "." . $p['0'] . "." . "tor.ahbl.org";

		// Get the IP address corresponding to a given host name.
		$ahbl = gethostbyname($ahbladdr);
		
		// In the returned IP adress is one of the following, it is from the TOR network.
		// There is then a yes flag assigned to the flag array.
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
		
		// The flags array are returned to the isTor method.
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
	
	/**
	 * Checks the various blacklists, notifies the IRC channels and bans user.
	 * @param $blacklist Which type of blacklist to check for. { "emailblacklist" | "nameblacklist" }
 	 * @param $check The data to validate.
	 * @param $email The email adress to validate.
	 * @param $ircblname The IRC Blacklist name.
	 */
	public function checkBlacklist($blacklist,$check,$email,$ircblname) {
		// Get the needed objects from index file.
		global $tsSQL, $accbot, $messages;
		
		// Creates an IP variable.
		$ip = $_SERVER['REMOTE_ADDR'];
		
		// For loop to check whether the input data matches anything on the blacklist.
		foreach ($blacklist as $blname => $regex) {
			// Test variable to see if the data mathes something on the blacklist.
			$phail_test = @ preg_match($regex,$check);
			
			// When there is no match the operations are skipped.
			if ($phail_test == TRUE) {
				// Gets message to display to the user.
				$message = $messages->getMessage(15);
				
				// Displays the appropiate message to the user.
				// The message is displayed now, as the script would die.
				echo "$message<br />\n";
				
				// Gets the current date.
				$now = date("Y-m-d H-i-s");
				
				// Assigns the current blacklist item to a new variable.
				// The variable would be used as the reason in the ACC Log.
				$target = "$blname";
				
				// The data that was checked, either an username or email adress.
				$siuser = $tsSQL->escape($check);
				
				// ???
				$cmt = $tsSQL->escape("FROM $ip $email");
				
				// Sends a message to the ACC Bot which states that there was a hit on one of the blacklists.
				$accbot->send("[$ircblname] HIT: $blname - " . $check . " $ip $email " . $_SERVER['HTTP_USER_AGENT']);
				
				// Formulates and executes the SQL query to add the match to the ACC Log.
				// ???
				$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
				$tsSQL->query($query);
				
				// Formulates and executes the SQL query to add the match to the ACC Ban list.
				// Cluebot is used as the ban user, as this was done by a script.
				// The IP is banned for 172800 seconds, or 2 days.
				$query = "INSERT INTO acc_ban (ban_type, ban_target, ban_user, ban_reason, ban_date, ban_duration) VALUES (\'IP\',\'' . $tsSQL->escape($ip) . '\',\'ClueBot\',\'' . $tsSQL->escape('Blacklist Hit: ' . $blname . ' - ' . $check . ' ' . $ip . ' ' . $email . ' ' . $_SERVER['HTTP_USER_AGENT']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');";
				$tsSQL->query($query);
				
				// Terminates the current script, as the data mathed the blacklist.
				// This is done because the requesting process should be stopped.
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
	
	private function isOnWhitelist($user) {
		// Reads the entire Whitelist file into a string.
		$apir = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=Wikipedia:Request_an_account/Whitelist&rvprop=content&format=php");
		
		// Takes the variable and converts it back into a PHP value.
		$apir = unserialize($apir);
		
		$apir = $apir['query']['pages'];
	
		foreach($apir as $r) {
			$text = $r['revisions']['0']['*'];
		}
	
		if( preg_match( '/\*\[\[User:'.preg_quote($user,'/').'\]\]/', $text ) ) {
			return true;
		}
		return false;
	}
	
	public function blockedOnEn() {
		// Get global variable from configuration file and an object from the index file.
		global $dontUseWikiDb, $asSQL;
		
		if(!$dontUseWikiDb) {
			// Formulates and executes the SQL query to check if the IP is blocked on the Eng Wiki. 
			$query = 'SELECT * FROM ipblocks WHERE ipb_address = \''.$asSQL->escape($_SERVER['REMOTE_ADDR']).'\';';
			$result = $asSQL->query($query);
			
			// Get number of rows in the result.
			$rows = mysql_num_rows($result);
			
			// When there where rows preset it means that there is a block on the IP.
			// There is also checked if the IP is not on the Eng Wiki Whitelist.
			if(($rows > 0) && !isOnWhitelist($_SERVER['REMOTE_ADDR'])) {												
				// Gets message to display to the user.
				$message = $messages->getMessage(9);
			
				// Displays the appropiate message to the user.
				echo "$message<br />\n";
				
				// Display the footer of the interface.
				$skin->displayfooter();
			
				// Terminates the current script, as the user is banned.
				// This is done because the requesting process should be stopped. 
				die();
			}
		}
	}
	
	public function doDnsBlacklistCheck() {
		global $enableDnsblChecks, $tsSQL, $accbot, $enableSQLError;
		if( $enableDnsblChecks == 1 ){
			$ip = $_SERVER['REMOTE_ADDR'];
			$email = $_POST['email'];
			$dnsblcheck = $this->checkdnsbls($ip);
			if ($dnsblcheck['0'] == true) {
				$now = date("Y-m-d H-i-s");
				$siuser = $tsSQL->escape($_POST['name']);
				$cmt = $tsSQL->escape("FROM $ip $email<br />" . $dnsblcheck['1']);
				$accbot->send("[DNSBL] HIT: " . $_POST['name'] . " $ip $email " . $_SERVER['HTTP_USER_AGENT']);
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
	}
	
	public function finalChecks($user,$email) {
		global $messages, $tsSQL;
		$fail = 0;
		
		// Checks whether the username is already in use on Wikipedia.
		$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=" . urlencode($_POST['name']) . "&format=php");
		$ue = unserialize($userexist);
		if (!isset ($ue['query']['users']['0']['missing'])) {
			$message = $messages->getMessage(10);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether the username consists entirely of numbers.
		$nums = preg_match("/^[0-9]+$/", $_POST['name']);
		if ($nums > 0) {
			$message = $messages->getMessage(11);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether the username is an email adress.
		$unameismail = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['name']);
		if ($unameismail > 0) {
			$message = $messages->getMessage(12);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether the username contains invalid characters.
		$unameisinvalidchar = preg_match('/[\#\/\|\[\]\{\}\@\%\:\<\>]/', $_POST['name']);
		if ($unameisinvalidchar > 0 || ltrim( rtrim( $_POST['name'] == "" ) ) ) {
			$message = $messages->getMessage(13);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether the email adress is valid.
		if (!$this->emailvalid($_POST['email'])) {
			$message = $messages->getMessage(14);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether the email adress is valid.
		$mailiswmf = preg_match('/.*wiki(m.dia|p.dia).*/i', $email);
		if ($mailiswmf != 0) {
			$message = $messages->getMessage(14);
			echo "$message<br />\n";
			$fail = 1;
		}

		// (JIRA) ACC-55
		// Checks whether the username has a traling space of underscore.
		$trailingspace = substr($_POST['name'], strlen($_POST['name']) - 1);
		if ($trailingspace == " " || $trailingspace == "_"  ) {
			$message = $messages->getMessage(25);
			echo "$message<br />\n";
			$fail = 1;
		}

		// Checks whether there arent already a request for the username.
		$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_name = '$user'";
		$result = $tsSQL->query($query);
		$row = mysql_fetch_assoc($result);
		if ($row['pend_id'] != "") {
			$message = $messages->getMessage(17);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether there arent already a request for the email adress.
		$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_email = '$email'";
		$result = $tsSQL->query($query);
		$row = mysql_fetch_assoc($result);
		if ($row['pend_id'] != "") {
			$message = $messages->getMessage(18);
			echo "$message<br />\n";
			$fail = 1;
		}
		
		// Checks whether any of the automated checks were failed.
		// Notifies the requester that the request was unsuccessfull.
		if ($fail == 1) {
			// Gets message to display to the user.
			$message = $messages->getMessage(16);
			
			// Displays the appropiate message to the user.
			echo "$message<br />\n";
			
			// Display the request form and footer of the interface.
			$this->displayform();
			$skin->displayfooter();
			
			// Terminates the current script, as automated checks are failed.
			die();
		}
	}
	
	public function insertRequest($user,$email) {
		global $enableEmailConfirm, $messages, $tsSQL, $defaultReserver;
		if ($enableEmailConfirm == 1) {
			$message = $messages->getMessage(15);
		} else {
			$message = $messages->getMessage(24);
		}
		
		// Display message and leave blank line before the footer.
		echo "$message<br />\n";
		
		$user = htmlentities($user);
		$email = htmlentities($email);
		$comments = $tsSQL->escape(htmlentities($_POST['comments']));
		$ip = $tsSQL->escape(htmlentities($_SERVER['REMOTE_ADDR']));
		$dnow = date("Y-m-d H-i-s");
		
		if( $this->getSpoofs( $user ) ) { $uLevel = "Admin"; } else { $uLevel = "Open"; }
		$query = "INSERT INTO acc_pend (pend_id , pend_email , pend_ip , pend_name , pend_cmt , pend_status , pend_date, pend_reserved ) VALUES ( NULL , '$email', '$ip', '$user', '$comments', '$uLevel' , '$dnow', '$defaultReserver' );";
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
			$this->upcsum($pid);
		}
		if ($enableEmailConfirm == 1) {	
			$this->confirmEmail( $pid );
		}
	}
}
?>