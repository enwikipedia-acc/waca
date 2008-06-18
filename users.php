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
**                                                           **
**************************************************************/
require_once('config.inc.php');
include('devlist.php');
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
    echo "</div>";
        echo $row[mail_text];
}
function showfooter() {
    echo "</body></html>\n";
}
function sanitize($what) {
        $what = mysql_real_escape_string($what);
        return($what);
}

mysql_connect($toolserver_host,$toolserver_username,$toolserver_password);
@mysql_select_db($toolserver_database) or print mysql_error();
if ($_GET[viewuser] != "") {
    displayheader();
    $gid = sanitize($_GET[viewuser]);
    $query = "SELECT * FROM acc_user WHERE user_id = $_GET[viewuser] AND user_level != 'Declined' AND user_level != 'New' ;";
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $row = mysql_fetch_assoc($result);
    if($row[user_id] == "") {
        echo "Invalid user!<br />\n";
        showfooter();
        die();
    }
    echo "<h2>Detail report for user: $row[user_name]</h2>\n";
    echo "<ol>\n";
    echo "<li>User ID: $row[user_id]</li>\n";
    echo "<li>User Level: $row[user_level]</li>\n";
    echo "<li>User On-wiki name: <a href=\"http://en.wikipedia.org/wiki/User:$row[user_onwikiname]\">$row[user_onwikiname]</a>  |  <a href=\"http://en.wikipedia.org/wiki/User talk:$row[user_onwikiname]\">talk page</a> </li>\n";
    $query = 'SELECT `user_lastactive` AS `time` FROM `acc_user` WHERE `user_id` = \''.mysql_real_escape_string($_GET['viewuser']).'\' LIMIT 1;';
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $lastactive = mysql_fetch_assoc($result);
    $lastactive = $lastactive['time'];
    if($lastactive == "0000-00-00 00:00:00") {
        echo "<li>User has never used the interface</li>\n";
    } else {
        echo "<li>User last active: $lastactive</li>\n";
    }
    $query = 'SELECT * FROM `acc_user` WHERE `user_id` = \''.mysql_real_escape_string($_GET['viewuser']).'\' LIMIT 1;';
    $result = mysql_query($query);
    if(!$result) Die("ERROR: No result returned.");
    $welcomee = mysql_fetch_assoc($result);
    $welcomee = $welcomee['user_welcome'];
    if ($welcomee == "1") {
        $welcomee = "Yes";
    }
    else {
        $welcomee = "No";
    }
    echo "<li>User has <a href=\"acc.php?action=welcomeperf\"><span style=\"color: red;\" title=\"Login required to continue\">automatic welcoming</span></a> enabled: $welcomee.</li>\n";
    echo "</ol>\n";
    echo "<h2>Users created</h2>\n";
        $query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = '$gid' AND log_action = 'Closed 1';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
        while($row = mysql_fetch_assoc($result)) {
        if($row[log_time] == "0000-00-00 00:00:00") { $row[log_time] = "Date unknown"; }
        echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:$row[pend_name]\">$row[pend_name]</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:$row[pend_name]\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/$row[pend_name]\">contribs</a> - <a href=\"http://toolserver.org/~sql/acc/acc.php?action=zoom&id=$row[pend_id]\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at $row[log_time]</li>\n";
        // Not every row $noc = count($row[pend_name]); //Define total number of users created
    // Not every row echo "<b>Number of users created: $noc</b>\n"; //Display total number of users created
    }
    echo "</ol>\n";
    echo "<h2>Users not created</h2>\n";
        $query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = '$gid' AND log_action != 'Closed 1';";
        $result = mysql_query($query);
        if(!$result) Die("ERROR: No result returned.");
    echo "<ol>\n";
        while($row = mysql_fetch_assoc($result)) {
        if($row[log_time] == "0000-00-00 00:00:00") { $row[log_time] = "Date unknown"; }
        echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:$row[pend_name]\">$row[pend_name]</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:$row[pend_name]\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/$row[pend_name]\">contribs</a> - <a href=\"http://toolserver.org/~sql/acc/acc.php?action=zoom&id=$row[pend_id]\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at $row[log_time]</li>\n";
    }
    echo "<br /><a href=\"users.php\">User list</a><br /><a href=\"acc.php\"><span style=\"color: red;\" title=\"Login required to continue\">Return to request management interface</span></a>\n";
    echo "</ol>\n";
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
        echo "<li><a href=\"users.php?viewuser=$row[user_id]\">$row[user_name]</a>";
        if (in_array($regdevlist, $row[user_name])) {
        	echo " (Developer)";
        }
        echo "</li>\n";
    }
    $lastlevel = $row[user_level];
}
echo "<ul>\n";
echo "<br /><a href=\"users.php\">User list</a><br /><a href=\"acc.php\"><span style=\"color: red;\" title=\"Login required to continue\">Return to request management interface</span></a>\n";
displayfooter();
?>
