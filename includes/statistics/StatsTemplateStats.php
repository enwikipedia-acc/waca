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
	protected function execute()
	{
		$query = <<<SQL
SELECT
    t.id as "Template ID",
    t.usercode as "Template Code",
    u.count as "Active users using template",
    countall as "All users using template"
FROM welcometemplate t
    LEFT JOIN
    (
        SELECT
            welcome_template,
            COUNT(*) as count
        FROM user
        WHERE
            (status = "User" OR status = "Admin")
            AND welcome_template IS NOT NULL
        GROUP BY welcome_template
    ) u ON u.welcome_template = t.id
    LEFT JOIN
    (
        SELECT
            welcome_template as allid,
            COUNT(*) as countall
        FROM user
        WHERE welcome_template IS NOT NULL
        GROUP BY welcome_template
    ) u2 ON u2.allid = t.id;
SQL;

		$qb = new QueryBrowser();
		$r = $qb->executeQueryToTable($query);

		return $r;
	}

	public function getPageName()
	{
		return "TemplateStats";
	}

	public function getPageTitle()
	{
		return "Template Stats";
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
