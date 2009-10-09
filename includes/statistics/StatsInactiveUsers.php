<?php
class StatsInactiveUsers extends StatisticsPage
{
	function execute()
	{
		global $tsSQL;
		
		if( isset( $_SESSION['user'] ) ) {
			$sessionuser = $_SESSION['user'];
		} else {
			$sessionuser = "";
		}
		
		$date = new DateTime();
		$date->modify("-45 days");


		$query = "SELECT `user_id` as 'tooluserid', `user_name` as 'tooluser', `user_level` AS 'toolaccesslevel', CONCAT('User:', `user_onwikiname`) AS 'enwikiuser', `user_lastactive` as 'lasttoollogon'
		FROM `acc_user` 
		WHERE 
		     user_lastactive < '".$date->format("Y-m-d H:i:s")."' 
		 and user_level != 'Suspended'
		 and user_level != 'Declined'
		 and user_level != 'New'
		ORDER BY user_lastactive ASC
		;
		";

		$result = $tsSQL->query($query);
		if (!$result)
			Die("ERROR: No result returned.");
		$out= 'This list contains the usernames of all accounts that have not logged in in the past 45 days.';

		$out.= "<table><tr><th>User ID</th><th>Tool Username</th><th>User access level</th><th>enwiki username</th><th>Last activity</th><th>Approval</th>";
		if(hasright($sessionuser, "Admin")) {
			$out.= "<th>Suspend</th>";
		}
		$out.= "</tr>";
		$currentrow = 0;
		while ($r = mysql_fetch_assoc($result)) {
		
			$tooluser = $r['tooluser'];
			global $regdevlist;

			
			if(! array_search_recursive( $tooluser, $regdevlist) && $r['tooluserid'] != 6 ) // hack by st - hide JR from list (converted from livehack)
			{
				$userid = $r['tooluserid'];
				$q2 = 'select log_time from acc_log where log_pend = '.$userid.' and log_action = "Approved" order by log_id desc limit 1;';
				$res2 = $tsSQL->query($q2);
				if (!$res2)
					die("ERROR: No result returned.");
				$row2 = mysql_fetch_assoc($res2);
				$approved = $row2['log_time'];
		
				$appr_array = date_parse($approved);
				$appr_ts = mktime($appr_array['hour'], $appr_array['minute'], $appr_array['second'], $appr_array['month'], $appr_array['day'], $appr_array['year'] );
		
				if( $appr_ts < mktime($date->format("H"), $date->format("i"), $date->format("s"), $date->format("m"), $date->format("d"), $date->format("Y") )) {
					$currentrow +=1;
					$out.= "<tr";		
					if ($currentrow % 2 == 0) {
						$out.= ' class="alternate">';
					} else {
						$out.= ' >';
					}	
					$out.= "<th>$userid</th><td>$tooluser</td><td>".$r['toolaccesslevel']."</td><td>".$r['enwikiuser']."</td><td>".$r['lasttoollogon']."</td><td>".$approved."</td>";
					if(hasright($sessionuser, "Admin")) {
						$inactivesuspend = "Inactive for 45 or more days. Please contact a tool admin if you wish to come back.";
						$out.= "<td><a class=\"request-req\" href=\"acc.php?action=usermgmt&amp;suspend=$userid&amp;preload=$inactivesuspend\">Suspend!</a></td>";
					}
					$out.= "</tr>";
				}
			}
		}
		$out.= "</table>";
		return $out;
	}
	function getPageName()
	{
		return "InactiveUsers";
	}
	function getPageTitle()
	{
		return "Inactive tool users";
	}
	function isProtected()
	{
		return true;
	}
	
	function requiresWikiDatabase()
	{
		return false;
	}
}