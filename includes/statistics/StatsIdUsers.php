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

class StatsIdUsers extends StatisticsPage
{
	function execute()
	{
		return $this->getUserList();
	}
	
	function getPageTitle()
	{
		return "All identified users";
	}
	
	function getPageName()
	{
		return "IdUsers";
	}
	
	function isProtected()
	{
		return true;
	}
	
	function getUserList()
	{
		$query = "select username, status, checkuser from user where identified = 1 order by user_name;";
	
		global $baseurl;
		$qb = new QueryBrowser();
		$qb->rowFetchMode = PDO::FETCH_NUM;
        $qb->overrideTableTitles = array("User name", "Access level", "Checkuser?");    
		$r = $qb->executeQueryToTable($query);

		return $r;
	}
	
	function requiresWikiDatabase()
	{
		return false;
	}
}