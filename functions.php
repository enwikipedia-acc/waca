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

global $ACC;
global $tsurl;
global $dontUseWikiDb;

if ($ACC != "1") {
	header("Location: $tsurl/");
	die();
} //Re-route, if you're a web client.

require_once 'queryBrowser.php';
require_once 'LogClass.php';
require_once 'includes/messages.php';
include_once 'AntiSpoof.php';
require_once 'includes/internalInterface.php';
require_once 'includes/session.php';

// Initialize the class objects.
$messages = new messages();
$internalInterface = new internalInterface();
$session = new session();

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
	$what = htmlentities($what,ENT_COMPAT,'UTF-8');
	return ($what);
}

function xss ($string) {
	return htmlentities($string,ENT_QUOTES,'UTF-8');
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

function sendemail($messageno, $target, $id) {
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
	mail($target, "RE: [ACC #$id] English Wikipedia Account Request", $mailtxt, $headers);
}

function listrequests($type, $hideip, $correcthash) {
	/*
	 * List requests, at Zoom, and, on the main page
	 */
	global $toolserver_database, $tsSQLlink;
	global $secure;
	global $enableEmailConfirm;
	global $dontUseWikiDb;
	global $session;
	if($secure != 1) { die("Not logged in"); }
	@ mysql_select_db($toolserver_database, $tsSQLlink) or sqlerror(mysql_error(),"Error selecting database.");

	if ($enableEmailConfirm == 1) {
		if ($type == 'Admin' || $type == 'Open' || $type == 'Checkuser') {
			$query = "SELECT * FROM acc_pend WHERE pend_status = '$type' AND pend_mailconfirm = 'Confirmed';";
		} else {
			$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
		}
	} else {
		if ($type == 'Admin' || $type == 'Open' || $type == 'Checkuser') {
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
		$uname = str_replace("%26amp%3B", "%26", $uname);
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
		if ($type == 'Admin' || $type == 'Open' || $type == 'Checkuser') {
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

		if ($hideip == FALSE || $correcthash == TRUE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) ) {
			// Email.
			$out .= '[ </small></td>';
			$out .= '<td><small><a class="request-src" href="mailto:' . $row['pend_email'] . '">' . $row['pend_email'] . '</a>';

			$out .= '</small></td><td><small><span class="request-src">' . "\n";
			if ($otheremailreqs['count'] == 0) {
				$out .= '(' . $otheremailreqs['count'] . ')';
			} else {
				$out .= '(</span><b><span class="request-mult">' . $otheremailreqs['count'] . '</span></b><span class="request-src">)';
			}
		}

		if ( $row4['user_secure'] > 0 ) {
			$wikipediaurl = "https://secure.wikimedia.org/wikipedia/en/";
			$metaurl = "https://secure.wikimedia.org/wikipedia/meta/";
		} else {
			$wikipediaurl = "http://en.wikipedia.org/";
			$metaurl = "http://meta.wikimedia.org/";
		}


		if ($hideip == FALSE ||  $correcthash == TRUE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) ) {
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
			if ($dontUseWikiDb == 0) {
				$out .= '<a class="request-src" href="http://toolserver.org/~luxo/contributions/contributions.php?lang=en&amp;blocks=true&amp;user=' . $row['pend_ip'] . '" target="_blank">gc</a> ';
			}
			elseif ($dontUseWikiDb == 1) {
				$out .= '';
			}
			// IP blocks
			$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special:Log&amp;type=block&amp;page=User:';
			$out .= $row['pend_ip'] . '" target="_blank">b</a> ';

			// rangeblocks
			$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special%3ABlockList&amp;ip=';
			$out .= $row['pend_ip'] . '" target="_blank">r</a> ';

			// Global blocks
			$out .= '<a class="request-src" href="'.$metaurl.'w/index.php?title=Special:Log&amp;type=gblblock&amp;page=User:';
			$out .= $row['pend_ip'] . '" target="_blank">gb</a> ';

			// Global range blocks/Locally disabled Global Blocks
			$out .= '<a class="request-src" href="'.$wikipediaurl.'w/index.php?title=Special%3AGlobalBlockList&amp;ip=';
			$out .= $row['pend_ip'] . '" target="_blank">gr</a> ';

			// IP whois
			if ($dontUseWikiDb == 0) {
				$out .= '<a class="request-src" href="http://toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=' . $row['pend_ip'] . '" target="_blank">w</a> ';
			}
			elseif ($dontUseWikiDb == 1) {
				$out .= '';
			}
			// Abuse Filter
			$out .= '<a class="request-src" href="' . $wikipediaurl . 'w/index.php?title=Special:AbuseLog&amp;wpSearchUser=' . $row['pend_ip'] . '" target="_blank">af</a> ';

		}
		// Username U:
		$duname = _utf8_decode($row['pend_name']);
		$out .= '</small></td><td><small><a class="request-req" href="'.$wikipediaurl.'wiki/User:' . $uname . '" target="_blank"><strong>' . $duname . '</strong></a> ';

		// 	Creation log
		$out .= '</small></td><td><small>(<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page=User:';
		$out .= $uname . '" target="_blank">Creation</a> ';

		// 	SUL link
		if ($dontUseWikiDb == 0) {
			$out .= '<a class="request-req" href="http://toolserver.org/~vvv/sulutil.php?user=';
			$out .= $uname . '" target="_blank">SUL</a> ';
		}
		elseif ($dontUseWikiDb == 1) {
			$out .= '';
		}

		// 	User list
		$out .= '<a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special%3AListUsers&amp;username=';
		$out .= $uname . '&amp;group=&amp;limit=1" target="_blank">List</a> ';

		// Google
		$out .= '<a class="request-req" href="http://www.google.com/search?q=';
		$out .= preg_replace("/_/","+",$uname) . '" target="_blank">Google</a> ';

		global $protectReservedRequests, $enableReserving;

		if(! isProtected($row['pend_id']))
		{
			if ($hideip == FALSE ||  $correcthash == TRUE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']) ) { //Hide create user link because it contains the E-Mail address. 
				// Create user link
				$out .= '<b><a class="request-req" href="'.$wikipediaurl.'w/index.php?title=Special:UserLogin/signup&amp;wpName=';
				$out .= $uname . '&amp;wpEmail=' . $row['pend_email'] . '&amp;uselang=en-acc" target="_blank">Create!</a></b>';
			}
		}

		$out .= ')</small></td><td><small> |</small></td><td><small> ';

		if(! isProtected($row['pend_id']))
		{
			// Done
			$out .= '<a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=1&amp;sum=' . $row['pend_checksum'] . '"><strong>Created!</strong></a>';

			// Similar
			$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=2&amp;sum=' . $row['pend_checksum'] . '">Similar</a>';

			// Taken
			$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=3&amp;sum=' . $row['pend_checksum'] . '">Taken</a>';

			// SUL Taken
			if ($dontUseWikiDb == 0) {
				$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=26&amp;sum=' . $row['pend_checksum'] . '">SUL Taken</a>';
			}
			elseif ($dontUseWikiDb == 1){
				$out .= '';
			}

			// UPolicy
			$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=4&amp;sum=' . $row['pend_checksum'] . '">UPolicy</a>';

			// Invalid
			$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=5&amp;sum=' . $row['pend_checksum'] . '">Invalid</a>';

			// Custom
			$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=custom&amp;sum=' . $row['pend_checksum'] . '">Custom</a>';


			// Drop
			$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=0&amp;sum=' . $row['pend_checksum'] . '">Drop</a>' . "\n";



			// Defer to admins or users
			if (is_numeric($type)) {
				$type = $row['pend_status'];
			}
			if (!isset ($target)) {
				$target = "zoom";
			}


			if ($type == 'Open') {
				$target1 = 'admins';
				$message1 = "Flagged Users";
				$target2 = 'cu';
				$message2 = "Checkusers";
			}
			elseif ($type == 'Admin') {
				$target1 = 'users';
				$message1 = "Users";
				$target2 = 'cu';
				$message2 = "Checkusers";
			}
			elseif ($type == 'Checkuser') {
				$target1 = 'users';
				$message1 = "Users";
				$target2 = 'admins';
				$message2 = "Flagged Users";
			}

			if($row['pend_status'] == "Admin" || $row['pend_status'] == "Open" || $row['pend_status'] == "Checkuser")
			{
				$out.= " - Defer to: ";
				$out .= "<a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=$target1\">$message1</a>";
				$out .= " / ";
				$out .= "<a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=$target2\">$message2</a>";

			}
			else
			{
				$out .= " - <a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=users\">Reset Request</a>";
			}
		}
		else
		{
			$out .= 'This request is reserved';
		}

		if($session->hasright($_SESSION['user'], "Admin")) {
			// Ban IP
			$out .= '</small></td><td><small> |</small></td><td><small> Ban: </small></td><td><small><a class="request-ban" href="acc.php?action=ban&amp;ip=' . $row['pend_id'] . '">IP</a> ';

			// Ban email
			$out .= '- <a class="request-ban" href="acc.php?action=ban&amp;email=' . $row['pend_id'] . '">E-Mail</a>';

			//Ban name
			$out .= ' - <a class="request-ban" href="acc.php?action=ban&amp;name=' . $row['pend_id'] . '">Name</a>';
		}

		// Check to see if we want to have all the reserving stuff on
		if( $enableReserving )
		{
			$reserveByUser = isReserved($row['pend_id']);
			// if request is reserved, show reserved message
			if( $reserveByUser != 0 )
			{
				if( $reserveByUser == $_SESSION['userID'])
				{
					$out .= "</small></td><td><small> | </small></td><td><small>YOU are handling this request. <a href=\"acc.php?action=breakreserve&amp;resid=" . $row['pend_id']. "\">Break reservation</a>";
				} else {
					$out .= "</small></td><td><small> | </small></td><td><small>Being handled by <a href=\"statistics.php?page=Users&user=$reserveByUser\">" . $session->getUsernameFromUid($reserveByUser) . "</a>";

					// force break?
					global $enableAdminBreakReserve;
					if( $enableAdminBreakReserve && $session->hasright($_SESSION['user'], "Admin"))
					{
						$out .= " - <a href=\"acc.php?action=breakreserve&amp;resid=" . $row['pend_id']. "\">Force break</a>";
					}
				}
			}
			else // not being handled, do you want to handle this request?
			{
				$out .= "</small></td><td><small> | </small></td><td><small><a href=\"acc.php?action=reserve&amp;resid=" . $row['pend_id']. "\">Mark as being handled</a>";
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

function isProtected($requestid)
{
	global $enableReserving, $protectReservedRequests;

	if(! $enableReserving)
	return false;

	if(! $protectReservedRequests)
	return false;

	$reservedTo = isReserved($requestid);

	if($reservedTo)
	{
		if($reservedTo == $_SESSION['userID'])
		return false;
		else
		return true;
	}
	else
	return false;

	return false;
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
	Die("Error determining reserved status of request. Check the request id.");
	$row = mysql_fetch_assoc($result);
	if(isset($row['pend_reserved']) && $row['pend_reserved'] != 0) { return $row['pend_reserved'];} else {return false;}
}

function showlogin($action=null, $params=null) {
	/*
	 * Show the login page.
	 */
	global $_SESSION, $tsSQLlink, $useCaptcha, $skin;

	// Create the variable used for the coding.
	$html ='<div id="sitenotice">Please login first, and we\'ll send you on your way!</div>
    <div id="content">
    <h2>Login</h2>';

	// Check whether there are any errors.
	if (isset($_GET['error'])) {
		if ($_GET['error']=='authfail') {
			$html .= "<p>Username and/or password incorrect. Please try again.</p>";
		} elseif ($_GET['error']=='captchafail') {
			$html .= "<p>I'm sorry, the captcha you entered was incorrect, please try again.</p>";
		} elseif ($_GET['error']=='captchamissing') {
			$html .= "<p>Please complete the captcha.</p>";
		}
	}

	// Generate the login form; set the action to login and nocheck to true.
	// By setting nocheck to true would skip the checking procedures.
	$html .='<form action="acc.php?action=login&amp;nocheck=1';

	// Would perform clause for any action except logout.
	if (($action) && ($action != "logout")) {
		$html .= "&amp;newaction=" . xss($action);
			
		// Create an array of all the values in the $GET variable.
		// The variable supplied as the parameter would be used.
		foreach ($params as $param => $value) {
			if ($param != '' && $param != "action") {
				$html .= "&amp;".xss($param)."=".xss($value);
			}
		}
	}

	// Adds final coding to the HTML variable, such as for the forms to be created.
	$html .= '" method="post">
    <div class="required">
        <label for="username">Username:</label>
        <input id="username" type="text" name="username"/>
    </div>
    <div class="required">
        <label for="password">Password:</label>
        <input id="password" type="password" name="password"/>
    </div>';

	// Checks where Captcha should be used.
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

	// Adds a Submit button to the HTML code.
	// Below the forms a option to register would be displayed.
	$html .= '<div class="submit">
        <input type="submit" value="Login"/>
    </div>
    </form>
    <br />
    Need Tool access?
    <br /><a href="acc.php?action=register">Register!</a> (Requires approval)<br />
    <a href="acc.php?action=forgotpw">Forgot your password?</a><br />';

	// Finally the footer are added to the code.
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
	global $tsSQLlink, $toolserver_database, $skin;
	@mysql_select_db( $toolserver_database, $tsSQLlink) or sqlerror(mysql_error,"Could not select db");
	$html =<<<HTML
<h1>Create an account!</h1>
<h2>Open requests</h2>
HTML;

	$html .= listrequests("Open", TRUE, FALSE);
	$html .= "<h2>Flagged user needed</h2>";
	$html .= listrequests("Admin", TRUE, FALSE);
	$html .= "<h2>Checkuser needed</h2>";
	$html .= listrequests("Checkuser", TRUE, FALSE);
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
	return $html;
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

function zoomPage($id,$urlhash)
{
	global $tsSQLlink, $session, $skin, $enableReserving;

	$out = "";
	$gid = sanitize($id);
	$urlhash = sanitize($urlhash);
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['pend_mailconfirm'] != 'Confirmed' && $row['pend_mailconfirm'] != "") {
		$out .= "Email has not yet been confirmed for this request, so it can not yet be closed or viewed";
		$out .= $skin->displayIfooter();
		die();
	}
	$out .= "<h2>Details for Request #" . $id . ":</h2>";
	$thisip = $row['pend_ip'];
	$thisid = $row['pend_id'];
	$thisemail = $row['pend_email'];
	if ($row['pend_date'] == "0000-00-00 00:00:00") {
		$row['pend_date'] = "Date Unknown";
	}
	$sUser = $row['pend_name'];
	if ($enableReserving == false) {
	$query = "SELECT * FROM acc_pend WHERE pend_email = '$thisemail' AND pend_mailconfirm = 'Confirmed' AND ( pend_status = 'Open' OR pend_status = 'Admin' OR pend_status = 'Checkuser' );";
	}
	else {
	$sessionuser = $_SESSION['userID'];
	$query = "SELECT * FROM acc_pend WHERE pend_email = '$thisemail' AND pend_reserved = '$sessionuser' AND pend_mailconfirm = 'Confirmed' AND ( pend_status = 'Open' OR pend_status = 'Admin' OR pend_status = 'Checkuser' );";
	}
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
	$hideemail = TRUE;
	if (mysql_num_rows($result) > 0) {
	$hideemail = FALSE;
	}
	if ($enableReserving == false) {
	$query2 = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_mailconfirm = 'Confirmed' AND ( pend_status = 'Open' OR pend_status = 'Admin' OR pend_status = 'Checkuser' );";
	}
	else {
	$sessionuser = $_SESSION['userID'];
	$query2 = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_reserved = '$sessionuser' AND pend_mailconfirm = 'Confirmed' AND ( pend_status = 'Open' OR pend_status = 'Admin' OR pend_status = 'Checkuser' );";
	}
	$result2 = mysql_query($query2, $tsSQLlink);
	if (!$result2)
	Die("Query failed: $query2 ERROR: " . mysql_error());
	$hideip = TRUE;
	if (mysql_num_rows($result2) > 0)
	$hideip = FALSE;
	if( $hideip == FALSE || $hideemail == FALSE ) {
		$hideinfo = FALSE;
	} else {
		$hideinfo = TRUE;
	}
	if ($row['pend_status'] == "Closed") {
		$hash = md5($thisid. $thisemail . $thisip . microtime()); //If the request is closed, change the hash based on microseconds similar to the checksums. 
	} else {
		$hash = md5($thisid . $thisemail . $thisip);
	}
	if ($hash == $urlhash) {
		$correcthash = TRUE;
	}
	else {
		$correcthash = FALSE;
	}
	$requesttable = listrequests($thisid, $hideinfo, $correcthash);
	$out .= $requesttable;

	//Escape injections.
	$out .= "<br /><strong>Requester Comment</strong>: " . $row['pend_cmt'] . "<br />\n";

	global $enableReserving, $tsurl;
	if( $enableReserving )
	{
		$reservingUser = isReserved($thisid);
		if( $reservingUser != 0 )
		{
			$out .= "<h3>This request is currently being handled by " . $session->getUsernameFromUid($reservingUser) ."</h3>";
		}
		if ($reservingUser == $_SESSION['userID'] && $row['pend_status'] != "Closed") {
			$out .= '<p><b>URL to allow other users to see IP/Email:</b> <a href="acc.php?action=zoom&amp;id=' . $thisid . '&amp;hash=' . $hash . '">' . $tsurl . '/acc.php?action=zoom&id=' . $thisid . '&hash=' . $hash . '</a></p>';
		}
	}

	global $allowViewingOfUseragent;
	if($allowViewingOfUseragent)
	{
		global $session, $suser;
		if($session->isCheckuser($_SESSION['user']))
		{
			$out .= "<h3>User agent: \"" . $row['pend_useragent'] . "\"</h3>";
		}
	}
	$out .= '<p><b>Date request made:</b> ' . $row['pend_date'] . '</p>';

	$out2 = "<h2>Possibly conflicting usernames</h2>\n";
	$spoofs = getSpoofs( $sUser );

	// Display message if there is no conflicting usernames.
	// This part would not be displayed as soon as an account was created.
	// This is because then the username would have a spoof, ie himself.
	if( !$spoofs ) {
		$out2 .= "<i>None detected</i><br />\n";
	}

	// Checks whether there is an array of spoofs.
	elseif ( !is_array($spoofs) ) {
		$out2 .= "<h3 style='color: red'>$spoofs</h3>\n";
	}

	// Display details for the different conflicting usernames.
	else {
		$out2 .= "<ul>\n";
		foreach( $spoofs as $oSpoof ) {
			// Wouldnt work for requests where there are conflicting names.
			// The conflicting names would be tested again the created username.
			if ( $oSpoof == $sUser ) {
				$out .= "<h3>Note: This account has already been created</h3>";
				continue;
			}

			// Convert all applicable characters to HTML entities.
			$oS = htmlentities($oSpoof,ENT_COMPAT,'UTF-8');

			// Show the Wikipedia Userpage of the conflicting users.
			$posc1 = '<a href="http://en.wikipedia.org/wiki/User:';
			$posc1 .= $oS . '" target="_blank">' . $oS . '</a> ';

			// Show the contributions of the conflicting users.
			$posc2 = '<a href="http://en.wikipedia.org/wiki/Special:Contributions/';
			$posc2 .= $oS . '" target="_blank">contribs</a> ';

			// Show the logs of the conflicting users.
			$posc3 = '<a href="http://en.wikipedia.org/w/index.php?title=Special%3ALog&amp;type=&amp;user=&amp;page=User%3A';
			$posc3 .= $oS . '" target="_blank">Logs</a> ';

			// Open the SUL of the conflicting users.
			$posc4 = '<a href="http://toolserver.org/~vvv/sulutil.php?user=';
			$posc4 .= $oS . '" target="_blank">SUL</a> ';

			// Adds all the variables together for one line.
			$out2 .= "<li>" . $posc1 . "( " . $posc2 . " | " . $posc3 . " | " . $posc4 . " )</li>\n";
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

    if ($urlhash != "") {
		$out .= "<h2>Comments on this request:<small> (<a href='acc.php?action=comment&amp;id=$gid&amp;hash=$urlhash'>new comment</a>)</small></h2>";
	} else {
		$out .= "<h2>Comments on this request:<small> (<a href='acc.php?action=comment&amp;id=$gid'>new comment</a>)</small></h2>";
	}
	
	if ($session->hasright($_SESSION['user'], 'Admin')) {
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
				$out .= "<li><a href='statistics.php?page=Users&amp;user=" . $row['user_id'] . "'>" .  $row['cmt_user'] ."</a> commented, " . $row['cmt_comment'] . "  at " . $row['cmt_time'] . " <font color='red'>(admin only)</font></li>";
			} else {
				$out .= "<li><a href='statistics.php?page=Users&amp;user=" . $row['user_id'] . "'>" .  $row['cmt_user'] ."</a> commented,  " . $row['cmt_comment'] . "  at " . $row['cmt_time'] . "</li>";
			}
			$numcomment++;
		}
		if ($numcomment == 0) {
			$out .= "<i>None.</i>\n";
		}
		$out .= "</ul>";
		
    	if ($urlhash != "") {
			$out .= "<form action='acc.php?action=comment-quick&amp;hash=$urlhash' method='post' />";
		} else {
			$out .= "<form action='acc.php?action=comment-quick' method='post' />";
		}
		$out .= "<input type='hidden' name='id' value='$gid' /><input type='text' name='comment' size='75' /><input type='hidden' name='visibility' value='user' /><input type='submit' value='Quick Reply' />";

		$ipmsg = 'this ip';
		if ($hideinfo == FALSE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user']))
		$ipmsg = $thisip;


		$out .= "<h2>Other requests from $ipmsg:</h2>\n";
		$query = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_id != '$thisid' AND pend_mailconfirm = 'Confirmed';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());

		$currentrow = 0;
		while ($row = mysql_fetch_assoc($result)) {
			if ($currentrow == 0) { $out .= "<table cellspacing=\"0\">\n"; }
			$currentrow += 1;
			$out .= "<tr";
			if ($currentrow % 2 == 0) {$out .= ' class="alternate"';}
			$out .= "><td>". $row['pend_date'] . "</td><td><a href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></td></tr>";
		}
		if ($currentrow == 0) {
			$out .= "<i>None.</i>\n";
		}
		else {$out .= "</table>\n";}

		// Displayes other requests from this email.
		$emailmsg = 'this email';
		if ($hideinfo == FALSE || $session->hasright($_SESSION['user'], 'Admin') || $session->isCheckuser($_SESSION['user'])) {
		$emailmsg = $thisemail;
		}
		$out .= "<h2>Other requests from $emailmsg:</h2>\n";
		$query = "SELECT * FROM acc_pend WHERE pend_email = '$thisemail' AND pend_id != '$thisid' AND pend_mailconfirm = 'Confirmed';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());

		$currentrow = 0;
		while ($row = mysql_fetch_assoc($result)) {
			// Creates the table for the first time.
			if ($currentrow == 0) {
				$out .= "<table cellspacing=\"0\">\n";
			}

			$currentrow += 1;
			$out .= "<tr";
			if ($currentrow % 2 == 0) {$out .= ' class="alternate"';}
			$out .= "><td>". $row['pend_date'] . "</td><td><a href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">" . $row['pend_name'] . "</a></td></tr>";
		}
		// Checks whether there were similar requests.
		if ($currentrow == 0) {
			$out .= "<i>None.</i>\n";
		}
		else {$out .= "</table>";}

		return $out;
}
?>
