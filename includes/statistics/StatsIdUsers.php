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
		$query = "select user_name, user_level, user_checkuser from acc_user where user_identified = 1 order by user_name;";
	
		global $tsurl;
		$qb = new QueryBrowser();
		$qb->rowFetchMode = MYSQL_NUM;
        $qb->overrideTableTitles = array("User name", "Access level", "Checkuser?");    
		$r = $qb->executeQueryToTable($query); 
		echo mysql_error();

		return $r;
	}
	
	function requiresWikiDatabase()
	{
		return false;
	}
}