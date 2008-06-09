<?
require_once('config.inc.php');
ini_set('session.cookie_path', $cookiepath);
ini_set('session.name', $sessionname);
session_start();
        mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
        @mysql_select_db($toolserver_database) or print mysql_error();
function sendtobot($message) {
    sleep(3);
    $fp = fsockopen("udp://127.0.0.1", 9001, $erno, $errstr, 30);
    fwrite($fp, "$message\r\n");
    fclose($fp);
}

function killbot() {
	$findbot = "/sql      ([0-9]{3,5})  [0-9]\.[0-9].*php .*accbot\.php/i";
	$output = shell_exec("ps aux | grep accbot.php");
	$output = explode("\n", $output);
	foreach ($output as $line) {
		$line = ltrim(rtrim($line));
		$match = preg_match($findbot, $line, $matches);
		#echo "$match\n";
		if($matches[1] != "") {
			exec("kill -9 $matches[1]");
		}
	}
}

function showmessage($messageno) {
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
        return($row[mail_text]);
}
function sanitize($what) {
    $what = mysql_real_escape_string($what);
    return($what);
}
function svnup ( ) { //Blatantly stolen from accbot.php
	$svn = popen( 'svn up 2>&1', 'r' );
	while( !feof( $svn ) ) {
		$svnin = trim( fgets( $svn, 512 ) );
		if( $svnin != '' ) {
			echo str_replace( array( "\n", "\r" ), '<br />', $svnin );
			echo "<br />";
		}
	}
	pclose( $svn );
}
function svnup_sand ( ) { //Blatantly stolen from accbot.php
	$svn = popen( 'sh svn-sand.sh 2>&1', 'r' );
	while( !feof( $svn ) ) {
		$svnin = trim( fgets( $svn, 512 ) );
		if( $svnin != '' ) {
			echo str_replace( array( "\n", "\r" ), '<br />', $svnin );
			echo "<br />";
		}
	}
	pclose( $svn );
}

    $suin = sanitize($_SESSION[user]);
    $query = "SELECT * FROM acc_user WHERE user_name = '$suin' LIMIT 1;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    $_SESSION[user_id] = $row[user_id];
    $out = showmessage('21');
    echo $out;
    if(isset($_SESSION[user])) { //Is user logged in?
        echo "<div id = \"header-info\">Logged in as <a href=\"users.php?viewuser=$_SESSION[user_id]\"><span title=\"View your user information\">$_SESSION[user]</span></a>.  <a href=\"acc.php?action=logout\">Logout</a>?</div>\n";
        //Update user_lastactive
        $now = date("Y-m-d H-i-s");
        $query = "UPDATE acc_user SET user_lastactive = '$now' WHERE user_id = '$_SESSION[user_id]';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
    } else { 
        echo "<div id = \"header-info\">Not logged in.  <a href=\"acc.php\"><span title=\"Click here to return to the login form\">Log in</span></a>/<a href=\"acc.php?action=register\">Create account</a>?</div>\n"; 
    }
echo "<div id=\"content\">\n";
if($_SESSION[user] != "Cobi" && $_SESSION[user] != "SQL") {
	echo "<br />Access Denied<br />\n";
	die();
}
echo "<br />Access Granted<br />\n";
if(isset($_GET[svnup])) {
	svnup();
	sendtobot("[WEB]: $_SESSION[user] synchronizing ACC installation");
}
if(isset($_GET[sandup])) {
	svnup_sand();
	sendtobot("[WEB]: $_SESSION[user] synchronizing ACC sandbox");
}
if(isset($_GET[startbot])) {
	echo "Starting bot...<br />\n";
	$outp = system('./startbot.sh');
	if($outp == FALSE) {
		echo "Failed!<br />\n";
		echo "$outp<br />\n";
	} else {
		echo "Bot started!<br />\n";
	}
}
if(isset($_GET[stopbot])) {
	sendtobot("[WEB]: $_SESSION[user] Ordered me to die!");
	sleep(1);
	killbot();
	echo "Bot Killed.<br />";
}
echo "<ul>\n<li><a href=\"update.php?svnup\">SVN Sync (main code)</a></li>\n<li><a href=\"update.php?sandup\">SVN Sync (Sandbox)</a></li>\n<li><a href=\"update.php?startbot\">Start bot</a></li>\n<li><a href=\"update.php?stopbot\">Stop bot</a></li>\n</ul>\n";


?>

