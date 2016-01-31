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

class StatsMain extends StatisticsPage
{
	protected function execute()
	{
		global $smarty, $filepath;

		$files = scandir($filepath . "/includes/statistics/");

		$statsPageDefinitions = preg_grep("/php$/", $files);

		$statsPages = array();

		foreach ($statsPageDefinitions as $i) {
			// TODO: is this require still needed? AutoLoader ftw.
			require_once($filepath . "/includes/statistics/" . $i);
			$expld = explode('.', $i);
			$className = $expld[0];

			/** @var StatisticsPage $statsPageObject */
			$statsPageObject = new $className;

			if ($statsPageObject->hideFromMenu() === false) {
				$statsPages[] = $statsPageObject;
			}
		}

		$this->smallStats();

		$smarty->assign("statsPages", $statsPages);

		$graphList = array("day", "2day", "4day", "week", "2week", "month", "3month");
		$smarty->assign("graphList", $graphList);

		return $smarty->fetch("statistics/main.tpl");
	}

	public function getPageTitle()
	{
		return "Account Creation Statistics";
	}

	public function getPageName()
	{
		return "Main";
	}

	public function isProtected()
	{
		return true;
	}

	public function requiresWikiDatabase()
	{
		return false;
	}

	public function requiresSimpleHtmlEnvironment()
	{
		return false;
	}

	public function hideFromMenu()
	{
		return true;
	}

	/**
	 * Gets the relevant statistics from the database for the small statistics table
	 */
	private function smallStats()
	{
		global $smarty;

		$database = gGetDb();
		$requestsQuery = "SELECT COUNT(*) FROM request WHERE status = :status AND emailconfirm = 'Confirmed';";

		$requestsStatement = $database->prepare($requestsQuery);

		// TODO: use the request states thing here.

		// Open Requests
		$requestsStatement->execute(array(":status" => "Open"));
		$open = $requestsStatement->fetchColumn();
		$requestsStatement->closeCursor();
		$smarty->assign("statsOpen", $open);

		// Admin Requests
		$requestsStatement->execute(array(":status" => "Flagged users"));
		$admin = $requestsStatement->fetchColumn();
		$requestsStatement->closeCursor();
		$smarty->assign("statsAdmin", $admin);

		// Checkuser Requests
		$requestsStatement->execute(array(":status" => "Checkuser"));
		$checkuser = $requestsStatement->fetchColumn();
		$requestsStatement->closeCursor();
		$smarty->assign("statsCheckuser", $checkuser);

		// Unconfirmed requests
		$unconfirmedStatement = $database->query("SELECT COUNT(*) FROM request WHERE emailconfirm != 'Confirmed' AND emailconfirm != '';");
		$unconfirmed = $unconfirmedStatement->fetchColumn();
		$unconfirmedStatement->closeCursor();
		$smarty->assign("statsUnconfirmed", $unconfirmed);

		$userStatusStatement = $database->prepare("SELECT COUNT(*) FROM user WHERE status = :status;");

		// Admin users
		$userStatusStatement->execute(array(":status" => "Admin"));
		$adminusers = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$smarty->assign("statsAdminUsers", $adminusers);

		// Users
		$userStatusStatement->execute(array(":status" => "User"));
		$users = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$smarty->assign("statsUsers", $users);

		// Suspended users
		$userStatusStatement->execute(array(":status" => "Suspended"));
		$suspendedUsers = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$smarty->assign("statsSuspendedUsers", $suspendedUsers);

		// New users
		$userStatusStatement->execute(array(":status" => "New"));
		$newUsers = $userStatusStatement->fetchColumn();
		$userStatusStatement->closeCursor();
		$smarty->assign("statsNewUsers", $newUsers);

		// Most comments on a request
		$mostCommentsStatement = $database->query("SELECT request FROM comment GROUP BY request ORDER BY COUNT(*) DESC LIMIT 1;");
		$mostComments = $mostCommentsStatement->fetchColumn();
		$mostCommentsStatement->closeCursor();
		$smarty->assign("mostComments", $mostComments);
	}
}
