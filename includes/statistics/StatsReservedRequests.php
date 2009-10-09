<?php
class StatsReservedRequests extends StatisticsPage
{
	function execute()
	{
		global $tsurl;
		$qb = new QueryBrowser();
		return $qb->executeQueryToTable('SELECT CONCAT("<a href=\"'.$tsurl.'/acc.php?action=zoom&id=", p.`pend_id`, "\">",p.`pend_id`,"</a>") AS "#", p.`pend_name` AS "Requested Name", p.`pend_status` AS "Status", u.`user_name` AS "Reserved by" FROM `acc_pend` p INNER JOIN `acc_user` u on u.`user_id` = p.`pend_reserved` WHERE `pend_reserved` != 0;'); 
	}
	function getPageName()
	{
		return "ReservedRequests";
	}
	function getPageTitle()
	{
		return "All currently reserved requests";
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