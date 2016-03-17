<?php

namespace Waca\Pages;

use Waca\Security\SecurityConfiguration;
use Waca\Tasks\InternalPageBase;

class Page404 extends InternalPageBase
{
	/**
	 * Main function for this page, when no actions are called.
	 */
	protected function main()
	{
		header("HTTP/1.1 404 Not Found");

		$this->setTemplate("404.tpl");
	}

	/**
	 * Sets up the security for this page. If certain actions have different permissions, this should be reflected in
	 * the return value from this function.
	 *
	 * If this page even supports actions, you will need to check the route
	 *
	 * @return \Waca\Security\SecurityConfiguration
	 * @category Security-Critical
	 */
	protected function getSecurityConfiguration()
	{
		// public because 404s will never contain private data.
		return SecurityConfiguration::publicPage();
	}
}