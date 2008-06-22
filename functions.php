<?php

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
	if ($pend[pend_checksum] == "") {
		upcsum($id);
		return (1);
	}
	if ($pend[pend_checksum] == $sum) {
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
	$fp = fsockopen("udp://91.198.174.201", 9001, $erno, $errstr, 30);
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
	$mailtxt = $row[mail_text];
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($target, "RE: English Wikipedia Account Request", $mailtxt, $headers);
}

?>
