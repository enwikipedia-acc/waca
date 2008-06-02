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

require_once('../../database.inc');
require_once('blacklist.php');
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
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
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
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
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
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
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
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
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
	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['email']);
	if ($mailisvalid == 0) { 
		$message = showmessage(14);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	
	$mailiswmf = preg_match('/.*wiki(media|pedia).*/i', $email);
	if ($mailiswmf != 0) {
		$message = showmessage(14);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	
	mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
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
	$query = "SELECT * FROM acc_ban WHERE ban_type = 'Name' AND ban_target = '$_POST[name]'";
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
	foreach ($nameblacklist as $wnbl => $nbl) {
		$phail_test = preg_match($nbl, $_POST[name]);
		if($phail_test == TRUE) {
                $message = showmessage(15);
                echo "$message<br />\n";
	        $now = date("Y-m-d H-i-s");
		$target = "BL";
		$siuser = "Blacklist";
		$cmt = "$_POST[name] matched $wnbl FROM $ip";
		$fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
		fwrite($fp, "[Blacklist] HIT: $wnbl - $_POST[name] $ip2 $email\r\n");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_timem log_cmt) VALUES ('$target', '$siuser', 'Blacklist Hit', '$now', '$cmt');";
		echo "<br />$query<br />";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		fclose($fp);
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
	@mysql_select_db("u_sql") or print mysql_error();
	$dnow = date("Y-m-d H-i-s");	
	$query = "INSERT INTO u_sql.acc_pend (pend_id , pend_email , pend_ip , pend_name , pend_cmt , pend_status , pend_date ) VALUES ( NULL , '$email', '$ip', '$_POST[name]', '$_POST[comments]', 'Open' , '$dnow' );";
	$result = mysql_query($query);
	$query = "SELECT pend_id FROM u_sql.acc_pend WHERE pend_name = '$_POST[name]' ORDER BY pend_id DESC LIMIT 1;";
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


