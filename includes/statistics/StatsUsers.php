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

class StatsUsers extends StatisticsPage
{
	function execute()
	{
		
		if(!isset($_GET['user']))
		{
			return $this->getUserList();
		}
		else
		{
			return $this->getUserDetail($_GET['user']);
		}
	}
	
	function getPageTitle()
	{
		return "Account Creation Tool users";
	}
	
	function getPageName()
	{
		return "Users";
	}
	
	function isProtected()
	{
		return false;
	}
	
	function getUserList()
	{
		global $tsSQL;
		$out = "";
		$result = $tsSQL->query("SELECT * FROM acc_user ORDER BY user_level, user_name;");
		if (!$result)
		{
			return "No users found.";
		}
		// Build list of users
		$lastlevel = NULL;
		while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
		{
			if ($row['user_level'] != $lastlevel && $row['user_level'] != "Suspended" && $row['user_level'] != "Declined" && $row['user_level'] != "New")
			{
				// If the user level has changed, we are on the next group of users (ie, if it was admin and is now user, then we must start a new ordered list for users
				if ($lastlevel == NULL)
				{
					// Initial list
					$out.= "\n<h3>" . $row['user_level'] . "</h3>\n<ul>\n"; // Build header, start unordered list
				}
				else
				{
					// Any additional lists
					$out.= "</ul>\n<h3>" . $row['user_level'] . "</h3>\n<ul>\n"; // End previous unordered list, build header, start new unordered list
				}
			}
		
			// We only want to list the user if they were approved and are not currently on suspension
			if ($row['user_level'] != "Suspended" && $row['user_level'] != "Declined" && $row['user_level'] != "New")
			{
				$out.= "<li><a href=\"?page=Users&amp;user=" . $row['user_id'] . "\">"; // Start list item, link to user page
				$uid = array ($row['user_name'], $row['user_onwikiname'], $row['user_id']); // Build an array of the user's name, onwiki name, and ID to compare with users in devlist
				// Write the users name
				$out.= $row['user_name'];
				$out.= "</a></li>\n"; // End the list item
			}
			$lastlevel = $row['user_level']; // Set lastlevel to the level of this user so we can see if we need to start a new list
		}
		$out.= "</ul>\n<br />\n";
		
		return $out;
	}
	
	function getUserDetail($userId)
	{
		$out="";
		global $tsSQL, $enableRenames, $tsurl, $session;
		$gid = $tsSQL->escape($userId); // Validate the user ID for security (SQL Injection, etc)
		if (!preg_match('/^[0-9]+$/i',$gid)) {
			return "User ID invalid";
		}
	
		$query = "SELECT * FROM acc_user WHERE user_id = ". $gid ;
		//if(!isset($_GET['showall'])) $query.=" AND user_level != 'Declined' AND user_level != 'New'";
		$query.=";"; 
		$result = $tsSQL->query($query); // Get information on the selected user; Must not show if the user has not been approved
		if (!$result)
		{
			// If query fails, kill script
			return "User not found.";
		}
		$row = mysql_fetch_assoc($result); // Return the result of the database query as an associative array
		$username = $row['user_name'];

		
		// Show basic user details: Name, wiki name, ID, rights, etc.
		$out.= "<h2>Detail report for user: " . $row['user_name'] . "</h2>\n";
		$out.= "<ul>\n";
		$out.= "<li>User ID: " . $row['user_id'] . "</li>\n";
		$out.= "<li>User Level: " . $row['user_level'] . "</li>\n";
		$out.= "<li>User On-wiki name: <a href=\"http://en.wikipedia.org/wiki/User:" . $row['user_onwikiname'] . "\">" . $row['user_onwikiname'] . "</a>  |  <a href=\"http://en.wikipedia.org/wiki/User talk:" . $row['user_onwikiname'] . "\">talk page</a> </li>\n";
		
		// Display the date and time of the user's last activity on the interface;
		// If the user has not used the interface, display message
		if ($row['user_lastactive'] == "0000-00-00 00:00:00")
		{
			$out.= "<li>User has never used the interface</li>\n";
		}
		else
		{
			$out.= "<li>User last active: " . $row['user_lastactive'] . "</li>\n";
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
		if($session->hasright($_SESSION['user'], 'User') || $session->hasright($_SESSION['user'], 'Admin'))
		{
			$out.= "<li>User has <a href=\"acc.php?action=welcomeperf\">automatic welcoming</a> enabled: " . $welcome . ".</li>\n";
		}
		else
		{
			$out.= "<li>User has <a href=\"acc.php?action=welcomeperf\" style=\"color: red;\" title=\"Login required to continue\">automatic welcoming</a> enabled: " . $welcome . ".</li>\n";
		}
		$out.= "</ul>\n<br/>";
		
		// If the user has admin privileges, build a string of links for the user (rename, edit, promote, suspend, etc.
		if($session->hasright($_SESSION['user'], 'Admin'))
		{
			$out.= "Tools:   ";
			if( $enableRenames == 1 )
			{
				// If renaming is on, add links to edit and rename the user
				$tools = "[ <a href=\"users.php?rename=" . $row['user_id'] . "\">Rename!</a> - <a href=\"users.php?edituser=" . $row['user_id'] . "\">Edit!</a> -";
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
					$tools .= " <a href=\"users.php?suspend=" . $row['user_id'] . "\">Suspend!</a> - <a href=\"users.php?promote=" . $row['user_id'] . "\">Promote!</a> ]";
					$out.= $tools;
					break;
					
				case "Admin":
					// Build suspend and demote links
					$tools .= " <a href=\"users.php?suspend=" . $row['user_id'] . "\">Suspend!</a> - <a href=\"users.php?demote=" . $row['user_id'] . "\">Demote!</a> ]";
					$out.= $tools;
					break;
					
				case "Suspended":
					// Build unsuspend link
					$tools .= " <a href=\"users.php?approve=" . $row['user_id'] . "\">Unsuspend!</a> ]";
					$out.= $tools;
					break;
					
				default:
					// No need to build links if the user has not yet been approved
					break;
			}
		}
		
		// List the requests this user has marked as 'created'
		$out.= "<h2>Users created</h2>\n";
		$query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = " . $gid . " AND log_action = 'Closed 1';";
		$result = $tsSQL->query($query); // Get all the requests this user has marked as 'created'
		if (!$result)
		{
			// If query fails, tell us about it
			$out.="<span style=\"color:red;font-weight:bold\">" . $tsSQL->getError() . "</span>";
		}
		else
		{
			// If the query returns at least one row
			if (mysql_num_rows($result) != 0)
			{
				$out.= "<ol>\n"; // Start an ordered list
				while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
				{
					if ($row['log_time'] == "0000-00-00 00:00:00")
					{
						// If the time was not set on insertion, we'll write "Date unknown" instead
						$row['log_time'] = "Date unknown";
					}
		
					// Display the name of the account that was created
					if($session->hasright($_SESSION['user'], 'User') || $session->hasright($_SESSION['user'], 'Admin')) 
					{
							$out.= "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">zoom</a>) at " . $row['log_time'] . "</li>\n";
					}
					else
					{
							$out.= "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\" style=\"color: red;\" title=\"Login required to view request\">zoom</a>) at " . $row['log_time'] . "</li>\n";
					}
				}
				$out.= "</ol>\n"; // End the ordered list
			}
		}
		// List the requests this user has *not* marked as 'created'
		$out.= "<h2>Users not created</h2>\n";
		$query = "SELECT * FROM acc_log JOIN acc_user ON user_name = log_user JOIN acc_pend ON pend_id = log_pend WHERE user_id = " . $gid . " AND log_action != 'Closed 1' AND log_action LIKE 'Closed %' AND log_action != 'Closed custom';";
		$result = $tsSQL->query($query); // Get all the requests this user has *not* marked as 'created'
		if (!$result)
		{
			// If query fails, tell us about it
			$out.="<span style=\"color:red;font-weight:bold\">" . $tsSQL->getError() . "</span>";
		}
		else
		{
			// If the query returns at least one row
			if (mysql_num_rows($result) != 0)
			{	
				$out.= "<ol>\n"; // Start an ordered list
				while ($row = mysql_fetch_assoc($result)) // Return the result of the database query as an associative array; then , for each row returned...
				{
					if ($row['log_time'] == "0000-00-00 00:00:00")
					{
						// If the time was not set on insertion, we'll write "Date unknown" instead
						$row['log_time'] = "Date unknown";
					}
		
					// Display the name of the account that was not created
					if($session->hasright($_SESSION['user'], 'User') || $session->hasright($_SESSION['user'], 'Admin'))
					{
							$out.= "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">zoom</a>) at " . $row['log_time'] . "</li>\n";
					}
					else
					{
							$out.= "<li> <a href=\"http://en.wikipedia.org/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"http://en.wikipedia.org/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"http://en.wikipedia.org/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$tsurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at " . $row['log_time'] . "</li>\n";
					}
				}
				$out.= "</ol>\n"; // End the ordered list
			}
		}

		// List actions that have been executed in relation to this account (approval, promotions, suspensions, etc)
		$out.= "<h2>Account log</h2>\n";
		$query = "SELECT * FROM acc_log where log_pend = '" . $gid . "' AND log_action RLIKE '(Approved|Suspended|Declined|Promoted|Demoted|Renamed|fchange)';";
		$result = $tsSQL->query($query); // Get log entries where the user is the subject (not the executor)
		if (!$result)
		{
			// If query fails, tell us about it
			$out.="<span style=\"color:red;font-weight:bold\">" . $tsSQL->getError() . "</span>";
		}
		else
		{
			// If the query returns at least one row
			if (mysql_num_rows($result) != 0)
			{	
				$out.= "<ol>\n"; // Start an ordered list
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
					$uid_result = $tsSQL->query($uid_query); // Get the details of the user who performed the action
					if (!$uid_result)
					{
						// If query fails, tell us about it
						$out.="<span style=\"color:red;font-weight:bold\">" . $tsSQL->getError() . "</span>";
						break;
					}
					$uid_r = mysql_fetch_assoc($uid_result);  // Return the result of the database query as an associative array
					
					// Build an appropriate summary, depending on the action of the log event
					switch ($row['log_action'])
					{
						case "Prefchange":
							// Another user changed this user's preferences
							$out.= "<li><a href=\"statistics.php?page=Users&amp;user=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> changed user preferences for " . $username . " at " . $row['log_time'] . "</li>\n";
							break;
						
						case "Renamed":
							// Another user renamed this user
							$out.= "<li><a href=\"statistics.php?page=Users&amp;user=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> <strong>" . $row['log_action'] . "</strong> " . $row['log_cmt'] . " at " . $row['log_time'] . ".</li>\n";	
							break;
						
						default:
							// Anything else			
							$out.= "<li><a href=\"statistics.php?page=Users&amp;user=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> <strong>" . $row['log_action'] . "</strong> at " . $row['log_time'] . $comments . "</li>\n";
							break;
					}
				}
				$out.= "</ol>\n"; // End the ordered list
				
			}
		}
		return $out;
	}
	
	function requiresWikiDatabase()
	{
		return false;
	}
}