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

class StatsMain extends StatisticsPage
{
	function execute()
	{
        global $smarty, $filepath;

		$files = scandir( $filepath . "/includes/statistics/" );

		$statsPageDefinitions = preg_grep("/php$/",$files);
		
        $statsPages = array();
        
		foreach ($statsPageDefinitions as $i) 
        {
			require_once($filepath . "/includes/statistics/" . $i);
			$expld = explode('.', $i);
			$className = $expld[0];
			$statsPageObject = new $className;
            
			if($statsPageObject->hideFromMenu() == false)
			{
			    $statsPages[] = $statsPageObject;
			}
		}
        
		$smallStats = $this->smallStats();
		$rrdToolGraphs = $this->rrdtoolGraphs();
        
        $smarty->assign("statsPages", $statsPages);
        $smarty->assign("smallStats", $smallStats);
        $smarty->assign("rrdToolGraphs", $rrdToolGraphs);
       
		return $smarty->fetch("statistics/main.tpl");
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
    function requiresSimpleHtmlEnvironment()
    {
        return false;   
    }
	function hideFromMenu()
	{
		return true;
	}
	function smallStats()
	{
		global $tsSQL, $baseurl;
		$out= '<h4>Statistics</h4><table class="table table-striped table-condensed">';
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
		
		$checkuserq = "SELECT COUNT(*) FROM acc_pend WHERE pend_status = 'Checkuser' AND pend_mailconfirm = 'Confirmed';";
		$result = $tsSQL->query($checkuserq);
		if (!$result)
			Die("ERROR: No result returned.2");
		$checkuser = mysql_fetch_assoc($result);
		$out.= "<tr><th>Requests needing a checkuser</th><td>".$checkuser['COUNT(*)']."</td></tr>";
		
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
		
		$mostComments = "select request from comment group by request order by count(*) desc limit 1;";
		$mostCommentsResult = $tsSQL->query($mostComments);
		if(!$mostCommentsResult) Die("ERROR: No result returned. (mc)");
		$mostCommentsRow = mysql_fetch_assoc($mostCommentsResult);
		$mostCommentsId = $mostCommentsRow['request'];
		$out.="<tr><th>Request with most comments</th><td><a href=\"$baseurl/acc.php?action=zoom&amp;id=".$mostCommentsId."\">".$mostCommentsId."</a></td></tr>";
	
		$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));
		
		//Process log for stats
		$logq = "select * from acc_log AS A
			JOIN acc_pend AS B ON log_pend = pend_id
			where log_time RLIKE '^$now.*' AND
			log_action RLIKE '^(Closed.*|Deferred.*|Blacklist.*)';";
		$result = $tsSQL->query($logq);
		if (!$result)
			Die("ERROR: No result returned.7");
        
		$WQquery = "SELECT COUNT(*) AS pending FROM acc_welcome WHERE welcome_status = \"Open\";";
		$WQresult = $tsSQL->query($WQquery);
		if(!$WQresult) Die("ERROR: No result returned. (WQ)");
		$WQrow = mysql_fetch_assoc($WQresult);
		$out.="<tr><th>Welcome queue length</th><td>". $WQrow['pending']."</td></tr>";
		
		$out.="</table>";
		return $out;
	}

	function rrdtoolGraphs()
	{
		$out = "<h4>Graphs (<a href=\"http://acc.stwalkerster.info/acc-new/\">see more!</a>)</h4>";
		$pathbase = "http://acc.stwalkerster.info/acc-new/";
		$pathsuffix = "/acc.svg";
		$time = array("day", "2day", "4day", "week", "2week", "month", "3month");
		
		foreach($time as $t){
			$out.= "<img src=\"$pathbase$t$pathsuffix\" alt=\"graph\"/><br />";
		}
		
		return $out;
	}
}