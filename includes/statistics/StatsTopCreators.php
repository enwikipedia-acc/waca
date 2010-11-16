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

class StatsTopCreators extends StatisticsPage
{
	function execute()
	{
		$qb = new QueryBrowser();
		$qb->numberedList = true;
		$qb->numberedListTitle = "Postition";
		
		$qb->tableCallbackFunction="statsTopCreatorsRowCallback";
		$qb->overrideTableTitles = array("# Created", "Username");
		
		/*
		 * Retrieve all-time stats
		 */
		
		$top5aout = $qb->executeQueryToTable('SELECT COUNT(*), `user_id`, `log_user`, u.`user_level` FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE (`log_action` = "Closed 1" OR `log_action` = "Closed custom-y") GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');
		
		/*
		 * Retrieve today's stats (so far)
		 */
		
		$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")));
		$top5out = $qb->executeQueryToTable('SELECT COUNT(*), `user_id`, `log_user`, u.`user_level` FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE (`log_action` = "Closed 1" OR `log_action` = "Closed custom-y") AND `log_time` LIKE "'.$now.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');
		
		/*
		 * Retrieve Yesterday's stats
		 */
		
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));
		$top5yout = $qb->executeQueryToTable('SELECT COUNT(*), `user_id`, `log_user`, u.`user_level` FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE (`log_action` = "Closed 1" OR `log_action` = "Closed custom-y") AND `log_time` LIKE "'.$yesterday.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');
		
		/*
		 *  Retrieve last 7 days
		 */
		
		$lastweek = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7));
		$top5wout = $qb->executeQueryToTable('SELECT COUNT(*), `user_id`, `log_user`, u.`user_level` FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE (`log_action` = "Closed 1" OR `log_action` = "Closed custom-y") AND `log_time` > "'.$lastweek.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');
		 
		
		/*
		 * Retrieve last month's stats
		 */
		
		$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 28));
		$top5mout = $qb->executeQueryToTable('SELECT COUNT(*), `user_id`, `log_user`, u.`user_level` FROM `acc_log` l INNER JOIN `acc_user` u ON u.`user_name` = l.`log_user` WHERE (`log_action` = "Closed 1" OR `log_action` = "Closed custom-y") AND `log_time` > "'.$lastmonth.'%" GROUP BY `log_user`, `user_id` ORDER BY COUNT(*) DESC;');

		$out = "<h2>Contents</h2><ul><li><a href=\"#today\">Today's creators</a></li><li><a href=\"#yesterday\">Yesterday's creators</a></li><li><a href=\"#lastweek\">Last 7 days</a></li><li><a href=\"#lastmonth\">Last 28 days</a></li></ul>";
		$out.= '<p><a href="#">Username</a> means an active account.<br /><a class="topcreators-suspended" href="#">Username</a> means a suspended account.<br /><a class="topcreators-admin" href="#">Username</a> means a tool admin account.</p>';
		$out.= "<h2>All-time top creators</h2>";
		$out.= $top5aout;
		$out.= '<a name="today"></a><h2>Today\'s creators</h2>';
		$out.= $top5out;
		$out.= '<a name="yesterday"></a><h2>Yesterday\'s creators</h2>';
		$out.= $top5yout;
		$out.= '<a name="lastweek"></a><h2>Last 7 days</h2>';
		$out.= $top5wout;
		$out.= '<a name="lastmonth"></a><h2>Last 28 days</h2>';
		$out.= $top5mout;
		
		return $out;
	}

	function getPageTitle()
	{
		return "Top Account Creators";
	}
	
	function getPageName()
	{
		return "TopCreators";
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
function statsTopCreatorsRowCallback($row, $rowno) 
{   $out = "";
	
	// $out = '<!-- vardump: $row:' . print_r($row,true) . "-->";

	$out .= "<tr";
	if($rowno % 2 == 0)
	{
		$out .= ' class="alternate"';	
	}
	$out .= '>';
	
	$out .= '<th>'.$rowno.'</th>';
	$out .= '<td>'.$row['COUNT(*)'].'</td>';
	
	global $tsurl;
	$out .= '<td><a ';
	
	
	if($row['user_level'] == "Suspended") $out .= 'class="topcreators-suspended" '; 
	if($row['user_level'] == "Admin") $out .= 'class="topcreators-admin" ';	
	
	$out .= 'href="'.$tsurl.'/statistics.php?page=Users&amp;user='.$row['user_id'].'">'.$row['log_user'].'</a></td>';
	
	$out .= '</tr>';
	
	return $out;
}
