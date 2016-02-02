<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\PageBase;
use Waca\SecurityConfiguration;

class StatsIdUsers extends PageBase
{
	public function main()
	{
		$query = "select id, username, status, checkuser from user where identified = 1 order by username;";

		$database = gGetDb();
		$statement = $database->query($query);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		$this->assign('dataTable', $data);
		$this->assign('statsPageTitle','All identified users');
		$this->setTemplate('statistics/identified-users.tpl');
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
