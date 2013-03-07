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

class StatsTemplateStats extends StatisticsPage
{
	function execute()
	{
		$query = <<<QUERY
select template_id as "Template ID", template_usercode as "Template Code", count as "Active users using template", countall as "All users using template" from acc_template left join (select user_welcome_templateid, count(*) as count from acc_user where (user_level = "User" or user_level = "Admin") and user_welcome_templateid != 0 group by user_welcome_templateid) u on user_welcome_templateid = template_id left join (select user_welcome_templateid as allid, count(*) as countall from acc_user where user_welcome_templateid != 0 group by user_welcome_templateid) u2 on allid = template_id;
QUERY;
		global $tsurl;
		$qb = new QueryBrowser();
		$qb->rowFetchMode = MYSQL_NUM;
		$r = $qb->executeQueryToTable($query); 
		echo mysql_error();

		return $r;
	}
	function getPageName()
	{
		return "TemplateStats";
	}
	function getPageTitle()
	{
		return "Template Stats";
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

