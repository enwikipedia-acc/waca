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
**                                                           **
**************************************************************/

require_once('config.inc');
function sanitize($what) {
	$what = mysql_real_escape_string($what);
	return($what);
}

function checkdnsbls ($addr) {
	global $dnsbls;

	$dnsblip = implode('.',array_reverse(explode('.',$addr)));
	$dnsbldata = '<ul>';
	$banned = false;

	foreach ($dnsbls as $dnsblname => $dnsbl) {
		echo '<!-- Checking '.$dnsblname.' ... ';
		$tmpdnsblresult = gethostbyname($dnsblip.'.'.$dnsbl['zone']);
		echo $tmpdnsblresult.' -->';
		if (long2ip(ip2long($tmpdnsblresult)) != $tmpdnsblresult) { $tmpdnsblresult = 'Nothing.'; continue; }
//		if (!isset($dnsbl['ret'][$lastdigit]) and ($dnsbl['bunk'] == false)) { $tmpdnsblresult = 'Nothing.'; continue; }
		$dnsbldata .= '<li> '.$dnsblip.'.'.$dnsbl['zone'].' ('.$dnsblname.') = '.$tmpdnsblresult;
		$lastdigit = explode('.',$tmpdnsblresult);
		$lastdigit = $lastdigit[3];
		if (isset($dnsbl['ret'][$lastdigit])) { $dnsbldata .= ' ('.$dnsbl['ret'][$lastdigit].')'; $banned = true; }
		else { $dnsbldata .= ' (unknown)'; if ($dnsbl['bunk']) $banned = true; }
		$dnsbldata .= ' &mdash;  <a href="'.str_replace('%i',$addr,$dnsbl['url'])."\"> more information</a>.\n";
	}
	unset($dnsblip,$dnsblname,$dnsbl,$tmpdnsblresult,$lastdigit);

	$dnsbldata .= '</ul>';
	echo '<!-- '.$dnsbldata.' -->';
	return array($banned,$dnsbldata);
}

function checktor ($addr) {
	$flags = array();
	$flags['tor'] = "no";
	$p = explode(".", $addr);
	$ahbladdr = $p[3] . "." . $p[2] . "." . $p[1] . "." . $p[0] . "." . "tor.ahbl.org";;
	$ahbl = gethostbyname($ahbladdr);
	if ($ahbl == "127.0.0.2") { $flags['transit'] = "yes"; "yes"; $flags['tor'] = "yes";}
	if ($ahbl == "127.0.0.3") { $flags['exit'] = "yes"; "yes"; $flags['tor'] = "yes";}
	return($flags);
}

function displayheader() {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '8';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row[mail_text];	
}
function displayfooter() {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '7';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row[mail_text];	
}
function displayform() {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '6';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row[mail_text];	
}
function showmessage($messageno) {
	global $toolserver_username;
	global $toolserver_password;
	global $toolserver_host;
	global $toolserver_database;
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '$messageno';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	return($row[mail_text]);	
}
displayheader();
if ($_POST['name'] != NULL && $_POST['email'] != NULL) {
	if ($_POST['debug'] == "on") {
		echo "<pre>\n";
		print_r($_POST);
		echo "</pre>\n";
	}
	$_POST['name'] = str_replace(" ", "_", $_POST['name']);
	$_POST['name'] = ucfirst($_POST['name']);
	mysql_connect("enwiki-p.db.ts.wikimedia.org",$toolserver_username,$toolserver_password);
	@mysql_select_db("enwiki_p") or print mysql_error();
	$query = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$ip = $_SERVER['REMOTE_ADDR'];
	$ip2 = $_SERVER['REMOTE_ADDR'];
	$ip = mysql_real_escape_string($ip);
	$userblocked = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$ip2&format=php");
	$ub = unserialize($userblocked);
	$email = $_POST['email'];
	$email = ltrim($email);
	$email = rtrim($email);
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	foreach ($nameblacklist as $wnbl => $nbl) {
		$phail_test = @preg_match($nbl, $_POST[name]);
		if($phail_test == TRUE) {
        	        $message = showmessage(15);
	                echo "$message<br />\n";
	        	$now = date("Y-m-d H-i-s");
			$target = "$wnbl";
			$siuser = mysql_real_escape_string("$_POST[name]");
			$cmt = mysql_real_escape_string("FROM $ip $email");
			$fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
			fwrite($fp, "[Name-Bl] HIT: $wnbl - $_POST[name] $ip2 $email $_SERVER[HTTP_USER_AGENT]\r\n");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
			//echo "<br />$query<br />";
			$result = mysql_query($query);
			if(!$result) Die("ERROR: No result returned.");
			fclose($fp);
			$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\''.$ip.'\',\'ClueBot\',\''.mysql_real_escape_string('Blacklist Hit: '.$wnbl.' - '.$_POST['name'].' '.$ip2.' '.$email.' '.$_SERVER['HTTP_USER_AGENT']).'\',\''.$now.'\',\''.(time() + 172800).'\');';
			mysql_query($query);
			die();		
		}
	}
	foreach ($emailblacklist as $wnbl => $nbl) {
		$phail_test = @preg_match($nbl, $_POST[email]);
		if($phail_test == TRUE) {
        	        $message = showmessage(15);
	                echo "$message<br />\n";
	        	$now = date("Y-m-d H-i-s");
			$target = "$wnbl";
			$siuser = mysql_real_escape_string("$_POST[name]");
			$cmt = mysql_real_escape_string("FROM $ip $email");
			$fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
			fwrite($fp, "[Email-Bl] HIT: $wnbl - $_POST[name] $ip2 $email $_SERVER[HTTP_USER_AGENT]\r\n");
			$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
			//echo "<br />$query<br />";
			$result = mysql_query($query);
			if(!$result) Die("ERROR: No result returned.");
			fclose($fp);
			$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\''.$ip.'\',\'ClueBot\',\''.mysql_real_escape_string('Blacklist Hit: '.$wnbl.' - '.$_POST['name'].' '.$ip2.' '.$email.' '.$_SERVER['HTTP_USER_AGENT']).'\',\''.$now.'\',\''.(time() + 172800).'\');';
			mysql_query($query);
			die();		
		}
	}
	$dnsblcheck = checkdnsbls($ip2);
	if ($dnsblcheck[0] == true) {
		$now = date("Y-m-d H-i-s");
		$siuser = mysql_real_escape_string("$_POST[name]");
		$cmt = mysql_real_escape_string("FROM $ip $email<br />$dnsblcheck[1]");
		$fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
		fwrite($fp, "[DNSBL] HIT: $_POST[name] $ip2 $email $_SERVER[HTTP_USER_AGENT]\r\n");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('DNSBL', '$siuser', 'DNSBL Hit', '$now', '$cmt');";
		echo '<!-- Query: '.$query.' -->';
		mysql_query($query);
		echo '<!-- Error: '.mysql_error().' -->';
		fclose($fp);
		$query = 'INSERT INTO `acc_ban` (`ban_type`,`ban_target`,`ban_user`,`ban_reason`,`ban_date`,`ban_duration`) VALUES (\'IP\',\''.$ip.'\',\'ClueBot\',\''.mysql_real_escape_string("DNSBL Hit:<br />\n".$dnsblcheck[1]).'\',\''.$now.'\',\''.(time() + 172800).'\');';
		echo '<!-- Query: '.$query.' -->';
		mysql_query($query);
		echo '<!-- Error: '.mysql_error().' -->';
//		$message = showmessage(15);
//		echo "$message<br />\n";
//		die();
	}

	if(isset($ub[query][blocks][0][id])) {
		$message = showmessage(9);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	mysql_connect("enwiki-p.db.ts.wikimedia.org",$toolserver_username,$toolserver_password);
	@mysql_select_db("enwiki_p") or print mysql_error();
	$user = $_POST['name'];
	$user = ltrim($user);
	$user = rtrim($user);
	$user = mysql_real_escape_string($user);
	$email = $_POST['email'];
	$email = ltrim($email);
	$email = rtrim($email);
	$email = mysql_real_escape_string($email);	
	$userexist = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=users&ususers=$_POST[name]&format=php");
	$ue = unserialize($userexist);
	foreach ($ue[query][users] as $oneue) {
        	if(!isset($oneue[missing])) {
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
	if ($unameisinvalidchar > 0) { 
		$message = showmessage(13);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
//	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['email']);
	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$/i', $_POST['email']);
	if ($mailisvalid == 0) { 
		$message = showmessage(14);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	
	$mailiswmf = preg_match('/.*wiki(m*dia|p*dia).*/i', $email);
	if ($mailiswmf != 0) {
		$message = showmessage(14);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_name = '$user'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	if ($row[pend_id] != "") {
		$message = showmessage(17);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_email = '$email'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	if ($row[pend_id] != "") {
		$message = showmessage(18);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	mysql_query('DELETE FROM `acc_ban` WHERE `ban_duration` < UNIX_TIMESTAMP()');
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'IP' AND ban_target = '$ip'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	$dbanned = $row[ban_duration];
	$toruser = checktor($ip2);
	if ($row[ban_id] != "" || $toruser[tor] == "yes") {
		if ($dbanned < 0 || $dbanned == "") {
			$dbanned = time() + 100;
		}
		if($toruser[tor] == "yes") { $row[ban_reason] = "<a href=\"http://en.wikipedia.org/wiki/Tor_%28anonymity_network%29\">TOR</a> nodes are not permitted to use this tool."; 
} 
		if ($dbanned < time()) {
			//Not banned!
		} else { //Still banned!
			$message = showmessage(19);
			echo "$message<strong>$row[ban_reason]</strong><br />\n"; 
			$fail = 1; 
			displayfooter();
			die();
		}
	}
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'Name' AND ban_target = '$user'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
        $dbanned = $row[ban_duration];
        if ($row[ban_id] != "") {
                if ($dbanned < 0 || $dbanned == "") {
                        $dbanned = time() + 100;
                }

                if ($dbanned < time()) {
                        //Not banned!
                } else { //Still banned!
			$message = showmessage(19);
			echo "$message<strong>$row[ban_reason]</strong><br />\n"; 
			$fail = 1; 
			displayfooter();
			die();
		}
	}
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'EMail' AND ban_target = '$email'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
        $dbanned = $row[ban_duration];
        if ($row[ban_id] != "") {
                if ($dbanned < 0 || $dbanned == "") {
                        $dbanned = time() + 100;
                }

                if ($dbanned < time()) {
                        //Not banned!
                } else { //Still banned!
			$message = showmessage(19);
			echo "$message<strong>$row[ban_reason]</strong><br />\n"; 
			$fail = 1; 
			displayfooter();
			die();
		}
	}

	if ($fail != 1) { 
		$message = showmessage(15);
		echo "$message<br />\n"; 
	} else { 
		$message = showmessage(16);
		echo "$message<br />\n"; 
	}
	if ($fail == 1) { displayform(); displayfooter(); die();}
	mysql_close();	
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db($toolserver_database) or print mysql_error();
	$comments = sanitize($_POST[comments]);
	$dnow = date("Y-m-d H-i-s");	
	$query = "INSERT INTO u_sql.acc_pend (pend_id , pend_email , pend_ip , pend_name , pend_cmt , pend_status , pend_date ) VALUES ( NULL , '$email', '$ip', '$user', '$comments', 'Open' , '$dnow' );";
	$result = mysql_query($query);
	$query = "SELECT pend_id FROM u_sql.acc_pend WHERE pend_name = '$user' ORDER BY pend_id DESC LIMIT 1;";
	$result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
	$pid = $row['pend_id'];
	$fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
	fwrite($fp, "[[acc:$pid]] N http://toolserver.org/~sql/acc/acc.php?action=zoom&id=$pid * /* $_POST[name] */ ".substr(str_replace(array("\n","\r"), array('\n','\r'),$_POST[comments]),0,200).((strlen($_POST[comments]) > 200) ? '...' : '')."\r\n");
	fclose($fp);
	if(!$result) Die("ERROR: No result returned.");
	mysql_close();		
} else {
	displayform();
	displayfooter();
	die();
} 
?>


