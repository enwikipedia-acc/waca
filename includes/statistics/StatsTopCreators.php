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
	protected function execute()
	{
        global $smarty;
        
		$qb = new QueryBrowser();
		$qb->numberedList = true;
		$qb->numberedListTitle = "Postition";
		
		$qb->tableCallbackFunction="statsTopCreatorsRowCallback";
		$qb->overrideTableTitles = array("# Created", "Username");
		
		// Retrieve all-time stats
		$top5aout = $qb->executeQueryToTable(<<<SQL
            SELECT 
                COUNT(*), 
                u.`id` user_id, 
                `log_user`, 
                u.`status` user_level 
            FROM `acc_log` l 
                INNER JOIN `user` u ON u.`username` = l.`log_user` 
                LEFT JOIN `emailtemplate` e 
                    ON concat('Closed ', e.`id`) = l.`log_action` 
            WHERE (e.`oncreated` = "1" OR `log_action` = "Closed custom-y") 
            GROUP BY `log_user`, u.`id` 
            ORDER BY COUNT(*) DESC;
SQL
        );
		
        // Retrieve all-time stats for active users only
        $top5activeout = $qb->executeQueryToTable(<<<SQL
            SELECT 
                COUNT(*), 
                u.`id` user_id, 
                `log_user`, 
                u.`status` user_level 
            FROM `acc_log` l 
                INNER JOIN `user` u 
                    ON u.`username` = l.`log_user` 
                LEFT JOIN `emailtemplate` e 
                    ON concat('Closed ', e.`id`) = l.`log_action` 
            WHERE (e.`oncreated` = "1" OR `log_action` = "Closed custom-y") 
                AND u.`status` != "Suspended" 
            GROUP BY `log_user`, u.`id` 
            ORDER BY COUNT(*) DESC;
SQL
        );
        
		// Retrieve today's stats (so far)
		$now = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")));
		$top5out = $qb->executeQueryToTable(<<<SQL
            SELECT 
                COUNT(*), 
                u.`id` user_id, 
                `log_user`, 
                u.`status` user_level
            FROM `acc_log` l 
                INNER JOIN `user` u 
                    ON u.`username` = l.`log_user` 
                LEFT JOIN `emailtemplate` e 
                    ON concat('Closed ', e.`id`) = l.`log_action` 
            WHERE (e.`oncreated` = "1" OR `log_action` = "Closed custom-y") 
                AND `log_time` LIKE "{$now}%" 
            GROUP BY `log_user`, u.`id` 
            ORDER BY COUNT(*) DESC;
SQL
	    );	
        
		// Retrieve Yesterday's stats
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1));
		$top5yout = $qb->executeQueryToTable(<<<SQL
            SELECT 
                COUNT(*), 
                u.`id` user_id,
                `log_user`, 
                u.`status` user_level
            FROM `acc_log` l 
                INNER JOIN `user` u 
                    ON u.`username` = l.`log_user` 
                LEFT JOIN `emailtemplate` e 
                    ON concat('Closed ', e.`id`) = l.`log_action` 
            WHERE (e.`oncreated` = "1" OR `log_action` = "Closed custom-y") 
                AND `log_time` LIKE "{$yesterday}%" 
            GROUP BY `log_user`, u.`id` 
            ORDER BY COUNT(*) DESC;
SQL
        );
		
		// Retrieve last 7 days
		$lastweek = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7));
		$top5wout = $qb->executeQueryToTable(<<<SQL
            SELECT 
                COUNT(*), 
                u.`id` user_id,
                `log_user`, 
                u.`status` user_level
            FROM `acc_log` l 
                INNER JOIN `user` u 
                    ON u.`username` = l.`log_user` 
                LEFT JOIN `emailtemplate` e 
                    ON concat('Closed ', e.`id`) = l.`log_action` 
            WHERE (e.`oncreated` = "1" OR `log_action` = "Closed custom-y") 
                AND `log_time` > "{$lastweek}%" 
            GROUP BY `log_user`, u.`id` 
            ORDER BY COUNT(*) DESC;
SQL
        );
		 
		// Retrieve last month's stats
		$lastmonth = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 28));
		$top5mout = $qb->executeQueryToTable(<<<SQL
            SELECT 
                COUNT(*), 
                u.`id` user_id,
                `log_user`, 
                u.`status` user_level
            FROM `acc_log` l 
                INNER JOIN `user` u 
                    ON u.`username` = l.`log_user` 
                LEFT JOIN `emailtemplate` e 
                    ON concat('Closed ', e.`id`) = l.`log_action` 
            WHERE (e.`oncreated` = "1" OR `log_action` = "Closed custom-y") 
                AND `log_time` > "{$lastmonth}%" 
            GROUP BY `log_user`, u.`id` 
            ORDER BY COUNT(*) DESC;
SQL
        );

        // Put it all together
	    $smarty->assign("top5aout", $top5aout);
	    $smarty->assign("top5activeout", $top5activeout);
	    $smarty->assign("top5out", $top5out);
	    $smarty->assign("top5yout", $top5yout);
	    $smarty->assign("top5wout", $top5wout);
	    $smarty->assign("top5mout", $top5mout);
        
		return $smarty->fetch("statistics/topcreators.tpl");
	}

	public function getPageTitle()
	{
		return "Top Account Creators";
	}
	
	public function getPageName()
	{
		return "TopCreators";
	}
    
	public function isProtected()
	{
		return true;
	}

	public function requiresWikiDatabase()
	{
		return false;
	}
}

function statsTopCreatorsRowCallback($row, $rowno) 
{   	
	$out = "<tr";
    if($row['log_user'] == User::getCurrent()->getUsername())
    {
        $out .= ' class="info"';   
    }
    
	$out .= '>';
	
	$out .= '<td>'.$rowno.'</td>';
	$out .= '<td>'.$row['COUNT(*)'].'</td>';
	
	global $baseurl;
	$out .= '<td><a ';
	
	
	if($row['user_level'] == "Suspended") $out .= 'class="muted" '; 
	if($row['user_level'] == "Admin") $out .= 'class="text-success" ';	
	
	$out .= 'href="'.$baseurl.'/statistics.php?page=Users&amp;user='.$row['user_id'].'">'.$row['log_user'].'</a></td>';
	
	$out .= '</tr>';
	
	return $out;
}
