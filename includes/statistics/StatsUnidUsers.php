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

class StatsUnidUsers extends StatisticsPage
{
	function execute()
	{
		

			return $this->getUserList();
		
	}
	
	function getPageTitle()
	{
		return "Unidentified users";
	}
	
	function getPageName()
	{
		return "UnidUsers";
	}
	
	function isProtected()
	{
		return false;
	}
	
	function getUserList()
	{
		global $tsSQL;
		$out = "";
		$result = $tsSQL->query("SELECT * FROM acc_user WHERE user_identified=0 ORDER BY user_level, user_name;");
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
	
	function requiresWikiDatabase()
	{
		return false;
	}
}