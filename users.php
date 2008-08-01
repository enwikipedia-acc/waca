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
**Prodego    ( http://en.wikipedia.org/wiki/User:Prodego )   **
**                                                           **
**************************************************************/
require_once ('config.inc.php');
require_once ('devlist.php');
require_once ('functions.php');


$link = mysql_connect( $toolserver_host, $toolserver_username, $toolserver_password );
if ( !$link ) {
	die( 'Could not connect: ' . mysql_error( ) );
}
@ mysql_select_db( $toolserver_database ) or print mysql_error( );
session_start( );

if ($_GET['edituser'] != "" && $enableRenames == 1) {
	displayheader();
	$sid = sanitize($_SESSION['user']);
	if (!hasright($_SESSION['user'], "Admin"))
		Die("You are not authorized to edit account data $sid test");
	if ($_POST['user_email'] == "" || $_POST['user_onwikiname'] == "") {
		$gid = sanitize($_GET['edituser']);
		$query = "SELECT * FROM acc_user WHERE user_id = $gid AND user_level != 'New' ;";
		$result = mysql_query($query);
		if (!$result)
			Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		if ($row['user_id'] == "") {
			echo "Invalid user!";
			die();
		}
		echo "<h2>User Settings for {$row['user_name']}</h2>\n";
		echo "<ul>\n";
		echo "<li>User Name: " . $row['user_name'] . "</li>\n";
		echo "<li>User ID: " . $row['user_id'] . "</li>\n";
		echo "<li>User Level: " . $row['user_level'] . "</li>\n";
		echo "</ul>\n";
		echo "<form action=\"users.php?edituser=" . $_GET['edituser'] . "\" method=\"post\">\n";
		echo "<div class=\"required\">\n";
		echo "<label for=\"user_email\">Email Address:</label>\n";
		echo "<input id=\"user_email\" type=\"text\" name=\"user_email\" value=\"" . stripslashes($row['user_email']) . "\"/>\n";
		echo "</div>\n";
		echo "<div class=\"required\">\n";
		echo "<label for=\"user_onwikiname\">On-wiki Username:</label>\n";
		echo "<input id=\"user_onwikiname\" type=\"text\" name=\"user_onwikiname\" value=\"" . stripslashes($row['user_onwikiname']) . "\"/>\n";
		echo "</div>\n";
		echo "<div class=\"submit\">\n";
		echo "<input type=\"submit\"/>\n";
		echo "</div>\n";
		echo "</form>\n";	
		echo "<br />\n <b>Note: misuse of this interface can cause problems, please use it wisely</b>";
	} else {
		$gid = sanitize($_GET['edituser']);
		$newemail = sanitize($_POST['user_email']);
		$newwikiname = sanitize($_POST['user_onwikiname']);
		$query = "UPDATE acc_user SET user_email = '$newemail', user_onwikiname = '$newwikiname' WHERE user_id = '$gid';";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());	
		$now = date("Y-m-d H-i-s");			
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$gid', '$sid', 'Prefchange', '$now', '');";
		$result = mysql_query($query);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changes saved";
	}
	echo "<br /><br />";
	displayfooter();
	die();

} else if ($_GET['viewuser'] != "") {
	displayheader();
	$gid = sanitize($_GET['viewuser']);
	$query = "SELECT * FROM acc_user WHERE user_id = $gid AND user_level != 'Declined' AND user_level != 'New' ;";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$row = mysql_fetch_assoc($result);
	if ($row['user_id'] == "") {
		echo "Invalid user!<br />\n";
		displayfooter();
		die();
	}
	$username = $row['user_name'];
	echo "<h2>Detail report for user: $username</h2>\n";
	echo "<ul>\n";
	echo "<li>User ID: " . $row['user_id'] . "</li>\n";
	echo "<li>User Level: " . $row['user_level'] . "</li>\n";
	echo "<li>User On-wiki name: <a href=\"http://en.wikipedia.org/wiki/User:" . $row['user_onwikiname'] . "\">" . $row['user_onwikiname'] . "</a>  |  <a href=\"http://en.wikipedia.org/wiki/User talk:" . $row['user_onwikiname'] . "\">talk page</a> </li>\n";
	$query = 'SELECT `user_lastactive` AS `time` FROM `acc_user` WHERE `user_id` = \'' . mysql_real_escape_string($_GET['viewuser']) . '\' LIMIT 1;';
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$lastactive = mysql_fetch_assoc($result);
	$lastactive = $lastactive['time'];
	if ($lastactive == "0000-00-00 00:00:00") {
		echo "<li>User has never used the interface</li>\n";
	} else {
		echo "<li>User last active: $lastactive</li>\n";
	}
	$query = 'SELECT * FROM `acc_user` WHERE `user_id` = \'' . mysql_real_escape_string($_GET['viewuser']) . '\' LIMIT 1;';
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	$welcomee = mysql_fetch_assoc($result);
	$welcomee = $welcomee['user_welcome'];
	if ($welcomee == "1") {
		$welcomee = "Yes";
	} else {
		$welcomee = "No";
	}
	echo "<li>User has <a href=\"acc.php?action=welcomeperf\"><span style=\"color: red;\" title=\"Login required to continue\">automatic welcoming</span></a> enabled: $welcomee.</li>\n";
	echo "</ul>\n";
	echo "<h2>Users created</h2>\n";
	$query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = '$gid' AND log_action = 'Closed 1';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	if (mysql_num_rows($result) != 0) {
		echo "<ol>\n";
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['log_time'] == "0000-00-00 00:00:00") {
				$row['log_time'] = "Date unknown";
			}
			$pn = $row['pend_name'];
			echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:$pn\">$pn</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:$pn\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/$pn\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at " . $row['log_time'] . "</li>\n";
			// Not every row $noc = count($row[pend_name]); //Define total number of users created
			// Not every row echo "<b>Number of users created: $noc</b>\n"; //Display total number of users created
		}
		echo "</ol>\n";
	}
	echo "<h2>Users not created</h2>\n";
	$query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = '$gid' AND log_action != 'Closed 1';";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	if (mysql_num_rows($result) != 0) {	
		echo "<ol>\n";
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['log_time'] == "0000-00-00 00:00:00") {
				$row['log_time'] = "Date unknown";
			}
			$pn = $row['pend_name'];
			echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:$pn\">$pn</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:$pn\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/$pn\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at " . $row['log_time'] . "</li>\n";
		}
		echo "</ol>\n";
	}
	echo "<h2>Account log</h2>\n";
	$query = "SELECT * FROM acc_log where log_pend = '$gid' AND log_action RLIKE '(Approved|Suspended|Declined|Promoted|Demoted|Renamed|fchange)';";
	echo "\n\n<!-- RQ = $query -->\n\n";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	if (mysql_num_rows($result) != 0) {	
		echo "<ol>\n";
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['log_time'] == "0000-00-00 00:00:00") {
				$row['log_time'] = "Date unknown";
			}
			$pu = $row['log_user'];
			$pt = $row['log_time'];
			$pa = $row['log_action'];
			$lp = $row['log_pend'];
			$lc = $row['log_cmt'];
			$pu_s = mysql_real_escape_string($pu);
			$comments = "";
			if( $row['log_cmt'] != "" ) {
				$pc = $row['log_cmt'];
				$comments = " ($pc)";
			}
			if( $approved == 1 && $pa == "Approved" ) { $pa = "Demoted"; }
			$uid_query = "SELECT user_id FROM acc_user WHERE user_name = '$pu_s';";
			$uid_result = mysql_query($uid_query);
			if (!$uid_result)
				Die("ERROR: No result returned.");
			$uid_r = mysql_fetch_assoc($uid_result);
			$userid = $uid_r['user_id'];
			if ($pa == "Prefchange") {
			echo "<li><a href=\"users.php?viewuser=$userid\">$pu</a> changed user preferences for $username at $pt</li>\n";
			}
			else if ($pa == "Renamed") {
				echo "<li><a href=\"users.php?viewuser=$userid\">$pu</a> <strong>$pa</strong> user $lc at $pt.</li>\n";	
			} 
			else {
				echo "<li><a href=\"users.php?viewuser=$userid\">$pu</a> <strong>$pa</strong> $username at $pt$comments</li>\n";
				if( $pa == "Approved" ) { $approved = 1; }
				$pc = "";
				$row['log_cmt'] = "";
			}
		}
		echo "</ol>\n";
	}
	displayfooter();
	die();
} else {
	displayheader();
	$query = "SELECT * FROM acc_user ORDER BY user_level";
	$result = mysql_query($query);
	if (!$result)
		Die("ERROR: No result returned.");
	echo "<h2>User List</h2>\n";
	echo "<i>Developers are bolded</i>\n";
	$lastlevel == NULL;
	while ($row = mysql_fetch_assoc($result)) {
		if ($row['user_level'] != $lastlevel && $row['user_level'] != "Suspended" && $row['user_level'] != "Declined" && $row['user_level'] != "New") {
			if ($lastlevel == NULL) {
				echo "\n<h3>" . $row['user_level'] . "</h3>\n<ul>\n";
			}
			else {
				echo "</ul>\n<h3>" . $row['user_level'] . "</h3>\n<ul>\n";
			}
		}
		if ($row['user_level'] == "Suspended") {
			$row['user_name'] = "";
		}
		if ($row['user_level'] == "Declined") {
			$row['user_name'] = "";
		}
		if ($row['user_level'] == "New") {
			$row['user_name'] = "";
		}
		if ($row['user_name'] != "") {
			echo "<li><a href=\"users.php?viewuser=" . $row['user_id'] . "\">";
			$uid = array (
				$row['user_name'],
				$row['user_onwikiname'],
				$row['user_id']
			);
			if (in_array($uid, $regdevlist)) {
				echo "<b>" . $row['user_name'] . "</b>";
			} else {
				echo $row['user_name'];
			}
			echo "</a></li>\n";
		}
		$lastlevel = $row['user_level'];
	}
	echo "</ul>\n";
	echo "<br /><a href=\"users.php\">User list</a><br />\n";
	displayfooter();
}
?>