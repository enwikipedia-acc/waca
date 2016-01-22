<?php

namespace Waca\Pages;

use Waca\PageBase;
use Waca\SecurityConfiguration;

/**
 * Class PageLogin
 * @package Waca\Pages
 */
class PageLogin extends PageBase
{
	/**
	 * Main function for this page, when no specific actions are called.
	 */
	protected function main()
	{
		// TODO: Implement main() method.
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
		// Login pages, by definition, have to be accessible to the public
		return SecurityConfiguration::publicPage();
	}
}