<?php
/*****************************************************
** English Wikipedia Account Request Interface      **
** Wikipedia Account Request Graphic Design by      **
** Charles Melbye is licensed under a Creative      **
** Commons Attribution-Noncommercial-Share Alike    **
** 3.0 United States License. All other code        **
** released under Public Domain by the ACC          **
** Development Team.                                **
**             Developers:                          **
**  SQL ( http://en.wikipedia.org/User:SQL )        **
**  Cobi ( http://en.wikipedia.org/User:Cobi )      **
** Cmelbye ( http://en.wikipedia.org/User:cmelbye ) **
**                                                  **
*****************************************************/

declare(ticks=1);
 
$pidnum = pcntl_fork();
 
if ($pidnum == -1) {
	#Well, we can't daemonize for some reason.
	die("Problem - Could not fork child!\n"); 
} else if ($pidnum) {
	#We'll only be running a child process, so, this process need not continue.
	#echo "Detaching from terminal.\n";	
	exit();
} else {
	#We're the child. Continuing on below as the child.
}
 
if (posix_setsid() == -1) {
	#If we can't detach, we're not daemonized. No reason to go on.
	die("Problem - I could not detach!\n");
}
 
#Set up signal handlers -- Linked to the functions below.
pcntl_signal(SIGHUP, "SIGHUP");
pcntl_signal(SIGTERM, "SIGTERM");
 
function SIGHUP() {
	#Do what you want it to do upon receiving a SIGHUP
}
 
function SIGTERM() {
	#Do what you want it to do upon receiving a SIGTERM. In this case, die.
	fclose($fp); //Goodnight!
	die("Received SIGTERM\n");
}
 
#Initialize your daemon here (i.e. make the IRC connection, DB connection, set variables, whatever)
#we need teh sxwiki to alert me.
require_once('../../database.inc');
mysql_connect("sql",$toolserver_username,$toolserver_password);
@mysql_select_db("u_sql") or print mysql_error();
set_time_limit (0);
set_time_limit(0);
$host = "irc.freenode.org";
$port=6667;
$nick="SQLBot2";
$ident="SQLBot2";
$chan="#wikipedia-en-accounts";
$readbuffer="";
$realname = "SQLBot2";

$fp = fsockopen($host, $port, $erno, $errstr, 30);
if (!$fp) {
    #echo $errstr." (".$errno.")<br />\n";
}
    fwrite($fp, "NICK ".$nick."\r\n");
    fwrite($fp, "USER ".$ident." ".$host." bla :".$realname."\r\n");
    sleep(1);
    fwrite($fp, "JOIN :".$chan."\r\n");
    echo "Joined $chan\n";
$fpt = stream_socket_server("udp://0.0.0.0:9001", $errno, $errstr, STREAM_SERVER_BIND);

while (!feof($fp)) {
	#Here's where the meat of your daemon goes.
	stream_set_blocking($fp, 0);
	stream_set_blocking($fpt, 0);

        $line =  fgets($fp, 256);

        usleep(25000);
        $peer = fread($fpt, 256);
	if($peer != "") {
	        $toirc = "PRIVMSG $chan :$peer";
		fwrite($fp, "$toirc\r\n");
		echo "Packet received!\n";
	}
	if(stristr($line, "!count") != FALSE) {
		sleep(.75); 
		$cmatch = preg_match("/\:.* PRIVMSG #wikipedia-en-accounts :!count (.*)/", $line, $matches);
		if($cmatch > 0) {
			$matches[1] = ltrim(rtrim($matches[1]));
			$query = "SELECT COUNT(*) FROM acc_log WHERE log_action = 'Closed 1' AND log_user = '$matches[1]';";
			$result = mysql_query($query);
			if(!$result) Die("ERROR: No result returned.");
			$row = mysql_fetch_assoc($result);
			$howmany = $row['COUNT(*)'];
			$query = "SELECT COUNT(*) FROM acc_user WHERE user_name = '$matches[1]';";
			$result = mysql_query($query);
			if(!$result) Die("ERROR: No result returned.");
			$row = mysql_fetch_assoc($result);
			$userexist = $row['COUNT(*)'];
			$abit = "";
			if($userexist == "1") {
				$query = "SELECT * FROM acc_user WHERE user_name = '$matches[1]';";
				$result = mysql_query($query);
				if(!$result) Die("ERROR: No result returned.");
				$row = mysql_fetch_assoc($result);
				$level = $row['user_level'];
				if($level == "Admin") {
					$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$matches[1]' AND log_action = 'Suspended';";
	                                $result = mysql_query($query);
        	                        if(!$result) Die("ERROR: No result returned.");
                	                $row = mysql_fetch_assoc($result);
					$suspended = $row['COUNT(*)'];

					$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$matches[1]' AND log_action = 'Promoted';";
	                                $result = mysql_query($query);
        	                        if(!$result) Die("ERROR: No result returned.");
                	                $row = mysql_fetch_assoc($result);
					$promoted = $row['COUNT(*)'];


					$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$matches[1]' AND log_action = 'Approved';";
	                                $result = mysql_query($query);
        	                        if(!$result) Die("ERROR: No result returned.");
                	                $row = mysql_fetch_assoc($result);
					$approved = $row['COUNT(*)'];
					
					$abit = "Suspended: $suspended, Promoted: $promoted, Approved: $approved";
				}
			}
			$now = date("Y-m-d");
			$topq = "select log_user,count(*) from acc_log where log_time like '$now%' and log_action = 'Closed 1' and log_user = '$matches[1]' group by log_user ORDER BY count(*) DESC limit 5;";
			$result = mysql_query($topq);
			if(!$result) Die("ERROR: No result returned.6");
			$top = mysql_fetch_assoc($result);
			$ttop = $top['count(*)'];
			if($ttop == "") {
				$ttop = "none";
			}
			if($userexist != "1") {
				fwrite($fp, "PRIVMSG $chan :$matches[1] is not a valid user.\r\n");
			} else {
				fwrite($fp, "PRIVMSG $chan :$matches[1] ($level) has closed $howmany requests as 'Done', $ttop of them today. $abit\r\n");
			}

		}
	}
	if(stristr($line, "!status") != FALSE) {
		sleep(.75); 
		$query = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Open';";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$pending = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Admin';";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$admin = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_ban;";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$bans = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'Admin';";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$sadmins = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'User';";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$users = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'New';";
		$result = mysql_query($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$padmins = $row['COUNT(*)'];
	        $toirc = "PRIVMSG $chan :Open requests: $pending, Admin requests: $admin, Banned: $bans, Site users: $users, Site admins: $sadmins, Awaiting approval: $padmins";
		fwrite($fp, "$toirc\r\n");
	}
	if(stristr($line, "ping") != FALSE) { //quiet trigger
		echo "PRIVMSG ".$chan." :$line\n";
	        fwrite($fp, "PONG ".$line[1]."\r\n"); 
		sleep(.50);
	}
	if(stristr($line, "!die") != FALSE) { 
		$out = "PRIVMSG ".$chan." :Ok, dying!\n";
                fwrite($fp, "$out\r\n");
		sleep(1);
		socket_close($client);
		socket_close($sock);
		die("Killed via IRC\n");
	}
	$line_ex = explode(' ',str_replace(array("\r","\n"),'',$line));
	if (substr(strtolower($line_ex[3]),1) == '!svnup') {
		$nick = explode('!',$line_ex[0]);
		$nick = substr($nick[0],1);


		if (($nick == 'Cobi') or ($nick == 'SQLDb') or ($nick == '|Cobi|') or ($nick == 'Cobi-Laptop')) {
//			if (pcntl_fork() == 0) {
				$svn = popen('svn up 2>&1', 'r');
				while (!feof($svn)) {
					$svnin = ltrim(rtrim(fgets($svn,512)));
					if ($svnin != "") {
						fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': '.str_replace(array("\n","\r"),'',$svnin)."\n");
					}
					sleep(.75); //Slight delay so the bot does not kill itself on updating a lot of files.
				}
				pclose($svn);
//				die();
//			}
		}
	}
	if (substr(strtolower($line_ex[3]),1) == '!restart') {
		echo 'Restart from IRC!';
		fclose($fp);
		fclose($fpt);
		pcntl_exec('/usr/bin/php',$argv,$_ENV);
	}
}
 
#Clean up your connections, finish file writes here, or whatever you want to do as the daemon shuts down.

fclose($fp); //Goodnight!
fclose($fpt);
?>
