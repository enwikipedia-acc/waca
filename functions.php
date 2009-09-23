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
**FunPika    ( http://en.wikipedia.org/wiki/User:FunPika )   **
**PRom3th3an ( http://en.wikipedia.org/wiki/User:Promethean )**
**Chris_G ( http://en.wikipedia.org/wiki/User:Chris_G )      **
**************************************************************/

global $ACC;
global $tsurl;

if ($ACC != "1") {
    header("Location: $tsurl/");
    die();
} //Re-route, if you're a web client.

require_once('queryBrowser.php');
require_once('LogClass.php');
include_once('AntiSpoof.php');

function formatForBot( $data ) { 		
	global $ircBotCommunicationKey; 		
	$pData[0] = encryptMessage( $data, $ircBotCommunicationKey ); 		
	$pData[1] = $data; 		
	$sData = serialize( $pData ); 		
	return $sData; 		
} 		

function encryptMessage( $text, $key ) {
	$keylen = strlen($key);
	
	if( $keylen % 2 == 0 ) {
		$power = ord( $key[$keylen / 2] ) + $keylen;
	}
	else {
		$power = ord( $key[($keylen / 2) + 0.5] ) + $keylen;
	}
	
	$textlen = strlen( $text );
	while( $textlen < 64 ) {
		$text .= $text;
		$textlen = strlen( $text );
	}
	
	$newtext = null;
	for( $i = 0; $i < 64; $i++ ) {
		$pow = pow( ord( $text[$i] ), $power );
		$pow = str_replace( array( '+', '.', 'E' ), '', $pow );
		$toadd = dechex( substr($pow, -2) );
		while( strlen( $toadd ) < 2 ) {
			$toadd .= 'f';
		}
		if( strlen( $toadd ) > 2 ) $toadd = substr($toadd, -2);
		$newtext .= $toadd;
	}
	
	return $newtext;
}

function _utf8_decode($string) {
	/*
	* Improved utd8_decode() function
	*/
	$tmp = $string;
	$count = 0;
	while (mb_detect_encoding($tmp) == "UTF-8") {
		$tmp = utf8_decode($tmp);
		$count++;
	}

	for ($i = 0; $i < $count -1; $i++) {
		$string = utf8_decode($string);
	}
	return $string;
}

function setForceLogout( $uid ) {
	$uid = sanitize( $uid );
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	$link = mysql_select_db($toolserver_database);
	if( !$link ) {
		sqlerror(mysql_error(),"Error selecting database.");
	}
	$query = "UPDATE acc_user SET user_forcelogout = '1' WHERE user_id = '$uid';";
	$result = mysql_query($query);
}

function forceLogout( $uid ) {
	$uid = sanitize( $uid );
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	$link = mysql_select_db($toolserver_database);
	if( !$link ) { 
		sqlerror(mysql_error(),"Error selecting database.");	
	}
	$query = "SELECT user_forcelogout FROM acc_user WHERE user_id = '$uid';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$row = mysql_fetch_assoc($result);
	if( $row['user_forcelogout'] == "1" ) {
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy( );
		echo "You have been forcibly logged out, probably due to being renamed. Please log back in.";
		$query = "UPDATE acc_user SET user_forcelogout = '0' WHERE user_id = '$uid';";
		$result = mysql_query($query);
		die( showfootern( ) );
	}
}

function getSpoofs( $username ) {
	global $dontUseWikiDb;
	if( !$dontUseWikiDb ) {
		global $toolserver_username;
		global $toolserver_password;
		global $antispoof_host;
		global $antispoof_db;
		global $antispoof_table;
		global $antispoof_password;
		$spooflink = mysql_pconnect($antispoof_host, $toolserver_username, $antispoof_password);
		$link = mysql_select_db($antispoof_db, $spooflink);
		if( !$link ) { 
			sqlerror(mysql_error(),"Error selecting database.");	
		}
		$return = AntiSpoof::checkUnicodeString( $username );
		if($return[0] == 'OK' ) {		
			$sanitized = sanitize($return[1]);
			$query = "SELECT su_name FROM ".$antispoof_table." WHERE su_normalized = '$sanitized';";
			$result = mysql_query($query, $spooflink);
			if(!$result) sqlerror("ERROR: No result returned. - ".mysql_error(),"Database error.");
			$numSpoof = 0;
			$reSpoofs = array();
			while ( list( $su_name ) = mysql_fetch_row( $result ) ) {
				if( isset( $su_name ) ) { $numSpoof++; }
				array_push( $reSpoofs, $su_name );
			}
			mysql_close( $spooflink );
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

function sanitise($what) { return sanitize($what); }
function sanitize($what) {
	/*
	* Shortcut to mysql_real_escape_string
	*/
	global $tsSQLlink;
	$what = mysql_real_escape_string($what,$tsSQLlink);
	$what = htmlentities($what);
	return ($what);
}

function upcsum($id) {
	/*
	* Updates the entries checksum (on each load of that entry, to prevent dupes)
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	$link = mysql_select_db($toolserver_database);
	if( !$link ) { 
	 sqlerror(mysql_error(),"Error selecting database.");
	}
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$pend = mysql_fetch_assoc($result);
	$hash = md5($pend['pend_id'] . $pend['pend_name'] . $pend['pend_email'] . microtime());
	$query = "UPDATE acc_pend SET pend_checksum = '$hash' WHERE pend_id = '$id';";
	$result = mysql_query($query);
}

function csvalid($id, $sum) {
	/*
	* Checks to make sure the entries checksum is still valid
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	$link = mysql_select_db($toolserver_database);
	if( !$link ) { 
		sqlerror(mysql_error(),"Error selecting database.");
	}
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$pend = mysql_fetch_assoc($result);
	if (!isset($pend['pend_checksum'])) {
		upcsum($id);
		return (1);
	}
	if ($pend['pend_checksum'] == $sum) {
		return (1);
	} else {
		return (0);
	}
}

function sendtobot($message) {
	/*
	* Send to the IRC bot via UDP
	*/
	global $whichami;
	sleep(3);
	$fp = fsockopen("udp://91.198.174.211", 9001, $erno, $errstr, 30);
	if (!$fp) {
		echo "SOCKET ERROR: $errstr ($errno)<br />\n";
	}
	fwrite($fp, formatForBot( chr(2)."[$whichami]".chr(2).": $message" ) );
	fclose($fp);
}

function showhowma() {
	/*
	* Show how many users are logged in, in the footer
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$howma = gethowma();
	unset ($howma['howmany']);
	foreach ($howma as &$oluser) {
		$oluser = sanitize( $oluser );
		$query = "SELECT * FROM acc_user WHERE user_name = '$oluser';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error() . " f190","Database query error.");
	$row = mysql_fetch_assoc($result);
	$uid = $row['user_id'];
		$oluser = stripslashes($oluser);
		$oluser = "<a href=\"users.php?viewuser=$uid\">$oluser</a>";
	}
	unset($oluser);
	$out = "";
	$out = implode(", ", $howma);
	$out = ltrim(rtrim($out));
	return ($out);
}

function gethowma() {
	/*
	* Get how many people are logged in
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$last5min = time() - 300; // Get the users active as of the last 5 mins
	
	$last5mins = date("Y-m-d H:i:s", $last5min); // TODO: This produces a PHP Strict Standards error message. See next line
	//Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings. Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function. In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
	
	$query = "SELECT user_name FROM acc_user WHERE user_lastactive > '$last5mins';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$whoactive = array ();
	while ( list( $user_name ) = mysql_fetch_row( $result ) ) {
		array_push( $whoactive, $user_name );
	}
	$howma = count($whoactive);
	$whoactive['howmany'] = $howma;
	return ($whoactive);
}

function showmessage($messageno) {
	/* 
	* Show user-submitted messages from mySQL
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$row = mysql_fetch_assoc($result);
	return ($row['mail_text']);
}

function sendemail($messageno, $target) {
	/*
	* Send a "close pend ticket" email to the end user. (created, taken, etc...)
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$row = mysql_fetch_assoc($result);
	$mailtxt = $row['mail_text'];
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($target, "RE: English Wikipedia Account Request", $mailtxt, $headers);
}

function checksecurity($username) {
	/*
	* Check the user's security level on page load, and bounce accordingly
	*/
	global $secure;
	if (hasright($username, "New")) {
		echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
		echo showfootern();
		die();
	} elseif (hasright($username, "Suspended")) {
		echo "I'm sorry, but, your account is presently suspended.<br />\n";
		echo showfootern();
		die();
	} elseif (hasright($username, "Declined")) {
		$username = sanitize($username);
		$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
		$result = mysql_query($query);
		if (!$result) {
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		}
		$row = mysql_fetch_assoc($result);
		$query2 = "SELECT * FROM acc_log WHERE log_pend = '" . $row['user_id'] . "' AND log_action = 'Declined' ORDER BY log_id DESC LIMIT 1;";
		$result2 = mysql_query($query2);
		if (!$result2) {
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		}
		$row2 = mysql_fetch_assoc($result2);
		echo "I'm sorry, but, your account request was <strong>declined</strong> by <strong>" . $row2['log_user'] . "</strong> because <strong>\"" . $row2['log_cmt'] . "\"</strong> at <strong>" . $row2['log_time'] . "</strong>.<br />\n";
		echo "Related information (please include this if appealing this decision)<br />\n";
		echo "user_id: " . $row['user_id'] . "<br />\n";
		echo "user_name: " . $row['user_name'] . "<br />\n";
		echo "user_onwikiname: " . $row['user_onwikiname'] . "<br />\n";
		echo "user_email: " . $row['user_email'] . "<br />\n";
		echo "log_id: " . $row2['log_id'] . "<br />\n";
		echo "log_pend: " . $row2['log_pend'] . "<br />\n";
		echo "log_user: " . $row2['log_user'] . "<br />\n";
		echo "log_time: " . $row2['log_time'] . "<br />\n";
		echo "log_cmt: " . $row2['log_cmt'] . "<br />\n";
		echo "<br /><big><strong>To appeal this decision, please e-mail <a href=\"mailto:accounts-enwiki-l@lists.wikimedia.org\">accounts-enwiki-l@lists.wikimedia.org</a> with the above information, and a reasoning why you believe you should be approved for this interface.</strong></big><br />\n";
		echo showfootern();
		die();
	} elseif (hasright($username, "User") || hasright($username, "Admin") ) {
		$secure = 1;
	} else {
		//die("Not logged in!");
	}
}

function listrequests($type, $hideip) {
	/*
	* List requests, at Zoom, and, on the main page
	*/
	global $toolserver_database, $tsSQLlink;
	global $secure;
	global $enableEmailConfirm;
	if($secure != 1) { die("Not logged in"); }
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database.");

	if ($enableEmailConfirm == 1) {
		if ($type == 'Admin' || $type == 'Open') {
			$query = "SELECT * FROM acc_pend WHERE pend_status = '$type' AND pend_mailconfirm = 'Confirmed';";
		} else {
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
		}
        } else {
		if ($type == 'Admin' || $type == 'Open') {
			$query = "SELECT * FROM acc_pend WHERE pend_status = '$type';";
		} else {
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
		}
        }
    
	$result = mysql_query($query);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");

	$tablestart = "<table cellspacing=\"0\">\n";
	$tableend = "</table>\n";
	$reqlist = '';
	$currentreq = 0;
	while ( $row = mysql_fetch_assoc( $result ) ) {
		$currentreq += 1;
		$uname = urlencode($row['pend_name']);
		#$uname = str_replace("+", "_", $row[pend_name]);
		$rid = $row['pend_id'];
		if ($row['pend_cmt'] != "") {
			$cmt = "<a class=\"request-src\" href=\"acc.php?action=zoom&amp;id=$rid\">Zoom (CMT)</a> ";
		} else {
			$cmt = "<a class=\"request-src\" href=\"acc.php?action=zoom&amp;id=$rid\">Zoom</a> ";
		}
		$query2 = "SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_ip` = '" . $row['pend_ip'] . "' AND `pend_id` != '" . $row['pend_id'] . "' AND `pend_mailconfirm` = 'Confirmed';";
		$result2 = mysql_query($query2);
		if (!$result2)
			sqlerror("Query failed: $query2 ERROR: " . mysql_error(),"Database query error.");
		$otheripreqs = mysql_fetch_assoc($result2);
		$query3 = "SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_email` = '" . $row['pend_email'] . "' AND `pend_id` != '" . $row['pend_id'] . "' AND `pend_mailconfirm` = 'Confirmed';";
		$result3 = mysql_query($query3);
		if (!$result3)
			sqlerror("Query failed: $query3 ERROR: " . mysql_error(),"Database query error.");
		$otheremailreqs = mysql_fetch_assoc($result3);
		$out = '<tr';
		if ($currentreq % 2 == 0) {
			$out .= ' class="alternate">';
		} else {
			$out .= '>';
		}
		if ($type == 'Admin' || $type == 'Open') {
			$out .= '<td><small>' . $currentreq . '.    </small></td><td><small>'; //List item
			$out .= $cmt .'</small></td><td><small>'; // CMT link.
		} else {
			$out .= '<td><small>' . "\n"; //List item
		}

		$sid = sanitize($_SESSION['user']);
		$query4 = "SELECT * FROM acc_user WHERE user_name = '$sid';";
		$result4 = mysql_query($query4, $tsSQLlink);
		if (!$result4)
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
		$row4 = mysql_fetch_assoc($result4);

		// Email.
		$out .= '[ </small></td><td><small><a class="request-src" href="mailto:' . $row['pend_email'] . '">' . $row['pend_email'] . '</a>';

		$out .= '</small></td><td><small><span class="request-src">' . "\n";
		if ($otheremailreqs['count'] == 0) {
			$out .= '(' . $otheremailreqs['count'] . ')';
		} else {
			$out .= '(</span><b><span class="request-mult">' . $otheremailreqs['count'] . '</span></b><span class="request-src">)';
		}

		if ( $row4['user_secure'] > 0 ) {
			$wikipediaurl = "https://secure.wikimedia.org/wikipedia/en/";
			$metaurl = "https://secure.wikimedia.org/wikipedia/meta/";
		} else {
			$wikipediaurl = "http://en.wikipedia.org/";
			$metaurl = "http://meta.wikimedia.org/";
		}
		
            
		if ($hideip == FALSE || hasright($_SESSION['user'], 'Admin')) {
		// IP UT:
		$out .= '</span></small></td><td><small> | </small></td><td><small><a class="request-src" name="ip-link" href="'.$wikipediaurl.'wiki/User_talk:' . $row['pend_ip'] . '" target="_blank">';
		$out .= $row['pend_ip'] . '</a> ';

		$out .= '</small></td><td><small><span class="request-src">' . "\n";
		if ($otheripreqs['count'] == 0) {
			$out .= '(' . $otheripreqs['count'] . ')';
		} else {
			$out .= '(</span><b><span class="request-mult">' . $otheripreqs['count'] . '</span></b><span class="request-src">)';
		}

		// IP contribs
		$out .= '</span></small></td><td><small><a class="request-src" href="'.$wikipediaurl.'wiki/Special:Contributions/';
		$out .= $row['pend_ip'] . '" target="_blank">c</a> ';
		
		// IP global contribs
		$out .= '<a class="request-src" href="http://toolserver.org/~luxo/contributions/contributions.php?lang=en&blocks=true&user=' . $row['pend_ip'] . '" target="_blank">gc</a> ';
		
		// IP blocks
		$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special:Log&amp;type=block&amp;page=User:';
		$out .= $row['pend_ip'] . '" target="_blank">b</a> ';
		
		// rangeblocks
		$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special%3ABlockList&ip=';
		$out .= $row['pend_ip'] . '" target="_blank">r</a> ';
		
		// Global blocks
		$out .= '<a class="request-src" href="'.$metaurl.'w/index.php?title=Special:Log&amp;type=gblblock&amp;page=User:';
		$out .= $row['pend_ip'] . '" target="_blank">gb</a> ';
		
		// Global range blocks/Locally disabled Global Blocks
		$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special%3AGlobalBlockList&ip=';
		$out .= $row['pend_ip'] . '" target="_blank">gr</a> ';

		// IP whois
		$out .= '<a class="request-src" href="http://toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=' . $row['pend_ip'] . '" target="_blank">w</a> ';

		// Abuse Filter
		$out .= '<a class="request-src" href="' . $wikipediaurl . 'w/index.php?title=Special:AbuseLog&wpSearchUser=' . $row['pend_ip'] . '" target="_blank">af</a> ';
		
            }
		// Username U:
		$duname = _utf8_decode($row['pend_name']);
		$out .= '</small></td><td><small><a class="request-req" href="'.$wikipediaurl.'wiki/User:' . $uname . '" target="_blank"><strong>' . $duname . '</strong></a> ';

		// 	Creation log
		$out .= '</small></td><td><small>(<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page=User:';
		$out .= $uname . '" target="_blank">Creation</a> ';
		
		// 	SUL link
		$out .= '<a class="request-req" href="http://toolserver.org/~vvv/sulutil.php?user=';
		$out .= $uname . '" target="_blank">SUL</a> ';	
		
		// 	User list
    $out .= '<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special%3AListUsers&amp;username=';
    $out .= $uname . '&amp;group=&amp;limit=1" target="_blank">List</a> ';
  
    // Google
    $out .= '<a class="request-req" href="http://www.google.com/search?q=' . $uname . '">Google</a>) ' . "\n";

		// Create user link
		$out .= '<b><a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special:UserLogin/signup&amp;wpName=';
		$out .= $uname . '&amp;wpEmail=' . $row['pend_email'] . '&amp;uselang=en-acc" target="_blank">Create!</a></b></small></td><td><small> ';


		// Done
		$out .= '| </small></td><td><small><a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=1&amp;sum=' . $row['pend_checksum'] . '">Done!</a>';

		// Similar
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=2&amp;sum=' . $row['pend_checksum'] . '">Similar</a>';

		// Taken
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=3&amp;sum=' . $row['pend_checksum'] . '">Taken</a>';

		// UPolicy
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=4&amp;sum=' . $row['pend_checksum'] . '">UPolicy</a>';

		// Invalid
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=5&amp;sum=' . $row['pend_checksum'] . '">Invalid</a>';
		
		// Custom
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=custom&amp;sum=' . $row['pend_checksum'] . '">Custom</a>';
		
		// Defer to admins or users
		if (is_numeric($type)) {
			$type = $row['pend_status'];
		}
		if (!isset ($target)) {
			$target = "zoom";
		}
		if ($type == 'Open') {
			$target = 'admins';
		}
		elseif ($type == 'Admin') {
			$target = 'users';
		}
		if ($target == 'admins')
		{
			$out .= " - <a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=$target\">Defer to flagged users</a>";
		}
		elseif($target == 'users') {
			$out .= " - <a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=$target\">Defer to users</a>";
		} 
		else 
		{
			$out .= " - <a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=users\">Reset Request</a>";
		}
		// Drop
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=0&amp;sum=' . $row['pend_checksum'] . '">Drop</a>' . "\n";

		if(hasright($_SESSION['user'], "Admin")) {
		// Ban IP
		$out .= '</small></td><td><small> |</small></td><td><small> Ban: </small></td><td><small><a class="request-ban" href="acc.php?action=ban&amp;ip=' . $row['pend_id'] . '">IP</a> ';

		// Ban email
		$out .= '- <a class="request-ban" href="acc.php?action=ban&amp;email=' . $row['pend_id'] . '">E-Mail</a>';

		//Ban name
		$out .= ' - <a class="request-ban" href="acc.php?action=ban&amp;name=' . $row['pend_id'] . '">Name</a>';
		}
		
		// Check to see if we want to have all the reserving stuff on
		global $enableReserving;
		if( $enableReserving )
		{
			$reserveByUser = isReserved($row['pend_id']);			
			// if request is reserved, show reserved message
			if( $reserveByUser != 0 )
			{
				if( $reserveByUser == $_SESSION['userID'])
				{
					$out .= "</small></td><td><small> | </small></td><td><small>YOU are handling this request. <a href=\"acc.php?action=breakreserve&resid=" . $row['pend_id']. "\">Break reservation</a>";
				} else {
					$out .= "</small></td><td><small> | </small></td><td><small>Being handled by <a href=\"users.php?viewuser=$reserveByUser\">" . getUsernameFromUid($reserveByUser) . "</a>";
				}
			}
			else // not being handled, do you want to handle this request?
			{
				$out .= "</small></td><td><small> | </small></td><td><small><a href=\"acc.php?action=reserve&resid=" . $row['pend_id']. "\">Mark as being handled</a>";
			}
		}

		$out .= '</small></td></tr>';
		$reqlist .= $out;
	}
	if( $currentreq == 0 ) {
		return( "<i>No requests at this time</i>" );
	} else {
		return ($tablestart . $reqlist . $tableend);
	}

}

/**
* Retrieves a username from a user id
*/
function getUsernameFromUid($userid)
{
	$uid = sanitize($userid);
	$query = "SELECT user_name FROM acc_user WHERE user_id = $uid;";
	$result = mysql_query($query);
	if (!$result)
		Die("Error determining user from UID.");
	$row = mysql_fetch_assoc($result);
	return $row['user_name'];
	$result = mysql_query($query);
	if (!$result)
		Die("Error determining user from UID.");
}

/**
* Checks to see if a request is marked as reserved by a user
* Returns uid if reserved, false if not
*/
function isReserved($requestid)
{
	if (!preg_match('/^[0-9]*$/',$requestid)) {
		die('Invalid Input.'); // TODO: make this a pretty error message
	}
	$rqid = sanitize($requestid);
	$query = "SELECT pend_reserved FROM acc_pend WHERE pend_id = $rqid;";
	$result = mysql_query($query);
	if (!$result)
		Die("Error determining reserved status of request.");
	$row = mysql_fetch_assoc($result);
	if(isset($row['pend_reserved']) && $row['pend_reserved'] != 0) { return $row['pend_reserved'];} else {return false;}
}

function makehead($username) {
	/*
	* Show page header (retrieved by MySQL call)
	*/
	global $tsSQLlink, $toolserver_database;
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database. If the problem persists please contact a <a href='team.php'>developer</a>.");
	$suin = sanitize($username);
	$rethead = '';
	$query = "SELECT * FROM acc_user WHERE user_name = '$suin' LIMIT 1;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$row = mysql_fetch_assoc($result);
	$_SESSION['user_id'] = $row['user_id'];
	forceLogout( $_SESSION['user_id'] );
	$out = showmessage('21');
	if (isset ($_SESSION['user'])) { //Is user logged in?
		if (hasright($username, "Admin")) {
			$out = preg_replace('/\<a href\=\"acc\.php\?action\=messagemgmt\"\>Message Management\<\/a\>/', "\n<a href=\"acc.php?action=messagemgmt\">Message Management</a>\n<a href=\"acc.php?action=usermgmt\">User Management</a>\n", $out);
		}
		$rethead .= $out;
		$rethead .= "<div id = \"header-info\">Logged in as <a href=\"users.php?viewuser=" . $_SESSION['user_id'] . "\"><span title=\"View your user information\">" . $_SESSION['user'] . "</span></a>.  <a href=\"acc.php?action=logout\">Logout</a>?</div>\n";
		//Update user_lastactive
		
		$now = date("Y-m-d H-i-s"); // TODO: This produces a PHP Strict Standards error message. See next line
		//Strict Standards: date() [function.date]: It is not safe to rely on the system's timezone settings. Please use the date.timezone setting, the TZ environment variable or the date_default_timezone_set() function. In case you used any of those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
	
		$query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '" . $_SESSION['user_id'] . "';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	} else {
		$rethead .= $out;
		$rethead .= "<div id = \"header-info\">Not logged in.  <a href=\"acc.php\"><span title=\"Click here to return to the login form\">Log in</span></a>/<a href=\"acc.php?action=register\">Create account</a>?</div>\n";
	}
	return ($rethead);
}

function showfootern() {
	/*
	* Show footer (not logged in)
	*/
	return showmessage('22');
}

function showfooter() {
	/*
	* Show footer (logged in)
	*/
	global $enableLastLogin;
	if ($enableLastLogin) {
		$timestamp = "at ".date('H:i',$_SESSION['lastlogin_time']);
		if (date('jS \of F Y',$_SESSION['lastlogin_time'])==date('jS \of F Y')) {
			$timestamp .= " today";
		} else {
			$timestamp .= " on the ".date('jS \of F, Y',$_SESSION['lastlogin_time']);
		}
		if ($_SESSION['lastlogin_ip']==$_SERVER['REMOTE_ADDR']) {
			$out2 = "<br /><div align=\"center\"><small>You last logged in from this computer $timestamp.</small></div><br /><br />";
		} else {
			$out2 = "<br /><div align=\"center\"><small>You last logged in from <a href=\"http://toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=".$_SESSION['lastlogin_ip']."\">".$_SESSION['lastlogin_ip']."</a> $timestamp.</small></div><br /><br />";
		}
	} else {
		$out2 = '';
	}
	
	$howmany = array ();
	$howmany = gethowma();
	$howout = showhowma();
	$howma = $howmany['howmany'];
	$out = showmessage('23');
	if ($howma != 1) // not equal to one, as zero uses the plural form too.
		$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma Account Creators currently online (past 5 minutes): $howout</small></div>\n$out2", $out);
	else
		$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma Account Creator currently online (past 5 minutes): $howout</small></div>\n$out2", $out);
	return $out;
}

function showlogin( $action = null, $params = null ) {
	global $_SESSION, $tsSQLlink, $useCaptcha;
	$html ='<div id="sitenotice">Please login first, and we\'ll send you on your way!</div>
    <div id="content">
    <h2>Login</h2>';

    if (isset($_GET['error'])) {
    	if ($_GET['error']=='authfail') {
    		$html .= "<p>Username and/or password incorrect. Please try again.</p>";
    	} elseif ($_GET['error']=='captchafail') {
    		$html .= "<p>I'm sorry, the captcha you entered was incorrect, please try again.</p>";
    	} elseif ($_GET['error']=='captchamissing') {
    		$html .= "<p>Please complete the captcha.</p>";
    	}
    }
    $html .='<form action="acc.php?action=login&amp;nocheck=1';
    if (( $action ) && ($action != "logout")) {
    	$html .= "&amp;newaction=".$action;
    	foreach ($params as $param => $value) { 
    		if ($param != '' && $param != "action") {
    			$html .= "&amp;$param=".$value;
    		}
    	}
    }
    $html .= '" method="post">
    <div class="required">
        <label for="username">Username:</label>
        <input id="username" type="text" name="username"/>
    </div>
    <div class="required">
        <label for="password">Password:</label>
        <input id="password" type="password" name="password"/>
    </div>';
    if ($useCaptcha) {
            require_once 'includes/captcha.php';
            $captcha = new captcha;
            if ($captcha->showCaptcha()) {
            	$captcha_id = $captcha->generateId();
	    	$html .= '<div class="required">
		<label for="captcha">Captcha:</label>
		<input id="captcha" type="text" name="captcha"/>
		<input name="captcha_id" type="hidden" value="'.$captcha_id.'" />
		<img src="captcha.php?id='.$captcha_id.'" />
	    	</div>';
	    }
    }
    $html .= '<div class="submit">
        <input type="submit" value="Login"/>
    </div>
    </form>
    <br />
    Need Tool access?
    <br /><a href="acc.php?action=register">Register!</a> (Requires approval)<br />
    <a href="acc.php?action=forgotpw">Forgot your password?</a><br />';
	$html .= showfootern();
	return $html;
}

function getdevs() {
	global $regdevlist;
	$newdevlist = array_reverse($regdevlist);
	$temp = $newdevlist['0'];
	unset ($newdevlist['0']);
	foreach ($newdevlist as $dev) {
		$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:" . $dev['1'] . "\">" . $dev['0'] . "</a>, ";
	}
	$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:" . $temp['1'] . "\">" . $temp['0'] . "</a>";
	return $devs;
}

function defaultpage() {
	global $tsSQLlink, $toolserver_database;
	@mysql_select_db( $toolserver_database, $tsSQLlink) or sqlerror(mysql_error,"Could not select db");
	$html =<<<HTML
<h1>Create an account!</h1>
<h2>Open requests</h2>
HTML;

	$html .= listrequests("Open", FALSE);
	$html .= "<h2>Flagged user needed</h2>";
	$html .= listrequests("Admin", FALSE);
	$html .= "<h2>Last 5 Closed requests</h2><a name='closed'></a><span id=\"closed\"/>\n";
	$query = "SELECT pend_id, pend_name, pend_checksum FROM acc_pend JOIN acc_log ON pend_id = log_pend WHERE log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 5;";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	$html .= "<table cellspacing=\"0\">\n";
	$currentrow = 0;
	while ( list( $pend_id, $pend_name, $pend_checksum ) = mysql_fetch_row( $result ) ) {
		$currentrow += 1;
		$out = '<tr';
		if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
		} else {
			$out .= ' class="odd">';
		}
		$out .= "<td><small><a style=\"color:green\" href=\"acc.php?action=zoom&amp;id=" . $pend_id . "\">Zoom</a></small></td><td><small>  <a style=\"color:blue\" href=\"http://en.wikipedia.org/wiki/User:" . $pend_name . "\">" . _utf8_decode($pend_name) . "</a></small></td><td><small>  <a style=\"color:orange\" href=\"acc.php?action=defer&amp;id=" . $pend_id . "&amp;sum=" . $pend_checksum . "&amp;target=users\">Reset</a></small></td></tr>";
		$html .= $out;
	}
	$html .= "</table>\n";
	$html .= showfooter();
	return $html;
}

function hasright($username, $checkright) {
	global $tsSQLlink;
	$username = sanitize($username);
	$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result) {
		sqlerror("Query failed: $query ERROR: " . mysql_error(),"Database query error.");
	}
	$row = mysql_fetch_assoc($result);
	$rights = explode(':', $row['user_level']);
	foreach( $rights as $right) {
		if($right == $checkright ) {
			return true;
		}
	}
	return false;
}

function displayheader() {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$query = "SELECT * FROM acc_emails WHERE mail_id = '8';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row['mail_text'];
}

function displayfooter() {
	echo "<a href=\"index.php\">Return to account request interface.</a><br />\n";
	if(isset($_SESSION['user'])) {
		if(hasright($_SESSION['user'], 'User') || hasright($_SESSION['user'], 'Admin')){
			echo "<a href=\"acc.php\">Return to request management interface</a>\n";
		} else {
			echo "<a href=\"acc.php\"><span style=\"color: red;\" title=\"Login required to continue\">Return to request management interface</span></a>\n";
		}
	} else {
		echo "<a href=\"acc.php\"><span style=\"color: red;\" title=\"Login required to continue\">Return to request management interface</span></a>\n";
	}
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or sqlerror(mysql_error(),"Error selecting database.");
	$query = "SELECT * FROM acc_emails WHERE mail_id = '7';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo "</div>";
	echo $row['mail_text'];
}

function readOnlyMessage() {
	global $dontUseDb;
	global $dontUseDbReason;
	global $dontUseDbCulprit;
	if ($dontUseDb) {
		require_once('offline-messages.php');
		showInternalOfflineMessage($dontUseDbReason,$dontUseDbCulprit);
		die();
	}
}

/**
* If the Wiki/antispoof database is marked as disabled, then die.
*/
function ifWikiDbDisabledDie() {
	global $dontUseWikiDb;
	if( $dontUseWikiDb ){
		echo "Apologies, this command requires access to the wiki database, which is currently unavailable.";
		die();
	}
}

function sqlerror ($sql_error,$generic_error) {
	/*
	* Show the user an error 
	* depending on $enableSQLError.
	*/
	global $enableSQLError;
	if ($enableSQLError) {
		die($sql_error);
	} else {
		die($generic_error);
	}
}

function array_search_recursive($needle, $haystack, $path=array())
{
    foreach($haystack as $id => $val)
    {
         $path2=$path;
         $path2[] = $id;
 
         if($val === $needle)
              return $path2;
         else if(is_array($val))
              if($ret = array_search_recursive($needle, $val, $path2))
                   return $ret;
      }
      return false;
}

function getDBConnections() {
    global $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
	global $antispoof_host, $antispoof_db, $antispoof_password, $dontUseWikiDb;
    global $tsSQLlink;
    global $asSQLlink;
    $tsSQLlink = mysql_pconnect($toolserver_host, $toolserver_username, $toolserver_password);
    if( !$dontUseWikiDb) {
        $asSQLlink = mysql_pconnect($antispoof_host, $toolserver_username, $antispoof_password);
    }
    return array( $tsSQLlink, $asSQLlink );
}

function isOnWhitelist($user)
{
	$apir = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&prop=revisions&titles=Wikipedia:Request_an_account/Whitelist&rvprop=content&format=php");
	$apir = unserialize($apir);
	$apir = $apir['query']['pages'];
	
	foreach($apir as $r) {
		$text = $r['revisions']['0']['*'];
	}
	
	if( preg_match( '/\*\[\[User:'.$user.'\]\]/', $text ) ) {
		return true;
	}
	return false;
}

function zoomPage($id)
{
	global $tsSQLlink;
	
	$out = "";
	$gid = sanitize($id);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['pend_mailconfirm'] != 'Confirmed' && $row['pend_mailconfirm'] != "") {
		$out .= "Email has not yet been confirmed for this request, so it can not yet be closed or viewed";
		$out .= showfooter();
		die();
	}
	$out .= "<h2>Details for Request #" . $id . ":</h2>";
	$uname = urlencode($row['pend_name']);
	$thisip = $row['pend_ip'];
	$thisid = $row['pend_id'];
	$thisemail = $row['pend_email'];
	if ($row['pend_date'] == "0000-00-00 00:00:00") {
		$row['pend_date'] = "Date Unknown";
	}
	$sUser = $row['pend_name'];
	$query = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_mailconfirm = 'Confirmed' AND ( pend_status = 'Open' OR pend_status = 'Admin' );";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$hideip = TRUE;
	if (mysql_num_rows($result) > 0)
		$hideip = FALSE;
	$requesttable = listrequests($thisid, $hideip);
	$out .= $requesttable;

	//Escape injections.
	$out .= "<br /><strong>Requester Comment</strong>: " . $row['pend_cmt'] . "<br />\n";
	
	global $enableReserving;
	if( $enableReserving )
	{
		$reservingUser = isReserved($thisid);
		if( $reservingUser != 0 )
		{
			$out .= "<h3>This request is currently being handled by " . getUsernameFromUid($reservingUser) ."</h3>";
		}
	}
	
	$out .= '<p><b>Date request made:</b>' . $row['pend_date'] . '</p>';

	
	$out2 = "<h2>Possibly conflicting usernames</h2>\n";
	$spoofs = getSpoofs( $sUser );
	
	if( !$spoofs ) {
		$out2 .= "<i>None detected</i><br />\n";
	} elseif ( !is_array($spoofs) ) {
		$out2 .= "<h3 style='color: red'>$spoofs</h3>\n";
	} else {
		$out2 .= "<ul>\n";
		foreach( $spoofs as $oSpoof ) {
			if ( $oSpoof == $sUser ) {
				$out .= "<h3>Note: This account has already been created</h3>";
				continue;
			}
			$oS = htmlentities($oSpoof);
			$out2 .= "<li><a href=\"http://en.wikipedia.org/wiki/User:$oS\">$oSpoof</a> (<a href=\"http://en.wikipedia.org/wiki/Special:Contributions/$oS\">contribs</a> | <a href=\"http://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A$oS\">Logs</a> | <a href='http://toolserver.org/~vvv/sulutil.php?user=$oS'>SUL</a>)</li>\n";
		}
		$out2 .= "</ul>\n";
	}
	$out .= $out2;
	
	//// why are these here? st 24/05/09
	//mysql_pconnect( $toolserver_host, $toolserver_username, $toolserver_password );
	//@ mysql_select_db( $toolserver_database ) or print mysql_error( );

	$out .= "<h2>Logs for this request:</h2>";
	$logPage = new LogPage();
	$logPage->filterRequest=$thisid;
	$logPage->showPager=false;
	$out .= $logPage->showListLog(0,100);
	

    $out .= "<h2>Comments on this request:<small> (<a href='acc.php?action=comment&id=$gid'>new comment</a>)</small></h2>";
    if (hasright($_SESSION['user'], 'Admin')) {
    $query = "SELECT * FROM acc_cmt JOIN acc_user ON (user_name = cmt_user) WHERE pend_id = '$gid' ORDER BY cmt_id ASC;";
    } else {
    $user = sanitise($_SESSION['user']);
    $query = "SELECT * FROM acc_cmt JOIN acc_user ON (user_name = cmt_user) WHERE pend_id = '$gid' AND (cmt_visability = 'user' OR cmt_user = '$user') ORDER BY cmt_id ASC;";
    }
    $result = mysql_query($query, $tsSQLlink);
	if (!$result) {
		Die("Query failed: $query ERROR: " . mysql_error()); }
	$numcomment = 0;
    $out .= "<ul>";
	while ($row = mysql_fetch_assoc($result)) {
        if ($row['cmt_visability'] == "admin") {
		$out .= "<li><a href='users.php?viewuser=" . $row['user_id'] . "'>" .  $row['cmt_user'] ."</a> commented, " . $row['cmt_comment'] . "  at " . $row['cmt_time'] . " <font color='red'>(admin only)</font></li>";
        } else {
        $out .= "<li><a href='users.php?viewuser=" . $row['user_id'] . "'>" .  $row['cmt_user'] ."</a> commented,  " . $row['cmt_comment'] . "  at " . $row['cmt_time'] . "</li>";
        }
		$numcomment++;
	}
	if ($numcomment == 0) {
		$out .= "<i>None.</i>\n";
	}
    $out .= "</ul>";
    $out .= "<form action='acc.php?action=comment-quick' method='post'><input type='hidden' name='id' value='$gid'><input type='text' name='comment' size='75'' /><input type='hidden' name='visibility' value='user'$gid'><input type='submit' value='Quick Reply' />";

	$ipmsg = 'this ip';
	if ($hideip == FALSE || hasright($_SESSION['user'], 'Admin'))
		$ipmsg = $thisip;
	

	$out .= "<h2>Other requests from $ipmsg:</h2>\n";
	$query = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_id != '$thisid' AND pend_mailconfirm = 'Confirmed';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$numip = 0;
	$currentrow = 0;
 	while ($row = mysql_fetch_assoc($result)) {
		if ($numip == 0) { $out .= "<table cellspacing=\"0\">\n"; }
		$currentrow += 1;
		$out .= "<tr";
		if ($currentrow % 2 == 0) {$out .= ' class="alternate"';}
		$out .= "><td>". $row['pend_date'] . "</td><td><a href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></td></tr>";
		$numip++;
	}
	if ($numip == 0) {
		$out .= "<i>None.</i>\n";
	}
	else {$out .= "</table>\n";}
	
	
	$out .= "<h2>Other requests from $thisemail:</h2>\n";
	$query = "SELECT * FROM acc_pend WHERE pend_email = '$thisemail' AND pend_id != '$thisid' AND pend_mailconfirm = 'Confirmed';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$numem = 0;
	$currentrow = 0;
	while ($row = mysql_fetch_assoc($result)) {
		if ($numem == 0) { $out .= "<table cellspacing=\"0\">\n"; }
		$currentrow += 1;
		$out .= "<tr";
		if ($currentrow % 2 == 0) {$out .= ' class="alternate"';}
		$out .= "><td>". $row['pend_date'] . "</td><td><a href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></td></tr>";
		$numem++;
	}
	if ($numem == 0) {
		$out .= "<i>None.</i>\n";
	}
	else {$out .= "</table>\n";}

	return $out;
}
