<?php

namespace Waca\Pages;

use Waca\PageBase;
use Waca\SecurityConfiguration;
use Waca\Session;

class PageLogout extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		Session::destroy();
		$this->redirect("login");
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