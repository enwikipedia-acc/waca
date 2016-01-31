<?php
namespace Waca\Pages\Statistics;

use PDO;
use QueryBrowser;
use Waca\StatisticsPage;

class StatsIdUsers extends StatisticsPage
{
	protected function executeStatisticsPage()
	{
		return $this->getUserList();
	}

	public function getPageTitle()
	{
		return "All identified users";
	}

	public function getPageName()
	{
		return "IdUsers";
	}

	public function isProtected()
	{
		return true;
	}

	private function getUserList()
	{
		$query = "select username, status, checkuser from user where identified = 1 order by username;";

		$qb = new QueryBrowser();
		$qb->rowFetchMode = PDO::FETCH_NUM;
		$qb->overrideTableTitles = array("User name", "Access level", "Checkuser?");
		$r = $qb->executeQueryToTable($query);

		return $r;
	}

	public function requiresWikiDatabase()
	{
		return false;
	}
}
