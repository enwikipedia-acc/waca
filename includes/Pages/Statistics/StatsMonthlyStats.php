<?php
namespace Waca\Pages\Statistics;

use QueryBrowser;
use Waca\StatisticsPage;

class StatsMonthlyStats extends StatisticsPage
{
	protected function executeStatisticsPage()
	{
		$qb = new QueryBrowser();

		$query = <<<SQL
SELECT
    COUNT(DISTINCT id) AS 'Requests Closed',
    YEAR(timestamp) AS 'Year',
    MONTHNAME(timestamp) AS 'Month'
FROM log
WHERE action LIKE 'Closed%'
GROUP BY EXTRACT(YEAR_MONTH FROM timestamp)
ORDER BY YEAR(timestamp) , MONTH(timestamp) ASC;
SQL;

		$out = $qb->executeQueryToTable($query);

		return $out;

		// TODO: would be nice to get the graphs back without horribly-managed dependencies
	}

	public function getPageName()
	{
		return "MonthlyStats";
	}

	public function getPageTitle()
	{
		return "Monthly Statistics";
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
