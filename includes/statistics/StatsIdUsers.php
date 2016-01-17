<?php
/**************************************************************************
**********      English Wikipedia Account Request Interface      **********
***************************************************************************
** Wikipedia Account Request Graphic Design by Charles Melbye,           **
** which is licensed under a Creative Commons                            **
** Attribution-Noncommercial-Share Alike 3.0 United States License.      **
**                                                                       **
** All other code are released under the Public Domain                   **
** by the ACC Development Team.                                          **
**                                                                       **
** See CREDITS for the list of developers.                               **
***************************************************************************/

class StatsIdUsers extends StatisticsPage
{
	protected function execute()
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
