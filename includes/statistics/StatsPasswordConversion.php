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
SELECT "0" AS "Version", "Active" AS "Type", COUNT(*) AS "Count" FROM user WHERE password NOT LIKE ":%" AND (status = "User" OR status = "Admin")
UNION
SELECT "0", "Inactive", COUNT(*) FROM user WHERE password NOT LIKE ":%" AND NOT (status = "User" OR status = "Admin")
UNION
SELECT SUBSTRING(password FROM 2 FOR 1), "Active", COUNT(*) FROM user WHERE password LIKE ":%" AND (status = "User" OR status = "Admin") GROUP BY SUBSTRING(password FROM 2 FOR 1)
UNION
SELECT SUBSTRING(password FROM 2 FOR 1), "Inactive", COUNT(*) FROM user WHERE password LIKE ":%" AND NOT (status = "User" OR status = "Admin") GROUP BY SUBSTRING(password FROM 2 FOR 1)
ORDER BY `Version` ASC, "Type" ASC
;
sql;
        
		global $baseurl;
		$qb = new QueryBrowser();
		$qb->rowFetchMode = PDO::FETCH_NUM;
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
