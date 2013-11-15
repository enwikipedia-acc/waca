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

class StatsFlaggedUsers extends StatisticsPage
{
	function execute()
	{
		global $asSQL, $tsSQL;
		$query = 'select g.ug_user, n.user_name from user_groups g inner join user_ids n on g.ug_user=n.user_id where ug_group = "accountcreator";';
		$results = $asSQL->query($query);
		$out= "<table cellspacing=\"0\">";
		$out.= "<tr><th>en.wiki User ID</th><th>en.wiki Username</th><th /><th /><th /><th>Tool username</th><th>Tool access level</th></tr>";
		$currentreq = 0;
		while($row = mysql_fetch_assoc($results))
		{
			$query='SELECT user_id, user_name, user_level FROM `acc_user` WHERE user_onwikiname = "'.$row['user_name'].'" LIMIT 1;'; //  AND (`user_level` = "Admin" OR `user_level` = "User")
			$accresult = $tsSQL->query($query);
			if($accresult)
			{
				$accrow = mysql_fetch_assoc($accresult);
			} 
			else 
			{ 
				$accrow = array('user_name' => '--', 'user_id' => '--', 'user_level' => '--'); 
			}
			if( $accrow['user_id'] == '')
			{
				$accrow = array('user_name' => '--', 'user_id' => '--', 'user_level' => '--');
			}
			if( ($accrow['user_level'] != 'User') && ($accrow['user_level'] != 'Admin'))
			{
				$currentreq++;
				$out.= '<tr>';
				$out.="<td>".$row['ug_user']."</td>" . 
					"<td><a href=\"http://en.wikipedia.org/wiki/User:".$row['user_name']."\">".$row['user_name']."</a></td>" . 
					"<td><a href=\"http://en.wikipedia.org/wiki/User_talk:".$row['user_name']."\">talk</a></td>" . 
					"<td><a href=\"http://en.wikipedia.org/wiki/Special:Contributions/".$row['user_name']."\">contribs</a></td>" . 
					"<td><a href=\"http://en.wikipedia.org/wiki/Special:UserRights/".$row['user_name']."\">rights</a></td>" . 
					"<td>".$accrow['user_name']."</td>" . 
					"<td>".$accrow['user_level']."</td>" . 
					"</tr>";
			}	
	
		}
		$out.="</table>";
		
		return $out;
	}
	
	function getPageName()
	{
		return "FlaggedUsers";
	}
	function getPageTitle()
	{
		return "Account creators without tool access";
	}
	function isProtected()
	{
		return false;
	}
	
	function requiresWikiDatabase()
	{
		return true;
	}
}