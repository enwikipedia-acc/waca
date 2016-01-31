<?php
namespace Waca\Pages\Statistics;

use QueryBrowser;
use Waca\StatisticsPage;

class StatsReservedRequests extends StatisticsPage
{
	protected function executeStatisticsPage()
	{
		global $baseurl;

		$query = <<<sql
SELECT
    CONCAT('<a href="', '$baseurl', '/acc.php?action=zoom&amp;id=', p.id, '">', p.id, '</a>') AS '#',
    p.name AS 'Requested Name',
    p.status AS 'Status',
    u.username AS 'Reserved by'
FROM request p
    INNER JOIN user u ON u.id = p.reserved
WHERE reserved != 0;
sql;

		$qb = new QueryBrowser();
		return $qb->executeQueryToTable($query);
	}

	public function getPageName()
	{
		return "ReservedRequests";
	}

	public function getPageTitle()
	{
		return "All currently reserved requests";
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
