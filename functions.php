<?php

function _utf8_decode ( $string ) {
	/*
	* Improved utd8_decode() function
	*/
	$tmp = $string;
	$count = 0;
	while ( mb_detect_encoding ( $tmp ) == "UTF-8" ) {
		$tmp = utf8_decode($tmp);
		$count++;
	}
 
	for ( $i = 0; $i < $count-1 ; $i++ ) {
		$string = utf8_decode($string);
	}
	return $string;
}


function sanitize ( $what ) {
	/*
	* Shortcut to mysql_real_escape_string
	*/
	$what = mysql_real_escape_string($what);
	return($what);
}

function upcsum ( $id ) {
	/*
	* Updates the entries checksum (on each load of that entry, to prevent dupes)
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$pend = mysql_fetch_assoc($result);
	$hash = md5($pend['pend_id'].$pend['pend_name'].$pend['pend_email'].microtime());
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
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_pend WHERE pend_id = '$id';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
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
	@mysql_select_db($toolserver_database) or print mysql_error();
	$howma = gethowma();
	unset ($howma['howmany']);
	$out = "";
	foreach ($howma as $oneonline) {
		$query = "SELECT * FROM acc_user WHERE user_name = '$oneonline';";
		$result = mysql_query($query);
		if (!$result)
			Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$uid = $row['user_id'];
		$out .= " <a href=\"users.php?viewuser=$uid\">$oneonline</a>";
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
	@mysql_select_db($toolserver_database) or print mysql_error();
	$last5min = time() - 300; // Get the users active as of the last 5 mins
	$last5mins = date("Y-m-d H:i:s", $last5min);
	$query = "SELECT * FROM acc_user WHERE user_lastactive > '$last5mins';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
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
	@mysql_select_db($toolserver_database) or print mysql_error();
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
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
	@mysql_select_db($toolserver_database) or print mysql_error();
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	$mailtxt = $row['mail_text'];
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($target, "RE: English Wikipedia Account Request", $mailtxt, $headers);
}

function checksecurity($username) {
	/*
	* Check the user's security level on page load, and bounce accordingly
	*/
	$username = sanitize($username);
	$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	if ($row['user_level'] == "New") {
		echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
		echo showfootern();
		die();
	}
	if ($row['user_level'] == "Suspended" && $username != "SQL") {
		echo "I'm sorry, but, your account is presently suspended.<br />\n";
		echo showfootern();
		die();
	}
	if ($row['user_level'] == "Declined" && $username != "SQL") {
		$query2 = "SELECT * FROM acc_log WHERE log_pend = '$row[user_id]' AND log_action = 'Declined' ORDER BY log_id DESC LIMIT 1;";
		$result2 = mysql_query($query2);
		if (!$result2)
			Die("ERROR: No result returned.");
		$row2 = mysql_fetch_assoc($result2);
		echo "I'm sorry, but, your account request was <strong>declined</strong> by <strong>$row2[log_user]</strong> because <strong>\"$row2[log_cmt]\"</strong> at <strong>$row2[log_time]</strong>.<br />\n";
		echo "Related information (please include this if appealing this decision)<br />\n";
		echo "user_id: $row[user_id]<br />\n";
		echo "user_name: $row[user_name]<br />\n";
		echo "user_onwikiname: $row[user_onwikiname]<br />\n";
		echo "user_email: $row[user_email]<br />\n";
		echo "log_id: $row2[log_id]<br />\n";
		echo "log_pend: $row2[log_pend]<br />\n";
		echo "log_user: $row2[log_user]<br />\n";
		echo "log_time: $row2[log_time]<br />\n";
		echo "log_cmt: $row2[log_cmt]<br />\n";
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
	@mysql_select_db($toolserver_database) or print mysql_error();
	if ($type == 'Admin' || $type == 'Open') {
		$query = "SELECT * FROM acc_pend WHERE pend_status = '$type';";
	} else {
		$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
	}
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	
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
			$cmt = "<a style=\"color:green\" href=\"acc.php?action=zoom&id=$rid\">Zoom (CMT)</a> ";
		} else {
			$cmt = "<a style=\"color:green\" href=\"acc.php?action=zoom&id=$rid\">Zoom</a> ";
		}
		$query2 = 'SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_ip` = \'' . $row['pend_ip'] . '\' AND `pend_id` != \'' . $row['pend_id'] . '\';';
		$otherreqs = mysql_fetch_assoc(mysql_query($query2));
		$out = '<tr';
		if ($currentreq % 2 == 0) {
			$out .= ' class="even">';
		} else {
			$out .= ' class="odd">';
		}
		if ($type == 'Admin' || $type == 'Open') {
			$out .= '<td><small>' . $currentreq . '.    </small></td><td><small>'; //List item
			$out .= $cmt; // CMT link.
		} else {
			$out .= '<td><small>'; //List item
		}

		// Email.
		$out .= '</small></td><td><small>[ <a style="color:green" href="mailto:' . $row['pend_email'] . '">' . $row['pend_email'] . '</a>';

		// IP UT:
		$out .= '</small></td><td><small> | <a style="color:green" href="http://en.wikipedia.org/wiki/User_talk:' . $row['pend_ip'] . '">';
		$out .= $row['pend_ip'] . '</a> ';

		$out .= '</small></td><td><small><span style="color:';
		if ($otherreqs['count'] == 0) {
			$out .= 'green">(' . $otherreqs['count'] . ')';
		} else {
			$out .= 'black">(</span><b><span style="color:red">' . $otherreqs['count'] . '</span></b><span style="color:black">)';
		}
		$out .= " <span>";

		// IP contribs
		$out .= '</span></small></td><td><small><a style="color:green" href="http://en.wikipedia.org/wiki/Special:Contributions/';
		$out .= $row['pend_ip'] . '" target="_blank">c</a> ';

		// IP blocks
		$out .= '<a style="color:green" href="http://en.wikipedia.org/w/index.php?title=Special:Log&type=block&page=User:';
		$out .= $row['pend_ip'] . '">b</a> ';

		// IP whois
		$out .= '<a style="color:green" href="http://ws.arin.net/whois/?queryinput=' . $row['pend_ip'] . '">w</a> ] ';

		// Username U:
		$duname = _utf8_decode($row['pend_name']);
		$out .= '</small></td><td><small><a style="color:blue" href="http://en.wikipedia.org/wiki/User:' . $uname . '"><strong>' . $duname . '</ strong></a> ';

		// Creation log    
		$out .= '</small></td><td><small>(<a style="color:blue" href="http://en.wikipedia.org/w/index.php?title=Special:Log&type=newusers&user=&page=User:';
		$out .= $uname . '">Creation</a> ';

		// User contribs
		$out .= '<a style="color:blue" href="http://en.wikipedia.org/wiki/Special:Contributions/';
		$out .= $uname . '">Contribs</a> ';
		$out .= '<a style="color:blue" href="http://en.wikipedia.org/w/index.php?title=Special%3AListUsers&username=' . $uname . '&group=&limit=50">List</a>) ';

		// Create user link
		$out .= '<b><a style="color:blue" href="http://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&wpName=';
		$out .= $uname . '&wpEmail=' . $row['pend_email'] . '&uselang=en-acc" target="_blank">Create!</a></b> ';

		// Done
		$out .= '| <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=1&sum=' . $row['pend_checksum'] . '">Done!</a>';

		// Similar
		$out .= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=2&sum=' . $row['pend_checksum'] . '">Similar</a>';

		// Taken
		$out .= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=3&sum=' . $row['pend_checksum'] . '">Taken</a>';

		// UPolicy
		$out .= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=4&sum=' . $row['pend_checksum'] . '">UPolicy</a>';

		// Invalid
		$out .= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=5&sum=' . $row['pend_checksum'] . '">Invalid</a>';

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
			$out .= " - <a style=\"color:orange\" href=\"acc.php?action=defer&id=$row[pend_id]&sum=$row[pend_checksum]&target=$target\">Defer to $target" . "s</a>";
		} else {
			$out .= " - <a style=\"color:orange\" href=\"acc.php?action=defer&id=$row[pend_id]&sum=$row[pend_checksum]&target=user\">Reset Request</a>";
		}
		// Drop
		$out .= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=0&sum=' . $row['pend_checksum'] . '">Drop</a>';

		// Ban IP
		$out .= ' | Ban: <a style="color:red" href="acc.php?action=ban&ip=' . $row['pend_id'] . '">IP</a> ';

		// Ban email
		$out .= '- <a style="color:red" href="acc.php?action=ban&email=' . $row['pend_id'] . '">E-Mail</a>';

		//Ban name
		$out .= ' - <a style="color:red" href="acc.php?action=ban&name=' . $row['pend_id'] . '">Name</a>';

		$out .= '</small></td></tr>';
		$reqlist .= $out;
	}
	return ($tablestart . $reqlist . $tableend);
	
}

function makehead($suin) {
	/*
	* Show page header (retrieved by MySQL call)
	*/
	$rethead = '';
	$query = "SELECT * FROM acc_user WHERE user_name = '$suin' LIMIT 1;";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	$_SESSION['user_id'] = $row['user_id'];
	$out = showmessage('21');
	if (isset ($_SESSION['user'])) { //Is user logged in?
		$mquery = "SELECT * FROM acc_user WHERE user_name = '$suin';";
		$mresult = mysql_query($mquery);
		if (!$mresult)
			echo ("<!-- ERROR: No result returned. mysql_error() --!>");
		$mrow = mysql_fetch_assoc($mresult);
		if ($mrow['user_level'] == "Admin") {
			$out = preg_replace('/\<a href\=\"acc\.php\?action\=messagemgmt\"\>Message Management\<\/a\>/', "\n<a href=\"acc.php?action=messagemgmt\">Message Management</a>\n<a href=\"acc.php?action=usermgmt\">User Management</a>\n", $out);
		}
		$rethead .= $out;
		$rethead .= "<div id = \"header-info\">Logged in as <a href=\"users.php?viewuser=$_SESSION[user_id]\"><span title=\"View your user information\">$_SESSION[user]</span></a>.  <a href=\"acc.php?action=logout\">Logout</a>?</div>\n";
		//Update user_lastactive
		$now = date("Y-m-d H-i-s");
		$query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '$_SESSION[user_id]';";
		$result = mysql_query($query);
		if (!$result)
			Die("ERROR: No result returned.");
	} else {
		$rethead .= $out;
		$rethead .= "<div id = \"header-info\">Not logged in.  <a href=\"acc.php\"><span title=\"Click here to return to the login form\">Log in</span></a>/<a href=\"acc.php?action=register\">Create account</a>?</div>\n";
	}
	return($rethead);
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
	$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><small><center>$howma users active within the last 5 mins! ($howout)</center></small><br /><br />", $out);
	return $out;
}

function showlogin() {
	global $_SESSION;
	$html = <<<HTML
    <div id="sitenotice">Please login first, and we'll send you on your way!</div>
    <div id="content">
    <h2>Login</h2>
    <form action="acc.php?action=login&nocheck=1" method="post">
    <div class="required">
        <label for="password">Username:</label>
        <input type="text" name="username">
    </div>
    <div class="required">
        <label for="password">Password:</label>
        <input type="password" name="password">
    </div>
    <div class="submit">
        <input type="submit">
    </div>
    </form>
    <br />
    Don't have an account? 
    <br /><a href="acc.php?action=register">Register!</a> (Requires approval)<br />
    <a href="acc.php?action=forgotpw">Forgot your password?</a><br />
    </div>
HTML;

  	return $html;
}

function getdevs() {
	global $regdevlist;
	$newdevlist = array_reverse($regdevlist);
	$temp = $newdevlist[0];
	unset ($newdevlist[0]);
	foreach ($newdevlist as $dev) {
		$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:" . $dev[1] . "\">" . $dev[0] . "</a>, ";
	}
	$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:" . $temp[1] . "\">" . $temp[0] . "</a>";
	return $devs;
}

function defaultpage() {
	$html = <<<HTML
<h1>Create an account!</h1>
<h2>Open requests</h2>
<a name="open"></a>
HTML;

	$html .= listrequests("Open");
	$html .= <<<HTML
<h2>Admin Needed!</h2>
<a name="admin"></a>
<span id="admin"/>
HTML;
	$html .= listrequests("Admin");

	$html .= "<h2>Last 5 Closed requests</h2><A name='closed'></A><span id=\"closed\"/>\n";
	$query = "SELECT * FROM acc_pend JOIN acc_log ON pend_id = log_pend WHERE log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 5;";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
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
		$out .= "<td><small><a style=\"color:green\" href=\"acc.php?action=zoom&id=".$row['pend_id']."\">Zoom</a></small></td><td><small>  <a style=\"color:blue\" href=\"http://en.wikipedia.org/wiki/User:".$row['pend_name']."\">"._utf8_decode($row['pend_name'])."</a></small></td><td><small>  <a style=\"color:orange\" href=\"acc.php?action=defer&id=".$row['pend_id']."&sum=".$row['pend_checksum']."&target=user\">Reset</a></small></td></tr>";
		$html .= $out;
	}
	$html .= "</table>\n";
	$html .= showfooter();
	return $html;
}
?>
