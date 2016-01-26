<?php
namespace Waca\Pages;

use Waca\Exceptions\EnvironmentException;
use Waca\PageBase;
use Waca\SecurityConfiguration;

class PageMain extends PageBase
{
	/**
	 * Main function for this page, when no actions are called.
	 */
	protected function main()
	{
		$this->redirectUrl($this->getSiteConfiguration()->getBaseUrl() . '/acc.php');
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
		// TODO: Implement getSecurityConfiguration() method.
		return SecurityConfiguration::internalPage();
	}
}