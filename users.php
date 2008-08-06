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

// Initialization
require_once ('config.inc.php');
require_once ('devlist.php');
require_once ('functions.php');

// Connect to MySQL server
$link = mysql_connect($toolserver_host, $toolserver_username, $toolserver_password);
if (!$link)
{
	// If connect fails, kill script
	die('Could not connect: ' . mysql_error());
}

// Select database
@ mysql_select_db($toolserver_database) or print mysql_error();

// Continue session
session_start();

if (!isset($_SESSION['user']) && !isset($_GET['nocheck']))
{
	// If the user is not logged in, display the external header
	displayHeader();
}
elseif (!isset($_GET['nocheck']))
{
	echo makehead($_SESSION['user']); // Build internal page header
	forceLogout(); // If the user needs to be logged out, log them out
	checksecurity($_SESSION['user']); // Determine what rights the current user has
	$out = showmessage('20');
	$out .= "<div id=\"content\">";
	echo $out; // Build site notice
}

/*
Viewing a specific user's details
*/
if (isset($_GET['viewuser']))
{
	$gid = sanitize($_GET['viewuser']); // Validate the user ID for security (SQL Injection, etc)
	$query = "SELECT * FROM acc_user WHERE user_id = ". $gid . " AND user_level != 'Declined' AND user_level != 'New';"; 
	$result = mysql_query($query); // Get information on the selected user; Must not show if the user has not been approved
	if (!$result)
	{
		// If query fails, kill script
		die("ERROR: No result returned.");
	}
	$row = mysql_fetch_assoc($result); // Return the result of the database query as an associative array
	if ($row['user_id'] == "")
	{
		// If the query returns an empty user_id, display error and kill the script
		//
		// Comment: Good god, this should never happen; why is this code here? - Nacimota
		echo "Invalid user!<br />\n";
		displayfooter();
		die();
	}
	
	// Show basic user details: Name, wiki name, ID, rights, etc.
	echo "<h2>Detail report for user: " . $row['user_name'] . "</h2>\n";
	echo "<ul>\n";
	echo "<li>User ID: " . $row['user_id'] . "</li>\n";
	echo "<li>User Level: " . $row['user_level'] . "</li>\n";
	echo "<li>User On-wiki name: <a href=\"http://en.wikipedia.org/wiki/User:" . $row['user_onwikiname'] . "\">" . $row['user_onwikiname'] . "</a>  |  <a href=\"http://en.wikipedia.org/wiki/User talk:" . $row['user_onwikiname'] . "\">talk page</a> </li>\n";
	
	// Display the date and time of the user's last activity on the interface;
	// If the user has not used the interface, display message
	if ($row['user_lastactive'] == "0000-00-00 00:00:00")
	{
		echo "<li>User has never used the interface</li>\n";
	}
	else
	{
		echo "<li>User last active: " . $row['user_lastactive'] . "</li>\n";
	}

	if ($row['user_welcome'] == "1")
	{
		$welcome = "Yes";
	}
	else 
	{
		$welcome = "No";
	}

	// State whether the user has auto welcoming enabled
	if(hasright($_SESSION['user'], 'User') || hasright($_SESSION['user'], 'Admin'))
	{
		echo "<li>User has <a href=\"acc.php?action=welcomeperf\">automatic welcoming</a> enabled: " . $welcome . ".</li>\n";
	}
	else
	{
		echo "<li>User has <a href=\"acc.php?action=welcomeperf\" style=\"color: red;\" title=\"Login required to continue\">automatic welcoming</a> enabled: " . $welcome . ".</li>\n";
	}
	echo "</ul>\n<br/>";
	
	// If the user has admin privileges, build a string of links for the user (rename, edit, promote, suspend, etc.
	if(hasright($_SESSION['user'], 'Admin'))
	{
		echo "Tools:";
		if( $enableRenames == 1 )
		{
			// If renaming is on, add links to edit and rename the user
			$tools = "[ <a href=\"acc.php?action=usermgmt&rename=" . $row['user_id'] . "\">Rename!</a> - <a href=\"acc.php?action=usermgmt&edituser=" . $row['user_id'] . "\">Edit!</a> -";
		}
		else
		{
			$tools = "[ ";
		}
		
		// Build appropriate links depending on the status of the selected user
		switch ($row['user_level'])
		{
			case "User":
				// Build suspend and promote links
				$tools .= " <a href=\"acc.php?action=usermgmt&suspend=" . $row['user_id'] . "\">Suspend!</a> - <a href=\"acc.php?action=usermgmt&promote=" . $row['user_id'] . "\">Promote!</a> ]";
				echo $tools;
				break;
				
			case "Admin":
				// Build suspend and demote links
				$tools .= " <a href=\"acc.php?action=usermgmt&suspend=" . $row['user_id'] . "\">Suspend!</a> - <a href=\"acc.php?action=usermgmt&demote=" . $row['user_id'] . "\">Demote!</a> ]";
				echo $tools;
				break;
				
			case "Suspended":
				// Build unsuspend link
				$tools .= " <a href=\"acc.php?action=usermgmt&approve=" . $row['user_id'] . "\">Unsuspend!</a> ]";
				echo $tools;
				break;
				
			default:
				// No need to build links if the user has not yet been approved
				break;
		}
	}
	
	// List the requests this user has marked as 'created'
	echo "<h2>Users created</h2>\n";
	$query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = " . $gid . " AND log_action = 'Closed 1';";
	$result = mysql_query($query); // Get all the requests this user has marked as 'created'
	if (!$result)
	{
		// If query fails, kill script
		die("ERROR: No result returned.");
	}
	
	// If the query returns at least one row
	if (mysql_num_rows($result) != 0)
	{
		echo "<ol>\n"; // Start an ordered list
		while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
		{
			if ($row['log_time'] == "0000-00-00 00:00:00")
			{
				// If the time was not set on insertion, we'll write "Date unknown" instead
				$row['log_time'] = "Date unknown";
			}

			// Display the name of the account that was created
			if(hasright($_SESSION['user'], 'User') || hasright($_SESSION['user'], 'Admin'))
			{
					echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&id=" . $row['pend_id'] . "\">zoom</a>) at " . $row['log_time'] . "</li>\n";
			}
			else
			{
					echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&id=" . $row['pend_id'] . "\" style=\"color: red;\" title=\"Login required to view request\">zoom</a>) at " . $row['log_time'] . "</li>\n";
			}
		}
		echo "</ol>\n"; // End the ordered list
	}
	
	// List the requests this user has *not* marked as 'created'
	echo "<h2>Users not created</h2>\n";
	$query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = " . $gid . " AND log_action != 'Closed 1';";
	$result = mysql_query($query); // Get all the requests this user has *not* marked as 'created'
	if (!$result)
	{
		// If query fails, kill script
		die("ERROR: No result returned.");
	}
	
	// If the query returns at least one row
	if (mysql_num_rows($result) != 0)
	{	
		echo "<ol>\n"; // Start an ordered list
		while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
		{
			if ($row['log_time'] == "0000-00-00 00:00:00")
			{
				// If the time was not set on insertion, we'll write "Date unknown" instead
				$row['log_time'] = "Date unknown";
			}

			// Display the name of the account that was not created
			if(hasright($_SESSION['user'], 'User') || hasright($_SESSION['user'], 'Admin'))
			{
					echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">zoom</a>) at " . $row['log_time'] . "</li>\n";
			}
			else
			{
					echo "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at " . $row['log_time'] . "</li>\n";
			}
		}
		echo "</ol>\n"; // End the ordered list
	}
	
	// List actions that have been executed in relation to this account (approval, promotions, suspensions, etc)
	echo "<h2>Account log</h2>\n";
	$query = "SELECT * FROM acc_log where log_pend = '" . $gid . "' AND log_action RLIKE '(Approved|Suspended|Declined|Promoted|Demoted|Renamed|fchange)';";
	$result = mysql_query($query); // Get log entries where the user is the subject (not the executor)
	if (!$result)
	{
		// If query fails, kill script
		die("ERROR: No result returned.");
	}
	
	// If the query returns at least one row
	if (mysql_num_rows($result) != 0)
	{	
		echo "<ol>\n"; // Start an ordered list
		while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
		{
			if ($row['log_time'] == "0000-00-00 00:00:00")
			{
				// If the time was not set on insertion, we'll write "Date unknown" instead
				$row['log_time'] = "Date unknown";
			}
			
			$comments = "";
			if ($row['log_cmt'] != "")
			{
				$comments = " (" . $row['log_cmt'] . ")";
			}
			
			$luser = mysql_real_escape_string($row['log_user']);  // Validate the user ID for security (SQL Injection, etc)
			$uid_query = "SELECT user_id FROM acc_user WHERE user_name = '" . $luser . "';";
			$uid_result = mysql_query($uid_query); // Get the details of the user who performed the action
			if (!$uid_result)
			{
				// If query fails, kill script
				die("ERROR: No result returned.");
			}
			$uid_r = mysql_fetch_assoc($uid_result);  // Return the result of the database query as an associative array
			
			// Build an appropriate summary, depending on the action of the log event
			switch ($row['log_action'])
			{
				case "Prefchange":
					// Another user changed this user's preferences
					echo "<li><a href=\"users.php?viewuser=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> changed user preferences for " . $gid . " at " . $row['log_time'] . "</li>\n";
					break;
				
				case "Renamed":
					// Another user renamed this user
					echo "<li><a href=\"users.php?viewuser=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> <strong>" . $row['log_action'] . "</strong> " . $row['log_cmt'] . " at " . $row['log_time'] . ".</li>\n";	
					break;
				
				default:
					// Anything else			
					echo "<li><a href=\"users.php?viewuser=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> <strong>" . $row['log_action'] . "</strong> " . $row['log_action'] . " at " . $row['log_time'] . $comments . "</li>\n";
					break;
			}
		}
		echo "</ol>\n"; // End the ordered list
	}
	displayfooter(); // Build the page footer
	die(); // We're done
}

/*
Viewing the entire user list
*/
else
{
	$query = "SELECT * FROM acc_user ORDER BY user_level";
	$result = mysql_query($query); // Get information on all tool users and order them by their access rights
	if (!$result)
	{
		// If query fails, kill script
		die("ERROR: No result returned.");
	}
	
	// Build list of users
	echo "<h2>User List</h2>\n";
	echo "<i>Developers are bolded</i>\n";
	$lastlevel = NULL;
	while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
	{
		if ($row['user_level'] != $lastlevel && $row['user_level'] != "Suspended" && $row['user_level'] != "Declined" && $row['user_level'] != "New")
		{
			// If the user level has changed, we are on the next group of users (ie, if it was admin and is now user, then we must start a new ordered list for users
			if ($lastlevel == NULL)
			{
				// Initial list
				echo "\n<h3>" . $row['user_level'] . "</h3>\n<ul>\n"; // Build header, start unordered list
			}
			else
			{
				// Any additional lists
				echo "</ul>\n<h3>" . $row['user_level'] . "</h3>\n<ul>\n"; // End previous unordered list, build header, start new unordered list
			}
		}
		
		// We only want to list the user if they were approved and are not currently on suspension
		if ($row['user_level'] != "Suspended" && $row['user_level'] != "Declined" && $row['user_level'] != "New")
		{
			echo "<li><a href=\"users.php?viewuser=" . $row['user_id'] . "\">"; // Start list item, link to user page
			$uid = array ($row['user_name'], $row['user_onwikiname'], $row['user_id']); // Build an array of the user's name, onwiki name, and ID to compare with users in devlist
			if (in_array($uid, $regdevlist))
			{
				// If the user is a developer, write their name in bold
				echo "<b>" . $row['user_name'] . "</b>";
			}
			else
			{
				// Write the users name
				echo $row['user_name'];
			}
			echo "</a></li>\n"; // End the list item
		}
		$lastlevel = $row['user_level']; // Set lastlevel to the level of this user so we can see if we need to start a new list
	}
	echo "</ul>\n<br />\n";
	displayfooter(); // Build page footer
}
?>