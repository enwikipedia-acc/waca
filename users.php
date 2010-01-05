<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/
// Get all the classes.
require_once 'config.inc.php';
require_once 'functions.php';
require_once 'includes/database.php';
require_once 'includes/messages.php';
require_once 'includes/skin.php';
require_once 'includes/accbotSend.php';
require_once 'includes/session.php';
require_once 'includes/offlineMessage.php';

// Check to see if the database is unavailable.
// Uses the false variable as its the internal interface.
$offlineMessage = new offlineMessage(false);
$offlineMessage->check();

// Initialize the database classes.
$tsSQL = new database("toolserver");
$asSQL = new database("antispoof");

// Creates database links for later use.
$tsSQLlink = $tsSQL->getLink();
$asSQLlink = $asSQL->getLink();

// Initialize the class objects.
$messages = new messages();
$accbotSend   = new accbotSend();
$skin     = new skin();
$session  = new session();

// Initialize the session data.
session_start();

// Display the header of the interface.
$skin->displayIheader($_SESSION['user']);

// A content block is created if the action is none of the above.
// This block would later be used to keep all the HTML except the header and footer.
$out = "<div id=\"content\">";
echo $out;

// Checks if the current user has admin rigths.
if(!$session->hasright($_SESSION['user'], 'Admin'))
{
	// Displays both the error message and the footer of the interface.
	$skin->displayRequestMsg("I'm sorry, but, this page is restricted to administrators only.<br />\n");	
	$skin->displayIfooter();
	die();
}

if (isset ($_GET['approve'])) {
	$aid = sanitize($_GET['approve']);
	$siuser = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_user WHERE user_id = '$aid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_level'] == "Admin") {
		echo "Sorry, the user you are trying to approve has Administrator access. Please use the <a href=\"users.php?demote=$aid\">demote function</a> instead.<br />\n";
		$skin->displayIfooter();
		die();
	}		
	if ($row['user_level'] == "User") {
		echo "Sorry, the user you are trying to approve has already been approved.<br />\n";
		$skin->displayIfooter();
		die();
	}
	$query = "UPDATE acc_user SET user_level = 'User' WHERE user_id = '$aid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$aid', '$siuser', 'Approved', '$now');";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "Changed User #" . $_GET['approve'] . " access to 'User'<br />\n";
	$uid = $aid;
	$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
	$result2 = mysql_query($query2, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$accbotSend->send("User $aid (" . $row2['user_name'] . ") approved by $siuser");
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($row2['user_email'], "ACC Account Approved", "Dear ".$row2['user_onwikiname'].",\nYour account ".$row2['user_name']." has been approved by $siuser. To login please go to $tsurl/acc.php.\n- The English Wikipedia Account Creation Team", $headers);
}
if (isset ($_GET['demote'])) {
	$did = sanitize($_GET['demote']);
	$siuser = sanitize($_SESSION['user']);
	if (!isset($_POST['demotereason'])) {
		echo "<h2>Demote Reason</h2><strong>The reason you enter here will be shown in the log. Please keep this in mind.</strong><br />\n<form action=\"users.php?demote=$did\" method=\"post\"><br />\n";
		echo "<textarea name=\"demotereason\" rows=\"20\" cols=\"60\">";
		if (isset($_GET['preload'])) {
			echo $_GET['preload'];
		}
		echo "</textarea><br />\n";
		echo "<input type=\"submit\"><input type=\"reset\"/><br />\n";
		echo "</form>";
		$skin->displayIfooter();
		die();
	} else {
		$demotersn = sanitize($_POST['demotereason']);
		$query = "UPDATE acc_user SET user_level = 'User' WHERE user_id = '$did';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Demoted', '$now', '$demotersn');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changed User #" . $_GET['demote'] . " access to 'User'<br />\n";
		$uid = $did;
		$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
		$result2 = mysql_query($query2, $tsSQLlink);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$accbotSend->send("User $did (" . $row2['user_name'] . ") demoted by $siuser because: \"" . $_POST['demotereason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($row2['user_email'], "ACC Account Demoted", "Dear ".$row2['user_onwikiname'].",\nYour account ".$row2['user_name']." has been demoted by $siuser because ".$_POST['demotereason'].". To contest this demotion please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		$skin->displayIfooter();
		die();
	}

}
if (isset ($_GET['suspend'])) {
	$did = sanitize($_GET['suspend']);
	$siuser = sanitize($_SESSION['user']);
	if (!isset($_POST['suspendreason'])) {
		echo "<h2>Suspend Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"users.php?suspend=$did\" method=\"post\"><br />\n";
		echo "<textarea name=\"suspendreason\" rows=\"20\" cols=\"60\">";
		if (isset($_GET['preload'])) {
			echo $_GET['preload'];
		}
		echo "</textarea><br />\n";
		echo "<input type=\"submit\" /><input type=\"reset\"/><br />\n";
		echo "</form>";
		$skin->displayIfooter();
		die();
	} else {
		$suspendrsn = sanitize($_POST['suspendreason']);
		$query = "UPDATE acc_user SET user_level = 'Suspended' WHERE user_id = '$did';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Suspended', '$now', '$suspendrsn');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changed User #" . $_GET['suspend'] . " access to 'Suspended'<br />\n";
		$uid = $did;
		$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
		$result2 = mysql_query($query2, $tsSQLlink);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$accbotSend->send("User $did (" . $row2['user_name'] . ") had tool access suspended by $siuser because: \"" . $_POST['suspendreason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($row2['user_email'], "ACC Account Suspended", "Dear ".$row2['user_onwikiname'].",\nYour account ".$row2['user_name']." has been suspended by $siuser because ".$_POST['suspendreason'].". To contest this suspension please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		$skin->displayIfooter();
		die();
	}

}
if (isset ($_GET['promote'])) {
	$aid = sanitize($_GET['promote']);
	$siuser = sanitize($_SESSION['user']);
	$query = "UPDATE acc_user SET user_level = 'Admin' WHERE user_id = '$aid';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$now = date("Y-m-d H-i-s");
	$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time) VALUES ('$aid', '$siuser', 'Promoted', '$now');";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	echo "Changed User #" . $_GET['promote'] . " access to 'Admin'<br />\n";
	$uid = $aid;
	$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
	$result2 = mysql_query($query2, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$accbotSend->send("User $aid (" . $row2['user_name'] . ") promoted to admin by $siuser");
	$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
	mail($row2['user_email'], "ACC Account Promoted", "Dear ".$row2['user_onwikiname'].",\nYour account ".$row2['user_name']." has been promted to admin status by $siuser.\n- The English Wikipedia Account Creation Team", $headers);
}
if (isset ($_GET['decline'])) {
	$did = sanitize($_GET['decline']);
	$siuser = sanitize($_SESSION['user']);
	$query = "SELECT * FROM acc_user WHERE user_id = '$did';";
	$result = mysql_query($query, $tsSQLlink);
	if (!$result)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row = mysql_fetch_assoc($result);
	if ($row['user_level'] != "New") {
		echo "You cannot decline this user because the user is not a New user.<br />\n";
		$skin->displayIfooter();
		die();
	}
	if (!isset($_POST['declinereason'])) {
		echo "<h2>Decline Reason</h2><strong>The user will be shown the reason you enter here. Please keep this in mind.</strong><br />\n<form action=\"users.php?decline=$did\" method=\"post\"><br />\n";
		echo "<textarea name=\"declinereason\" rows=\"20\" cols=\"60\">";
		if (isset($_GET['preload'])) {
			echo $_GET['preload'];
		}
		echo "</textarea><br />\n";
		echo "<input type=\"submit\"><input type=\"reset\"/><br />\n";
		echo "</form>";
		$skin->displayIfooter();
		die();
	} else {
		$declinersn = sanitize($_POST['declinereason']);
		$query = "UPDATE acc_user SET user_level = 'Declined' WHERE user_id = '$did';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$now = date("Y-m-d H-i-s");
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$did', '$siuser', 'Declined', '$now', '$declinersn');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changed User #" . $_GET['decline'] . " access to 'Declined'<br />\n";
		$uid = $did;
		$query2 = "SELECT * FROM acc_user WHERE user_id = '$uid';";
		$result2 = mysql_query($query2, $tsSQLlink);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$accbotSend->send("User $did (" . $row2['user_name'] . ") declined access by $siuser because: \"" . $_POST['declinereason'] . "\"");
		$headers = 'From: accounts-enwiki-l@lists.wikimedia.org';
		mail($row2['user_email'], "ACC Account Declined", "Dear ".$row2['user_onwikiname'].",\nYour account ".$row2['user_name']." has been declined access to the account creation tool by $siuser because ".$_POST['declinereason'].". For more infomation please email accounts-enwiki-l@lists.wikimedia.org.\n- The English Wikipedia Account Creation Team", $headers);
		$skin->displayIfooter();
		die();
	}
}
if ( isset ($_GET['rename']) && $enableRenames == 1 ) {
	$siuser = sanitize($_SESSION['user']);
	if (!isset($_POST['newname'])) {
		$result = mysql_query("SELECT user_name FROM acc_user WHERE user_id = '{$_GET['rename']}';");
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$oldname = mysql_fetch_assoc($result);
		echo "<form action=\"users.php?rename=" . $_GET['rename'] . "\" method=\"post\">";						
		echo "<div class=\"required\">";
		echo "<label for=\"oldname\">Old Username:</label>";
		echo "<input id=\"oldname\" type=\"text\" name=\"oldname\" readonly=\"readonly\" value=\"" . $oldname['user_name'] . "\"/>";
		echo "</div>";
		echo "<div class=\"required\">";
		echo "<label for=\"newname\">New Username:</label>";
		echo "<input id=\"newname\" type=\"text\" name=\"newname\"/>";
		echo "</div>";
		echo "<div class=\"submit\">";
		echo "<input type=\"submit\"/>";
		echo "</div>";
		echo "</form>";
		$skin->displayIfooter();
		die();
	} else {
		$oldname = mysql_real_escape_string($_POST['oldname']);
		$newname = mysql_real_escape_string($_POST['newname']);
		$userid = sanitize($_GET['rename']);
		$result = mysql_query("SELECT user_name FROM acc_user WHERE user_id = '$userid';");
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$checkname = mysql_fetch_assoc($result);
		if ($checkname['user_name'] != ($_POST['oldname']))
			Die("Rename form corrupted");
		if(mysql_num_rows(mysql_query("SELECT * FROM acc_user WHERE user_name = '$oldname';")) != 1 || mysql_num_rows(mysql_query("SELECT * FROM acc_user WHERE user_name = '$newname';")) != 0)
			die("Target username in use, or current user does not exist.");
		$query = "UPDATE acc_user SET user_name = '$newname' WHERE user_id = '$userid';";
		$result = mysql_query($query, $tsSQLlink);
		$tgtmessage = "User " . $_GET['rename'] . " (" . $oldname . ")";
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());						
		$query = "UPDATE acc_log SET log_pend = '$newname' WHERE log_pend = '$tgtmessage' AND log_action != 'Renamed';";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());				
				$query = "UPDATE acc_log SET log_user = '$newname' WHERE log_user = '$oldname'";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		$now = date("Y-m-d H-i-s");
		if ($siuser == $oldname)
		{
			$logentry = "themself to " . $newname;
		}
		else
		{
			$logentry = $oldname . " to " . $newname;
		}
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$userid', '$siuser', 'Renamed', '$now', '$logentry');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
		echo "Changed User " . htmlentities($_POST['oldname']) . " name to ". htmlentities($_POST['newname']) . "<br />\n";
		$query2 = "SELECT * FROM acc_user WHERE user_name = '$oldname';";
		$result2 = mysql_query($query2, $tsSQLlink);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		if ($siuser == $oldname)
		{
				$_SESSION['user'] = $newname;
				$accbotSend->send("User $siuser changed their username to " . $_POST['newname']);
		}
		else
		{
				$session->setForceLogout(stripslashes($userid));
				$accbotSend->send("User $siuser changed " . $_POST['oldname'] . "'s username to " . $_POST['newname']);
		}
		$skin->displayIfooter();
		die();
	}
}
if (isset ($_GET['edituser']) && $enableRenames == 1) {
	$sid = sanitize($_SESSION['user']);
	if (!isset($_POST['user_email']) || !isset($_POST['user_onwikiname'])) {
		$gid = sanitize($_GET['edituser']);
		$query = "SELECT * FROM acc_user WHERE user_id = $gid;";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("ERROR: No result returned.");
		$row = mysql_fetch_assoc($result);
		if (!isset($row['user_id'])) {
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
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());	
		$now = date("Y-m-d H-i-s");			
		$query = "INSERT INTO acc_log (log_pend, log_user, log_action, log_time, log_cmt) VALUES ('$gid', '$sid', 'Prefchange', '$now', '');";
		$result = mysql_query($query, $tsSQLlink);
		if (!$result)
			Die("Query failed: $query ERROR: " . mysql_error());
					$query2 = "SELECT * FROM acc_user WHERE user_id = '$gid';";
		$result2 = mysql_query($query2, $tsSQLlink);
		if (!$result2)
			Die("Query failed: $query ERROR: " . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		$accbotSend->send("$sid changed preferences for User $gid (" . $row2['user_name'] . ")");
		echo "Changes saved";
	}
	echo "<br /><br />";
	$skin->displayIfooter();
	die();
}
echo <<<HTML
<h1>User Management</h1>
<strong>This interface is NOT a toy. If it says you can do it, you can do it.<br />Please use this responsibly.</strong>
<h2>Open requests</h2>
HTML;


$query = "SELECT * FROM acc_user WHERE user_level = 'New';";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
if (mysql_num_rows($result) != 0){
	echo "<ol>\n";
	while ($row = mysql_fetch_assoc($result)) {
		$uname = $row['user_name'];
		$uoname = $row['user_onwikiname'];
		$userid = $row['user_id'];
		$out = "<li><small>[ <span class=\"request-ban\">$uname</span> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
		$out .= " <a class=\"request-req\" href=\"users.php?approve=$userid\">Approve!</a> - <a class=\"request-req\" href=\"users.php?decline=$userid\">Decline</a> - <a class=\"request-req\" href=\"http://toolserver.org/~interiot/cgi-bin/count_edits?dbname=enwiki_p&amp;user=$uoname\">Count!</a></small></li>";
		echo "$out\n";
	}
	echo "</ol>\n";
}
echo <<<HTML
<div id="usermgmt-users">
<h2>Users</h2>
HTML;


$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Approved') WHERE user_level = 'User' GROUP BY log_pend ORDER BY log_pend DESC;";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];

	$out = "<li><small>[ <a class=\"request-ban\" href=\"statistics.php?page=Users&amp;user=$userid\">$uname</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"users.php?suspend=$userid\">Suspend!</a> - <a class=\"request-req\" href=\"users.php?promote=$userid\">Promote!</a> (Approved by $row[log_user])</small></li>";
	echo "$out\n";
}
echo <<<HTML
</ol>
</div>
<div id="usermgmt-admins">
<h2>Admins</h2>
HTML;


$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Promoted') WHERE user_level = 'Admin' GROUP BY log_pend ORDER BY log_time ASC;";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Suspended';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$suspended = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Promoted';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$promoted = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Approved';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$approved = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Demoted';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$demoted = $row2['COUNT(*)'];

	$query = "SELECT COUNT(*) FROM acc_log WHERE log_user = '$uname' AND log_action = 'Declined';";
	$result2 = mysql_query($query, $tsSQLlink);
	if (!$result2)
		Die("Query failed: $query ERROR: " . mysql_error());
	$row2 = mysql_fetch_assoc($result2);
	$declined = $row2['COUNT(*)'];

	$out = "<li><small>[ <a class=\"request-ban\" href=\"statistics.php?page=Users&amp;user=$userid\">$uname</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"users.php?suspend=$userid\">Suspend!</a> - <a class=\"request-req\" href=\"users.php?demote=$userid\">Demote!</a> (Promoted by $row[log_user] <span style=\"color:purple;\">[P:$promoted|S:$suspended|A:$approved|Dm:$demoted|D:$declined]</span>)</small></li>";
	echo "$out\n";
}
echo <<<HTML
</ol>
</div>
<h2>Suspended accounts</h2>
<div class="showhide" id="showhide-suspended-link" onclick="showhide('showhide-suspended');">[show]</div>
<div id="showhide-suspended" style="display: none;">
HTML;


//$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id) WHERE user_level = 'Suspended' AND  log_action = 'Suspended' AND log_id = ANY ( SELECT MAX(log_id) FROM acc_log WHERE log_action = 'Suspended' GROUP BY log_pend ) ORDER BY log_id DESC;";
$query = "SELECT * FROM acc_user JOIN (SELECT * FROM (SELECT * FROM acc_log WHERE log_action = 'Suspended' ORDER BY log_id DESC) AS l GROUP BY log_pend) AS log ON acc_user.user_id = log.log_pend WHERE user_level = 'Suspended';SELECT * FROM acc_user JOIN (SELECT * FROM (SELECT * FROM acc_log WHERE log_action = 'Suspended' ORDER BY log_id DESC) AS l GROUP BY log_pend) AS log ON acc_user.user_id = log.log_pend WHERE user_level = 'Suspended';";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$out = "<li><small>[ <a class=\"request-ban\" href=\"statistics.php?page=Users&amp;user=$userid\">$uname</a> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"users.php?approve=$userid\">Unsuspend!</a> (Suspended by " . $row['log_user'] . " because \"" . $row['log_cmt'] . "\")</small></li>";
	echo "$out\n";
}
echo <<<HTML
</ol>
</div>
<h2>Declined accounts</h2>
<div class="showhide" id="showhide-declined-link" onclick="showhide('showhide-declined');">[show]</div>
<div id="showhide-declined" style="display: none;">
HTML;


$query = "SELECT * FROM acc_user JOIN acc_log ON (log_pend = user_id AND log_action = 'Declined') WHERE user_level = 'Declined' GROUP BY log_pend ORDER BY log_id DESC;";
$result = mysql_query($query, $tsSQLlink);
if (!$result)
	Die("Query failed: $query ERROR: " . mysql_error());
echo "<ol>\n";
while ($row = mysql_fetch_assoc($result)) {
	$uname = $row['user_name'];
	$uoname = $row['user_onwikiname'];
	$userid = $row['user_id'];
	$out = "<li><small>[ <span class=\"request-ban\">$uname</span> / <a class=\"request-src\" href=\"http://en.wikipedia.org/wiki/User:$uoname\">$uoname</a> ]";
	if( $enableRenames == 1 ) {
		$out .= " <a class=\"request-req\" href=\"users.php?rename=$userid\">Rename!</a> -";
		$out .= " <a class=\"request-req\" href=\"users.php?edituser=$userid\">Edit!</a> -";
	}
	$out .= " <a class=\"request-req\" href=\"users.php?approve=$userid\">Approve!</a> (Declined by " . $row['log_user'] . " because \"" . $row['log_cmt'] . "\")</small></li>";
	echo "$out\n";
}
echo "</ol>\n</div><br clear=\"all\" />";

$skin->displayIfooter();
die();
