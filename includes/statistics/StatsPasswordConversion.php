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

class StatsPasswordConversion extends StatisticsPage
{
	function execute()
	{
		$query = <<<sql
SELECT "0" AS "Version", "Active" AS "Type", COUNT(*) AS "Count" FROM acc_user WHERE user_pass NOT LIKE ":%" AND (user_level = "User" OR user_level = "Admin")
UNION
SELECT "0", "Inactive", COUNT(*) FROM acc_user WHERE user_pass NOT LIKE ":%" AND NOT (user_level = "User" OR user_level = "Admin")
UNION
SELECT SUBSTRING(user_pass FROM 2 FOR 1), "Active", COUNT(*) FROM acc_user WHERE user_pass LIKE ":%" AND (user_level = "User" OR user_level = "Admin") GROUP BY "Version"
UNION
SELECT SUBSTRING(user_pass FROM 2 FOR 1), "Inactive", COUNT(*) FROM acc_user WHERE user_pass LIKE ":%" AND NOT (user_level = "User" OR user_level = "Admin") GROUP BY "Version"
ORDER BY "Version" ASC, "Type" ASC
;
sql;
        
		global $tsurl;
		$qb = new QueryBrowser();
		$qb->rowFetchMode = MYSQL_NUM;
		$r = $qb->executeQueryToTable($query); 
		echo mysql_error();

		return $r;
	}
	
	function getPageTitle()
	{
		return "Password conversion status";
	}
	
	function getPageName()
	{
		return "PasswordConversion";
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