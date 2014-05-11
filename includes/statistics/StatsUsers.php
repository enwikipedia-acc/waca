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
if (!defined("ACC")) {
	die();
} // Invalid entry point

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
        $lists = array(
            "Admin" => User::getAllWithStatus("Admin", gGetDb()),
            "User" => User::getAllWithStatus("User", gGetDb()),
            "CheckUsers" => User::getAllCheckusers(gGetDb())
        );
        
        global $smarty;
        $smarty->assign("lists", $lists);
		return $smarty->fetch("statistics/users.tpl");
	}
	
	function getUserDetail($userId)
	{
		$out="";
		global $tsSQL, $asSQL, $enableRenames, $baseurl, $session, $wikiurl, $dontUseWikiDb;
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
		$siuser = sanitize($username);
		
		// Show basic user details: Name, wiki name, ID, rights, etc.
		$out.= "<h2>Detail report for user: " . $row['user_name'] . "</h2>\n";
		$out.= "<ul>\n";
		$out.= "<li>User ID: " . $row['user_id'] . "</li>\n";
		$out.= "<li>User Level: " . $row['user_level'] . "</li>\n";
		$out.= "<li>User On-wiki name: <a href=\"http://$wikiurl/wiki/User:" . $row['user_onwikiname'] . "\">" . $row['user_onwikiname'] . "</a>  |  <a href=\"http://$wikiurl/wiki/User talk:" . $row['user_onwikiname'] . "\">talk page</a> </li>\n";
		if($row['user_confirmationdiff']!=0){
		$out .= "<li><a href=\"http://$wikiurl/w/index.php?diff=".$row['user_confirmationdiff']."\">Confirmation diff</a></li>";
		}
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
	
		if ($row['user_welcome_templateid'] == "0")
		{
			$welcome = "No";
		}
		else 
		{
			$welcome = "Yes";
		}
	
		// State whether the user has auto welcoming enabled
		if($session->hasright($_SESSION['user'], 'User') || $session->hasright($_SESSION['user'], 'Admin'))
		{
			$out.= "<li>User has <a href=\"$baseurl/acc.php?action=templatemgmt\">automatic welcoming</a> enabled: " . $welcome . ".</li>\n";
		}
		else
		{
			$out.= "<li>User has <a href=\"$baseurl/acc.php?action=templatemgmt\" style=\"color: red;\" title=\"Login required to continue\">automatic welcoming</a> enabled: " . $welcome . ".</li>\n";
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
					$tools .= " <a href=\"$baseurl/users.php?suspend=" . $row['user_id'] . "\">Suspend!</a> - <a href=\"users.php?promote=" . $row['user_id'] . "\">Promote!</a> ]";
					$out.= $tools;
					break;
					
				case "Admin":
					// Build suspend and demote links
					$tools .= " <a href=\"$baseurl/users.php?suspend=" . $row['user_id'] . "\">Suspend!</a> - <a href=\"users.php?demote=" . $row['user_id'] . "\">Demote!</a> ]";
					$out.= $tools;
					break;
					
				case "Suspended":
					// Build unsuspend link
					$tools .= " <a href=\"$baseurl/users.php?approve=" . $row['user_id'] . "\">Unsuspend!</a> ]";
					$out.= $tools;
					break;
					
				default:
					// No need to build links if the user has not yet been approved
					break;
			}
		}
		
		$out.="<h2>Summary of user activity:</h2>";
		
		$qb = new QueryBrowser();
		$out .= $qb->executeQueryToTable('SELECT mail_desc AS "Close type", COUNT(*) AS Count FROM acc_log l INNER JOIN closes ON `closes` = l.log_action WHERE l.log_user = "'.$siuser.'" AND l.log_action LIKE "Closed%" GROUP BY l.log_action;');
		
		
		// List the requests this user has marked as 'created'
		$out.= "<h2>Users created</h2>\n";
		$query = "SELECT log_time, pend_name, pend_id FROM acc_log JOIN acc_pend ON pend_id = log_pend LEFT JOIN emailtemplate ON concat('Closed ', id) = log_action WHERE log_user = '" . $siuser . "' AND log_action LIKE 'Closed %' AND (oncreated = '1' OR log_action = 'Closed custom-y') ORDER BY log_time;";
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

					$contrib_css_class = "";
					if (!$dontUseWikiDb) {
						// Check if the user has contribs.  If not, their contribs link will be red.
						$pendname = sanitize($row['pend_name']);
						$contrib_query = "SELECT `user_editcount` from `user` where `user_name`='" . $pendname . "' LIMIT 1;";
						$contrib_result = $asSQL->query($contrib_query);
						if($result) {
							$contrib_count = mysql_fetch_assoc($contrib_result);
							if ((!isset($contrib_count['user_editcount'])) || $contrib_count['user_editcount']=='0') 
								$contrib_css_class = "class=\"nocontribs\"";
						}
					}
					
					$contrib_link="<a href=\"https://" . $wikiurl . "/wiki/Special:Contributions/" . $row['pend_name'] . "\" $contrib_css_class>contribs</a>"; 
					
					// Display the name of the account that was created
					if($session->hasright($_SESSION['user'], 'User') || $session->hasright($_SESSION['user'], 'Admin')) 
					{
							$out.= "<li> <a href=\"https://" . $wikiurl . "/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"https://" . $wikiurl . "/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - $contrib_link - <a href=\"$baseurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">zoom</a>) at " . $row['log_time'] . "</li>\n";
					}
					else
					{
							$out.= "<li> <a href=\"https://" . $wikiurl . "/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"https://" . $wikiurl . "/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - $contrib_link - <a href=\"$baseurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\" style=\"color: red;\" title=\"Login required to view request\">zoom</a>) at " . $row['log_time'] . "</li>\n";
					}
				}
				
				$out.= "</ol>\n"; // End the ordered list
			}
		}
		// List the requests this user has *not* marked as 'created'
		$out.= "<h2>Users not created</h2>\n";
		$query = "SELECT log_time, pend_name, pend_id FROM acc_log JOIN acc_pend ON pend_id = log_pend LEFT JOIN emailtemplate ON concat('Closed ', id) = log_action WHERE log_user = '" . $siuser . "' AND log_action LIKE 'Closed %' AND (oncreated = '0' OR log_action = 'Closed custom-n' OR log_action='Closed 0') ORDER BY log_time";
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
							$out.= "<li> <a href=\"https://" . $wikiurl . "/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"https://" . $wikiurl . "/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"https://" . $wikiurl . "/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$baseurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\">zoom</a>) at " . $row['log_time'] . "</li>\n";
					}
					else
					{
							$out.= "<li> <a href=\"https://" . $wikiurl . "/wiki/User:" . $row['pend_name'] . "\">" . $row['pend_name'] . "</a> (<a href=\"https://" . $wikiurl . "/wiki/User_talk:" . $row['pend_name'] . "\">talk</a> - <a href=\"https://" . $wikiurl . "/wiki/Special:Contributions/" . $row['pend_name'] . "\">contribs</a> - <a href=\"$baseurl/acc.php?action=zoom&amp;id=" . $row['pend_id'] . "\"><span style = \"color: red;\" title=\"Login required to view request\">zoom</span></a>) at " . $row['log_time'] . "</li>\n";
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
							$out.= "<li><a href=\"$baseurl/statistics.php?page=Users&amp;user=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> changed user preferences for " . $username . " at " . $row['log_time'] . "</li>\n";
							break;
						
						case "Renamed":
							// Another user renamed this user
                            $data = unserialize($row['log_cmt']);
							$out.= "<li><a href=\"$baseurl/statistics.php?page=Users&amp;user=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> <strong>" . $row['log_action'] . "</strong> " . $data['old'] . " to " . $data['new'] . " at " . $row['log_time'] . ".</li>\n";
							break;
						
						default:
							// Anything else			
							$out.= "<li><a href=\"$baseurl/statistics.php?page=Users&amp;user=" . $uid_r['user_id'] . "\">" . $row['log_user'] . "</a> <strong>" . $row['log_action'] . "</strong> at " . $row['log_time'] . $comments . "</li>\n";
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
