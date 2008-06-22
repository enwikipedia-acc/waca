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
**                                                           **
**************************************************************/

require_once('config.inc.php');
require_once('devlist.php');
ini_set('session.cookie_path', $cookiepath);
ini_set('session.name', $sessionname);
$version = "0.9.7";

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
	$hash = md5($pend[pend_id].$pend[pend_name].$pend[pend_email].microtime());
	$query = "UPDATE acc_pend SET pend_checksum = '$hash' WHERE pend_id = '$id';";
        $result = mysql_query($query);
}

function csvalid ( $id, $sum ) {
	/*
	* Checks to make sure the entries checksum is still valid
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
	if($pend[pend_checksum] == "") {
		upcsum($id);
		return(1);
	}
	if($pend[pend_checksum] == $sum) {
		return(1);
	} else {
		return(0);
	}
}

function sendtobot ( $message ) {
	/*
	* Send to the IRC bot via UDP
	*/
	global $whichami;
	sleep(3);
	$fp = fsockopen("udp://91.198.174.201", 9001, $erno, $errstr, 30);
	if (!$fp) {
	  echo "SOCKET ERROR: $errstr ($errno)<br />\n";
	}
	fwrite($fp, "[$whichami]: $message\r\n");
	fclose($fp);
}

function showhowma ( ) {
	/*
	* Show how many users are logged in, in the footer
	*/
        global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
        mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
        @mysql_select_db($toolserver_database) or print mysql_error();
	$howma = gethowma();
	unset($howma['howmany']);
	$out = "";
	foreach ($howma as $oneonline) {
	        $query = "SELECT * FROM acc_user WHERE user_name = '$oneonline';";
	        $result = mysql_query($query);
	        if(!$result) Die("ERROR: No result returned.");
	        $row = mysql_fetch_assoc($result);
	        $uid = $row['user_id'];
	        $out .= " <a href=\"users.php?viewuser=$uid\">$oneonline</a>";    
	}
	$out = ltrim(rtrim($out));
	return($out);
}
	
function gethowma ( ) {
	/*
	* Get how many people are logged in
	*/
        global $toolserver_username;
        global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
        mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
        @mysql_select_db($toolserver_database) or print mysql_error();
	$last5min = time() - 300; // Get the users active as of the last 5 mins
	$last5mins = date("Y-m-d H:i:s", $last5min);
	$query = "SELECT * FROM acc_user WHERE user_lastactive > '$last5mins';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$whoactive = array();
	while ($row = mysql_fetch_assoc($result)) {
		array_push($whoactive, $row['user_name']);
	}
	$howma = count($whoactive);
	$whoactive['howmany'] = $howma;
	return($whoactive);
}

function showmessage ( $messageno ) {
	/* 
	* Show user-submitted messages from mySQL
	*/
        global $toolserver_username;
        global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
        mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
        @mysql_select_db($toolserver_database) or print mysql_error();
	$messageno = sanitize($messageno);
        $query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        return($row['mail_text']);
}

function sendemail ( $messageno, $target ) {
	/*
	* Send a "close pend ticket" email to the end user. (created, taken, etc...)
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$messageno = sanitize($messageno);
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	$mailtxt = $row[mail_text];    
    	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($target, "RE: English Wikipedia Account Request", $mailtxt, $headers);
}

function checksecurity ( $username ) {
	/*
	* Check the user's security level on page load, and bounce accordingly
	*/
	$username = sanitize($username);
	$query = "SELECT * FROM acc_user WHERE user_name = '$username';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	if($row['user_level'] == "New") {
	        echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
	        showfootern();
	        die();
	}
	if($row['user_level'] == "Suspended" && $username != "SQL") {
	        echo "I'm sorry, but, your account is presently suspended.<br />\n";
		showfootern();
        	die();
	}
	if($row['user_level'] == "Declined" && $username != "SQL") {
	        $query2 = "SELECT * FROM acc_log WHERE log_pend = '$row[user_id]' AND log_action = 'Declined' ORDER BY log_id DESC LIMIT 1;";
	        $result2 = mysql_query($query2);
	        if(!$result2) Die("ERROR: No result returned.");
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
	        showfootern();
	        die();
	}
}
function listrequests ( $type ) {
	/*
	* List requests, at Zoom, and, on the main page
	*/
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	if($type == 'Admin' || $type == 'Open') {
		$query = "SELECT * FROM acc_pend WHERE pend_status = '$type';";
	} else {
        	$query = "SELECT * FROM acc_pend WHERE pend_id = '$type';";
	}
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	echo "<table cellspacing=\"0\">\n";
	$currentreq = 0;
	while ($row = mysql_fetch_assoc($result)) {
	        $currentreq +=1;
	        $uname = urlencode($row['pend_name']);
		#    $uname = str_replace("+", "_", $row[pend_name]);
	        $rid = $row['pend_id'];
	        if($row['pend_cmt'] != "") {
	        	$cmt = "<a style=\"color:green\" href=\"acc.php?action=zoom&id=$rid\">Zoom (CMT)</a> ";
	        } else {
        		$cmt = "<a style=\"color:green\" href=\"acc.php?action=zoom&id=$rid\">Zoom</a> ";
        	}
        	$query2 = 'SELECT COUNT(*) AS `count` FROM `acc_pend` WHERE `pend_ip` = \''.$row['pend_ip'].'\' AND `pend_id` != \''.$row['pend_id'].'\';';
	        $otherreqs = mysql_fetch_assoc(mysql_query($query2));
	        $out = '<tr';
	        if($currentreq % 2 == 0) {
	        	$out.= ' class="even">';
	        } else {
			$out.= ' class="odd">';
		} 
	        if($type == 'Admin' || $type == 'Open') {
            		$out.= '<td><small>'.$currentreq.'.    </small></td><td><small>'; //List item
	        	$out.= $cmt; // CMT link.
	        } else {
                	$out.= '<td><small>'; //List item
	        }
    
        	// Email.
        	$out.= '</small></td><td><small>[ <a style="color:green" href="mailto:' . $row['pend_email'] . '">' . $row['pend_email'] . '</a>';
    
        	// IP UT:
        	$out.= '</small></td><td><small> | <a style="color:green" href="http://en.wikipedia.org/wiki/User_talk:' . $row['pend_ip'] . '">';
        	$out.= $row['pend_ip'] . '</a> ';
    	
        	$out.= '</small></td><td><small><span style="color:';
        	if($otherreqs['count'] == 0) {
        		$out.= 'green">('.$otherreqs['count'].')';
	        } else {        
                	$out.= 'black">(</span><b><span style="color:red">'.$otherreqs['count'].'</span></b><span style="color:black">)';
                }
	        $out.=" <span>";
    
        	// IP contribs
        	$out.= '</span></small></td><td><small><a style="color:green" href="http://en.wikipedia.org/wiki/Special:Contributions/';
        	$out.= $row['pend_ip'] . '" target="_blank">c</a> ';
    	
        	// IP blocks
        	$out.= '<a style="color:green" href="http://en.wikipedia.org/w/index.php?title=Special:Log&type=block&page=User:';
        	$out.= $row['pend_ip'] . '">b</a> ';
    
        	// IP whois
        	$out.= '<a style="color:green" href="http://ws.arin.net/whois/?queryinput=' . $row['pend_ip'] . '">w</a> ] ';
    
        	// Username U:
        	$out.= '</small></td><td><small><a style="color:blue" href="http://en.wikipedia.org/wiki/User:' . $uname . '"><strong>' . $uname . '</ strong></a> ';
    
        	// Creation log    
        	$out.= '</small></td><td><small>(<a style="color:blue" href="http://en.wikipedia.org/w/index.php?title=Special:Log&type=newusers&user=&page=User:';
        	$out.= $uname . '">Creation</a> ';
    
	        // User contribs
	        $out.= '<a style="color:blue" href="http://en.wikipedia.org/wiki/Special:Contributions/';
	        $out.= $uname . '">Contribs</a> ';
	        $out.= '<a style="color:blue" href="http://en.wikipedia.org/w/index.php?title=Special%3AListUsers&username=' . $uname . '&group=&limit=50">List</a>) ';

	        // Create user link
	        $out.= '<b><a style="color:blue" href="http://en.wikipedia.org/w/index.php?title=Special:UserLogin/signup&wpName=';
        	$out.= $uname . '&wpEmail=' . $row['pend_email'] . '&uselang=en-acc" target="_blank">Create!</a></b> '; 
    
	        // Done
        	$out.= '| <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=1&sum=' . $row['pend_checksum'] . '">Done!</a>';
    
	        // Similar
        	$out.= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=2&sum=' . $row['pend_checksum'] . '">Similar</a>';
    
	        // Taken
	        $out.= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=3&sum=' . $row['pend_checksum'] . '">Taken</a>';
    
	        // UPolicy
	        $out.= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=4&sum=' . $row['pend_checksum'] . '">UPolicy</a>';
    
	        // Invalid
	        $out.= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=5&sum=' . $row['pend_checksum'] . '">Invalid</a>';
    
	        // Defer to admins or users
		if(is_numeric($type)) {
			$type = $row['pend_status'];
		}
	        if($type == 'Open') { $target = 'admin'; } elseif ( $type == 'Admin') { $target = 'user'; }
	        if($target == 'admin' || $target == 'user') {
			$out.= " - <a style=\"color:orange\" href=\"acc.php?action=defer&id=$row[pend_id]&sum=$row[pend_checksum]&target=$target\">Defer to $target" . "s</a>";
	        } else {
        		$out.= " - <a style=\"color:orange\" href=\"acc.php?action=defer&id=$row[pend_id]&sum=$row[pend_checksum]&target=user\">Reset Request</a>";
	        }
        	// Drop
        	$out.= ' - <a style="color:orange" href="acc.php?action=done&id=' . $row['pend_id'] . '&email=0&sum=' . $row['pend_checksum'] . '">Drop</a>';
    
	        // Ban IP
        	$out.= ' | Ban: <a style="color:red" href="acc.php?action=ban&ip=' . $row['pend_id'] . '">IP</a> ';
    
        	// Ban email
        	$out.= '- <a style="color:red" href="acc.php?action=ban&email=' . $row['pend_id'] . '">E-Mail</a>';
    	
        	//Ban name
        	$out.= ' - <a style="color:red" href="acc.php?action=ban&name=' . $row['pend_id'	] . '">Name</a>';
    
	        $out.= '</small></td></tr>';
        	echo "$out\n";
	}    
	echo "</table>\n";
}

function showhead ( ) {
	/*
	* Show page header (retrieved by MySQL call)
	*/
	$suin = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_user WHERE user_name = '$suin' LIMIT 1;";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	$_SESSION['user_id'] = $row['user_id'];
	$out = showmessage('21');
	if(isset($_SESSION['user'])) { //Is user logged in?
        	$suser = sanitize($_SESSION['user']);
        	$mquery = "SELECT * FROM acc_user WHERE user_name = '$suser';";
        	$mresult = mysql_query($mquery);
        	if(!$mresult) echo("<!-- ERROR: No result returned. mysql_error() --!>");
        	$mrow = mysql_fetch_assoc($mresult);
        	if($mrow['user_level'] == "Admin") {
			$out = preg_replace('/\<a href\=\"acc\.php\?action\=messagemgmt\"\>Message Management\<\/a\>/', "\n<a href=\"acc.php?action=messagemgmt\">Message Management</a>\n<a href=\"acc.php?action=usermgmt\">User Management</a>\n", $out);
		}
		echo $out;
        	echo "<div id = \"header-info\">Logged in as <a href=\"users.php?viewuser=$_SESSION[user_id]\"><span title=\"View your user information\">$_SESSION[user]</span></a>.  <a href=\"acc.php?action=logout\">Logout</a>?</div>\n";
	        //Update user_lastactive
        	$now = date("Y-m-d H-i-s");
        	$query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '$_SESSION[user_id]';";
        	$result = mysql_query($query);
        	if(!$result) Die("ERROR: No result returned.");
	} else { 
		echo $out;
	        echo "<div id = \"header-info\">Not logged in.  <a href=\"acc.php\"><span title=\"Click here to return to the login form\">Log in</span></a>/<a href=\"acc.php?action=register\">Create account</a>?</div>\n"; 
	}
}

function showfootern ( ) {
	/*
	* Show footer (not logged in)
	*/
	$out = showmessage('22');
	echo $out;
}

function showfooter ( ) {
	/*
	* Show footer (logged in)
	*/
	$howmany = array();
	$howmany = gethowma();
	$howout = showhowma();
	$howma = $howmany['howmany'];
        $out = showmessage('23');
	$out = preg_replace('/\<br \/\>\<br \/\>/', "<br /><small><center>$howma users active within the last 5 mins! ($howout)</center></small><br /><br />", $out);
	echo $out;
}

$link = mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
if(!$link) {
	die('Could not connect: ' . mysql_error());
}
@mysql_select_db($toolserver_database) or print mysql_error();
session_start();
if ($_GET['action'] == "sreg") {
    showhead();
    foreach ($acrnamebl as $wnbl => $nbl) {
        $phail_test = @preg_match($nbl, $_POST[name]);
        if($phail_test == TRUE) {
                    #$message = showmessage(15);
                    echo "$message<br />\n";
            $target = "$wnbl";
            $host = gethostbyaddr($_SERVER[REMOTE_ADDR]);
            $fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
            fwrite($fp, "[Name-Bl-ACR] HIT: $wnbl - $_POST[name] / $_POST[wname] $_SERVER[REMOTE_ADDR] ($host) $_POST[email] $_SERVER[HTTP_USER_AGENT]\r\n");
            fclose($fp);
                echo "Account created!<br /><br />\n";
            die();        
        }
    }
    $dnsblcheck = checkdnsbls($_SERVER['REMOTE_ADDRR']);
    if ($dnsblcheck[0] == true) {
        $cmt = "FROM $ip $dnsblcheck[1]";
        $fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
        fwrite($fp, "[DNSBL-ACR] HIT: $_POST[name] - $_POST[wname] $_SERVER[REMOTE_ADDR] $_POST[email] $_SERVER[HTTP_USER_AGENT] $cmt\r\n");
        fclose($fp);
        die("Account not created, please see $dnsblcheck[1]");
    }
    $cu_name = urlencode($_REQUEST[wname]);
    $userblocked = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$cu_name&format=php");
    $ub = unserialize($userblocked);
    if(isset($ub[query][blocks][0][id])) {
        $message = showmessage(9);
        echo "ERROR: You are presently blocked on the English Wikipedia<br />\n"; 
        $fail = 1; 
    }
        $userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$cu_name&format=php");
        $ue = unserialize($userexist);
        foreach ($ue[query][users][0] as $oneue) {
                if($oneue[missing] == "") {
                        echo "Invalid On-Wiki username.<br />\n";
                $fail = 1;
                }
        }

    $user = mysql_real_escape_string($_REQUEST['name']);
    if (stristr($user, "'") !== FALSE) { die ("Username cannot contain the character '\n"); }
    $wname = mysql_real_escape_string($_REQUEST['wname']);
    $pass = mysql_real_escape_string($_REQUEST['pass']);
    $pass2 = mysql_real_escape_string($_REQUEST['pass2']);
    $email = mysql_real_escape_string($_REQUEST['email']);
    $sig = mysql_real_escape_string($_REQUEST['sig']);
    $template = mysql_real_escape_string($_REQUEST['template']);
    $welcomeenable = mysql_real_escape_string($_REQUEST['welcomeenable']);
    if($user == "" || $wname == "" || $pass == "" || $pass2 == "" || $email == "" || strlen($email) < 6) {
        echo "<h2>ERROR!</h2>Form data may not be blank.<br />\n";
        showfooter();
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
    if ($pass != $pass2) { echo "Passwords did not match!<br />\n"; $fail = 1; }
    $query = "SELECT * FROM acc_user WHERE user_name = '$user' LIMIT 1;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if ($row['user_id'] != "") { echo "I'm sorry, but that username is in use. Please choose another. <br />\n"; $fail = 1; }
    $query = "SELECT * FROM acc_user WHERE user_email = '$email' LIMIT 1;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if ($row['user_id'] != "") { echo "I'm sorry, but that e-mail address is in use.<br />\n"; $fail = 1; }
    $query = "SELECT * FROM acc_user WHERE user_onwikiname = '$wname' LIMIT 1;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if ($row['user_id'] != "") { echo "I'm sorry, but $wname already has an account here.<br />\n"; $fail = 1; }
    if($fail != 1) {
        if($welcomeenable == "1") {$welcome = 1;} else { $welcome = 0; }
        $user_pass = md5($pass);
        $query = "INSERT INTO acc_user (user_name, user_email, user_pass, user_level, user_onwikiname, user_welcome, user_welcome_sig, user_welcome_template) VALUES ('$user', '$email', '$user_pass', 'New', '$wname', '$welcome', '$sig', '$template');";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        sendtobot("New user: $user");
        echo "Account created!<br /><br />\n";
        showlogin();
    }
    showfootern();
    die();
}
if ($_GET['action'] == "register") {
    showhead();
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
    showfootern();
    die();
}
if ($_GET['action'] == "forgotpw") {
    showhead();
    if(isset($_GET['si']) && isset($_GET['id'])) {
        if(isset($_POST['pw']) && isset($_POST['pw2'])) {
            $puser = sanitize($_GET['id']);
            $query = "SELECT * FROM acc_user WHERE user_id = '$puser';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            $row = mysql_fetch_assoc($result);
            $hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
            $hash = md5($hashme);
            if ($hash == $_GET['si']) {
                if($_POST['pw'] == $_POST['pw2']) {
                    $pw = md5($_POST['pw2']);
                    $query = "UPDATE acc_user SET user_pass = '$pw' WHERE user_id = '$puser';";
                    $result = mysql_query($query);
                    if(!$result) Die("ERROR: No result returned.");
                    echo "Password reset!\n<br />\nYou may now <a href=\"acc.php\">Login</a>";
                } else {
                    echo "<h2>ERROR</h2>Passwords did not match!<br />\n";
                }
            } else {
                echo "<h2>ERROR</h2>\nInvalid request.1<br />";
            }
            showfootern();
            die();
        }
        $puser = sanitize($_GET['id']);
        $query = "SELECT * FROM acc_user WHERE user_id = '$puser';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        $hashme = $row['user_name'] . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
        $hash = md5($hashme);
        if ($hash == $_GET['si']) {
            ?><h2>Reset password for <?php echo "$row[user_name] ($row[user_email])";?></h2>
            <form action="acc.php?action=forgotpw&si=<?php echo $_GET['si']; ?>&id=<?php echo $_GET['id']; ?>" method="post">
            New Password: <input type="password" name="pw"><br />
            New Password (confirm): <input type="password" name="pw2"><br />
            <input type="submit"><input type="reset">
            </form><br />
            Return to <a href="acc.php">Login</a>
            <?php
        } else {
            echo "<h2>ERROR</h2>\nInvalid request.2<br />";
        }
        showfootern();
        die();
    }
    if(isset($_POST['username'])) {
        $puser = sanitize($_POST['username']);
        $query = "SELECT * FROM acc_user WHERE user_name = '$puser';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        if($row['user_id'] == "") {
            echo "<h2>ERROR</h2>Missing or invalid information supplied.\n";
            die();
        }
        if(strtolower($_POST['email']) != strtolower($row['user_email'])) {
            echo "<h2>ERROR</h2>Missing or invalid information supplied (ERR 2).\n";
            showfootern();
            die();
        }
        $hashme = $puser . $row['user_email'] . $row['user_welcome_template'] . $row['user_id'] . $row['user_pass'];
        $hash = md5($hashme);
        $mailtxt = "Hello! You, or a user from $_SERVER[REMOTE_ADDR], has requested a password reset for your account.\n\nPlease go to $tsurl/acc.php?action=forgotpw&si=$hash&id=$row[user_id] to complete this request.\n\nIf you did not request this reset, please disregard this message.\n\n";
        $headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
        mail($row['user_email'], "English Wikipedia Account Request System - Forgotten password", $mailtxt, $headers);
        echo "Your password reset request has been completed. Please check your e-mail.\n<br />";
        showfootern();
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
    showfootern();
    die();
}
if ($_GET['action'] == "login") {
    $puser = sanitize($_POST[username]);
    $query = "SELECT * FROM acc_user WHERE user_name = \"$puser\";";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if($row[user_level] == "New") {
        echo "I'm sorry, but, your account has not been approved by a site administrator yet. Please stand by.<br />\n";
        showfootern();
        die();
    }
    if($row[user_level] == "Suspended" && $_SESSION['user'] != "SQL") {
        echo "I'm sorry, but, your account is presently suspended.<br />\n";
        showfootern();
        die();
    }
    $calcpass = md5($_POST[password]);
    if ($row[user_pass] == $calcpass) { 
        $_SESSION['user'] = $row[user_name]; 
        header("Location: $tsurl/acc.php"); 
    } else {
        echo "<h2>ERROR</h2>\n";
        echo "Username and/or password incorrect.<br />\n";
    }
}
function showlogin() {
    global $_SESSION;
    ?>
    <div id="sitenotice">Please login first, and we'll send you on your way!</div>
    <div id="content">
    <h2>Login</h2>
    <form action="acc.php?action=login" method="post">
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
    <?php
}
showhead();
if ($_SESSION['user'] == "") { 
    showlogin();
    die();
    } else { 
        checksecurity($_SESSION['user']);
        $out = showmessage('20');
        $out .= "<div id=\"content\">";
        echo $out;
        $out = "";
}
if ($_GET['action'] == "messagemgmt") {
    if($_GET['view'] != "") {
        $mid = sanitize($_GET['view']);
        $query = "SELECT * FROM acc_emails WHERE mail_id = $mid;";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        $mailtext = htmlentities($row[mail_text]);
        echo "<h2>View message</h2><br />Message ID: $row[mail_id]<br />\n";
        echo "Message count: $row[mail_count]<br />\n";
        echo "Message title: $row[mail_desc]<br />\n";
        echo "Message text: <br /><pre>$mailtext</pre><br />\n";
        showfooter();
        die();
    }    
    if($_GET['edit'] != "") {
        $siuser = sanitize($_SESSION[user]);
        $query = "SELECT * FROM acc_user WHERE user_name = '$siuser';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        if($row[user_level] != "Admin"  && $_SESSION['user'] != "SQL") {
            echo "I'm sorry, but, this page is restricted to administrators only.<br />\n";
            showfooter();
            die();
        }
        $mid = sanitize($_GET['edit']);        
        if($_GET['submit'] == "1") {
            $mtext = html_entity_decode($mtext);
            $mtext = sanitize($_POST['mailtext']);
            $mdesc = sanitize($_POST['maildesc']);
            $siuser = sanitize($_SESSION[user]);
            $query = "UPDATE acc_emails SET mail_desc = '$mdesc' WHERE mail_id = '$mid';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            $query = "UPDATE acc_emails SET mail_text = '$mtext' WHERE mail_id = '$mid';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            $now = date("Y-m-d H-i-s");
            $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$mid', '$siuser', 'Edited', '$now');";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");            
            echo "Message $mid updated.<br />\n";
            sendtobot("Message $mid edited by $siuser");
            showfooter();
            die();
        }
        $query = "SELECT * FROM acc_emails WHERE mail_id = $mid;";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        $mailtext = htmlentities($row['mail_text']);
        echo "<h2>Edit message</h2><strong>This is NOT a toy. If you can see this form, you can edit this message. <br />WARNING: MISUSE OF THIS FUNCTION WILL RESULT IN LOSS OF ACCESS.</strong><br />\n<form action=\"acc.php?action=messagemgmt&edit=$mid&submit=1\" method=\"post\"><br />\n";
        echo "<input type=\"text\" name=\"maildesc\" value=\"$row[mail_desc]\"><br />\n";
        echo "<textarea name=\"mailtext\" rows=\"20\" cols=\"60\">$mailtext</textarea><br />\n";
        echo "<input type=\"submit\"><input type=\"reset\"><br />\n";        
        echo "</form>";
        showfooter();
        die();
    }
    $query = "SELECT * FROM acc_emails WHERE mail_type = 'Message';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<h2>Mail messages</h2>\n";
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        $mailn = $row['mail_id'];
        $mailc = $row['mail_count'];
        $maild = $row['mail_desc'];
        $out = "<li><small>[ $maild - $mailc ] <a href=\"acc.php?action=messagemgmt&edit=$mailn\">Edit! (admin only)</a> - <a href=\"acc.php?action=messagemgmt&view=$mailn\">View!</a></small></li>";
        echo "$out\n";
    }
    echo "<br />\n";
    $query = "SELECT * FROM acc_emails WHERE mail_type = 'Interface';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<h2>Public Interface messages</h2>\n";
    echo "\n";
    while ($row = mysql_fetch_assoc($result)) {
        $mailn = $row['mail_id'];
        $mailc = $row['mail_count'];
        $maild = $row['mail_desc'];
        $out = "<li><small>[ $maild ] <a href=\"acc.php?action=messagemgmt&edit=$mailn\">Edit! (admin only)</a> - <a href=\"acc.php?action=messagemgmt&view=$mailn\">View!</a></small></li>";
        echo "$out\n";
    }
    echo "<br />\n";
    $query = "SELECT * FROM acc_emails WHERE mail_type = 'Internal';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<h2>Internal Interface messages</h2>\n";
    echo "\n";
    while ($row = mysql_fetch_assoc($result)) {
        $mailn = $row['mail_id'];
        $mailc = $row['mail_count'];
        $maild = $row['mail_desc'];
        $out = "<li><small>[ $maild ] <a href=\"acc.php?action=messagemgmt&edit=$mailn\">Edit! (admin only)</a> - <a href=\"acc.php?action=messagemgmt&view=$mailn\">View!</a></small></li>";
        echo "$out\n";
    }
    echo "</ol><br />\n";
    showfooter();
    die();    
}
if ($_GET['action'] == "sban" && $_GET['user'] != "") {
    if ($_POST['banreason'] == "") {
        echo "<h2>ERROR</h2>\n<br />You must specify a ban reason.\n";
        showfooter();
        die();
    }
    $duration = sanitize($_POST['duration']);
    if ($duration == "-1") {
        $duration = -1;
    } else {
        $duration = $duration + time();
    }
    $reason = sanitize($_POST['banreason']);
    $siuser = sanitize($_GET['user']);
    $target = sanitize($_GET['target']);
    $type = sanitize($_GET['type']);
    $now = date("Y-m-d H-i-s");
    upcsum($target);
    $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$target', '$siuser', 'Banned', '$now');";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $query = "INSERT INTO acc_ban (ban_type, ban_target, ban_user, ban_reason, ban_date, ban_duration) VALUES ('$type', '$target', '$siuser', '$reason', '$now', $duration);";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "Banned $target for $reason<br />\n";
        if($duration == "" || $duration == "-1") {
                $until  = "Forever";
        } else {
                $until = date("F j, Y, g:i a", $duration);
        }
    sendtobot("$target banned by $siuser for $reason until $until");
    showfooter();
    die();
}
if ($_GET['action'] == "unban" && $_GET['id'] != "") {
    $siuser = sanitize($_SESSION[user]);
    $bid = sanitize($_GET['id']);
    $query = "DELETE FROM acc_ban WHERE ban_id = '$bid';";
    echo "$query\n";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
        $now = date("Y-m-d H-i-s");
    $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$bid', '$siuser', 'Unbanned', '$now');";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "Unbanned ban #$bid<br />\n";
    showfooter();
    die();
}
if ($_GET['action'] == "ban") {
    $siuser = sanitize($_SESSION[user]);
    if($_GET['ip'] != "" || $_GET['email'] != "" || $_GET['name'] != "") {
        if($_GET['ip'] != "") {
            $ip2 = sanitize($_GET['ip']);
            $query = "SELECT * FROM acc_pend WHERE pend_id = '$ip2';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            $row = mysql_fetch_assoc($result);
            $target = $row[pend_ip];
            $type = "IP";
        } elseif ($_GET['email'] != "") {
            $email2 = sanitize($_GET['email']);
            $query = "SELECT * FROM acc_pend WHERE pend_id = '$email2';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            $row = mysql_fetch_assoc($result);
            $target = $row[pend_email];
            $type = "EMail";
        } elseif ($_GET['name'] != "") {
                       $name2 = sanitize($_GET['name']);
                       $query = "SELECT * FROM acc_pend WHERE pend_id = '$name2';";
                       $result = mysql_query($query);
                       if(!$result) Die("ERROR: No result returned.");
                       $row = mysql_fetch_assoc($result);
                       $target = $row[pend_name];
                       $type = "Name";
        }
        $query = "SELECT * FROM acc_ban WHERE ban_target = '$target';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $row = mysql_fetch_assoc($result);
        if($row[ban_id] != "") {
            echo "<h2>ERROR</h2>\n<br />\nCould not ban. Already banned!<br />";
            showfooter();
            die();
        } else {
            echo "<h2>Ban an IP, Name or E-Mail</h2>\n<form action=\"acc.php?action=sban&user=$siuser&target=$target&type=$type\" method=\"post\">Ban target: $target\n<br />Reason: <input type=\"text\" name=\"banreason\">\n<br />Duration: <SELECT NAME=\"duration\"><OPTION VALUE=\"-1\">Forever<OPTION VALUE=\"604800\">One Week<OPTION VALUE=\"2629743\">One Month</SELECT><br /><input type=\"submit\"></form>\n";
        }
    }
    echo "<h2>Active Ban List</h2>\n<ol>\n";
    $query = "SELECT * FROM acc_ban;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    while($row = mysql_fetch_assoc($result)) {
        if($row['ban_duration'] == "" || $row['ban_duration'] == "-1") {
            $until  = "Forever";
        } else {
            $until = date("F j, Y, g:i a", $row['ban_duration']);
        }
        echo "<li><small><strong>".$row['ban_target']."</strong> - Banned by: <strong>".$row['ban_user']."</strong> for <strong>".$row['ban_reason']."</strong> at <strong>".$row['ban_date']."</strong> Until <strong>$until</strong>. (<a href=\"acc.php?action=unban&id=".$row['ban_id']."\">UNBAN</a>)</small></li>";
    }
    echo "</ol>\n";
    showfooter();
    die();
}
if ($_GET['action'] == "usermgmt") {
    $siuser = sanitize($_SESSION['user']);
    $query = "SELECT * FROM acc_user WHERE user_name = '$siuser';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if($row['user_level'] != "Admin" && $_SESSION['user'] != "SQL") {
        echo "I'm sorry, but, this page is restricted to administrators only.<br />\n";
        showfooter();
        die();
    }
    if($_GET['approve'] != "") {
        $aid = sanitize($_GET[approve]);
        $siuser = sanitize($_SESSION[user]);
        $query = "UPDATE acc_user SET user_level = 'User' WHERE user_id = '$aid';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
                $now = date("Y-m-d H-i-s");
        $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$aid', '$siuser', 'Approved', '$now');";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        echo "Changed User #$_GET[approve] access to 'User'<br />\n";
        $uid = $aid;
        $query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
        $result2 = mysql_query($query2);
        if(!$result2) Die("ERROR: No result returned.");
        $row2 = mysql_fetch_assoc($result2);
        sendtobot("User $aid ($row2[user_name]) approved by $siuser");
    }
    if($_GET['suspend'] != "") {
        $did = sanitize($_GET[suspend]);
        $siuser = sanitize($_SESSION[user]);
        if($_POST['suspendreason'] == "") {
            echo "<h2>Suspend Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"acc.php?action=usermgmt&suspend=$did\" method=\"post\"><br />\n";
            echo "<textarea name=\"suspendreason\" rows=\"20\" cols=\"60\"></textarea><br />\n";
            echo "<input type=\"submit\"><input type=\"reset\"><br />\n";        
            echo "</form>";
            showfooter();
            die();
        } else {
            $suspendrsn = sanitize($_POST['suspendreason']);
            $query = "UPDATE acc_user SET user_level = 'Suspended' WHERE user_id = '$did';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
                    $now = date("Y-m-d H-i-s");
            $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Suspended', '$now', '$suspendrsn');";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            echo "Changed User #$_GET[suspend] access to 'Suspended'<br />\n";
            $uid = $did;
            $query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
            $result2 = mysql_query($query2);
            if(!$result2) Die("ERROR: No result returned.");
            $row2 = mysql_fetch_assoc($result2);
            sendtobot("User $did ($row2[user_name]) suspended access by $siuser because: \"$suspendrsn\"");
            showfooter();
            die();
        }

    }
    if($_GET['promote'] != "") {
        $aid = sanitize($_GET[promote]);
        $siuser = sanitize($_SESSION[user]);
        $query = "UPDATE acc_user SET user_level = 'Admin' WHERE user_id = '$aid';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
                $now = date("Y-m-d H-i-s");
        $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$aid', '$siuser', 'Promoted', '$now');";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        echo "Changed User #$_GET[promote] access to 'Admin'<br />\n";
        $uid = $aid;
        $query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
        $result2 = mysql_query($query2);
        if(!$result2) Die("ERROR: No result returned.");
        $row2 = mysql_fetch_assoc($result2);
        sendtobot("User $aid ($row2[user_name]) promoted to admin by $siuser");
    }
    if($_GET['decline'] != "") {
        $did = sanitize($_GET[decline]);
        $siuser = sanitize($_SESSION[user]);
        if($_POST['declinereason'] == "") {
            echo "<h2>Decline Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"acc.php?action=usermgmt&decline=$did\" method=\"post\"><br />\n";
            echo "<textarea name=\"declinereason\" rows=\"20\" cols=\"60\"></textarea><br />\n";
            echo "<input type=\"submit\"><input type=\"reset\"><br />\n";        
            echo "</form>";
            showfooter();
            die();
        } else {
            $declinersn = sanitize($_POST['declinereason']);
            $query = "UPDATE acc_user SET user_level = 'Declined' WHERE user_id = '$did';";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
                    $now = date("Y-m-d H-i-s");
            $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Declined', '$now', '$declinersn');";
            $result = mysql_query($query);
            if(!$result) Die("ERROR: No result returned.");
            echo "Changed User #$_GET[decline] access to 'Declined'<br />\n";
            $uid = $did;
            $query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
            $result2 = mysql_query($query2);
            if(!$result2) Die("ERROR: No result returned.");
            $row2 = mysql_fetch_assoc($result2);
            sendtobot("User $did ($row2[user_name]) declined access by $siuser because: \"$declinersn\"");
            showfooter();
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
    if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        $uname = $row[user_name];
        $uoname = $row[user_onwikiname];
        $userid = $row[user_id];
        $out = "<li><small>[ $uname / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ] <a href=\"acc.php?action=usermgmt&approve=$userid\">Approve!</a> - <a href=\"acc.php?action=usermgmt&decline=$userid\">Decline</a> - <a href=\"http://toolserver.org/~sql/sqlbot.php?user=$uoname\">Count!</a></small></li>";
        echo "$out\n";
    }
    ?>
    </ol>
	<div id="usermgmt-users">
    <h2>Users</h2>
    <?php
    $query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Approved') WHERE user_level = 'User' GROUP BY log_pend ORDER BY log_pend DESC;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        $uname = $row['user_name'];
        $uoname = $row['user_onwikiname'];
        $userid = $row['user_id'];
        
        $out = "<li><small>[ <a href=\"users.php?viewuser=$userid\">$uname</a> / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ] <a href=\"acc.php?action=usermgmt&suspend=$userid\">Suspend!</a> - <a href=\"acc.php?action=usermgmt&promote=$userid\">Promote!</a> (Approved by $row[log_user])</small></li>";
        echo "$out\n";
    }
    ?>
    </ol>
	</div>
	<div id="usermgmt-admins">
    <h2>Admins</h2>
    <?php
    $query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Promoted') WHERE user_level = 'Admin' GROUP BY log_pend ORDER BY log_pend ASC;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        $uname = $row['user_name'];
        $uoname = $row['user_onwikiname'];
        $userid = $row['user_id'];
                $query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Suspended';";
                           $result2 = mysql_query($query);
                                    if(!$result2) Die("ERROR: No result returned.");
                                    $row2 = mysql_fetch_assoc($result2);
                    $suspended = $row2['COUNT(*)'];

                    $query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Promoted';";
                                    $result2 = mysql_query($query);
                                    if(!$result2) Die("ERROR: No result returned.");
                                    $row2 = mysql_fetch_assoc($result2);
                    $promoted = $row2['COUNT(*)'];


                    $query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Approved';";
                                    $result2 = mysql_query($query);
                                    if(!$result2) Die("ERROR: No result returned.");
                                    $row2 = mysql_fetch_assoc($result2);
                    $approved = $row2['COUNT(*)'];

        $out = "<li><small>[ <a href=\"users.php?viewuser=$userid\">$uname</a> / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ] <a href=\"acc.php?action=usermgmt&suspend=$userid\">Suspend!</a> - <a href=\"acc.php?action=usermgmt&approve=$userid\">Demote!</a> (Promoted by $row[log_user] [P:$promoted|S:$suspended|A:$approved])</small></li>";
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
    if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        $uname = $row['user_name'];
        $uoname = $row['user_onwikiname'];
        $userid = $row['user_id'];
        $out = "<li><small>[ <a href=\"users.php?viewuser=$userid\">$uname</a> / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ] <a href=\"acc.php?action=usermgmt&approve=$userid\">Unsuspend!</a> (Suspended by " . $row['log_user']."<!-- FREAKING PIECE OF CRAP ISN'T WORKING RIGHT NOW because " . $row['log_cmt'] . " --!>)</small></li>";
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
    if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        $uname = $row['user_name'];
        $uoname = $row['user_onwikiname'];
        $userid = $row['user_id'];
        $out = "<li><small>[ $uname / <a href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ] <a href=\"acc.php?action=usermgmt&approve=$userid\">Approve!</a> (Declined by " . $row['log_user'] . " because \"$row[log_cmt]\")</small></li>";
        echo "$out\n";
    }
    ?>
    </ol>
	</div>
    <?php    
    showfooter();
    die();
}

$devs = null;
$newdevlist = array_reverse($regdevlist);
$temp = $newdevlist[0];
unset($newdevlist[0]);
foreach ($newdevlist as $dev) {
	$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:".$dev[1]."\">".$dev[0]."</a>, ";
}
$devs .= "<a href=\"http://en.wikipedia.org/wiki/User talk:".$temp[1]."\">".$temp[0]."</a>";

if ($_GET['action'] == "defer" && $_GET['id'] != "" && $_GET['sum'] != "") {
    if ($_GET['target'] == "admin" || $_GET['target'] == "user") {
        if ($_GET['target'] == "admin") {
            $target = "Admin";
        } else {
            $target = "Open";
        }
        $gid = sanitize($_GET[id]);
	if(csvalid($gid, $_GET['sum']) != 1) {
		echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
		showfooter();
		die();
	}
        $sid = sanitize($_SESSION[user]);
	$query = "SELECT pend_status FROM acc_pend WHERE pend_id = '$gid';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	if ( $row[pend_status] == $target ) {
		echo "Cannot set status, target already deferred to $target<br />\n";
		showfooter();
		die();
	}
        $query = "UPDATE acc_pend SET pend_status = '$target' WHERE pend_id = '$gid';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        if ($_GET['target'] == "admin") {
            $deto = "admins";
        } else {
            $deto = "users";
        }
                $now = date("Y-m-d H-i-s");
        $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Deferred to $deto', '$now');";
	upcsum($gid);
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        sendtobot("Request $gid deferred to $deto by $sid");
        echo "Request $_GET[id] deferred to $deto.<br />";
    } else {
        echo "Target not specified.<br />\n";
    }
}
if ($_GET['action'] == "welcomeperf" || $_GET['action'] == "prefs") {//Welcomeperf is deprecated, but to avoid conflicts, include it still.
    if ($_POST['sig'] != "") {
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
        $query3= "UPDATE acc_user SET user_welcome_template = '$template' WHERE user_name = '$sid'";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
        $result = mysql_query($query2);
        if(!$result) Die("ERROR: No result returned.");
        $result = mysql_query($query3);
        if(!$result) Die("ERROR: No result returned.");
        echo "Preferences updated!<br />\n";
    }
    $sid = $_SESSION['user'];
    $query = "SELECT * FROM acc_user WHERE user_name = '$sid'";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if ($row['user_welcome'] > 0) { 
        $welcomeing = " checked";
    }
    $sig = " value=\"" . htmlentities($row['user_welcome_sig']) . "\"";
    $template = $row['user_welcome_template'];
    ?>
    <table>
    <th>Table of Contents</th>
    <tr><td><a href="#1">Welcome settings</a></td></tr>
    <tr><td><a href="#2">Change password</a></td></tr>
    </table>
    <a name="1"></a><h2>Welcome settings</h2>
    <form action="acc.php?action=welcomeperf" method="post">
    <input type="checkbox" name="welcomeenable"<?php echo $welcomeing ?>> Enable <a href="http://en.wikipedia.org/wiki/User:SQLBot-Hello">SQLBot-Hello</a> welcoming of the users I create<br />
    Your signature (wikicode) <input type="text" name="sig" size ="40"<?php echo $sig; ?>><br>
    <i>This would be the same as ~~~ on-wiki. No date, please.</i><br />
    <select name="template" size="0">
    <option value="welcome"<?php if($template == "welcone") { echo " selected"; } ?>>{{welcome|user}} ~~~~</option>
    <option value="welcomeg"<?php if($template == "welcomeg") { echo " selected"; } ?>>{{welcomeg|user}} ~~~~</option>
    <option value="w-screen"<?php if($template == "w-screen") { echo " selected"; } ?>>{{w-screen|sig=~~~~}}</option>
    <option value="welcome-personal"<?php if($template == "welcome-personal") { echo " selected"; } ?>>{{welcome-personal|user}} ~~~~</option>
    <option value="w-kk"<?php if($template == "w-kk") { echo " selected"; } ?>>{{User:KrakatoaKatie/Welcome1}} ~~~~</option>
    <option value="werdan7"<?php if($template == "werdan7") { echo " selected"; } ?>>{{User:Werdan7/W}} ~~~~</option>
    <option value="welcomemenu"<?php if($template == "welcomemenu") { echo " selected"; } ?>>{{WelcomeMenu|sig=~~~~}}</option>
    <option value="welcomeicon"<?php if($template == "welcomeicon") { echo " selected"; } ?>>{{WelcomeIcon}} ~~~~</option>
    <option value="welcomeshout"<?php if($template == "welcomeshout") { echo " selected"; } ?>>{{WelcomeShout|user}} ~~~~</option>
    <option value="welcomesmall"<?php if($template == "welcomesmall") { echo " selected"; } ?>>{{WelcomeSmall|user}} ~~~~</option>
    <option value="hopes"<?php if($template == "hopes") { echo " selected"; } ?>>{{Hopes Welcome}} ~~~~</option>
    <option value="welcomeshort"<?php if($template == "welcomeshort") { echo " selected"; } ?>>{{Welcomeshort|user}} ~~~~</option>
    <option value="w-riana"<?php if($template == "w-riana") { echo " selected"; } ?>>{{User:Riana/Welcome|name=user|sig=~~~~}}</option>
    <option value="wodup"<?php if($template == "wodup") { echo " selected"; } ?>>{{User:WODUP/Welcome}} ~~~~</option>
    <option value="williamh"<?php if($template == "williamh") { echo " selected"; } ?>>{{User:WilliamH/Welcome|user}} ~~~~</option>
    <option value="malinaccier"<?php if($template == "malinaccier") { echo " selected"; } ?>>{{User:Malinaccier/Welcome|~~~~}}</option>
    </select><br />
    <i>If you'd like more templates added, please contact one of the developers: <?php echo $devs; ?>.</i><br />
    <input type="submit"><input type="reset">
    </form>
    <a name="2"></a><h2>Change your password</h2>
    <form action="acc.php?action=forgotpw" method="post">
    Your username: <input type="text" name="username"><br />
    Your e-mail address: <input type="text" name="email"><br />
    <input type="submit"><input type="reset">
    </form><br />
    <?php
    showfooter();
    die();
}
if ($_GET['action'] == "done" && $_GET['id'] != "") {
    if($_GET['email'] == "" | $_GET['email'] >= 6) {
        echo "Invalid close reason";    
        showfooter();
        die();
    } 
    $gid = sanitize($_GET[id]);
    if(csvalid($gid, $_GET['sum']) != 1) {
		echo "Invalid checksum (This is similar to an edit conflict on Wikipedia; it means that <br />you have tried to perform an action on a request that someone else has performed an action on since you loaded the page)<br />";
	showfooter();
	die();
    }
    $query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
    $result = mysql_query($query);
    if(!$result) Die("Query failed: $query ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if($row[pend_emailsent] == "1" && $_GET['override'] != "yes") {
	echo "<br />This request has already been closed in a manner that has generated an e-mail to the user, Proceed?<br />\n";
	echo "<a href=\"acc.php?sum=$_GET[sum]&action=done&id=$_GET[id]&override=yes&email=$_GET[email]\">Yes</a> / <a href=\"acc.php\">No</a><br />\n";
	showfooter();
	die();
    }
    $gem = sanitize($_GET[email]);
    $sid = sanitize($_SESSION[user]);
    $query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
    $result = mysql_query($query);
    if(!$result) Die("Query failed: $query ERROR: No result returned.");
    $row2 = mysql_fetch_assoc($result);
    $gus = $row2[pend_name];
    if ($row2[pend_status] == "Closed") {    
        echo "<h2>ERROR</h2>Cannot close this request. Already closed.<br />\n";
        showfooter();
        die();
    }
    $query = "SELECT * FROM acc_user WHERE user_name = '$sid';";
    $result = mysql_query($query);
    if(!$result) Die("Query failed: $query ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if($row[user_welcome] > 0 && $gem == "1") {
        $sig = $row[user_welcome_sig];
        if($sig == "") { $sig = "[[User:$sid|$sid]] ([[User_talk:$sid|talk]])"; }
        $template = $row[user_welcome_template];
        $sig = sanitize($sig);
        if($template == "") { $template = "welcome"; }
        $query = "INSERT INTO acc_welcome (welcome_uid, welcome_user, welcome_sig, welcome_status, welcome_pend, welcome_template) VALUES ('$sid', '$gus', '$sig', 'Open', '$gid', '$template');";
        $result = mysql_query($query);
        if(!$result) Die("Query failed: $query ERROR: No result returned.");
    }
    $query = "UPDATE acc_pend SET pend_status = 'Closed' WHERE pend_id = '$gid';";
    $result = mysql_query($query);
    if(!$result) Die("Query failed: $query ERROR: No result returned.");
        $now = date("Y-m-d H-i-s");
    $query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$gid', '$sid', 'Closed $gem', '$now');";
    $result = mysql_query($query);
    if(!$result) Die("Query failed: $query ERROR: No result returned.");
        switch ($gem) {
        case 0:
            $crea = "Dropped";
            break;
        case 1:
            $crea = "Created";
            break;
        case 2:
            $crea = "Too Similar";
            break;
        case 3:
            $crea = "Taken";
            break;
        case 4:
            $crea = "Username vio";
            break;
        case 5:
            $crea = "Impossible";
            break;
    }
    $now = explode("-", $now);
    $now = $now[0]."-".$now[1]."-".$now[2].":".$now[3].":".$now[4];
    sendtobot("Request $_GET[id] ($gus) Marked as 'Done' ($crea) by $sid on $now");
    echo "Request " . $_GET['id'] . " ($gus) marked as 'Done'.<br />";
    $towhom = $row2[pend_email];
    if($gem != "0") { 
	sendemail($gem, $towhom); 
	$query = "UPDATE acc_pend SET pend_emailsent = '1' WHERE pend_id = '$_GET[id]';";
        $result = mysql_query($query);
    }
    upcsum($_GET[id]);
}
if ($_GET['action'] == "zoom") {
    if($_GET[id] == "") {
        echo "No user specified!<br />\n";
        showfooter();
        die();
    }
    $gid = sanitize($_GET[id]);
    $query = "SELECT * FROM acc_pend WHERE pend_id = '$gid';";
    $result = mysql_query($query);
    if(!$result) Die("Query failed: $query ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    echo "<h2>Details for Request #$_GET[id]:</h2>";    
    $uname = urlencode($row[pend_name]);
    $thisip = $row[pend_ip];
    $thisid = $row[pend_id];
    $thisemail = $row[pend_email];
    if($row['pend_date'] == "0000-00-00 00:00:00") { $row['pend_date'] = "Date Unknown"; }
    listrequests($thisid);
    $row[pend_cmt] = preg_replace('/\<\/?(div|span|script|\?php|\?|img)\s?(.*)\s?\>/i', '', $row[pend_cmt]);//Escape injections.
    echo "<br /><strong>Comment</strong>: $row[pend_cmt]<br />\n";
    $query = "SELECT * FROM acc_log WHERE log_pend = '$gid';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<h2>Logs for Request #$_GET[id]:</h2>";
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        if($row[log_action] == "Deferred to admins" || $row[log_action] == "Deferred to users") { 
            echo "<li>$row[log_user] $row[log_action], <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed") { 
            echo "<li>$row[log_user] $row[log_action], <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 0") { 
            echo "<li>$row[log_user] Dropped, <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 1") { 
            echo "<li>$row[log_user] Closed (Account created), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 2") { 
            echo "<li>$row[log_user] Closed (Too Similar), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 3") { 
            echo "<li>$row[log_user] Closed (Taken), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 4") { 
            echo "<li>$row[log_user] Closed (Username vio), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 5") { 
            echo "<li>$row[log_user] Closed (Technically impossibly), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 6") { 
            echo "<li>$row[log_user] Closed (Custom reason), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Blacklist Hit") { 
            echo "<li>$row[log_user] Rejected by Blacklist $row[log_pend], $row[log_cmt] at $row[log_time].</li>\n";
        }
    }

    echo "</ol>\n";
    echo "<h2>Other requests from $thisip:</h2>\n";
    echo "<ol>\n";
    $query = "SELECT * FROM acc_pend WHERE pend_ip = '$thisip' AND pend_id != '$thisid';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $numip = 0;
    while ($row = mysql_fetch_assoc($result)) {
        echo "<li><a href=\"acc.php?action=zoom&id=$row[pend_id]\">$row[pend_name]</a></li>";
        $numip++;
    }
    if($numip == 0) { echo "<i>None.</i>\n"; }
    echo "</ol>\n";
    echo "<h2>Other requests from $thisemail:</h2>\n";
    echo "<ol>\n";
    $query = "SELECT * FROM acc_pend WHERE pend_email = '$thisemail' AND pend_id != '$thisid';";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $numem = 0;
    while ($row = mysql_fetch_assoc($result)) {
        echo "<li><a href=\"acc.php?action=zoom&id=$row[pend_id]\">$row[pend_name]</a></li>";
        $numem++;
    }
    if($numem == 0) { echo "<i>None.</i>\n"; }
    echo "</ol>\n";
    showfooter();    
    die();
}
if ($_GET['action'] == "logout") {
    session_unset();
    showlogin();
    die("Logged out!\n");
}
if ($_GET['action'] == "logs") {
    if($_GET['limit'] != "") {
        $limit = $_GET['limit'];
        $limit = sanitize($limit);
    } else {
        $limit = 100;
    }
    if($_GET['from'] != "") {
        $from = sanitize($_GET['from']);
        $query = "SELECT * FROM acc_log ORDER BY log_time DESC LIMIT $limit OFFSET $from;";
    } else {
        $query = "SELECT * FROM acc_log ORDER BY log_time DESC LIMIT $limit;";
        $from = 0;
    }
    $next = $from + 100;
    $prev = $from - 100;
    if($from > 0) {
        $n1 = "<h4><a href=\"acc.php?action=logs&from=$prev\">Previous 100</a> <a href=\"acc.php?action=logs&from=$next\">Next 100</a></h4>\n";
        echo $n1;
    } else {
        $n1 = "<h4><a href=\"acc.php?action=logs&from=$next\">Next 100</a></h4>\n";
        echo $n1;
    }
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
    while ($row = mysql_fetch_assoc($result)) {
        if($row['log_time'] == "0000-00-00 00:00:00") { $row['log_time'] = "Date Unknown"; }
        if($row[log_action] == "Deferred to admins" || $row[log_action] == "Deferred to users") { 
            echo "<li>$row[log_user] $row[log_action], <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed") { 
            echo "<li>$row[log_user] $row[log_action], <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 0") { 
            echo "<li>$row[log_user] Dropped, <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 1") { 
            echo "<li>$row[log_user] Closed (Account created), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 2") { 
            echo "<li>$row[log_user] Closed (Too Similar), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 3") { 
            echo "<li>$row[log_user] Closed (Taken), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 4") { 
            echo "<li>$row[log_user] Closed (Username vio), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 5") { 
            echo "<li>$row[log_user] Closed (Technically impossibly), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Closed 6") { 
            echo "<li>$row[log_user] Closed (Custom reason), <a href=\"acc.php?action=zoom&id=$row[log_pend]\">Request $row[log_pend]</a> at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Blacklist Hit") { 
            echo "<li>$row[log_user] <strong>Rejected by Blacklist</strong> $row[log_pend], $row[log_cmt] at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Unbanned") { 
            echo "<li>$row[log_user] Unbanned $row[log_pend] at $row[log_time]</li>\n";
        }
        if($row[log_action] == "Banned") { 
            $mid = $row[log_pend];
            $query3 = "SELECT * FROM acc_ban WHERE ban_target = '$mid';";
            $result3 = mysql_query($query3);
            if(!$result3) Die("ERROR: No result returned.");
            $row3 = mysql_fetch_assoc($result3);
            echo "<li>$row[log_user] Banned $row3[log_pend] #$row3[ban_id] ($row3[ban_target])</a>, Reason: $row3[ban_reason], at $row[log_time].</li>\n";
        }

        if($row[log_action] == "Edited") { 
            $mid = $row[log_pend];
            $query3 = "SELECT * FROM acc_emails WHERE mail_id = '$mid';";
            $result3 = mysql_query($query3);
            if(!$result3) Die("ERROR: No result returned.");
            $row3 = mysql_fetch_assoc($result3);
            echo "<li>$row[log_user] Edited Message <a href=\"acc.php?action=messagemgmt&view=$row[log_pend]\">$row[log_pend] ($row3[mail_desc])</a>, at $row[log_time].</li>\n";
        }
        if($row[log_action] == "Promoted" || $row[log_action] == "Approved" || $row[log_action] == "Suspended" || $row[log_action] == "Declined") {
            $uid = $row[log_pend];
            $query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
            $result2 = mysql_query($query2);
            if(!$result2) Die("ERROR: No result returned.");
            $row2 = mysql_fetch_assoc($result2);
            $moreinfo = "";
            if($row[log_action] == "Declined") {
                $moreinfo = " because \"$row[log_cmt]\"";
            }
            echo "<li>$row[log_user] $row[log_action], User $row[log_pend] ($row2[user_name]) at $row[log_time]$moreinfo.</li>\n";
        }
    }
    echo "</ol>\n";
    echo $n1;
    showfooter();
    die();
}
?>
<h1>Create an account!</h1>
<h2>Open requests</h2>
<A name="open"></A>
<?php
listrequests("Open");
?>
<h2>Admin Needed!</h2>
<a name="admin"></a>
<span id="admin"/>
<?php
listrequests("Admin");
echo "<h2>Last 5 Closed requests</h2><A name='closed'></A><span id=\"closed\"/>\n";
$query = "SELECT * FROM acc_pend JOIN acc_log ON pend_id = log_pend WHERE log_action LIKE 'Closed%' ORDER BY log_time DESC LIMIT 5;";
$result = mysql_query($query);
if(!$result) Die("ERROR: No result returned.");
echo "<table cellspacing=\"0\">\n";
$currentrow = 0;
while ($row = mysql_fetch_assoc($result)) {
    $currentrow +=1;
    $out = '<tr';
    if($currentrow % 2 == 0) 
    {
        $out.= ' class="even">';
    }
    else
    {
        $out.= ' class="odd">';
    } 
    $out.= "<td><small><a style=\"color:green\" href=\"acc.php?action=zoom&id=$row[pend_id]\">Zoom</a></small></td><td><small>  <a style=\"color:blue\" href=\"http://en.wikipedia.org/wiki/User:$row[pend_name]\">$row[pend_name]</a></small></td><td><small>  <a style=\"color:orange\" href=\"acc.php?action=defer&id=$row[pend_id]&sum=$row[pend_checksum]&target=user\">Reset</a></small></td></tr>";
    echo $out;
}
echo "</table>\n";
showfooter();
?>
