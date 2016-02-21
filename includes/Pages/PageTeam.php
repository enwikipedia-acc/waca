<?php

namespace Waca\Pages;

use Waca\SecurityConfiguration;
use Waca\Tasks\InternalPageBase;

class PageTeam extends InternalPageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 * @return void
	 */
	protected function main()
	{
		$path = $this->getSiteConfiguration()->getFilePath() . 'team.json';
		$json = file_get_contents($path);

		$teamData = json_decode($json, true);

		$active = array();
		$inactive = array();

		foreach ($teamData as $name => $item) {
			if (count($item['Role']) == 0) {
				$inactive[$name] = $item;
			}
			else {
				$active[$name] = $item;
			}
		}

		$this->assign('developer', $active);
		$this->assign('inactiveDeveloper', $inactive);
		$this->setTemplate('team/team.tpl');
	}

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return SecurityConfiguration
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		return SecurityConfiguration::publicPage();
	}
}