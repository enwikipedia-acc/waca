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

class StatsReservedRequests extends StatisticsPage
{
	function execute()
	{
		global $baseurl;
		$qb = new QueryBrowser();
		return $qb->executeQueryToTable('SELECT CONCAT("<a href=\"'.$baseurl.'/acc.php?action=zoom&amp;id=", p.`pend_id`, "\">",p.`pend_id`,"</a>") AS "#", p.`pend_name` AS "Requested Name", p.`pend_status` AS "Status", u.`user_name` AS "Reserved by" FROM `acc_pend` p INNER JOIN `acc_user` u on u.`user_id` = p.`pend_reserved` WHERE `pend_reserved` != 0;');
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
