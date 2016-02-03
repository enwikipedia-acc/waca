<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\PageBase;
use Waca\SecurityConfiguration;

class StatsMonthlyStats extends PageBase
{
	public function main()
	{
		$query = <<<SQL
SELECT
    COUNT(DISTINCT id) AS closed,
    YEAR(timestamp) AS year,
    MONTHNAME(timestamp) AS month
FROM log /* StatsMonthlyStats */
WHERE action LIKE 'Closed%'
GROUP BY EXTRACT(YEAR_MONTH FROM timestamp)
ORDER BY YEAR(timestamp) , MONTH(timestamp) ASC;
SQL;

		$database = gGetDb();
		$statement = $database->query($query);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);

		$this->assign('dataTable', $data);
		$this->assign('statsPageTitle', 'Monthly Statistics');
		$this->setTemplate('statistics/monthly-stats.tpl');
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
