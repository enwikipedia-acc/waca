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
		global $messages, $skin;
		
		// Checks whether the IP is of the TOR network.
		$toruser = $this->checktor($_SERVER['REMOTE_ADDR']);
		
		// Checks whether the tor field in the array is said to yes.
		if ($toruser['tor'] == "yes") {
			// Gets message to display to the user.
			$message = $messages->getMessage(19);
			
			// Displays the appropiate message to the user.
			echo "$message<strong><a href=\"http://en.wikipedia.org/wiki/Tor_%28anonymity_network%29\">TOR</a> nodes are not permitted to use this tool, due to abuse.</strong><br /></div>\n";
			
			// Display the footer of the interface.
			$skin->displayPfooter();
			
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
		global $messages, $tsSQL, $skin;
		
		// Formulates and executes the SQL query to check for the ban.
		$query = "SELECT * FROM acc_ban WHERE ban_type = '".$tsSQL->escape($type)."' AND ban_target = '".$tsSQL->escape($target)."' AND (ban_duration > UNIX_TIMESTAMP() OR ban_duration = -1) AND ban_active = 1";
		$result = $tsSQL->query($query);
		
		// Fetch the result row as an array.
		$row = mysql_fetch_assoc($result);
		
		// When there is no ban_id it means there is no ban, so the checks are skipped.
		if ($row['ban_id'] != "") {	
				// User is still banned.
				// Gets message to display to the user.
				$message = $messages->getMessage(19);
				
				// Displays the appropiate message to the user and the retrieved reason.
				echo "$message<strong>" . $row['ban_reason'] . "</strong><br /></div>\n";
				
				// Display the footer of the interface.
				$skin->displayPfooter();
			
				// Terminates the current script, as the user is still banned.
				// This is done because the requesting process should be stopped. 
				die();
		}
	}
	
	// TODO: Setting most of these functions to public to be safe,
	// however some of them could be moved over to private.
	/*
	* Confirms either a new users e-mail, or a requestor's e-mail.
	* @param $id The ID of the request.
	*/
	public function confirmEmail($id=null) {
		// Get requered objects from index file.
		global $tsSQL, $tsurl;
		
		// Assigns the ID if the param ID is null.
		if ($id==null) {
			$id = $this->id;
		}
		
		// Assigns the ID and escapes for MySQL.
		$pid = $tsSQL->escape($id);
		
		// Formulates and executes SQL query to return the request.
		$query = "SELECT * FROM acc_pend WHERE pend_id = '$pid';";
		$result = $tsSQL->query($query);
		
		// Display error upon failure.
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		
		// Assigns the row to the varibale.
		$row = mysql_fetch_assoc($result);
		
		// Checks whether the ID is not empty.
		if ($row['pend_id'] == "") {
			echo "<h2>ERROR</h2>Missing or invalid information supplied.\n";
			// Sends kill to script as the ID is empty.
			die();
		}
		
		// Sets the seed variable as the current Unix timestamp with microseconds.
		// The following lines of code ensure that the HASH is unique.
		$seed = microtime(true);
		
		// Delay execution for a random number of miliseconds.
		// Adds the current Unix timestamp to the seed variable.
		usleep(rand(0,3000));
		$seed = $seed +  microtime(true);
		
		// Delay execution for a random number of miliseconds.
		// Adds the current Unix timestamp to the seed variable.
		usleep(rand(0,300));
		$seed = $seed +  microtime(true);
		
		// Delay execution for a random number of miliseconds.
		// Subtracts the current Unix timestamp to the seed variable.
		usleep(rand(0,300));
		$seed = $seed -  microtime(true);
		
		// Seed the better random number generator.
		mt_srand($seed);
		
		// Generates the salt which would be used to generate the HASH.
		$salt = mt_rand();
		
		// Generates the HASH.
		$hash = md5($id . $salt);
		
		// Formulates the email message that should be send to the user.
		$mailtxt = "Hello! You, or a user from " . (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] . " (via " . $_SERVER['REMOTE_ADDR'] . ")" : $_SERVER['REMOTE_ADDR']) . ", has requested an account on the English Wikipedia ( http://en.wikipedia.org ).\n\nPlease go to $tsurl/index.php?action=confirm&si=$hash&id=" . $row['pend_id'] . "&nocheck=1 in order to complete this request.\n\nOnce your click this link, your request will be reviewed, and you will shortly receive a seperate email with more information.  Your password\nis not yet available.\n\nIf you did not make this request, please disregard this message.\n\n";
		
		// Creates the needed headers.
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		
		// Sends the confirmation email to the user.
		$mailsuccess = mail($row['pend_email'], "[ACC #$id] English Wikipedia Account Request", $mailtxt, $headers);
		// Confirms mail went through (JIRA ACC-44)
		if ($mailsuccess == false) {
			$skin->displayRequestMsg("Sorry, it appears we were unable to send an email to the email address you specified. Please check the spelling and try again.");
			$skin->displayPfooter();
			die();			
		}
		
		// Formulates and executes SQL query to update the request and add the HASH.
		$query = "UPDATE acc_pend SET pend_mailconfirm = '$hash' WHERE pend_id = '$pid';";
		$result = $tsSQL->query($query);
		
		// Display error upon failure.
		if (!$result) {
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"ERROR: database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		}
	}
	
	public function checkConfirmEmail() {
		// Get global variables from configuration file.
		global $enableEmailConfirm, $tsurl;
		
		// Get variables and objects from index file.
		global $tsSQL, $messages, $action, $accbot, $skin;
		
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
					$user = $tsSQL->escape($row['pend_name']);
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
						$what = "<Account Creator Needed!>";
					}
					$comments = html_entity_decode(stripslashes($row['pend_cmt']));
						$accbot->send("\00314[[\00303acc:\00307$pid\00314]]\0034 N\00310 \00302$tsurl/acc.php?action=zoom&id=$pid\003 \0035*\003 \00303$user\003 \0035*\00310 $what\003 " . substr(str_replace(array (
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
				echo $messages->getMessage(23);
				die();
			} elseif ( $action == "confirm" ) {
				echo "Invalid Parameters. Please be sure you copied the URL correctly<br />\n";
				
				// Display the footer of the interface.
				$skin->displayPfooter();
			
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
	
	public function istrusted($ip) {
		global $tsSQL;
		$query = "SELECT * FROM `acc_trustedips` WHERE `trustedips_ipaddr` = '$ip';";
		$result = $tsSQL->query($query);
		if (!$result)
			$tsSQL->showError("Query failed: $query ERROR: ".$tsSQL->getError(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
		if (mysql_num_rows($result))
			return True;
		else
			return False;
	}

	public function isblacklisted($user) {
		global $tsSQL, $enableTitleblacklist;
		if ($enableTitleblacklist == 1) { 
			$query = "SELECT * FROM `acc_titleblacklist`;";
			$result = $tsSQL->query($query);
			if (!$result)
				$tsSQL->showError("Query failed: $query ERROR: ".$tsSQL->getError(),"ERROR: Database query failed. If the problem persists please contact a <a href='team.php'>developer</a>.");
			while (list($regex, $casesensitive) = mysql_fetch_row($result)) {
				$regex = '/'.$regex.'/';
				if (!$casesensitive)
					$regex .= 'i';
				if (preg_match($regex, $user))
					return true;
			}
		}
		return false;
	}
	
	public function emailvalid($email) {
		if (!strpos($email, '@')) {
			return false;
		}
		$parts = explode("@", $email);
		$username = isset($parts[0]) ? $parts[0] : '';
		$domain = isset($parts[1]) ? $parts[1] : '';
		
		if(strpos($username, ",") !== false) {
			return false;
		}
		
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
	
	/**
	 * Checks whether there any conflicts are.
	 * @param $username The username to check for spoofs.
	 */
	public function getSpoofs($username) {
		// Get global variables from configuration file and index objects.
		global $dontUseWikiDb, $asSQL, $antispoof_table;
		
		if(!$dontUseWikiDb) {
			// Checks the username using the AntiSpoof class.
			$return = AntiSpoof::checkUnicodeString($username);
			
			// Checks whether the results were positive.
			if($return[0] == 'OK' ) {
				// Assigns the username and escapes it for MySQL.
				$sanitized = $asSQL->escape($return[1]);
				
				// Formulates and executes SQL query to check for usernames.
				$query = "SELECT su_name FROM " . $antispoof_table . " WHERE su_normalized = '$sanitized';";
				$result = $asSQL->query($query);
				
				// Creates empty variables.
				$numSpoof = 0;
				$reSpoofs = array();
				
				// Assigns the current row of the SQL query to a list.
				// Each row of the SQL query is a conflict.
				while (list($su_name) = mysql_fetch_row($result)) {
					// When the variable is set, it indicates a conflict.
					if(isset($su_name)) {
						$numSpoof++;
					}
					// Adds to conflicting username to the array.
					array_push($reSpoofs, $su_name);
				}
				
				// When there was no conflicts FALSE are returned.
				if($numSpoof == 0) {
					return(FALSE);
				} else {
					// If there were conflicts, the conflicts are returned.
					return($reSpoofs);
				}
			} else {
				// If the first variable wasnt OK, the variable are returned.
				return ($return[1]);
			}
		} else {
			// When spoof checking is disabled, FALSE is automatically returned.
			return FALSE;
			}
	}
	
	/**
	 * Do a DNS check on the supplied IP adress.
	 * @param $addr The IP to do the DNS Check on.
	 */
	public function checkdnsbls($addr) {
		// Get object from blacklist file.
		global $dnsbls;
		
		// Reverses the IP adress and stores it as an array.
		$dnsblip = implode('.', array_reverse(explode('.', $addr)));
		
		// Created varaibles and assigns data.
		// These variables would be returned on the end.
		$dnsbldata = '<ul>';
		$banned = false;
	
		// For loop to check whether the IP mathes any of the DNS Blacklists.
		foreach ($dnsbls as $dnsblname => $dnsbl) {
			// Stating which DNS Blacklist are checked.
			echo '<!-- Checking ' . $dnsblname . ' ... ';
			
			// Adds the reversed IP to the current zone.
			// Gets the IP adress of the formed host name.
			// If this method fails, the result would be the same as the input.
			$tmpdnsblresult = gethostbyname($dnsblip . '.' . $dnsbl['zone']);
			
			// Add this result to the same comment as above.
			echo $tmpdnsblresult . ' -->';
			
			// Converts the IP back and forth to make sure it is valid.
			// For an IP to be valid, the result should be equal to the original IP.
			// Note: This step would be skipped if they are equal.
			if (long2ip(ip2long($tmpdnsblresult)) != $tmpdnsblresult) {
				$tmpdnsblresult = 'Nothing.';
				// When the results doesnt match, the current loop is skipped.
				// This would also happen if the gethostbyname originally failed.
				continue;
			}
			
			// TO-DO: Why is the underneath line commented? Couldnt we remove it? LouriePieterse
			// if (!isset($dnsbl['ret'][$lastdigit]) and ($dnsbl['bunk'] == false)) { $tmpdnsblresult = 'Nothing.'; continue; }
			
			// Adds the blacklisted data together and adds to the dnsbldata variable.
			$dnsbldata .= '<li> ' . $dnsblip . '.' . $dnsbl['zone'] . ' (' . $dnsblname . ') = ' . $tmpdnsblresult;
			
			// Creates an array and returns the last part of the reversed IP.
			$lastdigit = explode('.', $tmpdnsblresult);
			$lastdigit = $lastdigit['3'];
			
			// Checks whether the lastdigit matches the ret array.
			// When there is a match it means that the IP is on the blacklisted DNS.
			if (isset ($dnsbl['ret'][$lastdigit])) {
				// Both variables are added to the dnsbldata variable.
				$dnsbldata .= ' (' . $dnsbl['ret'][$lastdigit] . ')';
				// Banned variable set to true.
				$banned = true;
			} else {
				// The lastdigit doesnt match the ret array.
				// Unknown statement assigned to the dnsbldata variable.
				$dnsbldata .= ' (unknown)';
				
				// Checks whether the current DNS Blacklist uses bunk.
				if ($dnsbl['bunk'])
					// Banned variable set to true.
					$banned = true;
			}
			// Link is added to current WHOIS lookup tool for more information.
			$dnsbldata .= ' &mdash;  <a href="' . str_replace('%i', $addr, $dnsbl['url']) . "\"> more information</a>.\n";
		}
		
		// All the used variables are cleared.
		unset ($dnsblip, $dnsblname, $dnsbl, $tmpdnsblresult, $lastdigit);
		
		// Variable is closed with the needed HTML code.
		$dnsbldata .= '</ul>';
		
		// The data are printed in a comment.
		echo '<!-- ' . $dnsbldata . ' -->';
		
		// Both arrays are returned for further use.
		// If the banned array was set to true at any stage, it would be here true as well.
		// The dnsbldata variable would contain all of the matching data.
		return array (
			$banned,
			$dnsbldata);
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
				// The requester is fooled that the request was successful.
				echo "$message\n";
				
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
				$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $tsSQL->escape($ip) . '\',\'ClueBot\',\'' . $tsSQL->escape('Blacklist Hit: ' . $blname . ' - ' . $check . ' ' . $ip . ' ' . $email . ' ' . $_SERVER['HTTP_USER_AGENT']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
				$tsSQL->query($query);
				
				// Terminates the current script, as the data matched the blacklist.
				// This is done because the requesting process should be stopped.
				die();
			}
		}
	}
	
	/*
	* Updates the entries checksum (on each load of that entry, to prevent dupes).
	* @param $id The ID to use.
	*/
	public function upcsum($id) {
		// Get the needed objects from index file.
		global $tsSQL;
		
		// Formulates and executes SQL query to return the request.
		$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
		$result = $tsSQL->query($query);
		
		// Display error upon failure.
		if (!$result) {
			$tsSQL->showError("Query failed: $query ERROR: " . $tsSQL->getError(),"Database query error.");
		}
		
		// Assigns the row to the varibale.
		$pend = mysql_fetch_assoc($result);
		
		// Generates the required HASH.
		$hash = md5($pend['pend_id'] . $pend['pend_name'] . $pend['pend_email'] . microtime());
		
		// Formulates and executes SQL query to update the request HASH.
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
		// not working, and not needed. Also causing problems with other things
		// TODO: remove all calls to this function.
	}
	
	public function doDnsBlacklistCheck() {
		// Get global variable from configuration file and an object from the index file.
		global $enableDnsblChecks, $tsSQL, $accbot, $enableSQLError, $messages;
		
		if($enableDnsblChecks == 1){
			// Assings IP and email variables.
			$ip = $_SERVER['REMOTE_ADDR'];
			$email = $_POST['email'];
			
			// Do the DNS check on the IP variable.
			$dnsblcheck = $this->checkdnsbls($ip);
			
			// Checks whether there was any occurance of a blacklisted DNS.
			// The first variable if the array only states whether there were matchcs.
			if ($dnsblcheck['0'] == true) {
				// Gets message to display to the user.
				$message = $messages->getMessage(15);
				
				// Displays the appropiate message to the user.
				// The requester is fooled that the request was successful.
				echo "$message\n";
				
				// Gets the current date.
				$now = date("Y-m-d H-i-s");
				
				// Assings username and escapes for MySQL.
				$siuser = $tsSQL->escape($_POST['name']);
				
				// ???
				$cmt = $tsSQL->escape("FROM $ip $email<br />" . $dnsblcheck['1']);
				
				// Sends a message to the ACC Bot which states that there was a hit on the DNS blacklists.
				$accbot->send("[DNSBL] HIT: " . $_POST['name'] . " $ip $email " . $_SERVER['HTTP_USER_AGENT']);
				
				// Formulates and executes the SQL query to add the match to the ACC Log.
				// ???
				$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('DNSBL', '$siuser', 'DNSBL Hit', '$now', '$cmt');";
				$tsSQL->query($query);
				
				// Formulates and executes the SQL query to add the match to the ACC Ban list.
				// Cluebot is used as the ban user, as this was done by a script.
				// The IP is banned for 172800 seconds, or 2 days.
				$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\'' . $ip . '\',\'ClueBot\',\'' . $tsSQL->escape("DNSBL Hit:<br />\n" . $dnsblcheck['1']) . '\',\'' . $now . '\',\'' . (time() + 172800) . '\');';
				$tsSQL->query($query);
				
				// Terminates the current script, as the data matched the blacklist.
				// This is done because the requesting process should be stopped.
				die();
			}
		}
	}
	
	/**
	 * Do some automated checks on the username and email adress.
	 * @param $user The username to check.
	 * @param $email The email adress to check.
	 */
	public function finalChecks($user,$email) {
		// Get objects from the index file.
		global $messages, $tsSQL, $skin, $caSQL, $dontUseWikiDb;
		
		// Used to check if a request complies to the automated tests.
		// The value is reseted, as the user has another chance to complete the form.
		$fail = 0;
		
		// Checks whether the username is already in use on Wikipedia.
		$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=" . urlencode($_POST['name']) . "&format=php");
		$ue = unserialize($userexist);
		if (!isset ($ue['query']['users']['0']['missing'])) {
			$message = $messages->getMessage(10);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the username is already part of a SUL account.

		$reqname = str_replace("_", " ", $_POST['name']);
		$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&meta=globaluserinfo&guiuser=" . urlencode($reqname) . "&format=php");
		$ue = unserialize($userexist);
		if (isset ($ue['query']['globaluserinfo']['id'])) {
			$message = $messages->getMessage(28);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the username consists entirely of numbers.
		$nums = preg_match("/^[0-9]+$/", $_POST['name']);
		if ($nums > 0) {
			$message = $messages->getMessage(11);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the username is an email adress.
		$unameismail = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['name']);
		if ($unameismail > 0) {
			$message = $messages->getMessage(12);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the username contains invalid characters.
		$unameisinvalidchar = preg_match('/[\#\/\|\[\]\{\}\@\%\:\~\<\>]/', $_POST['name']);
		if ($unameisinvalidchar > 0 || ltrim( rtrim( $_POST['name'] == "" ) ) ) {
			$message = $messages->getMessage(13);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the email adresses match.
		if($_POST['email'] != $_POST['emailconfirm']) {
			$message = $messages->getMessage(27);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the email adress is valid.
		if (!$this->emailvalid($_POST['email'])) {
			$message = $messages->getMessage(14);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether the email adress is valid.
		$mailiswmf = preg_match('/.*@.*wiki(m.dia|p.dia)\.(org|com)/i', $email);
		if ($mailiswmf != 0) {
			$message = $messages->getMessage(14);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}

		// (JIRA) ACC-55
		// Checks whether the username has a traling space of underscore.
		$trailingspace = substr($_POST['name'], strlen($_POST['name']) - 1);
		if ($trailingspace == " " || $trailingspace == "_"  ) {
			// TODO: WTF?!? Message 25 does not exist in the database. 2010-03-06 stw.
			$message = $messages->getMessage(25);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}

		// Checks whether there arent already a request for the username.
		$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_name = '$user'";
		$result = $tsSQL->query($query);
		$row = mysql_fetch_assoc($result);
		if ($row['pend_id'] != "") {
			$message = $messages->getMessage(17);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether there arent already a request for the email adress.
		$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_email = '$email'";
		$result = $tsSQL->query($query);
		$row = mysql_fetch_assoc($result);
		if ($row['pend_id'] != "") {
			$message = $messages->getMessage(18);
			$skin->displayRequestMsg("$message<br />\n");
			$fail = 1;
		}
		
		// Checks whether any of the automated checks were failed.
		// Notifies the requester that the request was unsuccessfull.
		if ($fail == 1) {
			// Gets message to display to the user.
			$message = $messages->getMessage(16);
			
			// Displays the appropiate message to the user.
			$skin->displayRequestMsg("$message<br />\n");
			
			// Display the request form and footer of the interface.
			$skin->displayRequest();
			$skin->displayPfooter();
			
			// Terminates the current script, as automated checks are failed.
			die();
		}
	}
	
	/**
	 * Inserts the account request into the system database.
	 * @param $user The username to add.
	 * @param $email The email adress to add.
	 */
	public function insertRequest($user,$email) {
		// Get objects from the index file and globals from configuration.
		global $enableEmailConfirm, $messages, $tsSQL, $defaultReserver, $squidIpList;
		
		// Checks whether email confirmation is enabled.
		if ($enableEmailConfirm == 1) {
			$message = $messages->getMessage(15);
		} else {
			$message = $messages->getMessage(24);
		}
		
		// Display message
		echo "$message\n";
		
		// Convert all applicable characters to HTML entities.
		$user = htmlentities($user,ENT_COMPAT,'UTF-8');
		$email = htmlentities($email,ENT_COMPAT,'UTF-8');
		
		// Assigns the comment and IP to variables and escapes for MySQL.
		$comments = $tsSQL->escape(htmlentities($_POST['comments'],ENT_COMPAT,'UTF-8'));
		$ip = $tsSQL->escape(htmlentities($_SERVER['REMOTE_ADDR']),ENT_COMPAT,'UTF-8');
		$proxystring = 'NULL';
		if ($this->istrusted($ip)|| array_search($ip, $squidIpList)) {
			$xffheader = explode(",", getenv("HTTP_X_FORWARDED_FOR"));
			$sourceip = trim($xffheader[sizeof($xffheader)-1]);
			if (preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $sourceip)) {
				$proxyip = $ip;
				$ip = $sourceip;
			}
			
			if(!array_search($ip, $squidIpList)){
				$proxystring = "'" . $proxyip . "'";
			}
		}
		$useragent = $tsSQL->escape(htmlentities($_ENV["HTTP_USER_AGENT"],ENT_COMPAT,'UTF-8'));
		
		// Gets the current date and time.
		$dnow = date("Y-m-d H-i-s");
		
		if($this->getSpoofs($user)) {
			// If there were spoofs an Admin should handle the request.
			$uLevel = "Admin";
		} else {
			// Otherwise anyone could handle the request.
			$uLevel = "Open";
		}
		
		if ($uLevel != "Admin" && $this->isblacklisted($user))
			$uLevel = "Admin";
			
		// Formulates and executes SQL query to insert the new request.
		$query = "INSERT INTO acc_pend (pend_id , pend_email , pend_ip , pend_proxyip , pend_name , pend_cmt , pend_status , pend_date, pend_reserved, pend_useragent) VALUES ( NULL , '$email', '$ip', $proxystring, '$user', '$comments', '$uLevel' , '$dnow', '$defaultReserver', '$useragent' );";
		$result = $tsSQL->query($query);
		
		// Display error message upon failure.
		if (!$result) {
			die("ERROR: No result returned. (acc_pend)");
		}
		
		// Formulates and executes SQL query to return data regarding the request. 
		$query = "SELECT pend_id,pend_email FROM acc_pend WHERE pend_name = '$user' ORDER BY pend_id DESC LIMIT 1;";
		$result = $tsSQL->query($query);
		
		// Display error message upon failure.
		if (!$result) {
			die("ERROR: No result returned. (select)");
		}
		
		// Gets the current row from the SQL query.
		$row = mysql_fetch_assoc($result);
		
		// Gets the ID of the request.
		$pid = $row['pend_id'];
		
		// Checks whether the ID is not zero nor empty.
		if ($pid != 0 || $pid != "") {
			// Updates the entries checksum.
			$this->upcsum($pid);
		}
		
		// Checks whether email confirmation is activated.
		if ($enableEmailConfirm == 1) {
			// Confirms either a new users e-mail.
			$this->confirmEmail($pid);
		}
	}
}
?>
