<?php
namespace Waca\Pages\Statistics;

use User;
use Waca\SecurityConfiguration;
use Waca\StatisticsPage;
use Waca\WebRequest;

class StatsInactiveUsers extends StatisticsPage
{
	public function main()
	{
		$showImmune = false;
		if (WebRequest::getBoolean('showimmune')) {
			$showImmune = true;
		}

		$this->assign('showImmune', $showImmune);
		$inactiveUsers = User::getAllInactive(gGetDb());
		$this->assign('inactiveUsers', $inactiveUsers);

		$this->setTemplate('statistics/inactive-users.tpl');
		$this->assign('statsPageTitle', $this->getPageTitle());
	}

	public function getPageTitle()
	{
		return "Inactive tool users";
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
