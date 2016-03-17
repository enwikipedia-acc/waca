<?php
namespace Waca\Pages\Statistics;

use PDO;
use Waca\Security\SecurityConfiguration;
use Waca\Tasks\InternalPageBase;

class StatsIdUsers extends InternalPageBase
{
	public function main()
	{
		$this->setHtmlTitle('Identified Users :: Statistics');

		$query = "SELECT id, username, status, checkuser FROM user WHERE identified = 1 ORDER BY username;";

		$database = $this->getDatabase();
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
