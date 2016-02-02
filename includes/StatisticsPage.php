<?php
namespace Waca;

use Exception;

abstract class StatisticsPage extends PageBase
{
	/**
	 * Method provides the content of the statistics page
	 * @return string content of stats page.
	 * @deprecated Please move onto using main() instead.
	 */
	protected function executeStatisticsPage()
	{
	}

	/**
	 * Returns the title of the page (initial header, and name in menu)
	 * @return string
	 */
	abstract public function getPageTitle();

	/**
	 * Does nothing but cause problems.
	 *
	 * @throws Exception
	 * @deprecated Use security conf
	 */
	public function isProtected()
	{
		throw new Exception('Neither legacy protection nor SecurityConfiguration has been defined.');
	}

	/**
	 * Main function for this page, when no specific actions are called.
	 *
	 * @todo Change to protected.
	 * @return void
	 */
	public function main()
	{
		$this->setTemplate('statistics/base.tpl');
		$pageContent = $this->executeStatisticsPage();
		$this->assign('statsPageTitle', $this->getPageTitle());
		$this->assign('legacyContent', $pageContent);
	}

	/**
	 * @return SecurityConfiguration
	 * @throws Exception
	 */
	public function getSecurityConfiguration()
	{
		if ($this->isProtected()) {
			return SecurityConfiguration::internalPage();
		}
		else {
			return SecurityConfiguration::publicPage();
		}
	}
}
