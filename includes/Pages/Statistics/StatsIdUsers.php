<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\PageBase;
use Waca\SecurityConfiguration;

class StatsIdUsers extends PageBase
{
	public function main()
	{
		$query = "SELECT id, username, status, checkuser FROM user WHERE identified = 1 ORDER BY username;";

		$database = gGetDb();
		$statement = $database->query($query);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		$this->assign('dataTable', $data);
		$this->assign('statsPageTitle', 'All identified users');
		$this->setTemplate('statistics/identified-users.tpl');
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
