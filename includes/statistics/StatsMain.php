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

class StatsMain extends StatisticsPage
{
	function execute()
	{
		$out = "<h2>Menu</h2><ul>";
		global $filepath, $usePathInfo;
		$files = scandir($filepath . "/includes/statistics/");

		$statsPageDefinitions = preg_grep("/php$/",$files);
		
		$urlFragment = $usePathInfo ? "/" : "?page=";
		
		foreach ($statsPageDefinitions as $i) {
			require_once $filepath . "/includes/statistics/" . $i;
			$expld =  explode('.',$i);
			$c = $expld[0];
			$o = new $c;
			if($o->hideFromMenu() == false)
			{
				$out.='<li><a href="'.$urlFragment.$o->getPageName().'">'.$o->getPageTitle().'</a></li>';
			}
		}
		$out.="</ul>";
		$out.=$this->smallStats();
		return $out;
	}
	
	function getPageTitle()
	{
		return "Account Creation Statistics";
	}
	
	function getPageName()
	{
		return "Main";
	}
	
	function isProtected()
	{
		return true;
	}
	function requiresWikiDatabase()
	{
		return false;
	}
	function hideFromMenu()
	{
		return true;
	}
	function smallStats()
	{
		global $tsSQL;
		$out= '<h2>Statistics</h2><table>';
		$openq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Open' AND pend_mailconfirm = 'Confirmed';";
		$result = $tsSQL->query($openq);
		if (!$result)
			Die("ERROR: No result returned.1");
		$open = mysql_fetch_assoc($result);
		$out.= "<tr><th>Open Requests</th><td>".$open['COUNT(*)']."</td></tr>";
		
		$adminq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Admin' AND pend_mailconfirm = 'Confirmed';";
		$result = $tsSQL->query($adminq);
		if (!$result)
			Die("ERROR: No result returned.2");
		$admin = mysql_fetch_assoc($result);
		$out.= "<tr><th>Requests needing an account creator</th><td>".$admin['COUNT(*)']."</td></tr>";
		
		$unconfirmedq = "SELECT COUNT(*) FROM acc_pend WHERE pend_mailconfirm != 'Confirmed' AND pend_mailconfirm != '';";
		$result = $tsSQL->query($unconfirmedq);
		if (!$result)
			Die("ERROR: No result returned.2");
		$unconfirmed = mysql_fetch_assoc($result);
		$out.= "<tr><th>Unconfirmed requests</th><td>".$unconfirmed['COUNT(*)']."</td></tr>";
		
		$sadminq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'Admin';";
		$result = $tsSQL->query($sadminq);
		if (!$result)
			Die("ERROR: No result returned.3");
		$sadmin = mysql_fetch_assoc($result);
		$out.="<tr><th>Tool administrators</th><td>".$sadmin['COUNT(*)']."</td></tr>";
		
		$suserq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'User';";
		$result = $tsSQL->query($suserq);
		if (!$result)
			Die("ERROR: No result returned.4");
		$suser = mysql_fetch_assoc($result);
		$out.="<tr><th>Tool users</th><td>".$suser['COUNT(*)']."</td></tr>";
		
		$ssuspq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'Suspended';";
		$result = $tsSQL->query($ssuspq);
		if (!$result)
			Die("ERROR: No result returned.5");
		$ssusp = mysql_fetch_assoc($result);
		$out.="<tr><th>Tool suspended users</th><td>".$ssusp['COUNT(*)']."</td></tr>";
		
		$snewq = "SELECT COUNT(*) FROM acc_user WHERE user_level = 'New';";
		$result = $tsSQL->query($snewq);
		if (!$result)
			Die("ERROR: No result returned.6");
		$snew = mysql_fetch_assoc($result);
		$out.="<tr><th>New tool users</th><td>".$snew['COUNT(*)']."</td></tr>";
		
		$mostComments = "select pend_id from acc_cmt group by pend_id order by count(*) desc limit 1;";
		$mostCommentsResult = $tsSQL->query($mostComments);
		if(!$mostCommentsResult) Die("ERROR: No result returned. (mc)");
		$mostCommentsRow = mysql_fetch_assoc($mostCommentsResult);
		$mostCommentsId = $mostCommentsRow['pend_id'];
		$out.="<tr><th>Request with most comments</th><td><a href=\"acc.php?action=zoom&id=".$mostCommentsId."\">".$mostCommentsId."</a></td></tr>";
	
		$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));
		
		//Process log for stats
		$logq = "select * from acc_log AS A
			JOIN acc_pend AS B ON log_pend = pend_id
			where log_time RLIKE '^$now.*' AND
			log_action RLIKE '^(Closed.*|Deferred.*|Blacklist.*)';";
		$result = $tsSQL->query($logq);
		if (!$result)
			Die("ERROR: No result returned.7");
		$dropped = 0;
		$created = 0;
		$toosimilar = 0;
		
		$WQquery = "SELECT COUNT(*) AS pending FROM acc_welcome WHERE welcome_status = \"Open\";";
		$WQresult = $tsSQL->query($WQquery);
		if(!$WQresult) Die("ERROR: No result returned. (WQ)");
		$WQrow = mysql_fetch_assoc($WQresult);
		$out.="<tr><th>Welcome queue length</th><td>". $WQrow['pending']."</td></tr>";
		
		$out.="</table>";
		return $out;
	}
}