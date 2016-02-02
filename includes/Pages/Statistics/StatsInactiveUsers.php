<?php
namespace Waca\Pages\Statistics;

use User;
use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\WebRequest;

class StatsInactiveUsers extends PageBase
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
		$this->assign('statsPageTitle', 'Inactive tool users');
	}

	public function getSecurityConfiguration()
	{
		return SecurityConfiguration::internalPage();
	}
}
