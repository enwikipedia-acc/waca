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
	protected function execute()
	{
		$query = <<<sql
SELECT '0' AS 'Version', 'Active' AS 'Type', COUNT(*) AS 'Count' FROM user WHERE password NOT LIKE ':%' AND (status = 'User' OR status = 'Admin')
UNION
SELECT '0', 'Inactive', COUNT(*) FROM user WHERE password NOT LIKE ':%' AND NOT (status = 'User' OR status = 'Admin')
UNION
SELECT SUBSTRING(password FROM 2 FOR 1), 'Active', COUNT(*) FROM user WHERE password LIKE ':%' AND (status = 'User' OR status = 'Admin') GROUP BY SUBSTRING(password FROM 2 FOR 1)
UNION
SELECT SUBSTRING(password FROM 2 FOR 1), 'Inactive', COUNT(*) FROM user WHERE password LIKE ':%' AND NOT (status = 'User' OR status = 'Admin') GROUP BY SUBSTRING(password FROM 2 FOR 1)
ORDER BY `Version` ASC, 'Type' ASC
;
sql;

		$qb = new QueryBrowser();
		$qb->rowFetchMode = PDO::FETCH_NUM;
		$r = $qb->executeQueryToTable($query);

		return $r;
	}

	public function getPageTitle()
	{
		return "Password conversion status";
	}

	public function getPageName()
	{
		return "PasswordConversion";
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
