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

function displayheader() {
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
function showfooter() {
	echo "</body></html>\n";
}
function sanitize($what) {
        $what = mysql_real_escape_string($what);
        return($what);
}

require_once('../../database.inc');
mysql_connect("sql",$toolserver_username,$toolserver_password);
@mysql_select_db("u_sql") or print mysql_error();
if ($_GET[viewuser] != "") {
	displayheader();
	$gid = sanitize($_GET[viewuser]);
	$query = "SELECT * FROM acc_user WHERE user_id = $_GET[viewuser] AND user_level != 'Suspended' AND user_level != 'Declined' AND user_level != 'New' ;";
	$result = mysql_query($query);
	if(!$result) Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	echo "<h2>Detail report for user: $row[user_name]</h2>\n";
	echo "<ol>\n";
	echo "<li>User ID: $row[user_id]</li>\n";
	echo "<li>User Level: $row[user_level]</li>\n";
        echo "<li>User On-wiki name: <a href=\"http://en.wikipedia.org/wiki/User:$row[user_onwikiname]\">$row[user_onwikiname]</a>  |  <a href=\"http://en.wikipedia.org/wiki/User talk:$row[user_onwikiname]\">talk page</a> </li>\n";
	echo "</ol>\n";
	echo "<h2>Users created</h2>\n";
        $query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = '$gid' AND log_action = 'Closed 1';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
	echo "<ol>\n";
        while($row = mysql_fetch_assoc($result)) {
		if($row[log_time] == "0000-00-00 00:00:00") { $row[log_time] = "Date unknown"; }
		echo "<li><a href=\"http://en.wikipedia.org/wiki/User:$row[pend_name]\">$row[pend_name]</a> <a href=\"http://en.wikipedia.org/wiki/User_talk:$row[pend_name]\">(talk)</a> at $row[log_time]</li>\n";
	}
	displayfooter();
	die();
}
displayheader();
$query = "SELECT * FROM acc_user ORDER BY user_level";
$result = mysql_query($query);
if(!$result) Die("ERROR: No result returned.");
echo "<h2>User List</h2>\n<ul>\n";
while ($row = mysql_fetch_assoc($result)) {
	if($row[user_level] != $lastlevel && $row[user_level] != "Suspended" && $row[user_level] != "Declined") { echo "<h3>$row[user_level]</h3>\n"; }
	if($row[user_level] == "Suspended") { $row[user_name] = ""; }
	if($row[user_level] == "Declined") { $row[user_name] = ""; }
	if($row[user_level] == "New") { $row[user_name] = ""; }
	if($row[user_name] != "") {
		echo "<li><a href=\"users.php?viewuser=$row[user_id]\">$row[user_name]</a></li>\n";
	}
	$lastlevel = $row[user_level];
}
echo "<ul>\n";
displayfooter();
?>
