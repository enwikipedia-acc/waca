<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\PageBase;
use Waca\SecurityConfiguration;

class StatsReservedRequests extends PageBase
{
	public function main()
	{
		$query = <<<sql
SELECT
    p.id as requestid,
    p.name AS name,
    p.status AS status,
    u.username AS user,
    u.id as userid
FROM request p
    INNER JOIN user u ON u.id = p.reserved
WHERE reserved != 0;
sql;

		$database = gGetDb();
		$statement = $database->query($query);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		$this->assign('dataTable', $data);
		$this->assign('statsPageTitle','All currently reserved requests');
		$this->setTemplate('statistics/reserved-requests.tpl');
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
