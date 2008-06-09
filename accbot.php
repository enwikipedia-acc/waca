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
**                                                           **
**************************************************************/
if( $_SERVER['REMOTE_ADDR'] != "") { 
	header("Location: http://toolserver.org/~sql/acc/");
	die(); 
}

function sanitize($what) {
	$what = mysql_real_escape_string($what);
	return($what);
}

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
pcntl_signal(SIGCHLD, "SIGCHLD");
 
function SIGHUP() {
	#Do what you want it to do upon receiving a SIGHUP
}
 
function SIGTERM() {
	#Do what you want it to do upon receiving a SIGTERM. In this case, die.
	fclose($fp); //Goodnight!
	die("Received SIGTERM\n");
}

function SIGCHLD() {
	while (pcntl_waitpid(0, $status) != -1) {
		$status = pcntl_wexitstatus($status);
	}
}

#Initialize your daemon here (i.e. make the IRC connection, DB connection, set variables, whatever)
#we need teh sxwiki to alert me.
require_once('config.inc');
function myq($query) {
	global $mysql, $toolserver_username, $toolserver_password, $toolserver_host, $toolserver_database;
	if (!mysql_ping()) {
		mysql_connect($toolserver_host,$toolserver_username,$toolserver_password,true);
		@mysql_select_db($toolserver_database) or print mysql_error();
	}

	return mysql_query($query);
}
set_time_limit (0);
set_time_limit(0);
$host = "irc.freenode.org";
$port=6667;
$nick="ACCBot";
$ident="ACCBot";
$chan="#wikipedia-en-accounts";
$readbuffer="";
$realname = "ACC Bot";

$fp = fsockopen($host, $port, $erno, $errstr, 30);
if (!$fp) {
    echo $errstr." (".$errno.")<br />\n";
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
        $peer = fread($fpt, 4096);
	if($peer != "") {
	        $toirc = "PRIVMSG $chan :".str_replace("\n","\nPRIVMSG ".$chan.' :',$peer);
		fwrite($fp, "$toirc\r\n");
		echo "Packet received!\n";
	}

	#BEGIN HELP
	if(stristr($line, "!help") !== FALSE) { //This will display help if succesful in a NOTICE
		if (pcntl_fork() == 0) {
			$line_ex = explode(' ',str_replace(array("\r","\n"),'',$line));
			$nick = explode('!',$line_ex[0]);
			$nick = substr($nick[0],1);
			sleep(.75);
			fwrite($fp,"NOTICE $nick :Available commands (all should be run in #wikipedia-en-accounts):\r\n");
			sleep(.75);
			fwrite($fp,"NOTICE $nick :!count <username> - Displays statistics for the targeted user\r\n");
			sleep(3);
			fwrite($fp,"NOTICE $nick :!status - Displays interface statistics, such as number of open requests\r\n");
			sleep(3);
			fwrite($fp,"NOTICE $nick :!svninfo - Floods you with information about the SVN repository\r\n");
			sleep(3);
			fwrite($fp,"NOTICE $nick :!stats <username> - Gives a readout similar to a user list user information page\r\n");
			sleep(3);
			fwrite($fp,"NOTICE $nick :!svnup - RESTRICTED - Allows those with access to synch the SVN repository with the live server copy\r\n");
			sleep(3);
			fwrite($fp,"NOTICE $nick :!restart - RESTRICTED - Allows those with access to restart the bot immediately\r\n");
			sleep(3);
			fwrite($fp,"NOTICE $nick :!recreatesvn - RESTRICTED - Commands the bot to attempt and recreate/repair the SVN repository\r\n");
			die();
		}
	}
	#END HELP

	if(stristr($line, "!count") != FALSE) {
		sleep(.75); 
		$cmatch = preg_match("/\:.* PRIVMSG #wikipedia-en-accounts :!count (.*)/", $line, $matches);
		if($cmatch > 0) {
			$matches[1] = sanitize(ltrim(rtrim($matches[1])));
			$query = "SELECT COUNT(*) FROM acc_log WHERE log_action = 'Closed 1' AND log_user = '$matches[1]';";
			$result = myq($query);
			if(!$result) Die("ERROR: No result returned.");
			$row = mysql_fetch_assoc($result);
			$howmany = $row['COUNT(*)'];
			$query = "SELECT COUNT(*) FROM acc_user WHERE user_name = '$matches[1]';";
			$result = myq($query);
			if(!$result) Die("ERROR: No result returned.");
			$row = mysql_fetch_assoc($result);
			$userexist = $row['COUNT(*)'];
			$abit = "";
			if($userexist == "1") {
				$query = "SELECT * FROM acc_user WHERE user_name = '$matches[1]';";
				$result = myq($query);
				if(!$result) Die("ERROR: No result returned.");
				$row = mysql_fetch_assoc($result);
				$level = $row['user_level'];
				if($level == "Admin") {
					$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$matches[1]' AND log_action = 'Suspended';";
	                                $result = myq($query);
        	                        if(!$result) Die("ERROR: No result returned.");
                	                $row = mysql_fetch_assoc($result);
					$suspended = $row['COUNT(*)'];

					$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$matches[1]' AND log_action = 'Promoted';";
	                                $result = myq($query);
        	                        if(!$result) Die("ERROR: No result returned.");
                	                $row = mysql_fetch_assoc($result);
					$promoted = $row['COUNT(*)'];


					$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$matches[1]' AND log_action = 'Approved';";
	                                $result = myq($query);
        	                        if(!$result) Die("ERROR: No result returned.");
                	                $row = mysql_fetch_assoc($result);
					$approved = $row['COUNT(*)'];
					
					$abit = "Suspended: $suspended, Promoted: $promoted, Approved: $approved";
				}
			}
			$now = date("Y-m-d");
			$topq = "select log_user,count(*) from acc_log where log_time like '$now%' and log_action = 'Closed 1' and log_user = '$matches[1]' group by log_user ORDER BY count(*) DESC limit 5;";
			$result = myq($topq);
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
	if(stristr($line, "!stats") != FALSE) {
		sleep(.75); 
		$cmatch = preg_match("/\:.* PRIVMSG #wikipedia-en-accounts :!stats (.*)/", $line, $matches);
		if($cmatch > 0) {
			$matches[1] = sanitize(ltrim(rtrim($matches[1])));
			$query = "SELECT COUNT(*) FROM acc_user WHERE user_name = '$matches[1]';";
			$result = myq($query);
			if(!$result) Die("ERROR: No result returned.");
			$row = mysql_fetch_assoc($result);
			$userexist = $row['COUNT(*)'];
			if($userexist == "1") {
				$query = "SELECT * FROM acc_user WHERE user_name = '$matches[1]';";
				$result = myq($query);
				if(!$result) Die("ERROR: No result returned.");
				$row = mysql_fetch_assoc($result);
				$level = $row['user_level'];
				$onwiki = "[[User:$row[user_onwikiname]]]";
				$welcomee = $row['user_welcome'];
				$lastactive = $row['user_lastactive'];
			}
			if ($welcomee == "1") {
				$welcomee = "enabled";
			}
			else {
				$welcomee = "disabled";
			}
			if($lastactive == "0000-00-00 00:00:00") { 
				$lastactive = "unknown"; 
			}
			if($userexist != "1") {
				fwrite($fp, "PRIVMSG $chan :$matches[1] is not a valid user.\r\n");
			} else {
				fwrite($fp, "PRIVMSG $chan :$matches[1] ($level) was last active $lastactive. He/she currently has automatic welcoming of users $welcomee. His/her onwiki username is $onwiki. \r\n");
			}

		}
	}
	if(stristr($line, "!status") != FALSE) {
		sleep(.75); 
		$query = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Open';";
		$result = myq($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$pending = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Admin';";
		$result = myq($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$admin = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_ban;";
		$result = myq($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$bans = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'Admin';";
		$result = myq($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$sadmins = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'User';";
		$result = myq($query);
		if(!$result) Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		$users = $row['COUNT(*)'];
		$query = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'New';";
		$result = myq($query);
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
	$line_ex = explode(' ',str_replace(array("\r","\n"),'',$line));
	if ((substr(strtolower($line_ex[3]),1) == '!svnup') and (strtolower($line_ex[2]) == '#wikipedia-en-accounts')) {
		$nick = explode('!',$line_ex[0]);
		$nick = substr($nick[0],1);
		$hostA = explode('@',$line_ex[0]);
		$host = $hostA[1];
		if (($nick == 'Cobi') && (strtolower($host) == 'cobi.cluenet.org') or ($nick == 'SQLDb') && ($host == 'wikipedia/SQL') or ($nick == '|Cobi|') or ($nick == 'Cobi-Laptop')) {
//			fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.": /whois $nick\n"); // What the ... !?
			if (pcntl_fork() == 0) {
				$svn = popen('svn up 2>&1', 'r');
				while (!feof($svn)) {
					$svnin = ltrim(rtrim(fgets($svn,512)));
					if ($svnin != "") {
						fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': '.str_replace(array("\n","\r"),'',$svnin)."\n");
					}
					sleep(.75); //Slight delay so the bot does not kill itself on updating a lot of files.
				}
				pclose($svn);
				die();
			}
		}
	}
	if ((substr(strtolower($line_ex[3]),1) == '!sand-svnup') and (strtolower($line_ex[2]) == '#wikipedia-en-accounts')) {
		$nick = explode('!',$line_ex[0]);
		$nick = substr($nick[0],1);
		$hostA = explode('@',$line_ex[0]);
		$host = $hostA[1];
		if (($nick == 'Cobi') && (strtolower($host) == 'cobi.cluenet.org') or ($nick == 'SQLDb') && ($host == 'wikipedia/SQL') or ($nick == '|Cobi|') or ($nick == 'Cobi-Laptop') or ($nick == 'FastLizard4') && ($host == 'wikipedia/FastLizard4') or ($nick == 'Soxred93') && ($host == 'unaffiliated/soxred93') or ($nick == 'Alexfusco5') && ($host =='wikimedia/Alexfusco5')) {
//			fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.": /whois $nick\n"); // What the ... !?
			if (pcntl_fork() == 0) {
				$svn = popen('sh svn-sand.sh 2>&1', 'r');
				while (!feof($svn)) {
					$svnin = ltrim(rtrim(fgets($svn,512)));
					if ($svnin != "") {
						fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': '.str_replace(array("\n","\r"),'',$svnin)."\n");
					}
					sleep(.75); //Slight delay so the bot does not kill itself on updating a lot of files.
				}
				fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': '."Please see the sandbox at http://toolserver.org/~sql/acc_sand/acc.php\n");

				pclose($svn);
				die();
			}
		}
	}

	if ((substr(strtolower($line_ex[3]),1) == '!svninfo') and (strtolower($line_ex[2]) == '#wikipedia-en-accounts')) {
		if (pcntl_fork() == 0) {
			$nick = explode('!',$line_ex[0]);
			$nick = substr($nick[0],1);
			$svn = popen('svn info 2>&1', 'r');
			while (!feof($svn)) {
				$svnin = ltrim(rtrim(fgets($svn,512)));
				if ($svnin != "") {
					fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': '.str_replace(array("\n","\r"),'',$svnin)."\n");
				}
				sleep(3);
			}
			pclose($svn);
			die();
		}
	}

	if ((substr(strtolower($line_ex[3]),1) == '!restart') and (strtolower($line_ex[2]) == '#wikipedia-en-accounts')) {
		$hostA = explode('@',$line_ex[0]);
		$host = $hostA[1];
                $nick = explode('!',$line_ex[0]);
                $nick = substr($nick[0],1);


                if (($nick == 'Cobi') && (strtolower($host) == 'cobi.cluenet.org') or ($nick == 'SQLDb') && ($host == 'wikipedia/SQL') or ($nick == '|Cobi|') or ($nick == 'Cobi-Laptop')) {
			echo 'Restart from IRC!';
			fclose($fp);
			fclose($fpt);
			pcntl_exec('/usr/bin/php',$argv,$_ENV);
		}
	}

	if ((substr(strtolower($line_ex[3]),1) == '!recreatesvn') and (strtolower($line_ex[2]) == '#wikipedia-en-accounts')) {
		$nick = explode('!',$line_ex[0]);
		$nick = substr($nick[0],1);

		 if (($nick == 'Cobi') && (strtolower($host) == 'cobi.cluenet.org') or ($nick == 'SQLDb') && ($host == 'wikipedia/SQL') or ($nick == '|Cobi|') or ($nick == 'Cobi-Laptop')) {
			fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': Please wait while I try to fix the SVN.'."\n");
			system('tar -jcvpf ~/accinterface-svn-broken.'.time().'.tbz2 .');
			system('svn list | xargs rm -f');
			system('svn up');
			fwrite($fp,'PRIVMSG '.$chan.' :'.$nick.': Thanks.  SVN has hopefully been fixed.'."\n");
		}
	}
}
 
#Clean up your connections, finish file writes here, or whatever you want to do as the daemon shuts down.

fclose($fp); //Goodnight!
fclose($fpt);
?>
