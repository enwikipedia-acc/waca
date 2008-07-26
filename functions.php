<?php
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
	require_once('equivset.php');
	global $toolserver_username;
	global $toolserver_password;
	$spooflink = mysql_connect("sql-s1", $toolserver_username, $toolserver_password);
	@ mysql_select_db("enwiki_p", $spooflink) or print mysql_error();
	$fone = strtr($username,$equivset);
	//$fone = mysql_real_escape_string( $fone );
	$query = "SELECT * FROM spoofuser WHERE su_normalized = 'v2:$fone';";
	$result = mysql_query($query, $spooflink);
	if(!$result) Die("ERROR: No result returned. - ".mysql_error());
	$numSpoof = 0;
	$reSpoofs = array();
	while ($row = mysql_fetch_assoc($result)) {
	        if( isset( $row['su_name'] ) ) { $numSpoof++; }
		array_push( $reSpoofs, $row['su_name'] );
	}
	mysql_close( $spooflink );
	if( $numSpoof == 0 ) {
	        return( FALSE );
	} else {
		return( $reSpoofs );
	}
}

function sanitize($what) {
	/*
	* Shortcut to mysql_real_escape_string
	*/
	$what = mysql_real_escape_string($what);
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
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
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
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$pend = mysql_fetch_assoc($result);
	if ($pend['pend_checksum'] == "") {
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
	$fp = fsockopen("udp://91.198.174.202", 9001, $erno, $errstr, 30);
	if (!$fp) {
		echo "SOCKET ERROR: $errstr ($errno)<br />\n";
	}
	fwrite($fp, "[$whichami]: $message\r\n");
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
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$howma = gethowma();
	$n2 = $howma;
	unset ($howma['howmany']);
	unset ($n2['howmany']);
	$out = "";
	$n = 1;
	foreach ($howma as $oneonline) {
		$oneonline = sanitize($oneonline);
		$query = "SELECT * FROM acc_user WHERE user_name = '$oneonline';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row = mysql_fetch_assoc($result);
		$uid = $row['user_id'];
		$oneonline = stripslashes($oneonline);
		if($n < $n2) {
			$comma = ",";
			$n++;
		} elseif($n2-$n1 == 1) {
			$comma = ", and";
		} else { 
			$comma = NULL;
		}
		$out .= " <a href=\"users.php?viewuser=$uid\">$oneonline</a>$comma";
	}
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
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$last5min = time() - 300; // Get the users active as of the last 5 mins
	$last5mins = date("Y-m-d H:i:s", $last5min);
	$query = "SELECT * FROM acc_user WHERE user_lastactive > '$last5mins';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$whoactive = array ();
	while ($row = mysql_fetch_assoc($result)) {
		array_push($whoactive, $row['user_name']);
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
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
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
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	$mailtxt = $row['mail_text'];
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($target, "RE: English Wikipedia Account Request", $mailtxt, $headers);
}

function checksecurity($username) {
	/*
	* Check the user's security level on page load, and bounce accordingly
	*/
	
	if (hasright($username, "New")) {
		echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
		echo showfootern();
		die();
	}
	if (hasright($username, "Suspended")) {
		echo "I'm sorry, but, your account is presently suspended.<br />\n";
		echo showfootern();
		die();
	}
	if (hasright($username, "Declined")) {
		$username = sanitize($username);
		$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
		$result = mysql_query($query);
		if (!$result) {
			Die("Query failed: $query ERROR: " . mysql_error());
		}
		$row = mysql_fetch_assoc($result);
		$query2 = "SELECT * FROM acc_log WHERE log_pend = '" . $row['user_id'] . "' AND log_action = 'Declined' ORDER BY log_id DESC LIMIT 1;";
		$result2 = mysql_query($query2);
		if (!$result2) {
			Die("Query failed: $query ERROR: " . mysql_error());
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
	}
}

function listrequests($type) {
	/*
	* List requests, at Zoom, and, on the main page
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
	@ mysql_select_db($toolserver_database) or print mysql_error();
	if ($type == 'Admin' || $type == 'Open') {
		$query = "SELECT * FROM acc_pend WHERE pend_status = '$type';";
	} else {
		$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
	}
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());

	$tablestart = "<table cellspacing=\"0\">\n";
	$tableend = "</table>\n";
	$reqlist = '';
	$currentreq = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$currentreq += 1;
		$uname = urlencode($row['pend_name']);
		#    $uname = str_replace("+", "_", $row[pend_name]);
		$rid = $row['pend_id'];
		if ($row['pend_cmt'] != "") {
			$cmt = "<a class=\"request-src\" href=\"acc.php?action=zoom&amp;id=$rid\">Zoom (CMT)</a> ";
		} else {
			$cmt = "<a class=\"request-src\" href=\"acc.php?action=zoom&amp;id=$rid\">Zoom</a> ";
		}
		$query2 = 'SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_ip` = \'' . $row['pend_ip'] . '\' AND `pend_id` != \'' . $row['pend_id'] . '\';';
		$otherreqs = mysql_fetch_assoc(mysql_query($query2));
		$out = '<tr';
		if ($currentreq % 2 == 0) {
			$out .= ' class="alternate">';
		} else {
			$out .= '>';
		}
		if ($type == 'Admin' || $type == 'Open') {
			$out .= '<td><small>' . $currentreq . '.    </small></td><td><small>'; //List item
			$out .= $cmt; // CMT link.
		} else {
			$out .= '<td><small>' . "\n"; //List item
		}

		// Email.
		$out .= '</small></td><td><small>[ <a class="request-src" href="mailto:' . $row['pend_email'] . '">' . $row['pend_email'] . '</a>';

		// IP UT:
		$out .= '</small></td><td><small> | <a class="request-src" id="ip-link" href="http://en.wikipedia.org/wiki/User_talk:' . $row['pend_ip'] . '" target="_blank">';
		$out .= $row['pend_ip'] . '</a> ';

		$out .= '</small></td><td><small><span class="request-src">' . "\n";
		if ($otherreqs['count'] == 0) {
			$out .= '(' . $otherreqs['count'] . ')';
		} else {
			$out .= '(</span><b><span class="request-mult">' . $otherreqs['count'] . '</span></b><span class="request-src">)';
		}

		// IP contribs
		$out .= '</span></small></td><td><small><a class="request-src" href="http://en.wikipedia.org/wiki/Special:Contributions/';
		$out .= $row['pend_ip'] . '" target="_blank">c</a> ';

		// IP blocks
		$out .= '<a class="request-src" href="http://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=block&amp;page=User:';
		$out .= $row['pend_ip'] . '" target="_blank">b</a> ';

		// IP whois
		$out .= '<a class="request-src" href="http://toolserver.org/~overlordq/cgi-bin/whois.cgi?lookup=' . $row['pend_ip'] . '" target="_blank">w</a> ] ';

		// Username U:
		$duname = _utf8_decode($row['pend_name']);
		$out .= '</small></td><td><small><a class="request-req" href="http://en.wikipedia.org/wiki/User:' . $uname . '" target="_blank"><strong>' . $duname . '</strong></a> ';

		// Creation log    
		$out .= '</small></td><td><small>(<a class="request-req" href="http://en.wikipedia.org/w/index.php?title=Special:Log&amp;type=newusers&amp;user=&amp;page=User:';
		$out .= $uname . '" target="_blank">Creation</a> ';

		// User contribs
		$out .= '<a class="request-req" href="http://en.wikipedia.org/wiki/Special:Contributions/';
		$out .= $uname . '" target="_blank">Contribs</a> ';
		$out .= '<a class="request-req" href="http://en.wikipedia.org/w/index.php?title=Special%3AListUsers&amp;username=' . $uname . '&amp;group=&amp;limit=50" target="_blank">List</a>) ' . "\n";

		// Create user link
		$out .= '<b><a class="request-req" href="http://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&amp;wpName=';
		$out .= $uname . '&amp;wpEmail=' . $row['pend_email'] . '&amp;uselang=en-acc" target="_blank">Create!</a></b> ';

		// Done
		$out .= '| <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=1&amp;sum=' . $row['pend_checksum'] . '">Done!</a>';

		// Similar
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=2&amp;sum=' . $row['pend_checksum'] . '">Similar</a>';

		// Taken
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=3&amp;sum=' . $row['pend_checksum'] . '">Taken</a>';

		// UPolicy
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=4&amp;sum=' . $row['pend_checksum'] . '">UPolicy</a>';

		// Invalid
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=5&amp;sum=' . $row['pend_checksum'] . '">Invalid</a>';

		// Defer to admins or users
		if (is_numeric($type)) {
			$type = $row['pend_status'];
		}
		if (!isset ($target)) {
			$target = "zoom";
		}
		if ($type == 'Open') {
			$target = 'admin';
		}
		elseif ($type == 'Admin') {
			$target = 'user';
		}
		if ($target == 'admin' || $target == 'user') {
			$out .= " - <a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=$target\">Defer to $target" . "s</a>";
		} else {
			$out .= " - <a class=\"request-done\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=user\">Reset Request</a>";
		}
		// Drop
		$out .= ' - <a class="request-done" href="acc.php?action=done&amp;id=' . $row['pend_id'] . '&amp;email=0&amp;sum=' . $row['pend_checksum'] . '">Drop</a>' . "\n";

		// Ban IP
		$out .= ' | Ban: <a class="request-ban" href="acc.php?action=ban&amp;ip=' . $row['pend_id'] . '">IP</a> ';

		// Ban email
		$out .= '- <a class="request-ban" href="acc.php?action=ban&amp;email=' . $row['pend_id'] . '">E-Mail</a>';

		//Ban name
		$out .= ' - <a class="request-ban" href="acc.php?action=ban&amp;name=' . $row['pend_id'] . '">Name</a>';

		$out .= '</small></td></tr>';
		$reqlist .= $out;
	}
	if( $currentreq == 0 ) {
		return( "<i>No requests at this time</i>" );
	} else {
		return ($tablestart . $reqlist . $tableend);
	}

}

function makehead($username) {
	/*
	* Show page header (retrieved by MySQL call)
	*/
	$suin = sanitize($username);
	$rethead = '';
	$query = "SELECT * FROM acc_user WHERE user_name = '$suin' LIMIT 1;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	$_SESSION['user_id'] = $row['user_id'];
	$out = showmessage('21');
	if (isset ($_SESSION['user'])) { //Is user logged in?
		if (hasright($username, "Admin")) {
			$out = preg_replace('/\<a href\=\"acc\.php\?action\=messagemgmt\"\>Message Management\<\/a\>/', "\n<a href=\"acc.php?action=messagemgmt\">Message Management</a>\n<a href=\"acc.php?action=usermgmt\">User Management</a>\n", $out);
		}
		$rethead .= $out;
		$rethead .= "<div id = \"header-info\">Logged in as <a href=\"users.php?viewuser=" . $_SESSION['user_id'] . "\"><span title=\"View your user information\">" . $_SESSION['user'] . "</span></a>.  <a href=\"acc.php?action=logout\">Logout</a>?</div>\n";
		//Update user_lastactive
		$now = date("Y-m-d H-i-s");
		$query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '" . $_SESSION['user_id'] . "';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
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
	$howmany = array ();
	$howmany = gethowma();
	$howout = showhowma();
	$howma = $howmany['howmany'];
	$out = showmessage('23');
	$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><div align=\"center\"><small>$howma users active within the last 5 mins! ($howout)</small></div><br /><br />", $out);
	return $out;
}

function showlogin() {
	global $_SESSION;
	$html =<<<HTML
    <div id="sitenotice">Please login first, and we'll send you on your way!</div>
    <div id="content">
    <h2>Login</h2>
    <form action="acc.php?action=login&amp;nocheck=1" method="post">
    <div class="required">
        <label for="username">Username:</label>
        <input id="username" type="text" name="username"/>
    </div>
    <div class="required">
        <label for="password">Password:</label>
        <input id="password" type="password" name="password"/>
    </div>
    <div class="submit">
        <input type="submit"/>
    </div>
    </form>
    <br />
    Don't have an account? 
    <br /><a href="acc.php?action=register">Register!</a> (Requires approval)<br />
    <a href="acc.php?action=forgotpw">Forgot your password?</a><br />
HTML;
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
	$html =<<<HTML
<h1>Create an account!</h1>
<h2>Open requests</h2>
HTML;

	$html .= listrequests("Open");
	$html .=<<<HTML
<h2>Admin Needed!</h2>
HTML;
	$html .= listrequests("Admin");

	$html .= "<h2>Last 5 Closed requests</h2><a name='closed'></a><span id=\"closed\"/>\n";
	$query = "SELECT * FROM acc_pend JOIN acc_log ON pend_id = log_pend WHERE log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 5;";
	$result = mysql_query($query);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$html .= "<table cellspacing=\"0\">\n";
	$currentrow = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$currentrow += 1;
		$out = '<tr';
		if ($currentrow % 2 == 0) {
			$out .= ' class="even">';
		} else {
			$out .= ' class="odd">';
		}
		$out .= "<td><small><a style=\"color:green\" href=\"acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">Zoom</a></small></td><td><small>  <a style=\"color:blue\" href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . _utf8_decode($row['pend_name']) . "</a></small></td><td><small>  <a style=\"color:orange\" href=\"acc.php?action=defer&amp;id=" . $row['pend_id'] . "&amp;sum=" . $row['pend_checksum'] . "&amp;target=user\">Reset</a></small></td></tr>";
		$html .= $out;
	}
	$html .= "</table>\n";
	$html .= showfooter();
	return $html;
}

function hasright($username, $checkright) {
	$username = sanitize($username);
	$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
	$result = mysql_query($query);
	if (!$result) {
		Die("Query failed: $query ERROR: " . mysql_error());
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
	
?>
