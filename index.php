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
function checktorbl ($addr) {
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
	/* WAS:
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	<html>
	<head>
	<title>Request an English Wikipedia account!</title>
	<meta name="generator" content="Bluefish 1.0.7">
	<meta name="author" content="sql">
	<meta name="date" content="2008-04-04T00:41:52-0400">
	<meta name="copyright" content="">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8">
	<meta http-equiv="content-style-type" content="text/css">
	<meta http-equiv="expires" content="0">
	</head>
	<body>
	*/
	global $toolserver_username;
	global $toolserver_password;
	mysql_connect("sql",$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '8';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row[mail_text];	
}
function displayfooter() {
	/* WAS:
	<center><small>Concept Mock-up by <a href="http://en.wikipedia.org/wiki/User:SQL">SQL</a> March 2008. Released into the public domain.</small></center>
	</body>
	</html>
	*/
	global $toolserver_username;
	global $toolserver_password;
	mysql_connect("sql",$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
	$query = "SELECT * FROM acc_emails WHERE mail_id = '7';";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo $row[mail_text];	
}
function displayform() {
	/* WAS:
	<h1>Welcome to the Page for Requesting an Account on the English Wikipedia</h1>
	<br />
	<table cellpadding="1" cellspacing="0" border="0">
		<form action="index.php" method="post">
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
					<td>Requesting IP: (test only)</td>
					<td><input type="text" name="ip" value="127.0.0.1"></td>
				</tr>
			</td>
			<td>
				<tr>
					<td>Comments:</td>
					<td><textarea name="comments" rows="5" cols="40"></textarea></td>
				</tr>
			</td>
			<td>
				<tr>
					<td>Debug: <input type="checkbox" name="debug"></td>
					<td><button name="Submit" value="submit" type="submit">Submit</button><button name="Reset" type="reset">Reset</button></td>
				</tr>
			</td>
		</tr>
		</form>
	</table>
	*/
	global $toolserver_username;
	global $toolserver_password;
	mysql_connect("sql",$toolserver_username,$toolserver_password);
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
	mysql_connect("sql",$toolserver_username,$toolserver_password);
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
	#$ip = $_POST['ip']; //Was for debugging
	$ip = $_SERVER['REMOTE_ADDR'];
	$ip2 = $_SERVER['REMOTE_ADDR'];
	#$ip = ltrim($ip);
	#$ip = rtrim($ip);
	$ip = mysql_real_escape_string($ip);
	#$query = "SELECT * FROM ipblocks WHERE ipb_address = '$ip';";
	#$result = mysql_query($query);
	#$row = mysql_fetch_assoc($result);
	#if ($row['ipb_id'] != "") { 
	$userblocked = file_get_contents("http://en.wikipedia.org/w/api.php?action=query&list=blocks&bkusers=$ip2&format=php");
	$ub = unserialize($userblocked);
	if(isset($ub[query][blocks][0][id])) {
		/* WAS: I'm sorry, but your IP address is presently blocked. Please contact unblock-en-l to create an account. */
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
		/* WAS: I'm sorry, but that user name is taken. Please try another. */
		$message = showmessage(10);
		echo "$message<br />\n"; 
		$fail = 1; 
	        }
	}
	$nums = preg_match("/^[0-9]+$/", $_POST['name']);
	if ($nums > 0) { 
		/* WAS: Invalid: Entirely numbers. */
		$message = showmessage(11);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	$unameismail = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['name']);
	if ($unameismail > 0) { 
		/* WAS: Invalid: Your username may not be an e-mail address. */
		$message = showmessage(12);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	$unameisinvalidchar = preg_match('/[\#\/\|\[\]\{\}\@\%\:\<\>]/', $_POST['name']);
	if ($unameisinvalidchar > 0) { 
		/* WAS: Invalid: Your username may not contain the charachters #/|[]{}<>@%:. */
		$message = showmessage(13);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	$mailisvalid = preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $_POST['email']);
	if ($mailisvalid == 0) { 
		/* WAS: Invalid E-mail address supplied. */
		$message = showmessage(14);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	
	$mailiswmf = preg_match('/.*wiki(media|pedia).*/i', $email);
	if ($mailiswmf != 0) {
		/* WAS: Invalid E-mail address supplied. */
		$message = showmessage(14);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	
	mysql_connect("sql",$toolserver_username,$toolserver_password);
	@mysql_select_db("u_sql") or print mysql_error();
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_name = '$user'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	if ($row[pend_id] != "") {
		/* WAS: There is already an open request for this username. Please choose another. */
		$message = showmessage(17);
		echo "$message<br />\n"; 
		$fail = 1; 
	}
	$query = "SELECT * FROM acc_pend WHERE pend_status = 'Open' AND pend_email = '$email'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	if ($row[pend_id] != "") {
		/* WAS: I'm sorry, but you have already put in a request. Please do not submit multiple requests. */
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
			/* WAS: I'm sorry, but you are banned for:  */
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
#	if ($row[ban_id] != "") {
        $dbanned = $row[ban_duration];
        if ($row[ban_id] != "") {
                if ($dbanned < 0 || $dbanned == "") {
                        $dbanned = time() + 100;
                }

                if ($dbanned < time()) {
                        //Not banned!
                } else { //Still banned!

		/* WAS: I'm sorry, but you are banned for:  */
			$message = showmessage(19);
			echo "$message<strong>$row[ban_reason]</strong><br />\n"; 
			$fail = 1; 
			displayfooter();
			die();
		}
	}
	if ($fail != 1) { 
		/* WAS: <h1>Request submitted!</h1>Your account request has been submitted. Please stand by, while your request is considered. This process may take up to 48 hours.<br /> */
		$message = showmessage(15);
		echo "$message<br />\n"; 
	} else { 
		/* WAS: The system would <strong>NOT</strong> have submitted this request. */
		$message = showmessage(16);
		echo "$message<br />\n"; 
	}
	if ($fail == 1) { displayform(); displayfooter(); die();}
	mysql_close();	
	mysql_connect("sql",$toolserver_username,$toolserver_password);
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

