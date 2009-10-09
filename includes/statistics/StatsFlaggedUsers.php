<?php
class StatsFlaggedUsers extends StatisticsPage
{
	function execute()
	{
		global $asSQL;
		$query = 'select g.ug_user, n.user_name from user_groups g inner join user_ids n on g.ug_user=n.user_id where ug_group = "accountcreator";';
		$results = $asSQL->query($query);
		$out= "<table cellspacing=\"0\">";
		$out.= "<tr><th>en.wiki User ID</th><th>en.wiki Username</th><th /><th /><th /></tr>";
		$currentreq = 0;
		while($row = mysql_fetch_assoc($results))
		{
			$query='SELECT user_id, user_name, user_level FROM `acc_user` WHERE user_onwikiname = "'.$row['user_name'].'" AND (`user_level` = "Admin" OR `user_level` = "User") LIMIT 1;';
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
			if( ($accrow['user_name'] == '--') ||  ($row['user_name']=='--'))
			{
				$currentreq++;
				$out.= '<tr';
				if ($currentreq % 2 == 0) 
				{
					$out.=' class="alternate">';
				}
				else 
				{
					$out.='>';
				}
				$out.="<td>".$row['ug_user']."</td><td><a href=\"http://en.wikipedia.org/wiki/User:".$row['user_name']."\">".$row['user_name']."</a></td><td><a href=\"http://en.wikipedia.org/wiki/User_talk:".$row['user_name']."\">talk</a></td><td><a href=\"http://en.wikipedia.org/wiki/Special:Contributions/".$row['user_name']."\">contribs</a></td><td><a href=\"http://en.wikipedia.org/wiki/Special:UserRights/".$row['user_name']."\">rights</a></td>"; //<td>".$accrow['user_id']."</td><td>".$accrow['user_name']."</td><td>".$accrow['user_level']."</td></tr>";
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
		return "Users on Wikipedia with +accountcreator, without a tool account";
	}
	function isProtected()
	{
		return true;
	}
	
	function requiresWikiDatabase()
	{
		return true;
	}
}